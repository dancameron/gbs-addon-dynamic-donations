<?php

class GB_Charities extends Group_Buying_Controller {
	const POST_TYPE = 'gb_charities';
	const REWRITE_SLUG = 'charities';
	const REPORT_SLUG = 'charity';
	const META_KEY = 'gb_purchase_charity';

	public static function init() {
		parent::init();

		// admin
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ), 10, 0 );
			add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 10, 2 );
		}

		add_filter( 'template_include', array( get_class(), 'override_template' ) );
	}

	public static function save_charity( $checkout ) {
		if ( $checkout->cache['gb_charity'] && $checkout->cache['purchase_id'] ) {
			$purchase = Group_Buying_Purchase::get_instance( $checkout->cache['purchase_id'] );
			self::set_purchase_charity( $purchase, $checkout->cache['gb_charity'] );
		}
	}

	public function set_purchase_charity( Group_Buying_Purchase $purchase, $charity_id ) {
		$purchase->save_post_meta( array(
				self::META_KEY => $charity_id,
			) );
	}

	public function get_purchase_charity_id( Group_Buying_Purchase $purchase ) {
		$charity = self::get_purchase_charity( $charity );
		if ( is_a( $charity, 'GB_Charity' ) ) {
			return $charity->get_id();
		}
		return 0;
	}

	public function get_purchase_charity( Group_Buying_Purchase $purchase ) {
		$charity_id = $purchase->get_post_meta( self::META_KEY );
		if ( $charity_id ) {
			$charity = GB_Charity::get_instance( $charity_id );
			if ( is_a( $charity, 'GB_Charity' ) ) {
				return $charity;
			}
		}
		return 0;
	}


	public static function add_meta_box() {
		add_meta_box( 'gb_charity_reports', gb__( 'Reports' ), array( __CLASS__, 'show_meta_box' ), self::POST_TYPE, 'advanced', 'high' );
		add_meta_box( 'gb_charity_payment_settings', gb__( 'Payment Information' ), array( __CLASS__, 'show_meta_box' ), self::POST_TYPE, 'advanced', 'high' );
	}

	public static function show_meta_box( $post, $metabox ) {
		$charity = GB_Charity::get_instance( $post->ID );
		switch ( $metabox['id'] ) {
		case 'gb_charity_reports':
			self::show_meta_box_reports( $charity, $post, $metabox );
			break;
		case 'gb_charity_payment_settings':
			self::show_meta_box_gb_charity_payments( $charity, $post, $metabox );
			break;
		default:
			self::unknown_meta_box( $metabox['id'] );
			break;
		}
	}

	public function show_meta_box_reports( GB_Charity $charity, $post, $metabox ) {
		echo '<a href="'.gb_get_charity_purchases_report_url( $charity->get_id() ).'" class="button" target="_blank">'.gb__('Purchase Report').'</a>';
	}

	/**
	 * Display the deal details meta box
	 *
	 * @static
	 * @param Group_Buying_Deal $charity
	 * @param int     $post
	 * @param array   $metabox
	 * @return void
	 */
	private static function show_meta_box_gb_charity_payments( GB_Charity $charity, $post, $metabox ) {
		$username = $charity->get_username();
		$password = $charity->get_password();
		$payment_notes = $charity->get_payment_notes();
		$percentage = ( $charity->get_percentage() ) ? $charity->get_percentage() : 15 ;
		?>
			<p>
				<label for="gb_payment_notes"><?php gb_e( 'Payment Notes' ); ?></label><br />
				<textarea name="gb_payment_notes" id="gb_payment_notes" class="large-text"><?php echo $payment_notes; ?></textarea>
			</p>
			<p>
				<label for="gb_charity_username"><?php gb_e( 'BluePay Username' ); ?></label><br />
				<input type="text" name="gb_charity_username" id="gb_charity_username" value="<?php echo esc_attr( $username ); ?>" class="large-text" />
			</p>
			<p>
				<label for="gb_charity_password"><?php gb_e( 'BluePay Password' ); ?></label><br />
				<input type="text" name="gb_charity_password" id="gb_charity_password" value="<?php echo esc_attr( $password ); ?>" class="large-text" />
			</p>
			<p>
				<label for="gb_charity_percentage"><?php gb_e( 'Payment Percentage' ); ?></label><br />
				<input type="number" min="1" max="99" name="gb_charity_percentage" id="gb_charity_percentage" value="<?php echo esc_attr( $percentage ); ?>" />%
			</p>
		<?php
	}

	public static function save_meta_box( $post_id, $post ) {
		// only continue if it's a deal post
		if ( $post->post_type != GB_Charity::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		if ( empty( $_POST['gb_charity_username'] ) && empty( $_POST['gb_charity_username'] ) ) {
			return;
		}
		$charity = GB_Charity::get_instance( $post->ID );
		$username = ( isset( $_POST['gb_charity_username'] ) ) ? $_POST['gb_charity_username'] : '' ;
		$password = ( isset( $_POST['gb_charity_password'] ) ) ? $_POST['gb_charity_password'] : '' ;
		$notes = ( isset( $_POST['gb_payment_notes'] ) ) ? $_POST['gb_payment_notes'] : '' ;
		$percentage = ( isset( $_POST['gb_charity_percentage'] ) && is_numeric( $_POST['gb_charity_percentage'] ) ) ? (int) $_POST['gb_charity_percentage'] : '' ;
		$charity->set_username( $username );
		$charity->set_password( $password );
		$charity->set_payment_notes( $notes );
		$charity->set_percentage( $percentage );
	}

	public static function override_template( $template ) {
		if ( GB_Charity::is_charity_query() ) {
			if ( is_single() ) {
				$template = self::locate_template( array(
						'charity/single.php',
						'charity/charity.php',
						'charity.php'
					), $template );
			} elseif ( is_archive() ) {
				$template = self::locate_template( array(
						'charity/charities.php',
						'business/index.php',
						'business/archive.php',
						'charities.php'
					), $template );
			}
		}
		return $template;
	}

	public static function get_purchase_by_charity( $charity = null, $date_range = null ) {
		if ( null == $charity ) return; // nothing more to to

		$args = array(
			'fields' => 'ids',
			'post_type' => gb_get_purchase_post_type(),
			'post_status' => 'any',
			'posts_per_page' => -1, // return this many
			'meta_query' => array(
				array(
					'key' => GB_Charities::META_KEY,
					'value' => $charity,
					'compare' => '='
				)
			)
		);
		add_filter( 'posts_where', array( get_class(), 'filter_where' ) );
		$purchases = new WP_Query( $args );
		remove_filter( 'posts_where', array( get_class(), 'filter_where' ) );
		return $purchases->posts;
	}

	public function filter_where( $where = '' ) {
		// range based
		if ( isset( $_GET['range'] ) ) {
			$range = ( empty( $_GET['range'] ) ) ? 7 : intval( $_GET['range'] ) ;
			$where .= " AND post_date > '" . date( 'Y-m-d', strtotime( '-'.$range.'days' ) ) . "'";
			return $where;
		}
		// date based
		if ( isset( $_GET['from'] ) ) {
			// from
			$from = $_GET['from'];
			// to
			if ( !isset( $_GET['to'] ) || $_GET['to'] == '' ) {
				$now = time() + ( get_option( 'gmt_offset' ) * 3600 );
				$to = gmdate( 'Y-m-d', $now );
			} else {
				$to = $_GET['to'];
			}

			$where .= " AND post_date >= '".$from."' AND post_date < '".$to."'";
		}
		return $where;
	}

}
