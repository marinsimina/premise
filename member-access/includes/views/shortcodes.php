<?php
/**
 * AccessPress Shortcodes for displaying front-end content
 *
 * @package AccessPress
 */

add_shortcode( 'checkout_form', 'accesspress_checkout_form_shortcode' );
add_shortcode( 'checkout-form', 'accesspress_checkout_form_shortcode' );
/**
 * Shortcode function for the checkout form.
 */
function accesspress_checkout_form_shortcode( $atts ) {

	/** Get shortcode $atts */
	$atts = array_merge( array(
		'account_box_heading'		=> __( '1. Create Your Account', 'premise' ),
		'account_box_heading_member'	=> __( '1. Your Account', 'premise' ),
		'payment_box_heading'		=> __( '2. Choose Payment Method', 'premise' ),
		'cc_box_heading'		=> __( '3. Enter Credit Card Information', 'premise' ),
		'member_text'			=> __( 'Complete Your Purchase', 'premise' ),
		'nonmember_text_free'		=> __( 'Create Account', 'premise' ),
		'member_text_free'		=> __( 'Submit', 'premise' ),
		'nonmember_text'		=> __( 'Create Account and Complete Your Purchase', 'premise' ),
		'already_purchased_text'	=> __( 'You have already purchased this product', 'premise' ),
		'product_id'			=> ''
	), (array) $atts );

	if ( ! is_user_logged_in() ) {

		$atts['show_username'] = '1';
		$atts['show_first_name'] = '1';
		$atts['show_last_name'] = '1';
		$atts['show_username'] = ! accesspress_get_option( 'email_as_username' );

	}

	add_filter( 'comments_open', '__return_false' );

	ob_start();
	accesspress_checkout_form( $atts );
	$checkout_form = ob_get_clean(); 
	
	return $checkout_form;
	
}

add_shortcode( 'login-form', 'accesspress_login_form_shortcode' );
add_shortcode( 'login_form', 'accesspress_login_form_shortcode' );
/**
 * Shortcode function for the login form
 */
function accesspress_login_form_shortcode( $atts ) {

	$atts = shortcode_atts( array(
		'redirect' => add_query_arg( 'just-logged-in', 'true', get_permalink( accesspress_get_option( 'login_page' ) ) ),
		'welcome_text'   => __( 'Welcome! You are now logged in!', 'premise' ),
		'logged_in_text' => __( 'You are already logged in', 'premise' )
	), $atts );

	add_filter( 'comments_open', '__return_false' );

	if ( is_user_logged_in() ) {

		if ( isset( $_REQUEST['just-logged-in'] ) )
			return $atts['welcome_text'];
		else
			return $atts['logged_in_text'];

	}

	if ( isset( $_REQUEST['redirect_to'] ) )
                $redirect = $_REQUEST['redirect_to'];
        else
                $redirect = $atts['redirect'];

	return wp_login_form( array( 'redirect' => esc_url_raw( $redirect ), 'echo' => false ) );

}

add_shortcode( 'logout-link', 'accesspress_logout_link_shortcode' );
add_shortcode( 'logout_link', 'accesspress_logout_link_shortcode' );
/**
 * Generate a logout link.
 */
function accesspress_logout_link_shortcode( $atts ) {
	
	$atts = shortcode_atts( array(
		'text' => __( 'Logout', 'premise' )
	), $atts );
	
	if ( ! is_user_logged_in() )
		return;
	
	$redirect = get_permalink( accesspress_get_option( 'login_page' ) );
	
	return sprintf( '<a href="%s">%s</a>', wp_logout_url( esc_url_raw( $redirect ) ), $atts['text'] );
	
}

add_shortcode( 'password-recovery-link', 'accesspress_password_recovery_link_shortcode' );
add_shortcode( 'password_recovery_link', 'accesspress_password_recovery_link_shortcode' );
/**
 * Generate a password recovery link.
 *
 * @since 2.0.2
 */
