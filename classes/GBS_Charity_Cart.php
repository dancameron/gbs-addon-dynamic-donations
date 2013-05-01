<?php

class GBS_Charity_Cart extends Group_Buying_Controller {
	const CART_OPTION_NAME = 'gb_donation_option';
	const CART_META_DONATION = 'gb_donation_total';
	const PURCHASE_META_KEY = 'gb_donation_total';

	public static function init() {
		// Add item to the cart based on amount
		add_action( 'gb_checkout_action_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'add_donation_to_cart' ), 10, 1 );

		// Filter the price based on the purchase 
		
		// Save donation
		add_action('gb_checkout_action_'.Group_Buying_Checkouts::PAYMENT_PAGE, array( get_class(), 'process_payment_page'), 19, 1);

		// Cart Display
		//add_filter('gb_cart_extras', array( get_class(), 'cart_donation') );
		//add_filter('gb_cart_line_items', array( get_class(), 'line_items' ), 10, 2 );
		//add_filter('gb_cart_get_total', array( get_class(), 'cart_total' ), 10, 2 );

	}

	public static function add_donation_to_cart( Group_Buying_Checkouts $checkout ) {
		$cart = Group_Buying_Cart::get_instance();
		$data = array(
				Group_Buying_Attribute::ATTRIBUTE_DATA_KEY => GB_Charities::get_donation_attribute_by_charity_id( $_POST['gb_charity'] ),
			);
		$cart->add_item( GB_Charities::get_donation_id(), 1, $data );
	}

	public function process_payment_page( Group_Buying_Checkouts $checkout ) {
		if ( isset($_POST[self::CART_OPTION_NAME]) && $_POST[self::CART_OPTION_NAME] ) {
			$donation = ( $_POST[self::CART_OPTION_NAME] > 0 ) ? $_POST[self::CART_OPTION_NAME] : 0;
			$checkout->cache[self::CART_META_DONATION] = $donation;
		}
	}
	
	public static function line_items( $line_items, Group_Buying_Cart $cart ) {
		$value = ( self::get_cart_donation_total() ) ? self::get_cart_donation_total() : '' ;
		if ( gb_on_checkout_page() && gb_get_current_checkout_page() === Group_Buying_Checkouts::PAYMENT_PAGE ) {
			$donation_row = array(
				'donation' => array(
					'label' => gb__('Donation Amount (select charity below)'),
					'data' => '<input type="text" name="'.self::CART_OPTION_NAME.'" class="input_mini" value="'.$value.'" placeholder="0"/>',
					'weight' => 25,
				),
			);
		}
		elseif ( gb_on_checkout_page() ) {
			$donation_row = array(
				'donation' => array(
					'label' => gb__('Donation Amount'),
					'data' => gb_get_formatted_money($value),
					'weight' => 25,
				),
			);
		}
		if ( is_array( $donation_row ) ) {
			$line_items = array_merge($donation_row,$line_items);
		}
		return $line_items;
	}
	
	public static function cart_total( $total, Group_Buying_Cart $cart ) {
		$donation = self::get_cart_donation_total();
		if ( $donation ) {
			$total = $total+$donation;
		}
		return $total;
	}

	///////////////
	// Utilities //
	///////////////

	public function get_cart_donation_total() {
		global $blog_id;
		$cache = get_user_meta(get_current_user_id(), $blog_id.'_'.Group_Buying_Checkouts::CACHE_META_KEY, TRUE);
		return $cache[self::CART_META_DONATION];
	}

	public function update_cart_donation( $donation_total ) {
		global $blog_id;
		$cache = get_user_meta(get_current_user_id(), $blog_id.'_'.Group_Buying_Checkouts::CACHE_META_KEY, TRUE);
		if ( !is_array($cache) ) {
			$cache = array();
		}
		$cache[self::CART_META_DONATION] = $donation_total;
		update_user_meta(get_current_user_id(), $blog_id.'_'.Group_Buying_Checkouts::CACHE_META_KEY, $cache);
		return $cache;
	}

	/**
	 * @return Hook into cart to show the donation
	 */
	public static function cart_has_donation() {
		$donation = self::get_cart_donation_total();
		if ( $donation >= 0.01 ) {
			return TRUE;
		}
		return;
	}

}