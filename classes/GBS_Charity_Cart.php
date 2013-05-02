<?php

class GBS_Charity_Cart extends Group_Buying_Controller {
	const CART_OPTION_NAME = 'gb_donation_option';
	const CART_META_DONATION = 'gb_donation_total';
	const PURCHASE_META_KEY = 'gb_donation_total';
	const ITEM_DATA_KEY = 'gb_donation_total';
	const DEFAULT_PERCENTAGE = 5;

	public static function init() {

		// force the donation item in the cart
		add_action( 'gb_processing_cart', array( get_class(), 'force_donation_to_cart' ), 10, 1 );

		// Add item to the cart based on amount
		add_action( 'gb_processing_cart', array( get_class(), 'maybe_add_donation_to_cart' ), 10, 1 );
		add_action( 'gb_checkout_action_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'maybe_add_donation_to_cart_on_checkout' ), 0, 1 );

		// Filter the price based on the purchase 
		add_filter( 'gb_get_deal_price_meta', array( get_class(), 'filter_deal_price' ), 10, 4 );
		add_filter( 'gb_deal_price', array( get_class(), 'filter_deal_price' ), 10, 4 );

		// title
		add_filter( 'gb_deal_title', array( get_class(), 'filter_deal_title' ), 20, 2 );

		// modify the line items
		add_filter( 'gb_cart_items', array( get_class(), 'line_items' ), 10, 2 );

		// Remove the charity selection pane
		self::deregister_payment_pane();
		self::deregister_review_pane();
		

	}

	public static function force_donation_to_cart( Group_Buying_Cart $cart ) {
		// check to make sure a post of a new charity donation isn't being sent (prevent dups)
		if ( !isset( $_POST['gb_charity'] ) && !isset( $_POST[self::CART_OPTION_NAME] ) ) {
			// make sure the cart doesn't already have a discount and there's a default discount percentage set.
			if ( !self::cart_has_donation( $cart ) && self::DEFAULT_PERCENTAGE ) {
				// only do this if there's a single charity for the site.
				$charities = GB_Charity::get_charities();
				if ( count( $charities ) == 1 ) { // TODO
					// calculate the donation
					$default_donation = ( self::DEFAULT_PERCENTAGE ) ? $cart->get_subtotal()*(self::DEFAULT_PERCENTAGE*0.01) : 0 ;
					// if a free cart don't add a donation
					if ( $default_donation ) {
						self::add_donation_item_to_cart( $charities[0], $default_donation, $cart );
					}
				}
			}
		}
	}

	public static function maybe_add_donation_to_cart( Group_Buying_Cart $cart ) {
		if ( isset( $_POST['gb_charity'] ) && isset( $_POST[self::CART_OPTION_NAME] ) ) {

			self::add_donation_item_to_cart( $_POST['gb_charity'], $_POST[self::CART_OPTION_NAME], $cart );
		}
	}

	public static function maybe_add_donation_to_cart_on_checkout( Group_Buying_Checkouts $checkout ) {
		if ( isset( $_POST['gb_charity'] ) && isset( $_POST[self::CART_OPTION_NAME] ) ) {
			self::add_donation_item_to_cart( $_POST['gb_charity'], $_POST[self::CART_OPTION_NAME] );
		}
	}

	public function add_donation_item_to_cart( $charity_id, $donation_total, $cart = 0 ) {
		if ( !$cart ) {
			$cart = Group_Buying_Cart::get_instance();
		}
		$item_id = GB_Charities::get_donation_id();
		$data = array(
				Group_Buying_Attribute::ATTRIBUTE_DATA_KEY => GB_Charities::get_donation_attribute_by_charity_id( $charity_id ),
				self::ITEM_DATA_KEY => $donation_total
			);
		$cart->remove_item( $item_id );
		$cart->add_item( $item_id, 1, $data );
	}

	public static function filter_deal_title( $title, $data ) {
		if ( !isset( $data[self::ITEM_DATA_KEY] ) ) {
			return $title; // isn't an attribute
		}
		$attribute_id = $data[Group_Buying_Attribute::ATTRIBUTE_DATA_KEY];
		$charity_id = GB_Charities::get_charity_id_by_attribute_id( $attribute_id );
		$title = 'Donation to ' . get_the_title( $charity_id ) . ' (' . gb_get_excerpt_char_truncation( 50, $charity_id  ) . ')';

		return $title;
	}

	public static function get_charity_id_from_data( $data ) {
		if ( !isset( $data[self::ITEM_DATA_KEY] ) ) {
			return 0;
		}
		$attribute_id = $data[Group_Buying_Attribute::ATTRIBUTE_DATA_KEY];
		$charity_id = GB_Charities::get_charity_id_by_attribute_id( $attribute_id );

		return $charity_id;
	}

	public static function filter_deal_price( $price, Group_Buying_Deal $deal, $qty, $data ) {
		if ( isset( $data[self::ITEM_DATA_KEY] ) ) {
			return $data[self::ITEM_DATA_KEY]; // isn't an attribute
		}
		return $price;
	}

	/**
	 * Rebuild the entire line items for the cart table
	 * @param  array            $items 
	 * @param  Group_Buying_Cart $cart  
	 * @return                    
	 */
	public static function line_items( $items, Group_Buying_Cart $cart ) {
		$donation_id = GB_Charities::get_donation_id();
		$charities = GB_Charity::get_charities();
		if ( empty( $charities ) ) {
			return $items;
		}

		if ( !self::cart_has_donation( $cart ) && !$static ) {
			$charities = GB_Charity::get_charities();
			$select_list = '<br/><select name="gb_charity" id="gb_charity">';
			$select_list .= '<option></option>';
			foreach ( $charities as $charity_id ) {
				$select_list .= '<option value="'.$charity_id.'">'.get_the_title( $charity_id ).'</option>';
			}
			$select_list .= '</select>';
			$row = array(
				'remove' => '',
				'name' => gb__('Donate to:') . $select_list,
				'quantity' => $static ? 1 : gb_get_quantity_select( 1, 1, 1, 'items['.$key.'][qty]' ),
				'price' => '<input type="text" name="'.self::CART_OPTION_NAME.'" class="input_mini" placeholder="0"/>'
			);
			$items[] = $row;
		}
		else { // If the cart has a donation item already.
			$account = Group_Buying_Account::get_instance();
			$items = array();
			foreach ( $cart->get_items() as $key => $item ) {

				if ( $donation_id === $item['deal_id'] ) {
					$price = $deal->get_price( NULL, $item['data'] );
					$price_input = '<span id="'.self::CART_OPTION_NAME.'">'.gb_get_formatted_money($price).' <small class="link">edit</small></span><input type="text" id="input_'.self::CART_OPTION_NAME.'" name="'.self::CART_OPTION_NAME.'" class="input_mini cloak" value="'.$price.'" placeholder="0"/>';
					$price_input .= '<style type="text/css">#input_'.self::CART_OPTION_NAME.' { display: none;}</style>';
					$price_input .= '<script type="text/javascript">
							jQuery(document).ready( function($) {
								$("span#'.self::CART_OPTION_NAME.'").live( "click", function() {
									$(this).remove();
									$("#input_'.self::CART_OPTION_NAME.'").fadeIn("fast");
								});
							});
						</script>';

					$row = array(
						'remove' => sprintf( '', $key ),
						'name' => $deal->get_title( $item['data'] ),
						'quantity' => $static ? $item['quantity']: gb_get_quantity_select( '1', 1, 1, 'items['.$key.'][qty]' ),
						'price' => $price_input
					);
					if ( $static ) {
						unset( $row['remove'] );
						$row['price'] = gb_get_formatted_money( $price );
					} else {
						$row['name'] .= sprintf( '<input type="hidden" value="%s" name="gb_charity" />', self::get_charity_id_from_data( $item['data'] ) );
					}
					$items[] = $row;
				}
				else {
					$deal = Group_Buying_Deal::get_instance( $item['deal_id'] );
					$max_quantity = $account->can_purchase( $item['deal_id'], $item['data'] );
					if ( $max_quantity == Group_Buying_Account::NO_MAXIMUM ) {
						$max_quantity = round( $item['quantity']+10, -1 );
					}
					if ( !is_object( $deal ) || !$deal->is_open() || $max_quantity < 1 ) {
						$cart = Group_Buying_Cart::get_instance();
						$cart->remove_item( $item['deal_id'], $item['data'] );
					} else {
						$price = $deal->get_price( NULL, $item['data'] )*$item['quantity'];
						$row = array(
							'remove' => sprintf( '<input type="checkbox" value="remove" name="items[%d][remove]" />', $key ),
							'name' => '<a href="'.get_permalink( $deal->get_ID() ).'">'.$deal->get_title( $item['data'] ).'</a>',
							'quantity' => $static ? $item['quantity']: gb_get_quantity_select( '1', $max_quantity, $item['quantity'], 'items['.$key.'][qty]' ),
							'price' => gb_get_formatted_money( $price ),
						);
						if ( $static ) {
							unset( $row['remove'] );
						} else {
							$row['name'] .= sprintf( '<input type="hidden" value="%s" name="items[%d][id]" />', $item['deal_id'], $key );
							$row['name'] .= sprintf( '<input type="hidden" value="%s" name="items[%d][data]" />', $item['data']?esc_attr( serialize( $item['data'] ) ):'', $key );
						}
						$items[] = $row;
					}
				}
			}
		}
		return $items;
	}

	public function cart_has_donation( Group_Buying_Cart $cart ) {
		$donation_id = GB_Charities::get_donation_id();
		foreach ( $cart->get_items() as $key => $item ) {
			if ( $donation_id === $item['deal_id'] ) {
				return TRUE;
			}
		}
		return FALSE;
	}

	private static function deregister_payment_pane() {
		remove_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( 'GB_Charities_Checkout', 'display_payment_page' ), 10, 2 );
		remove_action( 'gb_checkout_action_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( 'GB_Charities_Checkout', 'process_payment_page' ), 10, 1 );
	}

	private static function deregister_review_pane() {
		remove_filter( 'gb_checkout_panes_'.Group_Buying_Checkouts::REVIEW_PAGE, array( 'GB_Charities_Checkout', 'display_review_page' ), 10, 2 );
	}

}