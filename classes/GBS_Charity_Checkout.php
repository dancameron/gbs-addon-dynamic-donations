<?php

class GB_Charities_Checkout extends Group_Buying_Controller {
	const POST_TYPE = 'gb_charities';
	const REWRITE_SLUG = 'charities';
	const REPORT_SLUG = 'charity';
	const META_KEY = 'gb_purchase_charity';

	public static function init() {
		parent::init();

		// Checkout panes
		self::register_payment_pane();
		self::register_review_pane();
		//self::register_confirmation_pane();

		// Save charity record for purchase
		add_action( 'completing_checkout', array( get_class(), 'save_charity' ), 10, 1 );

		// Dynamic Entry of total based on submission
		
		// modify the purchase with an item 
		
		// 

	}


	/**
	 * Register action hooks for displaying and processing the payment page
	 *
	 * @return void
	 */
	private static function register_payment_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'display_payment_page' ), 10, 2 );
		add_action( 'gb_checkout_action_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'process_payment_page' ), 10, 1 );
	}

	private static function register_review_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::REVIEW_PAGE, array( get_class(), 'display_review_page' ), 10, 2 );
	}

	private static function register_confirmation_pane() {
		add_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::CONFIRMATION_PAGE, array( get_class(), 'display_confirmation_page' ), 10, 2 );
	}

	public static function display_payment_page( $panes, $checkout ) {
		$charities = GB_Charity::get_charities();
		$panes['charity'] = array(
			'weight' => 100,
			'body' => self::_load_view_to_string( 'checkout/charities', array( 'charity_ids' => $charities ) ),
		);
		return $panes;
	}

	public static function process_payment_page( Group_Buying_Checkouts $checkout ) {
		$valid = TRUE;
		if ( isset( $_POST['gb_charity'] ) ) {
			if ( $_POST['gb_charity'] == '' ) {
				self::set_message( "A Charity Selection is Required. ", self::MESSAGE_STATUS_ERROR );
				$valid = FALSE;
			}
		}
		if ( !$valid ) {
			$checkout->mark_page_incomplete( Group_Buying_Checkouts::PAYMENT_PAGE );
		} else {
			$checkout->cache['gb_charity'] = $_POST['gb_charity'];
		}
	}


	/**
	 * Display the review pane with a message about their generosity.
	 *
	 * @param array   $panes
	 * @param Group_Buying_Checkout $checkout
	 * @return array
	 */
	public static function display_review_page( $panes, $checkout ) {
		$charity_id = $checkout->cache['gb_charity'];
		if ( $checkout->cache['gb_charity'] ) {
			$panes['gb_charity'] = array(
				'weight' => 5,
				'body' => self::_load_view_to_string( 'checkout/charity-review', array( 'charity_id' => $charity_id ) ),
			);
		}
		return $panes;
	}


	private static function _load_view_to_string( $path, $args ) {
		ob_start();
		if ( !empty( $args ) ) extract( $args );
		@include GB_DYN_CHARITY_PATH . '/views/' . $path . '.php';
		return ob_get_clean();
	}

}