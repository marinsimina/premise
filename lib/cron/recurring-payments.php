<?php

add_action( 'init', 'premise_ipn_handler' );
function premise_ipn_handler() {

	global $wpdb;

	if ( ! isset( $_REQUEST['premiseipn'] ) || $_REQUEST['premiseipn'] != 'paypal' )
		return;

	$gateway = new MemberAccess_Paypal_Gateway;
	if ( ! $gateway->validate_IPN() )
		return;

	$ipn_cancel = $_POST['txn_type'] == 'recurring_payment_profile_cancel';
	$ipn_payment = $_POST['txn_type'] == 'recurring_payment';
	if ( ! isset( $_POST['txn_type'] ) || ! isset( $_POST['recurring_payment_id'] ) || ! ( $ipn_cancel || $ipn_payment ) )
		return;

	if ( $ipn_payment && ( ! isset( $_POST['txn_id'] ) || ! isset( $_POST['payment_status'] ) || strtolower( $_POST['payment_status'] ) != 'completed' ) )
		return;

	$blog_prefix = $wpdb->get_blog_prefix();
	$user_meta = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->usermeta} WHERE meta_key LIKE %s AND meta_value = %s", $blog_prefix . 'memberaccess_paypal_profile%', $_POST['recurring_payment_id'] ) );
	if ( ! $user_meta )
		return;

	// don't process a payment twice
	if ( $ipn_payment ) {

		$order_meta = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = '_acp_order_paypal_transaction_id' AND meta_value = %s", $_POST['txn_id'] ) );
		if ( $order_meta )
			return;

	}

	$user_orders = get_user_option( 'acp_orders', $user_meta->user_id );
	if ( empty( $user_orders ) )
		return;

	$order_products = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE post_id in (" . implode( ',', $user_orders ) . ") AND meta_key = '_acp_order_product_id'" );
	if ( empty( $order_products ) )
		return;

	$order_id = 0;
	$product_id = 0;
	foreach( $order_products as $product ) {

		if ( $user_meta->meta_key == $blog_prefix . 'memberaccess_paypal_profile_' . $product->meta_value ) {

			$order_id = $product->post_id;
			$product_id = $product->meta_value;
			break;

		}
	}

	if ( ! $product_id || ! $order_id )
		return;

	$duration = $gateway->get_subscription_duration( $product_id );
	if ( ! $duration )
		return;

	if ( $ipn_cancel ) {

		update_post_meta( $order_id, '_acp_order_status', __( 'cancel', 'premise ' ) );
		do_action( 'premise_cancel_subscription', $order_id, $product_id, $user_meta->user_id, true );

		return;

	}
	$renewal_time = get_post_meta( $order_id, '_acp_order_renewal_time', true );
	$now = time();
	// if expired more than a week renew from the current time
	if ( empty( $renewal_time ) || $renewal_time + ( 7 * 86400 ) < $now )
		$renewal_time = $now;

	update_post_meta( $order_id, '_acp_order_renewal_time', $renewal_time + ( $duration * 86400 ) );
	update_post_meta( $order_id, '_acp_order_paypal_transaction_id', $_POST['txn_id'] );
	update_post_meta( $order_id, '_acp_order_status', 'active' );

}

add_action( 'admin_init', 'premise_recurring_payment_handler' );
function premise_recurring_payment_handler() {

	global $premise_cron_args, $wpdb;

	if ( is_user_logged_in() || ! function_exists( 'memberaccess_get_cron_key' ) )
		return;

	if ( ! is_array( $premise_cron_args ) || empty( $premise_cron_args['key'] ) || memberaccess_get_cron_key() != $premise_cron_args['key'] )
		return;

	$now = time();
	// check for expired subscriptions
	$expired_subscriptions = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_acp_order_renewal_time' AND meta_value BETWEEN %s AND %s", $now - 259200, $now - 172800 ) );
	if ( ! empty( $expired_subscriptions ) ) {

		foreach( $expired_subscriptions as $order ) {

			$order_status = get_post_meta( $order, '_acp_order_status', true );
			if ( $order_status == __( 'cancel', 'premise' ) )
				continue;

			AccessPress_Orders::cancel_subscription( $order, false );

		}

	}
	// try renewing over a 3 day window
	$subscriptions = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_acp_order_renewal_time' AND meta_value BETWEEN %s AND %s", $now - 172800, $now + 86400 ) );
	if ( ! empty( $subscriptions ) )
		$orders = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'acp-orders' AND post_status = 'publish' AND ID IN (" . implode( ',', $subscriptions ) . ')' );

	if ( empty( $orders ) )
		premise_recurring_payment_notification( __( 'No subscriptions to renew.', 'premise' ) );

	$results = array();
	foreach( $orders as $order ) {

		$order_status = get_post_meta( $order, '_acp_order_status', true );
		if ( $order_status == __( 'cancel', 'premise' ) )
			continue;

		$order_post = get_post( $order );
		$result = AccessPress_Orders::renew_subscription( $order, false );
		if ( ! $result ) {

			$results[] = sprintf( __( 'Could not find product on order # %s', 'premise' ), $order_post->post_name );
			continue;

		}
		if ( is_wp_error( $result ) ) {

			$results[] = sprintf( __( 'Error on order # %s - %s', 'premise' ), $order_post->post_name, $result->get_error_message() );
			continue;

		}

		$product = get_post( $result['product_id'] );
		$results[] = sprintf( __( 'Processed order # %d - %s - %s - Amount %.2f', 'premise' ), $order, $order_post->post_name, $product->post_title, $result['order_details']['_acp_order_price'] );

	}

	premise_recurring_payment_notification( implode( "\n", $results ) );

}

function premise_recurring_payment_notification( $message ) {

	// bundle the message into an email
	$email_from = memberaccess_get_email_receipt_address();
	$from_description = accesspress_get_option( 'email_receipt_name' );
	$email_subject = sprintf( __( 'Premise scheduled payment settlement (%s)', 'premise' ), date( get_option( 'date_format' ), time() ) );
	wp_mail( $email_from, $email_subject, $message, "From: \"{$from_description}\" <{$email_from}>" );

	die;

}
