<?php
/**
 * This file controls the creation and references to stylesheets.
 *
 * @package Prose
 * @author StudioPress & Gary Jones
 * @since 0.9.7
 */

/**
 * Get the correct stylesheet location for URL or path, and for multisite or not.
 *
 * Takes account of multisite usage, and domain mapping.
 * @author Gary Jones
 * @param string $type Either 'url' or anything else e.g. 'path'
 * @return string
 * @since 0.9.7
 * @version 0.9.7.3
 */
function premise_get_stylesheet_location($type) {
	global $premise_base;
	$dir = ('url' == $type) ? $premise_base->get_css_url() : $premise_base->get_css_directory();

	return apply_filters('premise_get_stylesheet_location', $dir );
}

/**
 * Takes a stylesheet filename prefix, and appends '-X.css' where X is the
 * $blog_id if the $blog_id is greater than 1. Else adds '.css'.
 *
 * @author Ron Rennick & Gary Jones
 * @global int $blog_id
 * @param string $slug Filename prefix of the stylesheet, before '-X.css'
 * @return string
 */
function premise_get_stylesheet_name($slug='stylesheet', $key = 0) {
    global $blog_id;
    $id = '';
    if ( $blog_id > 1 ) {
        $id = '-' . $blog_id;
    }
	if ( $key != 0 ) {
		$key = "-{$key}";
	} else {
		$key = '';
	}

    return apply_filters( 'premise_get_stylesheet_name', $slug . $id . $key .'.css');
}

/**
 * Get the name of the generated combined minified stylesheet.
 *
 * Default filename is minified.css, although this is filterable via premise_get_minified_stylesheet_name.
 * @author Gary Jones
 * @return string
 * @since 0.9.7
 */
function premise_get_minified_stylesheet_name($key) {
    return apply_filters('premise_get_minified_stylesheet_name', premise_get_stylesheet_name('minified', $key));
}

/**
 * Get the name of the generated settings stylesheet.
 *
 * Default filename is settings.css, although this is filterable via premise_get_settings_stylesheet_name.
 * @author Gary Jones
 * @return string
 * @since 0.9.7
 */
function premise_get_settings_stylesheet_name($key) {
    return apply_filters('premise_get_settings_stylesheet_name', premise_get_stylesheet_name('settings', $key));
}

/**
 * Get the name of the custom stylesheet.
 *
 * Default filename is custom-style.css, although this is filterable via premise_get_custom_stylesheet_name.
 * @author Gary Jones
 * @return string
 * @since 1.0
 */
function premise_get_custom_stylesheet_name($key) {
    return apply_filters('premise_get_custom_stylesheet_name', premise_get_stylesheet_name('custom', $key));
}


/**
 * Get the file path of the minified stylesheet.
 *
 * @author Gary Jones
 * @return string
 * @since 0.9.7
 */
function premise_get_minified_stylesheet_path($key = 0) {
    return apply_filters('premise_get_minified_stylesheet_path', premise_get_stylesheet_location('path') . premise_get_minified_stylesheet_name($key));
}

/**
 * Get the file path of the settings stylesheet.
 *
 * @author Gary Jones
 * @return string
 * @since 0.9.7
 */
function premise_get_settings_stylesheet_path($key = 0) {
    return apply_filters('premise_get_settings_stylesheet_path', premise_get_stylesheet_location('path') . premise_get_settings_stylesheet_name($key));
}

function premise_get_settings_stylesheet_contents($key = 0) {
	return file_get_contents(premise_get_settings_stylesheet_path($key));
}

function premise_get_custom_buttons_stylesheet_path() {
	return apply_filters('premise_get_settings_stylesheet_path', premise_get_stylesheet_location('path') . 'custom-buttons.css');
}
function premise_get_custom_buttons_stylesheet_url() {
	return apply_filters('premise_get_settings_stylesheet_path', premise_get_stylesheet_location('url') . 'custom-buttons.css');
}

/**
 * Get the file path reference of the custom stylesheet.
 *
 * @author Gary Jones
 * @return string
 * @since 0.9.7
 * @version 1.0
 */
function premise_get_custom_stylesheet_path($key = 0) {
    return apply_filters('premise_get_custom_stylesheet_path', premise_get_stylesheet_location('path') . premise_get_custom_stylesheet_name($key));
}

/**
 * Get the URL reference of the minified stylesheet.
 *
 * @author Gary Jones
 * @return string
 * @since 0.9.7
 */
function premise_get_minified_stylesheet_url($key = 0) {
    return apply_filters('premise_get_minified_stylesheet_url', premise_get_stylesheet_location('url') . premise_get_minified_stylesheet_name($key));
}