function accesspress_password_recovery_link_shortcode( $atts ) {
	
	$atts = shortcode_atts( array(
		'text' => __( 'Lost Password?', 'premise' )
	), $atts );
	
	if ( is_user_logged_in() )
		return;

	$redirect = add_query_arg( 'just-logged-in', 'true', get_permalink( accesspress_get_option( 'login_page' ) ) );

	return sprintf( '<a href="%s">%s</a>', wp_lostpassword_url( esc_url_raw( $redirect ) ), current_user_can( 'unfiltered_html' ) ? $atts['text'] : esc_html( $atts['text'] ) );
	
}

add_shortcode( 'show-to', 'accesspress_show_segmented_content' );
add_shortcode( 'show_to', 'accesspress_show_segmented_content' );

function accesspress_show_segmented_content( $atts, $content = '' ) {

	$no_access = isset( $atts['no_access'] ) ? $atts['no_access'] : '';
	// allow absolute begin and end dates
	$now = time();
	if ( isset( $atts['start_date'] ) ) {

		$start_date = strtotime( $atts['start_date'] );
		if ( $start_date > $now )
			return $no_access;

	}

	if ( isset( $atts['end_date'] ) ) {

		$end_date = strtotime( $atts['end_date'] );
		if ( $end_date > 0 && $end_date < $now )
			return $no_access;

	}

	return accesspress_segmented_content( $atts, $content, $no_access, true );

}

add_shortcode( 'hide-from', 'accesspress_hide_segmented_content' );
add_shortcode( 'hide_from', 'accesspress_hide_segmented_content' );

function accesspress_hide_segmented_content( $atts, $content = '' ) {

	$has_access = isset( $atts['has_access'] ) ? $atts['has_access'] : '';
	return accesspress_segmented_content( $atts, $has_access, $content );

}

add_shortcode( 'product-title', 'accesspress_product_title_content' );
add_shortcode( 'product_title', 'accesspress_product_title_content' );

function accesspress_product_title_content( $atts, $content = '' ) {

	return accesspress_product_info_content( $atts, 'post_title' );

}

add_shortcode( 'product-description', 'accesspress_product_description_content' );
add_shortcode( 'product_description', 'accesspress_product_description_content' );

function accesspress_product_description_content( $atts, $content = '' ) {

	return accesspress_product_info_content( $atts, '_acp_product_description' );

}

add_shortcode( 'product-price', 'accesspress_product_price_content' );
add_shortcode( 'product_price', 'accesspress_product_price_content' );

function accesspress_product_price_content( $atts, $content = '' ) {

	$atts = shortcode_atts( array(
		'productid' => 0,
		'format' => '',
		'trial' => ''
	), $atts );

	$field = $atts['trial'] ? '_acp_product_trial_price' : '_acp_product_price';
	if( empty( $atts['format'] ) )
		$atts['format'] = '$ %.2f';

	return accesspress_product_info_content( $atts, $field );

}
add_shortcode( 'product-duration', 'accesspress_product_duration_content' );
add_shortcode( 'product_duration', 'accesspress_product_duration_content' );

function accesspress_product_duration_content( $atts, $content = '' ) {

	$atts = shortcode_atts( array(
		'productid' => 0,
		'trial' => ''
	), $atts );

	$field = $atts['trial'] ? '_acp_product_trial_duration' : '_acp_product_duration';
	$duration = accesspress_product_info_content( $atts, $field );
	if ( $duration )
		return sprintf( __( '%d Days', 'premise' ), $duration );

	return '';
}
add_shortcode( 'product-number-payments', 'accesspress_product_payments_content' );
add_shortcode( 'product_number_payments', 'accesspress_product_payments_content' );

function accesspress_product_payments_content( $atts, $content = '' ) {

	$payments = accesspress_product_info_content( $atts, '_acp_product_number_payments' );
	if ( $payments )
		return sprintf( __( '%d Payments', 'premise' ), $payments );

	return '';
}

add_shortcode( 'product-purchase', 'accesspress_product_purchase_content' );
add_shortcode( 'product_purchase', 'accesspress_product_purchase_content' );

function accesspress_product_purchase_content( $atts, $content = '' ) {

	return sprintf( accesspress_product_info_content( $atts, 'purchase_link' ), $content );

}

add_shortcode( 'member-first-name', 'accesspress_first_name_content' );
add_shortcode( 'member_first_name', 'accesspress_first_name_content' );

