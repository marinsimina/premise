<?php
$sort = array();
$settings = $this->getDesignSettings();

foreach( $settings as $key => $style ) {

	$orderkey = $orderby == 'title' ? 'premise_style_title' : 'premise_style_timesaved';
	if ( ! isset( $style[$orderkey] ) )
		continue;

	$order_value = $style[$orderkey];
	if ( $orderby == 'title' && isset( $sort[$order_value] ) ) {

		$index = 1;
		while ( $index < count( $settings ) && isset( $sort[$order_value] ) )
			$order_value = sprintf( '%s-%03d', $style[$orderkey], $index++ );

	}
	$sort[$order_value] = $key;

}
if ( count( $sort ) > 1 ) {

	if( $order == 'ASC' )
		ksort( $sort );
	else
		krsort( $sort );

}
$sort_url = menu_page_url( 'premise-styles', false );
$title_sort = $orderby == 'title' && $order == 'ASC' ? 'DESC' : 'ASC';
$date_sort = $orderby == 'lastsaved' && $order == 'ASC' ? 'DESC' : 'ASC';

$title_url = add_query_arg( array( 'orderby' => 'title', 'order' => $title_sort ), $sort_url );
$date_url = add_query_arg( array( 'orderby' => 'lastsaved', 'order' => $date_sort ), $sort_url );
?>
<h3><?php _e('Your Styles', 'premise' ); ?> <a href="<?php echo esc_url(admin_url('admin.php?page=premise-style-settings')); ?>" class="button button-secondary"><?php _e('Add New Style', 'premise' ); ?></a></h3>

<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><?php printf( '<a href="%s">%s</a>', $title_url, __('Title', 'premise' ) ); ?></th>
			<th scope="col"><?php _e('Actions', 'premise' ); ?></th>
			<th scope="col"><?php printf( '<a href="%s">%s</a>', $date_url, __('Last Saved', 'premise' ) ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col"><?php printf( '<a href="%s">%s</a>', $title_url, __('Title', 'premise' ) ); ?></th>
			<th scope="col"><?php _e('Actions', 'premise' ); ?></th>
			<th scope="col"><?php printf( '<a href="%s">%s</a>', $date_url, __('Last Saved', 'premise' ) ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<?php 
		foreach( $sort as $key ) {
			$style = $settings[$key];
			if ( ! is_array( $style ) )
				$style = array();
		?>
		<tr>
			<td><a href="<?php echo esc_url(add_query_arg(array('premise-design-key' => $key), admin_url('admin.php?page=premise-style-settings'))); ?>"><?php echo esc_html($style['premise_style_title']); ?></a></td>
			<td>
				<a href="<?php echo esc_url(add_query_arg(array('premise-design-key' => $key), admin_url('admin.php?page=premise-style-settings'))); ?>"><?php _e('Edit', 'premise' ); ?></a>
				| <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('premise-design-key' => $key, 'premise-duplicate-style' => 'true'), admin_url('admin.php?page=premise-style-settings')), 'premise-duplicate-style')); ?>"><?php _e('Duplicate', 'premise' ); ?></a>
				<?php if($key !== 0) { ?> 
				| <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('premise-design-key' => $key, 'premise-delete-style' => 'true'), admin_url('admin.php?page=premise-styles')), 'premise-delete-style')); ?>"><?php _e('Delete', 'premise' ); ?></a>
				<?php } ?>
			</td>
			<td>
			<?php
			$time = empty( $style['premise_style_timesaved'] ) ? current_time( 'timestamp' ) : $style['premise_style_timesaved'];
			echo esc_html( date( __( 'F j, Y \a\t g:iA', 'premise' ), $time ) );
			?>
			</td>
		</td>
		<?php } ?>
	</tbody> 
</table>



