<?php


class GBS_Dynamic_Charities_Addon {
	
	public static function init() {

		// Post Type
		require_once 'GBS_Charity_Post_Type.php';
		GB_Charity::init();

		// Controller
		require_once 'GBS_Charities.php';
		GB_Charities::init();

		// Controller
		require_once 'GBS_Charities.php';
		GB_Charities::init();

		// Checkout
		require_once 'GB_Charities_Checkout.php';
		GB_Charities_Checkout::init();

		// Template tags
		require_once GB_DYN_CHARITY_PATH . '/library/template-tags.php';
	}

	public static function gb_addon( $addons ) {
		$addons['charity_split_payments'] = array(
			'label' => gb__( 'Advanced Charity Payments' ),
			'description' => gb__( 'Splits up payments between a charity and the site, uses a modified BluePay payment processor.' ),
			'files' => array(),
			'callbacks' => array(
				array( __CLASS__, 'init' ),
			)
		);
		return $addons;
	}
}