function accesspress_first_name_content( $atts, $content = '' ) {

	return accesspress_member_content( 'first_name' );

}

add_shortcode( 'member-last-name', 'accesspress_last_name_content' );
add_shortcode( 'member_last_name', 'accesspress_last_name_content' );

function accesspress_last_name_content( $atts, $content = '' ) {

	return accesspress_member_content( 'last_name' );

}

add_shortcode( 'member-login', 'accesspress_member_login_content' );
add_shortcode( 'member_login', 'accesspress_member_login_content' );

function accesspress_member_login_content( $atts, $content = '' ) {

	return accesspress_member_content( 'user_login' );

}

add_shortcode( 'member-email-address', 'accesspress_member_email_content' );
add_shortcode( 'member_email_address', 'accesspress_member_email_content' );

function accesspress_member_email_content( $atts, $content = '' ) {

	return accesspress_member_content( 'user_email' );

}

function accesspress_member_content( $field = '' ) {

	global $product_member;

	$user_id = empty( $product_member->ID ) ? get_current_user_id() : $product_member->ID;
	if ( ! $user_id )
		return '';

	if ( in_array( $field, array( 'first_name', 'last_name' ) ) ) {

		$content = get_user_meta( $user_id, $field, true );

	} else {

		$user = get_user_by( 'id', $user_id );
		$content = isset( $user->$field ) ? $user->$field : '';

	}

	if ( empty( $content ) )
		return '';

	return $content;

}


add_shortcode( 'member-profile', 'accesspress_profile_content' );
add_shortcode( 'member_profile', 'accesspress_profile_content' );

function accesspress_profile_content( $atts, $content = '' ) {

	global $post;

	add_filter( 'comments_open', '__return_false' );

	$message = '';
	if ( isset( $_REQUEST['credentials-changed'] ) && $_REQUEST['credentials-changed'] == 'true' )
		$message .= '<span class="premise-message">' . __( 'Your information has been updated.', 'premise' ) . '</span><br />';

	if ( isset( $_REQUEST['password-changed'] ) && $_REQUEST['password-changed'] == 'fail' )
		$message .= '<span class="premise-message">' . __( 'Passwords did not match. Your password was not changed.', 'premise' ) . '</span><br />';

	if ( isset( $_REQUEST['email-changed'] ) && $_REQUEST['email-changed'] == 'used' )
		$message .= '<span class="premise-message">' . __( 'Email address already in use. Your email address was not changed.', 'premise' ) . '</span><br />';

	if ( isset( $_REQUEST['name-changed'] ) && $_REQUEST['name-changed'] == 'true' )
		$message .= '<span class="premise-message">' . __( 'Your information has been updated.', 'premise' ) . '</span><br />';

	if ( ! is_user_logged_in() )
		return $message . sprintf( __( 'Please <a href="%s">Log in</a> to view your account.', 'premise' ), memberaccess_login_redirect( get_permalink() ) );;

	$user = wp_get_current_user();

	/** Get shortcode $atts */
	$atts = shortcode_atts( array(
		'heading_text'       => __( 'Your Account', 'premise' ),
		'show_email_address' => false,
		'show_username'      => false,
		'label_separator'    => ':',
	), $atts );

	/** Merge $atts with $args */
	$args = wp_parse_args( $atts, array(
		'account_box_heading'	=> $atts['heading_text'],
		'first-name'		=> $user->first_name,
		'last-name'		=> $user->last_name,
		'disabled'		=> ! isset( $post->ID ) || $post->ID != accesspress_get_option( 'member_page' ),
	) );

	$submit = '';
	if ( ! $args['disabled'] ) {

		$submit = sprintf( '<input type="submit" value="%s" class="input-submit" />', __( 'Update', 'premise' ) );
		$args['nonce_key'] = 'premise-member-profile-' . $user->ID;

	}

	ob_start();
	accesspress_checkout_form_account( $args );

	return $message . '<form method="post"><div class="premise-checkout-wrap">' . ob_get_clean() . $submit . '</div></form>';

}

add_shortcode( 'member-products', 'memberaccess_member_products_content' );
add_shortcode( 'member_products', 'memberaccess_member_products_content' );

