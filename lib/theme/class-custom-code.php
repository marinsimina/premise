<?php
/**
 * This file is a modifies version of the Prose Child Theme Custom Code editor (v 1.5).
 *
 * @author StudioPress
 */

/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the Custom Code page.
 *
 * @since 1.5.0
 */
class Premise_Custom_Code extends Premise_Admin_Boxes {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * @since 1.5.0
	 *
	 * @uses 'premise-custom' settings field key
	 *
	 */
	function __construct() {

		$page_id = 'premise-custom';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'premise-main',
				'page_title'  => __( 'Custom Code', 'premise' ),
				'menu_title'  => __( 'Custom Code', 'premise' ),
				'capability'  => 'unfiltered_html',
			),
		);

		$page_ops = array(
			'screen_icon' => 'themes',
			'save_button_text' => __( 'Save Changes', 'premise' ),
			'reset_button_text' => __( 'Reset All', 'premise' ),
		);

		$settings_field = 'premise-custom';
		$default_settings = array(
			'css' => "/** Do not remove this line. Edit CSS below. */\n",
			'php' => "<?php\n/** Do not remove this line. Edit functions below. */\n"
		);

		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

		/** Add a sanitizer/validator */
		add_filter( 'pre_update_option_' . $this->settings_field, array( $this, 'save' ), 10, 2 );

	}


	/**
	 * Load the necessary scripts for this admin page.
	 *
	 * @since 1.5.0
	 *
	 */
	function scripts() {

		/** Load parent scripts as well as Genesis admin scripts */
		parent::scripts();

	}

	/**
	 * Save the Custom CSS and PHP files
	 *
	 * This function highjacks the option on the way & saves the CSS/PHP to the custom file. We don't actually
	 * want to save the custom code to the DB, just to the files in the media folder.
	 *
	 * @param array $newvalue Values submitted from the design settings page.
	 * @param array $oldvalue Unused
	 * @return false
	 * @since 1.5.0
	 */
	function save( $newvalue, $oldvalue = '' ) {

		global $premise_design_settings;

		/** Permission check */
		if ( ! current_user_can( 'unfiltered_html' ) )
			return false;

		/** Don't load custom.php when trying to save custom.php */
		remove_action( 'after_setup_theme', 'premise_do_custom_php' );

		if ( premise_make_stylesheet_path_writable() ) {

			foreach( $premise_design_settings->get_settings() as $key => $style ) {

				/** Write CSS */
				$f = fopen( premise_get_custom_stylesheet_path( $key ), 'w+' );
				if ( $f !== FALSE ) {

					fwrite( $f, stripslashes( $newvalue['css'][$key] ) );
					fclose( $f );

					if ( ! isset( $style['premise_custom_css'] ) )
						continue;

					unset( $style['premise_custom_css'] );
					$premise_design_settings->update_settings( $style, $key );

				}
			}

			premise_create_stylesheets();

			/** Write PHP */
			premise_edit_custom_php( $newvalue['php'] );

		}

		/** Retain only the reset value, if necessary, otherwise just revert to defaults */
		if ( isset( $newvalue['reset'] ) )
			return wp_parse_args( $newvalue, $this->default_settings );
		else
			return $this->default_settings;

	}

	/**
	 * Add notices to the top of the page when certain actions take place.
	 *
	 * Add default notices via parent::notices() as well as a few custom ones.
	 *
	 * @since 1.5.0
	 *
	 */
	function notices() {

		/** Check to verify we're on the right page */
		if ( ! accesspress_is_menu_page( $this->page_id ) )
			return;

		/** Show error if can't write to server */
		if ( ! premise_make_stylesheet_path_writable() ) {

			if ( ! is_multisite() || is_super_admin() )
				$message = __( 'The %s folder does not exist or is not writeable. Please create it or <a href="http://codex.wordpress.org/Changing_File_Permissions">change file permissions</a> to 777.', 'premise' );
			else
				$message = __( 'The %s folder does not exist or is not writeable. Please contact your network administrator.', 'premise' );

			$css_path =  premise_get_stylesheet_location( 'path' );

			echo '<div id="message-unwritable" class="error"><p><strong>'. sprintf( $message, _get_template_edit_filename( $css_path, dirname( $css_path ) ) ) . '</strong></p></div>';
		}

		/** Genesis_Admin notices */
		parent::notices();

	}

	/**
 	 * Register meta boxes on the Custom Code page.
 	 *
 	 * @since 1.5.0
 	 *
 	 */
	function metaboxes() {

		add_meta_box( 'premise-custom-css', __( 'Custom CSS', 'premise' ), array( $this, 'custom_css' ), $this->pagehook, 'main' );
		add_meta_box( 'premise-custom-php', __( 'Custom Functions', 'premise' ), array( $this, 'custom_PHP' ), $this->pagehook, 'main' );

	}

	/**
	 * CSS to edit.
	 *
	 * @author StudioPress
	 * @since 1.5.0
	 */
	function custom_css() {

		global $premise_design_settings;

		foreach( $premise_design_settings->get_settings() as $key => $style ) {

			$custom_css = premise_is_custom_stylesheet_used( $key ) ? file_get_contents( premise_get_custom_stylesheet_path( $key ) ) : '';
			if ( strlen( $custom_css < 3 && isset( $style['premise_custom_css'] ) ) )
				$custom_css = $style['premise_custom_css'];

			printf( __( '<h4>Style: %1$s</h4>' , 'premise' ), esc_html( $style['premise_style_title'] ) );
			printf( '<textarea name="%1$s[%2$s]" id="%1$s[%2$s]" cols="80" rows="22">%3$s</textarea>', $this->get_field_name( 'css' ), $key, esc_textarea( $custom_css ) );

		}
	}

	/**
	 * PHP to edit.
	 *
	 * @author StudioPress
	 * @since 1.5.0
	 */
	function custom_php() {

		$php_file = premise_get_custom_php_path();
		$custom_php = is_file( $php_file ) ? file_get_contents( $php_file ) : '';

		printf( '<textarea name="%s" id="%s" cols="80" rows="22">%s</textarea>', $this->get_field_name( 'php' ), $this->get_field_id( 'php' ), esc_textarea( $custom_php ) );

	}
	function enqueue_admin_css() {

		wp_enqueue_style( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.css', array( 'thickbox' ), PREMISE_VERSION );

	}

}

add_action( 'premise_admin_init', 'premise_custom_code_menu' );
/**
 * Instantiate the class to create the menu.
 *
 * @author StudioPress
 * @since 1.5.0
 */
function premise_custom_code_menu() {

	global $_premise_custom_code, $premise_base;

	/** Don't add submenu items if Premise theme is disabled */
	$settings = $premise_base->get_settings();
	if ( empty( $settings['main']['theme-support'] ) )
		$_premise_custom_code = new Premise_Custom_Code;

}