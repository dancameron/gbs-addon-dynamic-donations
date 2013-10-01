<?php

class GBS_Charity_Vouchers extends Group_Buying_Controller {
	

	public static function init() {
		add_action( 'create_voucher_for_purchase', 'maybe_delete_charity_voucher' );
	}

	/**
	 * Delete the newly created voucher because it's not necessary.
	 * @param  integer               $voucher_id 
	 * @param  Group_Buying_Purchase $purchase   
	 * @param  array                 $product    
	 * @return                             
	 */
	public function maybe_delete_charity_voucher( $voucher_id = 0, Group_Buying_Purchase $purchase, $product = array() ) {
		$donation_id = GB_Charities::get_donation_id();
		if ( $donation_id ) {
			$deal_id = $product['deal_id'];
			if ( $deal_id == $donation_id ) {
				wp_delete_post( $voucher_id, TRUE );
			}
		}
	}
}