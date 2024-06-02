<?php
/*
Description: Advanced member management for Premise.
*/

add_action( 'personal_options', 'premise_member_remove_profile_fields' );

function premise_member_remove_profile_fields( $user ) {

	$user_id = $user->ID;
	if ( user_can( $user_id, 'read' ) || ! user_can( $user_id, 'access_membership' ) )
		return;

	$is_profile = get_current_user_id() == $user_id;
	$hook = $is_profile ? 'show_user_profile' : 'edit_user_profile';

	// add member orders for admins
	if ( ! $is_profile )
		add_action( $hook, 'memberaccess_user_profile_show_orders' );

	// unhook Genesis
	remove_action( $hook, 'genesis_user_options_fields' );
	remove_action( $hook, 'genesis_user_archive_fields' );
	remove_action( $hook, 'genesis_user_seo_fields' );
	remove_action( $hook, 'genesis_user_layout_fields' );

	// hide unneeded profile fields
?>
<script type="text/javascript">
jQuery(document).ready(function() {
       jQuery('#profile-page table.form-table tr:has(#description, #rich_editing, #admin_color_classic, #comment_shortcuts, #admin_bar_front, #url, #aim, #yim, #jabber)').hide();
});
</script>
<?php
}

function memberaccess_user_profile_show_orders( $user ) {

	if ( empty( $user->ID ) )
		return;

	$member_orders = get_user_option( 'acp_orders', $user->ID );
	if ( empty( $member_orders ) || !is_array( $member_orders ) )
		return;

	$column_titles = AccessPress_Orders::columns_filter( array( 'order_id' => __( 'Order ID', 'premise' ), 'date' => __( 'Date', 'premise' ) ) );
	unset( $column_titles['member_name'] );
	$column_titles = apply_filters( 'memberaccess_member_profile_columns', $column_titles, $user );

	if ( empty( $column_titles ) )
		return;

	printf( '<h3>%s</h3><table class="form-table"><tr>', __( 'Orders', 'premise' ) );
	echo '<thead><tr><td>' . implode( '</td><td>', $column_titles ) . '</td></tr></thead>';
	echo '<tfoot><tr><td>' . implode( '</td><td>', $column_titles ) . '</td></tr></tfoot>';
	add_action( 'memberaccess_member_profile_columns_data', array( 'AccessPress_Orders', 'columns_data' ), 10, 2 );
	$alt = '';

	foreach ( $member_orders as $order ) {

		$order_post = get_post( $order );
		if ( ! isset( $order_post->post_status ) || 'publish' != $order_post->post_status )
			continue;

		$alt = $alt ? '' : 'alt';
		echo "<tr class='$alt'>";
		
		foreach( array_keys( $column_titles ) as $key ) {

			echo '<td>';

			switch( $key ) {
				case 'date':
					$order_time = get_post_meta( $order, '_acp_order_time', true );
					if ( $order_time )
						echo date( __( 'Y/m/d', 'premise' ), $order_time );
					break;

				case 'order_id':

					printf( '<a href="%s">%s</a>', add_query_arg( array( 'action' => 'edit', 'post' => $order ), admin_url( 'post.php' ) ), $order );
					break;

				default:
					do_action( 'memberaccess_member_profile_columns_data', $key, $order, $user->ID );
					break;
			}

			echo '</td>';

		}

		echo '</tr>';
		
	}

	echo '</table>';

}

add_action( 'admin_menu', 'premise_member_add_member_menu_item', 999 );

function premise_member_add_member_menu_item() {

	global $submenu;

	$submenu['premise-member'][] = array( 'Members', 'manage_options', 'users.php?role=premise_member' );

}

add_filter( 'pre_user_query', 'premise_member_user_query' );

function premise_member_user_query( $q ) {
	
	global $wpdb;
	
	if ( empty( $q->query_where ) || ! preg_match( '|user_login LIKE \'([^\']+)\'|', $q->query_where, $m ) )
		return $q;
	
	$extra = $wpdb->prepare( "ID in (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key IN ('first_name','last_name') AND meta_value LIKE %s) OR user_email LIKE %s OR ", $m[1], $m[1] );
	$q->query_where = preg_replace( '|(user_login LIKE \')|', $extra . '$1', $q->query_where );
	
	return $q;
}

add_filter( 'parse_query', 'premise_member_order_search_filter' );

function premise_member_order_search_filter( &$q ) {

	if ( $q->get( 'post_type' ) != 'acp-orders' )
		return $q;

	$field = '';
	foreach( array( 'member', 'product', 'coupon' ) as $key ) {
	
		$value = isset( $_GET[$key] ) ? (int) $_GET[$key] : 0;
		if ( $value ) {

			$field = $key;
			break;

		}
	}

	if ( ! $value )
		return $q;

	$q->set( 'meta_key', "_acp_order_{$field}_id" );
	$q->set( 'meta_value', $value );

	return $q;

}

add_filter( 'user_row_actions', 'premise_member_user_order_row_actions', 10, 2 );

function premise_member_user_order_row_actions( $actions, $user ) {

	$actions['member_orders'] = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => 'acp-orders', 'member' => $user->ID ), admin_url( 'edit.php' ) ), __( 'Orders', 'premise' ) );
	return $actions;

}

add_filter( 'post_row_actions', 'premise_member_post_row_actions', 10, 2 );

function premise_member_post_row_actions( $actions, $post ) {

	if ( ! in_array( $post->post_type, array( 'acp-products', 'acp-coupons' ) ) )
		return $actions;

	$actions[$post->post_type . '_orders'] = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'post_type' => 'acp-orders', substr( $post->post_type, 4, -1 ) => $post->ID ), admin_url( 'edit.php' ) ), __( 'Orders', 'premise' ) );
	return $actions;

}
