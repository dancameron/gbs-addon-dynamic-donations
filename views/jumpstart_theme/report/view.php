<?php
	if ( empty($columns) || empty($records)) {
		self::_e('No Data');
	} else {
		global $gb_report_pages;
	?>
<div id="report_navigation clearfix">
	<div class="btn_group">
		<?php
		$report = Group_Buying_Reports::get_instance( $_GET['report'] );
			$date_range = array(1,7,30,60,90,120,365);
		foreach ($date_range as $range) {
			$active = ($range == $_GET['range']) ? 'active' : '' ;
			$button = '<button class="btn report_nav_button '.$active.'"><a href="'.add_query_arg( array( 'report' => $_GET['report'], 'id' => $_GET['id'], 'range' => $range ), $report->get_url()).'">'.sprintf(self::__('Previous %s Days'),$range).'</a></button>';
			echo $button;
		} ?>
	</div>
</div>
<br/>
<table class="report table table_striped table_bordered table_hover">
	<thead>
		<tr>
		<?php foreach ( $columns as $key => $label ): ?>
			<th class="cart-<?php esc_attr_e($key); ?>" scope="col"><?php esc_html_e($label); ?></th>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $records as $record ): ?>
			<tr>
				<?php foreach ( $columns as $key => $label ): ?>
					<td class="cart-<?php esc_attr_e($key); ?>">
						<?php if ( isset($record[$key]) ) { echo $record[$key]; } ?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<br/>
<div id="report_navigation pagination clearfix">
	<div class="btn_group">
		<?php
			if ( $gb_report_pages > 1 ) {
				$report = Group_Buying_Reports::get_instance( $_GET['report'] );
				$report_url = $report->get_url();
				$current_page = $_GET['showpage']-1;
				for ($i=0; $i < $gb_report_pages; $i++) {
					$page_num = (int)$i; $page_num++;
					$active = ( $i == $current_page || ($i == 0) && !isset($_GET['showpage'])) ? 'active' : '' ;
					$button = '<button class="btn report_nav_button '.$active.'"><a class="report_button button contrast_button" href="'.add_query_arg( array( 'report' => $_GET['report'], 'id' => $_GET['id'], 'showpage' => $i ), $report_url).'">'.$page_num.'</a></button>';
					echo $button;
				}
			} ?>
	</div>
</div>
<?php
	}
	 ?>