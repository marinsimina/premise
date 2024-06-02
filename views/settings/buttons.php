<?php
$sort = array();
$buttons = (array) $this->getConfiguredButtons();

foreach( $buttons as $key => $button ) {

	if ( isset( $button[$orderby] ) )
		$sort[$button[$orderby]] = $key;

}
if ( count( $sort ) > 1 ) {

	if( $order == 'ASC' )
		ksort( $sort );
	else
		krsort( $sort );

}
$sort_url = menu_page_url( 'premise-buttons', false );
$title_sort = $orderby == 'title' && $order == 'ASC' ? 'DESC' : 'ASC';
$date_sort = $orderby == 'lastsaved' && $order == 'ASC' ? 'DESC' : 'ASC';

$title_url = add_query_arg( array( 'orderby' => 'title', 'order' => $title_sort ), $sort_url );
$date_url = add_query_arg( array( 'orderby' => 'lastsaved', 'order' => $date_sort ), $sort_url );
?>
<h3 id="your-buttons"><?php _e('Your Buttons', 'premise' ); ?> <a href="<?php echo add_query_arg(array('height' => 700), get_upload_iframe_src('premise-button-create')); ?>" class="thickbox button button-secondary"><?php _e('Add New Button', 'premise' ); ?></a></h3>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><?php printf( '<a href="%s">%s</a>', $title_url, __('Title', 'premise' ) ); ?></th>
			<th scope="col"><?php _e('Example Button', 'premise' ); ?></th>
			<th scope="col"><?php _e('Actions', 'premise' ); ?></th>
			<th scope="col"><?php printf( '<a href="%s">%s</a>', $date_url, __('Last Saved', 'premise' ) ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col"><?php printf( '<a href="%s">%s</a>', $title_url, __('Title', 'premise' ) ); ?></th>
			<th scope="col"><?php _e('Example Button', 'premise' ); ?></th>
			<th scope="col"><?php _e('Actions', 'premise' ); ?></th>
			<th scope="col"><?php printf( '<a href="%s">%s</a>', $date_url, __('Last Saved', 'premise' ) ); ?></th>
		</tr>
	</tfoot>
	<tbody>
		<?php
		foreach( $sort as $key ) {
			$button = $buttons[$key];
		?>
		<tr>
			<td><?php echo esc_html($button['title']); ?></td>
			<td><a href="#" onclick="return false;" style="display:block; float: left; margin: 5px 0;" class="premise-button-<?php echo $key; ?>"><?php echo esc_html($button['title']); ?></a></td>
			<td>
				<a class="thickbox" href="<?php echo esc_url(add_query_arg(array('height' => 500), premise_get_media_upload_src('premise-button-create', array('premise-button-id' => $key)))); ?>"><?php _e('Edit', 'premise' ); ?></a>
				| <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('premise-button-id' => urlencode($key), 'premise-duplicate-button' => 'true'), admin_url('admin.php?page=premise-buttons')), 'premise-duplicate-button')); ?>"><?php _e('Duplicate', 'premise' ); ?></a>
				| <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('premise-button-id' => urlencode($key), 'premise-delete-button' => 'true'), admin_url('admin.php?page=premise-buttons')), 'premise-delete-button')); ?>"><?php _e('Delete', 'premise' ); ?></a>
			</td>
			<td><?php echo esc_html(date('F j, Y \a\t g:iA', $button['lastsaved']));  ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>