/**
 * Get the URL reference of the settings stylesheet.
 *
 * @author Gary Jones
 * @return string
 * @since 0.9.7
 */
function premise_get_settings_stylesheet_url($key = 0) {
    return apply_filters('premise_get_settings_stylesheet_url', premise_get_stylesheet_location('url') . premise_get_settings_stylesheet_name($key));
}


/**
 * Get the URL reference of the custom stylesheet.
 *
 * @author Gary Jones
 * @return string
 * @since 0.9.7
 * @version 1.0
 */
function premise_get_custom_stylesheet_url($key = 0) {
    return apply_filters('premise_get_custom_stylesheet_url', premise_get_stylesheet_location('url') . premise_get_custom_stylesheet_name($key));
}

/**
 * Checks if custom stylesheet for this site has any content or not.
 *
 * @author Gary Jones
 * @link http://core.trac.wordpress.org/ticket/15025
 * @return boolean
 * @since 1.0
 */
function premise_is_custom_stylesheet_used( $key = 0 ) {

	if ( ! file_exists( premise_get_custom_stylesheet_path( $key ) ) )
	    return false;

        $css = file_get_contents( premise_get_custom_stylesheet_path( $key ) );

	// 1, not 0, as to create custom stylsheet, we have enter at least 1
	// (space) character, else get a PHP Notice if WP_DEBUG is true.
	return strlen( $css ) > 1;

}

/**
 * Get the custom stylesheet querystring for the theme editor link.
 *
 * @author Gary Jones
 * @global string $theme
 * @return string
 * @since 1.0
 */
function premise_get_custom_stylesheet_editor_querystring() {
    global $theme;
    if ( empty($theme) )
        $theme = get_current_theme();
    return 'file=' . _get_template_edit_filename(premise_get_custom_stylesheet_path(), dirname(premise_get_stylesheet_location('path'))) . '&amp;theme=' . urlencode($theme) . '&amp;dir=style';
}

/**
 * Loops through the mapping to prepare the CSS output.
 *
 * @author Gary Jones
 * @since 0.9.6
 * @return string $output Beautified CSS
 * @uses premise_get_mapping()
 * @version 1.0
 */
function premise_prepare_settings_stylesheet($key) {
	$mapping = premise_get_mapping();

	$output = array();
	foreach( $mapping as $selector => $declaration ) {
		if ( 'premise_custom_css' != $selector && 'minify_css' != $selector ) {
			$output[] = $selector . ' {';
			foreach ( $declaration as $property => $value ) {
				if ( strpos( $property, '_select' ) ) {
					if ( premise_get_fresh_design_option( $value, $key ) == 'hex' )
						continue;

					array_pop( $output );
					$property = substr( $property, 0, strlen( $property ) - 7 );
				}
				if( 'background-image' == $property ) {
					$imagekey = $value[1][0];
					$image = trim( premise_get_fresh_design_option( $imagekey, $key ) );
					if( '' == $image )
						continue;
				}
				$line = "\t" . $property . ':';
				if ( is_array( $value ) ) {
					foreach ( $value as $composite_value ) {
						$line .= ' ';
						$val = $composite_value[0];
						$type = $composite_value[1];
						if ( 'fixed_string' == $type ) {
							$line .= $val;
						} elseif ('string' == $type) {
							$line .=  premise_get_fresh_design_option( $val, $key );
						} else {
							$cache_val = premise_get_fresh_design_option( $val, $key );
							$line .= $cache_val;
							$line .= ( (int)$cache_val > 0 ) ? $type : null;
						}
					}
				} else {
	                		$line .= ' ' . premise_get_fresh_design_option( $value, $key );
	                	}

				$output[] = $line . ";";
			}
			$output[] = "}";
		} elseif ( 'premise_custom_css' == $selector ) {
			$output[] = premise_get_fresh_design_option( $declaration, $key );
		}
	}

	return apply_filters( 'premise_prepare_stylesheet', implode( "\n", $output ), $key );
}

/**
 * Calculates the width of the primary or secondary nav elements, or the child
 * UL elements, based on the border settings choices.
 *
 * @author Gary Jones
 * @param string $nav 'primary' or 'secondary'
 * @param boolean $ul True for getting width of child UL, false (default) for the (grand)parent element.
 * @return string
 * @since 1.0
 */
function premise_calculate_nav_width($nav, $ul = false) {
    $border = premise_get_fresh_design_option($nav . '_nav_border');
    $border_style = premise_get_fresh_design_option($nav . '_nav_border_style');
    if ( 'none' == $border_style )
        $border = 0;
    $width = 940 - 2 * $border;
    if ($ul) {
        $border = premise_get_fresh_design_option($nav . '_nav_inner_border');
        $border_style = premise_get_fresh_design_option($nav . '_nav_inner_border_style');
        if ( 'none' == $border_style )
            $border = 0;
        $width = $width - 2 * $border;
    }
    return ' ' . $width .'px';
}