function memberaccess_member_products_content( $atts, $content = '' ) {

	add_filter( 'comments_open', '__return_false' );

	if ( ! is_user_logged_in() )
		return '';

	$user = wp_get_current_user();

	/** Pull all the orders the member has ever made */
	$orders = get_user_option( 'acp_orders', $user->ID );
	if ( empty( $orders ) )
		return '';

	/** check for cancel requests */
	if ( ! empty( $_GET['cancel'] ) && ! empty( $_GET['order_id'] ) && ! empty( $_GET['_wpnonce'] ) && $_GET['cancel'] == 'true' && wp_verify_nonce( $_GET['_wpnonce'], 'cancel-subscription-' . $_GET['order_id'] ) )
		memberaccess_cancel_subscription ( $_GET['order_id'] );

	$output = '';
	$date_format = get_option( 'date_format' );
	$order_format = '<li><span class="premise-member-product">%s</span> - <span class="premise-member-product-expiry">%s</span> <span class="premise-member-product-cancel">%s</span></li>';
	/** Cycle through $orders looking for active (non-expired) subscriptions */
	foreach ( $orders as $order ) {

		// get product
		$product = (int) get_post_meta( $order, '_acp_order_product_id', true );
		$product_post = get_post( $product );
		if ( ! $product_post )
			continue;

		// get expiry time
		$expiration = memberaccess_get_order_expiry( $order, $product, 0, true );

		if ( 0 == $expiration ) {

			$output .= sprintf( $order_format, esc_html( $product_post->post_title ), __( 'Lifetime', 'premise' ), '' );
			continue;

		}

		$payment_profile = get_user_option( 'memberaccess_cc_payment_' . $product );
		if ( ! $payment_profile ) {

			$output .= sprintf( $order_format, esc_html( $product_post->post_title ), date( $date_format, $expiration ), '' );
			continue;

		}

		$renew_url = add_query_arg( array( 'renew' => 'true', 'product_id' => $product ), get_permalink( accesspress_get_option( 'checkout_page' ) ) );

		$cancel_url = '';
		$cancel_status = __( 'cancel', 'premise' );
		$renewal_time = get_post_meta( $order, '_acp_order_renewal_time', true );
		$status = get_post_meta( $order, '_acp_order_status', true );
		if ( $payment_profile && $renewal_time > ( time() - 172800 ) && $status != $cancel_status )
			$cancel_url = sprintf( __( '<a href="%s" %s>Cancel</a>', 'premise' ), wp_nonce_url( add_query_arg( array( 'cancel' => 'true', 'order_id' => $order ), get_permalink( accesspress_get_option( 'member_page' ) ) ), 'cancel-subscription-' . $order ), '' );

		$output .= sprintf( $order_format, esc_html( $product_post->post_title ), date( $date_format, $expiration ) . ' - ' . sprintf( __( '<a href="%s" %s>Renew</a>', 'premise' ), $renew_url, '' ), $cancel_url );

	}

	return '<ul class="premise-member-products">' . $output . '</ul>';

}

add_shortcode( 'order-transaction-id', 'memberaccess_member_order_title' );
add_shortcode( 'order_transaction_id', 'memberaccess_member_order_title' );

function memberaccess_member_order_title( $atts, $content = '' ) {

	global $checkout_order;

	if ( ! empty( $checkout_order->post_title ) )
		return $checkout_order->post_title;

	$atts = shortcode_atts( array(
			'orderid' => 0,
		),
		$atts
	);

	if ( ! $atts['orderid'] )
		return;

	$order = get_post( $atts['orderid'] );
	if ( ! empty( $order->post_title ) )
		return $order->post_title;

	return '';

}

add_shortcode( 'coupon', 'memberaccess_coupon_shortcode' );

