<?php


class GBS_Dynamic_Charities_Addon {
	
	public static function init() {

		// Post Type
		require_once 'GBS_Charity_Post_Type.php';
		GB_Charity::init();

		// Controller
		require_once 'GBS_Charities.php';
		GB_Charities::init();

		// Checkout
		require_once 'GBS_Charity_Checkout.php';
		GB_Charities_Checkout::init();

		// Reports
		require_once 'GBS_Charity_Reports.php';
		GBS_Charity_Reports::init();

		// Template tags
		require_once GB_DYN_CHARITY_PATH . '/library/template-tags.php';
	}

	public static function init_purchase() {
		// Cart
		require_once 'GBS_Charity_Cart.php';
		GBS_Charity_Cart::init();
	}

	public static function gb_addon( $addons ) {
		$addons['dyn_charities'] = array(
			'label' => gb__( 'Dynamic Charities' ),
			'description' => gb__( 'Basic charity selection at checkout. Charities can (with child theme customization) have a template and the selection will show a thumbnail at checkout when selecting an option.' ),
			'files' => array(),
			'callbacks' => array(
				array( __CLASS__, 'init' ),
			)
		);
		$addons['dyn_charities_purchase'] = array(
			'label' => gb__( 'Dynamic Charities: Donation Purchase' ),
			'description' => gb__( 'Allow the customer to select how much their donation should be by adding an item to their cart; removes the selection area added with the basic dynamic charities above.' ),
			'files' => array(),
			'callbacks' => array(
				array( __CLASS__, 'init_purchase' ),
			)
		);
		return $addons;
	}
}
