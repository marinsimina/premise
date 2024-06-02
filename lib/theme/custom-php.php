<?php
/**
 * This file is a modifies version of the Prose Child Theme Custom Code editor (v 1.5).
 *
 * @author StudioPress
 * @since 1.5.0
 */

/**
 * Return the full path to the custom.php file for editing and inclusion.
 *
 * @uses prose_get_stylesheet_location()
 *
 * @since 1.5.0
 *
 */
function premise_get_custom_php_path() {

	return premise_get_stylesheet_location( 'path' ) . 'custom.php';

}

/**
 * Helper function that will create custom.php file, if it does not already exist.
 *
 * @uses prose_get_custom_php_path()
 *
 * @since 1.5.0
 *
 */
function premise_create_custom_php() {
	
	if ( file_exists( premise_get_custom_php_path() ) )
		return;
		
	$handle = @fopen( premise_get_custom_php_path(), 'w' );
	@fwrite( $handle, stripslashes( "<?php\n/** Do not remove this line. Edit functions below. */\n" ) );
	@fclose( $handle );
	
}

/**
 * Helper function that will create custom.php file, if it does not already exist.
 *
 * @uses prose_get_custom_php_path()
 *
 * @since 1.5.0
 *
 */
function premise_edit_custom_php( $text = '' ) {
	
	/** Create file, if it doesn't exist */
	if ( ! file_exists( premise_get_custom_php_path() ) )
		premise_create_custom_php();
	
	/** Now that it exists, write text to that file */
	$handle = @fopen( premise_get_custom_php_path(), 'w+' );
	@fwrite( $handle, stripslashes( $text ) );
	@fclose( $handle );
	
}

/**
 * PHP require the custom.php file, if it exists.
 *
 * @uses prose_get_custom_php_path()
 *
 * @since 1.5.0
 *
 */
if ( ! is_admin() && file_exists( premise_get_custom_php_path() ) )
	require_once( premise_get_custom_php_path() );
	
