<p class="alert alert_info alert_block">
	<strong><?php self::_e('Charity Contribution:'); ?></strong>
	<?php printf(self::__("A portion of your purchase will be donated to <span class='charity-recipient'>%s</span>. Thank you!"), get_the_title( $charity_id ) ); ?>
</p>