function memberaccess_coupon_shortcode( $atts, $content = '' ) {

	$atts = shortcode_atts( array(
			'id' => 0,
			'product' => 0,
			'redirect' => ''
		),
		$atts
	);

	$url = $atts['id'] ? get_permalink( $atts['id'] ) : '';

	if ( $atts['product'] )
		$url = untrailingslashit( $url ) . '/product/' . $atts['product'] . ( $url != untrailingslashit( $url ) ? '/' : '' );

	$has_auth = false;
	if ( get_post_meta( $atts['id'], '_acp_coupon_auth_key', true ) )
		$url .= $has_auth = '?auth=' . MemberAccess_Coupons::get_authorization_key( $atts['id'], $atts['product'] );

	if ( $atts['redirect'] )
		$url .= ( $has_auth ? '&' : '?' ) . 'redir=' . urlencode( $atts['redirect'] );

	if ( $content )
		return sprintf( '<a href="%s">%s</a>', $url, $content );

	return $url;		
}

function accesspress_segmented_content( $atts, $has_access = '', $no_access = '', $check_delay = false ) {

	$atts = shortcode_atts( array(
			'accesslevel' => '',
			'coupon' => '',
			'delay' => '0',
			'duration' => 0,
			'visible_to' => ''
		),
		$atts
	);

	// if this is for a coupon
	if ( ! empty( $atts['coupon'] ) ) {

		if ( memberaccess_has_coupon( $atts['coupon'] ) )
			return do_shortcode( $has_access );

		return do_shortcode( $no_access );
		
	}

	if ( empty( $atts['accesslevel'] ) && ( $atts['visible_to' ] == 'public' || $atts['visible_to'] == 'member' ) ) {

		$show = is_user_logged_in() ^ ( 'public' == $atts['visible_to'] );
		return do_shortcode( $show ? $no_access : $has_access );

	}

	$delay = $check_delay ? (int) $atts['delay'] : 0;
	$end_access = $atts['duration'] ? $delay + (int) $atts['duration'] : 0;
	$member_has_access = ! empty( $atts['accesslevel'] ) && member_has_access_level( $atts['accesslevel'], 0, $delay ) && ( ! $end_access || ! member_has_access_level( $atts['accesslevel'], 0, $end_access ) );

	if ( 'member' == $atts['visible_to'] && ! $member_has_access )
		return do_shortcode( is_user_logged_in() ? $no_access : $has_access );

	return do_shortcode( $member_has_access ? $has_access : $no_access );

}

function accesspress_product_info_content( $atts, $field ) {

	global $product_post;

	$atts = shortcode_atts( array(
			'productid' => 0,
			'format' => '',
			'title' => '',
			'target' => '',
		),
		$atts
	);

	if ( ! $atts['productid'] && isset( $_REQUEST['product_id'] ) )
		$atts['productid'] = (int) $_REQUEST['product_id'];

	if ( ! $atts['productid'] && isset( $_POST['accesspress-checkout']['product_id'] ) )
		$atts['productid'] = (int) $_POST['accesspress-checkout']['product_id'];

	if ( ! $atts['productid'] && isset( $product_post->ID ) )
		$atts['productid'] = (int) $product_post->ID;

	if ( ! memberaccess_is_valid_product( $atts['productid'] ) )
		return '';

	if ( $field == 'post_title' ) {

		if ( ! empty( $product_post->post_title ) )
			return $product_post->post_title;

		$product = get_post( $atts['productid'] );
		if ( empty( $product->post_title ) )
			return '';

		return $product->post_title;

	}

	if ( $field == 'purchase_link' ) {

		$url = accesspress_get_checkout_link( $atts['productid'] );
		if( ! $url )
			return '%s';

		$target = $atts['target'] ? 'target="' . $atts['target'] .'"' : '';
		return sprintf( '<a href="%s" title="%s" %s>', $url, $atts['title'], $target ) . '%s</a>';

	}

	$coupon_id = MemberAccess_Coupons::get_product_coupon( $atts['productid'] );
	if ( $field == '_acp_product_price' )
		$meta = AccessPress_Products::get_product_price( $atts['productid'], $coupon_id );
	elseif ( $field == '_acp_product_trial_price' )
		$meta = AccessPress_Products::get_product_trial_price( $atts['productid'], $coupon_id );
	else
		$meta = get_post_meta( $atts['productid'], $field, true );

	if ( empty( $meta ) )
		return '';

	return $atts['format'] ? sprintf( $atts['format'], $meta ) : $meta;
}
