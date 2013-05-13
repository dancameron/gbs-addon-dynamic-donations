<?php
/*
Plugin Name: Dynamic Donations
Version: 1.3
Plugin URI: http://groupbuyingsite.com/marketplace
Description: Adds a dynamic charity selection to the checkout for customers with the ability for charity templates. Allow the customer to select how much their donation should be by adding an item to their cart; removes the selection area added with the basic dynamic charities above.
Plugin URI: http://groupbuyingsite.com/marketplace/
Author: Sprout Venture
Author URI: http://sproutventure.com/
Plugin Author: Dan Cameron
Plugin Author URI: http://sproutventure.com/
Contributors: Dan Cameron
Text Domain: group-buying
*/

define ('GB_DYN_CHARITY_URL', plugins_url( '', __FILE__) );
define( 'GB_DYN_CHARITY_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );

// Load after all other plugins since we need to be compatible with groupbuyingsite
add_action( 'plugins_loaded', 'gb_load_bundles' );
function gb_load_bundles() {
	$gbs_min_version = '4.3';
	if ( class_exists( 'Group_Buying_Controller' ) && version_compare( Group_Buying::GB_VERSION, $gbs_min_version, '>=' ) ) {
		require_once 'classes/GBS_Dynamic_Charities_Addon.php';

		// Hook this plugin into the GBS add-ons controller
		add_filter( 'gb_addons', array( 'GBS_Dynamic_Charities_Addon', 'gb_addon' ), 10, 1 );
	}
}