<div class="checkout_block clearfix">

	<header class="secondary_header">
		<span class="legend secondary_title"><?php gb_e( 'Select a Charity' ) ?></span>
	</header><!-- .secondary_header -->

	<div class="control_group">
		<span class="control_label">
			<label for="gb_charity"><?php gb_e( 'Where would you like a portion of your purchase donated to?' ) ?></label>
		</span>
		<div class="controls">
			<span class="gb-form-field gb-form-field-select gb-form-field-">
				<select name="gb_charity" id="gb_charity">
					<?php
						$selected = ( isset( $_POST[ 'gb_charity' ] ) ) ? $_POST[ 'gb_charity' ] : '' ;
						echo '<option value="">'.gb__( 'Select a Charity' ).'</option>';
						foreach ( $charity_ids as $charity_id ) {
							$option = '<option value="'.$charity_id.'" '.selected( $selected, $charity_id ).'>'.get_the_title( $charity_id ).'</option>';
							print $option;
						}
						?>
				</select>
			</span>
			<div class="charity_thumbs clearfix">
				<?php 
					foreach ( $charity_ids as $charity_id ) {
						echo '<span id="charity_thumb_' . $charity_id. '" class="charity_thumb cloak">' . get_the_post_thumbnail( $charity_id, array( 120, 120 ) ) . '</span>';
					}
				 ?>
			</div><!--  .charity_thumbs -->
		</div>
	</div>
</div>


<script type="text/javascript">
jQuery(document).ready( function($) {
	
	var show_charity_thumb = function(e) {
		var $select = $(this);
    	var $value = $select.val();
    	var $thumbs_class = $('.charity_thumb');
    	console.log( $value );
    	// Hide all thumbs
		$thumbs_class.fadeOut();
		// Show the thumb selected
		$( '#charity_thumb_' + $value ).fadeIn();

	};
	$('#gb_charity').live( 'change', show_charity_thumb );
});
</script>
<style type="text/css">
#gb_charity {
	width: 55%;
	float: left;
}
.charity_thumbs {
	float: right;
	width: 40%;
	height: 120px;
	position: relative;
}
.charity_thumb {
	position: absolute;
	top: 0px;
	left: 0px;
}
	
</style>
