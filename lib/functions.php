<?php
/**
 * Premise Functions for use throughout the plugin
 *
 * @package Premise
 */
/**
 * Helper function used to check that we're targeting a specific Premise admin page.
 *
 * @since 0.1.0
 *
 * @global string $page_hook Page hook for current page
 * @param string $pagehook Page hook string to check
 * @return boolean Returns true if the global $page_hook matches given $pagehook. False otherwise
 */
function accesspress_is_menu_page( $pagehook = '' ) {

	global $page_hook;

	if ( isset( $page_hook ) && $page_hook == $pagehook )
		return true;

	/* May be too early for $page_hook */
	if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == $pagehook )
		return true;

	return false;

}
/**
 * Retrieve and return an option from the database.
 *
 * @since 2.1.0
 */
function premise_get_option( $key, $setting = null ) {

	/**
	 * Get setting. The default is set here, once, so it doesn't have to be
	 * repeated in the function arguments for accesspress_option() too.
	 */
	$setting = $setting ? $setting : PREMISE_SETTINGS_FIELD;

	/** setup caches */
	static $settings_cache = array();
	static $options_cache = array();

	/** Short circuit */
	$pre = apply_filters( 'premise_pre_get_option_'.$key, false, $setting );
	if ( false !== $pre )
		return $pre;

	/** Check options cache */
	if ( isset( $options_cache[$setting][$key] ) ) {

		// option has been cached
		return $options_cache[$setting][$key];

	}

	/** check settings cache */
	if ( isset( $settings_cache[$setting] ) ) {

		// setting has been cached
		$options = apply_filters( 'premise_options', $settings_cache[$setting], $setting );

	} else {

		// set value and cache setting
		$options = $settings_cache[$setting] = apply_filters( 'premise_options', get_option( $setting ), $setting );

	}

	// check for non-existent option
	if ( ! is_array( $options ) || ! array_key_exists( $key, (array) $options ) ) {

		// cache non-existent option
		$options_cache[$setting][$key] = '';

		return '';
	}

	// option has been cached, cache option
	$options_cache[$setting][$key] = is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );

	return $options_cache[$setting][$key];

}
/**
 * This function redirects the user to an admin page, and adds query args
 * to the URL string for alerts, etc.
 *
 * @since 2.1.0
 */
function premise_admin_redirect( $page = '', $query_args = array() ) {

	if ( ! $page )
		return;

	$url = html_entity_decode( menu_page_url( $page, 0 ) );

	foreach ( (array) $query_args as $key => $value ) {
		if ( empty( $key ) && empty( $value ) ) {
			unset( $query_args[$key] );
		}
	}

	$url = add_query_arg( $query_args, $url );

	wp_redirect( esc_url_raw( $url ) );

}

