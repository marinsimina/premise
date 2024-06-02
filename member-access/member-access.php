<?php

add_action( 'plugins_loaded', 'memberaccess_init' );
/**
 * Initialize AccessPress.
 *
 * Include the libraries, define global variables, instantiate the classes.
 *
 * @since 0.1.0
 */
function memberaccess_init() {

	global $memberaccess_products_object;

	define( 'MEMBER_ACCESS_SETTINGS_FIELD', 'member-access-settings' );

	/** Includes */
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'core/class-api.php' );

	// gateways
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-authorize-net.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-express-checkout.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-mailchimp-optin-gateway.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-aweber-optin-gateway.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-free-product.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'core/class-opt-in-payment.php' );

	// CPTs
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-products.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-coupons.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-orders.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-order-summary.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-link-manager.php' );

	// utility
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'functions.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'members.php' );

	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'admin/settings.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'admin/post-access-metabox.php' );
	if ( is_admin() ) {

		require_once( PREMISE_MEMBER_INCLUDES_DIR . 'admin/report.php' );
		require_once( PREMISE_MEMBER_INCLUDES_DIR . 'admin/user-management.php' );

	} else {

		require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-checkout.php' );

	}

	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'views/template-tags.php' );
	require_once( PREMISE_MEMBER_INCLUDES_DIR . 'views/shortcodes.php' );
	
	$memberaccess_products_object = new AccessPress_Products;
	new MemberAccess_Coupons;
	new AccessPress_Orders;
	new Premise_Member_Access_Order_Summary;
	new Premise_Member_Access_Links;
	
	// for recurring processing
	if ( accesspress_get_option( 'authorize_net_recurring' ) == '1' )
		require_once( PREMISE_LIB_DIR . 'cron/recurring-payments.php' );

	// for vBulletin
	if ( memberaccess_is_vbulletin_enabled() ) {

		require_once( PREMISE_MEMBER_INCLUDES_DIR . 'class-vbulletinbridge.php' );
		new Premise_vBulletin_Bridge;

	}

	do_action( 'memberaccess_setup' );

}
/**
 * This function runs on membership activation.
 *
 */	
add_action( 'premise_admin_init', 'accesspress_create_role' );