/**
 * Try and make stylesheet directory writable. May not work if safe-mode or
 * other server configurations are enabled.
 *
 * @author Gary Jones
 * @since 1.0
 */
function premise_make_stylesheet_path_writable() {

	$stylesheet_path = premise_get_stylesheet_location('path');

	if ( ! is_dir( $stylesheet_path ) )
		mkdir( premise_get_stylesheet_location( 'path' ) );

	return is_writable( premise_get_stylesheet_location( 'path' ) );

}

/**
 * Uses the mapping output to write the beautified CSS to a file.
 *
 * @author Gary Jones
 * @since 0.9.6
 * @version 1.0
 */
function premise_create_settings_stylesheet( $key ) {
    premise_make_stylesheet_path_writable();

    $css = '/* ' . __( 'This file is auto-generated from the settings page. Any direct edits here will be lost if the settings page is saved', 'premise' ) . ' */'."\n";
    $css .= premise_prepare_settings_stylesheet( $key );
    $handle = @fopen( premise_get_settings_stylesheet_path( $key ), 'w' );
    @fwrite( $handle, $css );
    @fclose( $handle );
}

/**
 * Try to create custom stylesheet at the right place.
 *
 * @author Gary Jones
 * @param string $css Optional string of CSS to populate the custom stylesheet.
 * @since 1.0
 */
function premise_create_custom_stylesheet($css = ' ') {
    premise_make_stylesheet_path_writable();

    if ( !file_exists(premise_get_custom_stylesheet_path()) ||  ' ' != $css ) {
        $handle = @fopen(premise_get_custom_stylesheet_path(), 'w+');
        @fwrite($handle, $css);
        @fclose($handle);
        @chmod(premise_get_custom_stylesheet_path(), 0666);
    }
}

/**
 * Merges style.css, settings stylesheet and custom.css, then minifies it into
 * one minified.css file. Also creates individual beautified settings stylesheet
 * so they are in sync, and attempts to create custom stylesheet if it doesn't
 * exist.
 *
 * @author Gary Jones
 * @since 0.9.7
 * @version 1.0
 */
function premise_create_stylesheets() {
	premise_make_stylesheet_path_writable();

	global $premise_design_settings, $premise_base;
	$styles = $premise_design_settings->get_settings();
	$base_css = file_get_contents( $premise_base->get_theme_directory() . '/style.css' );
	$css_prefix = '/* ' . __( 'This file is auto-generated from the style.css, the settings page and custom.css. Any direct edits here will be lost if the settings page is saved', 'premise' ) .' */'."\n";
	
	foreach( $styles as $key => $style ) {

		$css = $base_css . premise_prepare_settings_stylesheet( $key );
		if ( premise_is_custom_stylesheet_used( $key ) )
			$css .= file_get_contents( premise_get_custom_stylesheet_path( $key ) );

		$css = $css_prefix . premise_minify_css( $css );
		
		$handle = @fopen( premise_get_minified_stylesheet_path( $key ), 'w' );
		@fwrite( $handle, $css );
		@fclose( $handle );
	
		premise_create_settings_stylesheet( $key );

	}
}

/**
 * Quick and dirty way to mostly minify CSS.
 *
 * @author Gary Jones
 * @param string $css String of CSS to minify.
 * @return string
 * @since 0.9.7
 */
function premise_minify_css($css) {
    // Normalize whitespace
    $css = preg_replace('/\s+/', ' ', $css);
    // Remove comment blocks, everything between /* and */, unless
    // preserved with /*! ... */
    $css = preg_replace('/\/\*[^\!](.*?)\*\//', '', $css);
    // Remove space after , : ; { }
    $css = preg_replace('/(,|:|;|\{|}) /', '$1', $css);
    // Remove space before , ; { }
    $css = preg_replace('/ (,|;|\{|})/', '$1', $css);
    // Strips leading 0 on decimal values (converts 0.5px into .5px)
    $css = preg_replace('/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css);
    // Strips units if value is 0 (converts 0px to 0)
    $css = preg_replace('/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css);
    // Converts all zeros value into short-hand
    $css = preg_replace('/0 0 0 0/', '0', $css);
    // Ensures image path is correct, if we're serving .css file from subfolder
    $css = preg_replace('/url\(([\'"]?)images\//', 'url(' . PREMISE_THEMES_URL . 'premise/images/', $css);
    return apply_filters('premise_minify_css', $css);
}