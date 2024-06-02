<?php
/*
Plugin Name: Premise
Plugin URI: http://getpremise.com/
Description: Quickly and easily create amazing landing pages & membership areas for your WordPress site.
Version: 2.5.5
Author: Copyblogger Media LLC
Author URI: http://www.copyblogger.com
*/

/** Credits
 *
 * Some TinyMCE icons adapted from the Led Icon set – http://led24.de/iconset/
 */
if( !class_exists( 'Premise_Base' ) ) :
/*
The base class is used to fire up Premise
hooking into init allows selective loading of the components we need 
*/
class Premise_Base {

	var $_post_type = 'landing_page';
	var $_metakey_Duplicated = '_premise_landing_page_duplicated';
	var $_metakey_LandingPageType = '_premise_landing_page_type';
	var $_metakey_LandingPageAdviceType = '_premise_landing_page_advice_type';
	var $_metakey_SeoSettings = '_premise_seo_settings';
	var $_metakey_Settings = '_premise_settings';
	var $_metakey_Skin = '_premise_skin';
	var $_metakey_TrackingSettings = '_premise_tracking_settings';
	var $_option_ConfiguredButtons = '_premise_configured_buttons';
	var $_option_DesignSettings = '_premise_design_settings';

	var $_optin_AweberApplicationId = '3ca8152d';
	var $_optin_AweberAuthenticationUrl = 'https://auth.aweber.com/1.0/oauth/authorize_app/';
	var $_optin_aweber_list_cache = 'premise_aweber_list_cache';
	var $_optin_ConstantContactCachedMessages = null;
	var $_optin_ConstantContactHttpStatuses = array( 200 => 'Success - The request was successful', 201 => 'You have been subscribed.', 400 => 'Invalid Request - There are many possible causes for this error, but most commonly there is a problem with the structure or content of XML your application provided. Carefully review your XML. One simple test approach is to perform a GET on a URI and use the GET response as an input to a PUT for the same resource. With minor modifications, the input can be used for a POST as well.', 401 => 'Unauthorized - This is an authentication problem. Primary reason is that the API call has either not provided a valid API Key, Account Owner Name and Associated Password or the API call attempted to access a resource (URI) which does not match the same as the Account Owner provided in the login credientials.', 404 => 'URL Not Found - The URI which was provided was incorrect. Compare the URI you provided with the documented URIs. Start here.', 409 => 'Conflict - There is a problem with the action you are trying to perform. Commonly, you are trying to "Create" (POST) a resource which already exists such as a Contact List or Email Address that already exists. In general, if a resource already exists, an application can "Update" the resource with a "PUT" request for that resource.', 415 => 'Unsupported Media Type - The Media Type (Content Type) of the data you are sending does not match the expected Content Type for the specific action you are performing on the specific Resource you are acting on. Often this is due to an error in the content-type you define for your HTTP invocation (GET, PUT, POST). You will also get this error message if you are invoking a method (PUT, POST, DELETE) which is not supported for the Resource (URI) you are referencing. To understand which methods are supported for each resource, and which content-type is expected, see the documentation for that Resource.', 500 => 'Server Error', );
	var $_optin_ConstantContactKey = 'a1a2e20e-e5e9-4879-8a20-0339affa0c6a';
	var $_optin_ConstantContactMessages = '_premise_optin_constant_contact_messages';
	var $_optin_ConstantContactNumber = 1;
	var $_optin_Keys = array('aweber' => 'Aweber', 'mailchimp' => 'MailChimp', 'constant-contact' => 'Constant Contact', 'manual' => 'Other (Copy & Paste)');
	var $_optin_ManualBase = '_premise_optin_manual_form_';
	var $_optin_MailChimpCachedMessages = null;
	var $_optin_MailChimpMergeVars = array();
	var $_optin_MailChimpMessages = '_premise_optin_mailchimp_messages';
	var $_optin_MailChimpNumber = 1;

	var $_premise_theme_support;
	
	function __construct() {

		if ( ! defined( 'PREMISE_VERSION' ) ) {

			define( 'PREMISE_VERSION', '2.5.5' );
			define( 'PREMISE_SETTINGS_FIELD', $this->_metakey_Settings );
			define( 'PREMISE_DIR', plugin_dir_path( __FILE__ ) );
			define( 'PREMISE_URL', plugin_dir_url( __FILE__ ) );
			define( 'PREMISE_BASENAME', plugin_basename( __FILE__ ) );
			define( 'PREMISE_LIB_DIR', PREMISE_DIR . 'lib/' );
			define( 'PREMISE_THEMES_DIR', PREMISE_DIR . 'themes/' );
			define( 'PREMISE_THEMES_URL', PREMISE_URL . 'themes/' );
			define( 'PREMISE_VIEWS_DIR', PREMISE_DIR . 'views/' );
			define( 'PREMISE_RESOURCES_URL', PREMISE_URL . 'resources/' );
			define( 'PREMISE_POST_TYPE', 'landing_page' );
			define( 'PREMISE_MEMBER_DIR', PREMISE_DIR . 'member-access/' );
			define( 'PREMISE_MEMBER_INCLUDES_DIR', PREMISE_MEMBER_DIR . 'includes/' );

		}

		$settings = $this->get_settings();
		if ( ! empty( $settings['main']['member-access'] ) && '1' == $settings['main']['member-access'] ) {

			require_once( PREMISE_LIB_DIR . 'functions.php' );
			require_once( PREMISE_LIB_DIR . 'class-admin.php' );
			require_once( PREMISE_MEMBER_DIR . 'member-access.php' );

		}

		$this->_premise_theme_support = $settings['main']['theme-support'];

		add_filter( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		add_action( 'setup_theme', array( $this, 'register_post_type' ) );
		if( ! is_admin() )
			add_action( 'setup_theme', array( $this, 'setup_theme' ), 11 );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'update_option_' . $this->_option_DesignSettings, array( $this, 'create_stylesheets' ), 11, 2 );
		add_filter( 'post_type_link', array( $this, 'post_type_link'), 10, 3 );

		load_plugin_textdomain( 'premise', false, '/premise/languages/' );
	}
	/**
	 * Registers the landing page type such that administrative users can create and edit them and frontend visitors can view
	 * them at the appropriate URLs.
	 * @return void
	 */
	function register_post_type() {

		$labels = array(
			'name'			=> __( 'Landing Pages', 'premise' ),
			'singular_name'		=> __( 'Landing Page', 'premise' ),
			'add_new'		=> __( 'Add New', 'premise' ),
			'add_new_item'		=> __( 'Add New Landing Page', 'premise' ),
			'edit_item'		=> __( 'Edit Landing Page', 'premise' ),
			'new_item'		=> __( 'New Landing Page', 'premise' ),
			'view_item'		=> __( 'View Landing Page', 'premise' ),
			'search_items'		=> __( 'Search Landing Pages', 'premise' ),
			'not_found'		=> __( 'No landing pages found', 'premise' ),
			'not_found_in_trash'	=> __( 'No landing pages found in Trash', 'premise' ),
			'parent_item_colon'	=> null
		);

		$args = array(
			'labels' => $labels,
			'description' =>__('Landing pages are designed to increase conversions for your product.  Create them quickly and easily.', 'premise' ),
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'capability_type' => 'page',
			'hierarchical' => false,
			'public' => true,
			'rewrite' => false,
			'query_var' => true,
			'supports' => array( 'title', 'editor', 'revisions', 'custom-fields' ),
			'register_meta_box_cb' => array( &$this, 'register_meta_box_cb' ),
			'taxonomies' => array(),
			'show_ui' => true,
			'menu_position' => 950,
			'menu_icon' => PREMISE_RESOURCES_URL . 'images/icon-16x16-landing.png',
			'permalink_epmask' => EP_PAGES,
			'can_export' => true,
			'show_in_nav_menus' => true
		);

		register_post_type( $this->_post_type, $args );

	}

	function generate_rewrite_rules( $wp_rewrite ) {
		$settings = $this->get_settings();

		$rules = array();
		if( ( isset( $settings['main']['rewrite-root'] ) && $settings['main']['rewrite-root'] == 1 ) || empty( $settings['main']['rewrite'] ) ) {
			$rule_prefix = 'index.php?post_type=' . $this->_post_type . '&name=';
			foreach( $this->get_landing_page_uris() as $uri )
				$rules["{$uri}/?$"] = $rule_prefix . $uri;
		} else {
			$rules[$settings['main']['rewrite'].'/(.+)/?$'] = 'index.php?post_type=' . $this->_post_type . '&name=' . $wp_rewrite->preg_index( 1 );
		}

		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	}
	function get_landing_page_uris() {
		global $wpdb;
		$uris = $wpdb->get_col( $wpdb->prepare( "SELECT post_name FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'", $this->_post_type ) );
		$uris = array_filter( $uris );
		return $uris;
	}
	/** Super Hack so we can tell whether we should register the Prose theme as our chosen theme or not **/
	function setup_theme() {

		global $wp, $wp_filter, $premise_theme, $wp_version;

		$use_class = class_exists( 'WP_Hook' );
		$parse_request = isset( $wp_filter['parse_request'] ) ? $wp_filter['parse_request'] : null;
		$request = $wp_filter['request'];

		$wp_filter['parse_request'] = $use_class ? new WP_Hook() : array();
		$wp_filter['request'] = $use_class ? new WP_Hook() : array();
		$wp->parse_request();
		$wp_filter['parse_request'] = $parse_request;
		$wp_filter['request'] = $request;
		$front = get_option( 'show_on_front' );
		$id = null;
		if( 'page' == $front ) {
			$id = get_option( 'page_on_front' );
			$post = get_post( $id );
		}

		$is_premise_home = false;
		$is_premise_theme = ( !empty( $wp->query_vars[$this->_post_type] ) || ( isset( $wp->query_vars['post_type'] ) && $this->is_premise_post_type( $wp->query_vars['post_type'] ) ) );
		if( !$is_premise_theme )
			$is_premise_home = ( !empty( $post ) && $this->is_premise_post_type( $post->post_type ) && empty( $wp->query_vars ) );

		if( $is_premise_theme || $is_premise_home ) {

			require_once( PREMISE_LIB_DIR . 'theme/class-theme.php' );
			$premise_theme = new Premise_Theme( $is_premise_home, $id, $this->_premise_theme_support );

			if ( ! $this->_premise_theme_support ) {

				require_once( PREMISE_LIB_DIR . 'stylesheets.php' );
				require_once( PREMISE_LIB_DIR . 'theme/custom-php.php' );

			} else {

				add_action( 'template_redirect', array( $premise_theme, 'setup_wp_head' ) );

			}

			// add video css/script
			add_action( 'premise_immediately_after_head', array( $premise_theme, 'enqueue_video_scripts_css' ) );
		}

	}
	function init() {
		global $Premise;

		require_once( PREMISE_LIB_DIR . 'functions.php' );
		require_once( PREMISE_LIB_DIR . 'class-design-settings.php' );
		require_once( PREMISE_LIB_DIR . 'template-tags.php' );
		require_once( PREMISE_LIB_DIR . 'stylesheets.php' );
		require_once( PREMISE_LIB_DIR . 'design-settings.php' );
		require_once( PREMISE_LIB_DIR . 'design-settings-support.php' );


		if ( ! $this->_premise_theme_support ) {

			require_once( PREMISE_LIB_DIR . 'class-admin.php' );
			require_once( PREMISE_LIB_DIR . 'theme/class-custom-code.php' );
			require_once( PREMISE_LIB_DIR . 'theme/custom-php.php' );

		}

		if( is_admin() ) {

			require_once( PREMISE_LIB_DIR . 'class-admin.php' );
			require_once( PREMISE_VIEWS_DIR . 'settings/class-main-settings.php' );
			require_once( PREMISE_LIB_DIR . 'class-premise.php' );
			require_once( PREMISE_LIB_DIR . 'api/premise-api.php' );
			require_once( PREMISE_LIB_DIR . 'api/premise-api-provider.php' );
			require_once( PREMISE_LIB_DIR . 'api/premise-api-education-provider.php' );
			require_once( PREMISE_LIB_DIR . 'api/premise-api-graphics-provider.php' );
			$Premise = new Premise();

			require_once( PREMISE_LIB_DIR . 'premise-support.php' );
			require_once( PREMISE_LIB_DIR . 'export.php' );
			require_once( PREMISE_LIB_DIR . 'import.php' );

			// check for update
			add_action( 'admin_init' , array( $this, 'update' ), 99 );

			do_action( 'premise_admin_init' );

		}

		if( isset( $_REQUEST['social-share-ID'] ) || isset( $_REQUEST['clear-share-ID'] ) ) {
			require_once( PREMISE_LIB_DIR . 'class-social-share.php' );
			if( isset( $_REQUEST['clear-share-ID'] ) )
				Premise_Social_Share::clear_social_share( $_REQUEST['clear-share-ID'] );
			else
				Premise_Social_Share::handle_social_share( $_REQUEST['social-share-ID'] );
		}

		require_once( PREMISE_LIB_DIR . 'class-shortcode.php' );
		new Premise_Shortcodes();
	}
	function register_meta_box_cb( $post ) {
		require_once( PREMISE_LIB_DIR . 'class-meta-boxes.php' );
		$premise_meta_boxes = new Premise_Meta_boxes( $post );
	}
	function activate() {
		global $premise_base;
		// TODO RESET DESIGN SETTINGS IF THEY AREN'T PRESENT
		// flush rewrite rules
		if( !is_object( $premise_base ) )
			$premise_base = new Premise_Base();
			
		if( !post_type_exists( $premise_base->_post_type ) )
			$premise_base->register_post_type();
			
		flush_rewrite_rules( false );
	}
	function post_type_link( $link, $post, $leavename ) {
		if( $post->post_type != $this->_post_type )
			return $link;

		$permalink_structure = get_option( 'permalink_structure' );
		if( '' == $permalink_structure || ( !$leavename && ( empty( $post->post_name ) || $post->post_status != 'publish' ) ) )
			return add_query_arg( array( 'post_type' => $this->_post_type, 'p' => $post->ID ), home_url( '/' ) );

		$settings = $this->get_settings();
		if( ( isset( $settings['main']['rewrite-root'] ) && $settings['main']['rewrite-root'] ) || empty( $settings['main']['rewrite'] ) )
			$link = home_url( "/%landing_page%/" );
		else
			$link = home_url( "/{$settings['main']['rewrite']}/%landing_page%/" );

		if ( substr( $permalink_structure, -1 ) != '/' )
			$link = untrailingslashit( $link );

		if( $leavename )
			return $link;

		return str_replace( '%landing_page%', $post->post_name, $link );
	}

	function get_transient( $key, $cache ) {
		if( !isset( $this->$key ) )
			return array();
					
		if( !is_array( $this->$cache ) ) {
			$this->$cache = get_transient( $this->$key );
			delete_transient( $this->$key );
			if( !is_array( $this->$cache ) )
				$this->$cache = array();
		}

		return $this->$cache;
	}
	function get_autonumber( $key ) {
		if( !isset( $this->$key ) )
			return 1;

		return $this->$key++;
	}
	function is_premise_post_type( $type = '' ) {

		if( !$type )
			return is_singular( $this->_post_type );
			
		return ( $type == $this->_post_type );
	}
	function get_post_type() {
		return $this->_post_type;
	}
	function get_page_type( $post_id ) {
		return $this->get_premise_meta( $post_id, 'type' );
	}
	function get_advice_type( $post_id = null ) {
		global $post;
		if( empty( $post_id ) )
			$post_id = $post->ID;

		$meta = $this->get_premise_meta( $post_id, 'advice' );
		if( empty( $meta ) || is_array( $meta ) ) {
			$meta = $this->get_page_type( $post_id );
			$meta = str_replace( '.php', '', $meta );
		}
		return $meta;
	}
	function get_premise_meta( $post_id, $meta_key = '' ) {
		global $post;
		if( empty( $post_id ) )
			$post_id = $post->ID;

		$key = $this->get_meta_key( $meta_key );
		$settings = wp_cache_get( $key, $post_id );

		if( !$settings || !is_array( $settings ) ) {
			$settings = get_post_meta( $post_id, $key, true );
			wp_cache_set( $key, $settings, $post_id, time() + 24*60*60 );
		}
		return $settings;
	}
	function update_premise_meta( $post_id, $meta, $meta_key = '' ) {
		global $post;
		if( !is_array( $meta ) )
			return;

		if( empty( $post_id ) )
			$post_id = $post->ID;

		$key = $this->get_meta_key( $meta_key );
		update_post_meta( $post_id, $key, $meta );
		wp_cache_set( $key, $meta, $post_id, time() + 24*60*60 );
	}
	function get_meta_key( $meta_key = '' ) {
		switch( $meta_key ) {
			case 'seo':
				$key = $this->_metakey_SeoSettings;
				break;
			case 'type':
				$key = $this->_metakey_LandingPageType;
				break;
			case 'tracking':
				$key = $this->_metakey_TrackingSettings;
				break;
			case 'advice':
				$key = $this->_metakey_LandingPageAdviceType;
				break;
			case 'duplicate':
				$key = $this->_metakey_Duplicated;
				break;
			default:
				$key = $this->_metakey_Settings;
				break;
		}
		
		return $key;
	}
	function get_settings() {
		$settings = wp_cache_get( $this->_metakey_Settings );
		if( !$settings || !is_array( $settings ) ) {
			$settings = get_option( $this->_metakey_Settings, false );
			if( ! is_array( $settings ) )
				$settings = $this->get_default_settings();

			if( !isset( $settings['main'] ) || !is_array( $settings['main'] ) )
				$settings['main'] = array();

			if( empty( $settings['main']['rewrite'] ) )
				$settings['main']['rewrite'] = 'landing';

			if( !isset( $settings['optin']['allowed'] ) || !is_array( $settings['optin']['allowed'] ) )
				$settings['option']['allowed'] = array();

			wp_cache_set( $this->_metakey_Settings, $settings );
		}

		return $settings;
	}

	function update_settings( $settings ) {
		if( !is_array( $settings ) )
			return;

		update_option( $this->_metakey_Settings, $settings );
		wp_cache_set( $this->_metakey_Settings, $settings, null, time() + 24*60*60 );
	}

	function get_default_settings() {

		return array(

			'main' => array(
				'api-key' => '',
				'rewrite-root' => '',
				'rewrite' => 'landing',
				'member-access' => '',
				'default-favicon' => '',
				'default-header-image' => '',
				'default-header-image-alt' => '',
				'default-header-image-url' => '',
				'default-footer-text' => '',
				'theme-support' => '',
			),
			'seo' => array(
				'indicator' => '1',
				'noindex' => '',
				'nofollow' => '',
				'noarchive' => '',
				'disable-feed' => '',
			),
			'optin' => array(
				'aweber-authorization' => '',
				'aweber-api' => '',
				'aweber-enhanced' => '',
				'aweber-app-id' => '',
				'constant-contact-username' => '',
				'constant-contact-password' => '',
				'mailchimp-api' => '',
				'mailchimp-single-optin' => '',
				'allowed' => array(
					'aweber' => '',
					'mailchimp' => '',
					'constant-contact' => '',
				),
				'aweber-account-info' => '',
			),
			'tracking' => array(
				'account-id' => '',
				'vwo-account-id' => '',
			),
			'scripts' => array(
				'header' => '',
				'footer' => '',
			),
			'sharing' => array(
				'type' => '',
				'twitter-consumer-key' => '',
				'twitter-consumer-secret' => '',
				'facebook-app-id' => '',
				'facebook-app-secret' => '',
			),
		);
	}
	function have_premise_seo() {

		$settings = $this->get_settings();
		return ( ! isset( $settings['seo']['indicator'] ) || '1' == $settings['seo']['indicator'] );

	}
	function get_social_share_icon( $post_id, $type ) {
		$meta = $this->get_premise_meta( $post_id );
		$icon = trim( $meta[$type . '-share-button'] );
		if( empty( $icon) )
			$icon = PREMISE_RESOURCES_URL . "images/{$type}-share.png";

		return $icon;
	}
	function get_css_directory() {
		$info = wp_upload_dir();
		return trailingslashit($info['basedir']).'premise/';
	}

	function get_css_url() {
		$info = wp_upload_dir();
		return trailingslashit($info['baseurl']).'premise/';
	}

	function get_theme_directory() {
		return PREMISE_THEMES_DIR . 'premise';
	}
	function create_stylesheets() {
		require_once( PREMISE_LIB_DIR . 'stylesheets.php' );
		premise_create_stylesheets();
	}
	function get_favicon( $post_id ) {
		global $premise_theme;
		$favicon = $this->get_premise_meta_item( $post_id, 'favicon' );
		if( !empty( $favicon ) )
			return $favicon;

		$settings = $this->get_settings();
		$favicon = trim($settings['main']['default-favicon']);
		if( !empty( $favicon ) )
			return $favicon;
		
		foreach( array( 'stylesheet', 'template' ) as $dir ) {
			$theme_dir = '_original_' . $dir;
			$root = get_theme_root( $premise_theme->$theme_dir ) . '/' . $premise_theme->$theme_dir . '/';
			if( file_exists( $root . 'favicon.ico' ) )
				return get_theme_root_uri( $premise_theme->$theme_dir ) . '/' . $premise_theme->$theme_dir . '/favicon.ico';
		}

		return $favicon;
	}
	function save_configured_buttons_stylesheet( $newvalue, $oldvalue ) {
		$css = '';
		if( is_array( $newvalue ) ) {
			foreach( $newvalue as $key => $button )
				$css .= strip_tags( $this->get_button_code( $button, $key ) );
		}

		$handle = @fopen( premise_get_custom_buttons_stylesheet_path(), 'w' );
		@fwrite( $handle, premise_minify_css( $css ) );
		@fclose( $handle );

		return $newvalue;
	}
	function get_configured_buttons() {
		$buttons = get_option( $this->_option_ConfiguredButtons );
		if( !is_array( $buttons ) )
			return array();

		return $buttons;
	}

	function get_button_code( $button, $key = null ) {
		ob_start();
		include( PREMISE_VIEWS_DIR . 'misc/button-code.php' );
		return ob_get_clean();
	}
	function RGB2hex( $color ){
		$color = str_replace( '#', '', $color );
		if( strlen( $color ) != 6 )
	    		return array(0,0,0);

		$rgb = array();
		for( $x = 0; $x < 3; $x++ )
			$rgb[$x] = hexdec( substr( $color, ( 2 * $x ), 2 ) );

		return implode( ',', $rgb );
	}
	function get_mailchimp_merge_vars( $id ) {
		require_once( PREMISE_LIB_DIR . 'mailchimp_api/MCAPI.class.php' );

		$settings = $this->get_settings();
		$mailchimp = new MCAPI( $settings['optin']['mailchimp-api'] );

		if( !isset( $this->_optin_MailChimpMergeVars[$id] ) ) {
			$vars = get_transient( 'mailchimp_merge_vars_' . $id );
			if( !is_array( $vars ) ) {
				$vars = $mailchimp->listMergeVars( $id );
				set_transient( 'mailchimp_merge_vars_' . $id, $vars, 1*60*60 );
			}
			$this->_optin_MailChimpMergeVars[$id] = $vars;
		}

		return $this->_optin_MailChimpMergeVars[$id];
	}

	function validate_mailchimp_key( $key ) {
		require_once( PREMISE_LIB_DIR . 'mailchimp_api/MCAPI.class.php' );

		$mailchimp = new MCAPI( $key );
		if( !$mailchimp->ping() )
			return array( 'error' => __( 'Invalid MailChimp API key.', 'premise' ) );

		return true;
	}
	/*
	Constant Contact
	*/
	function setup_constant_contact( $apikey, $username, $password, $action = 'ACTION_BY_CONTACT' ) {
		require_once( PREMISE_LIB_DIR . 'constant_contact_api/constant_contact_api.php' );
		CCUtility::$sapikey = $apikey;
		CCUtility::$susername = $username;
		CCUtility::$spassword = $password;
		CCUtility::$saction = $action;
	}
	function validate_constant_contact_credentials( $username, $password ) {
		require_once( PREMISE_LIB_DIR . 'constant_contact_api/constant_contact_api.php' );

		$this->setup_constant_contact( $this->_optin_ConstantContactKey, $username, $password );

		$utility = new CCUtility();
		$result = $utility->ping();

		if( !empty( $result['error'] ) )
			return array( 'error' => __( 'Invalid Constant Contact credentials.', 'premise' ) );

		return true;
	}
	/*
	AWeber
	*/
	function get_aweber_lists( $force_api = false ) {

		if ( ! $force_api ) {

			$lists = get_option( $this->_optin_aweber_list_cache );
			if ( $lists )
				return $lists;
			
		}

		$account = $this->get_aweber_account();
		$settings = $this->get_settings();

		$list_web_forms = array();
		if ( $account ) {

			foreach ($account->getWebForms() as $this_webform) {

				$link_parts = explode('/', $this_webform->url);
				$list_id = $link_parts[4];
				$account_id = $link_parts[2];
				if (!array_key_exists($list_id, $list_web_forms)) {
					$list_web_forms[$list_id] = array(
						'web_forms' => array(),
						'split_tests' => array()
					);
				}
				$list_web_forms[$list_id]['web_forms'][] = $this_webform;

			}

			foreach ($account->getWebFormSplitTests() as $this_webform) {
				$link_parts = explode('/', $this_webform->url);
				$list_id = $link_parts[4];
				if (!array_key_exists($list_id, $list_web_forms)) {
					$list_web_forms[$list_id] = array(
						'web_forms' => array(),
						'split_tests' => array()
					);
				}
				$list_web_forms[$list_id]['split_tests'][] = $this_webform;
			}

			$lists = $account->lists;

			foreach ($lists as $this_list) {

				if ( ! array_key_exists( $this_list->id, $list_web_forms ) ) {
					$list_web_forms[$this_list->id] = array(
						'web_forms' => array(),
						'split_tests' => array()
					);
				}

				if ( isset( $settings['optin']['aweber-api'] ) && $settings['optin']['aweber-api'] )
					$list_web_forms[$this_list->id]['custom_fields_url'] = $this_list->custom_fields_collection_link;

				$list_web_forms[$this_list->id]['list'] = $this_list;

			}

		}

		$return = array();
		foreach($list_web_forms as $id => $data) {
			$item = array('id' => $id, 'name' => $data['list']->name, 'forms' => array());
			foreach($data['web_forms'] as $web_form) {
				$item['forms'][] = array('id' => $web_form->id, 'url' => $web_form->url, 'name' => $web_form->name);
			}
			foreach($data['split_tests'] as $split_test) {
				$item['forms'][] = array('id' => $split_test->id, 'url' => $split_test->url, 'name' => sprintf(__('Split Test: %s', 'premise' ), $web_form->name));
			}

			if ( isset( $data['custom_fields_url'] ) )
				$item['custom_fields_url'] = $data['custom_fields_url'];

			$return[] = $item;
		}

		update_option( $this->_optin_aweber_list_cache, $return );

		return $return;
	}
	function refresh_aweber_list_custom_fields( $list_id ) {

		$lists = $this->get_aweber_lists();
		// no lists
		if ( empty( $lists ) )
			return 0;

		$the_list = false;
		foreach( $lists as $id => $list ) {

			if ( $list['id'] == $list_id ) {

				$the_list = $id;
				break;

			}
		}

		// list not found
		if ( $the_list === false )
			return 0;

		// has already been refreshed
		if ( empty( $lists[$the_list]['custom_fields_url'] ) )
			return array( 'list_name' => $lists[$the_list]['name'] );

		// get the custom fields
		$account = $this->get_aweber_account();
		$custom_fields = $account->loadFromUrl( $lists[$the_list]['custom_fields_url'] );
		unset( $lists[$the_list]['custom_fields_url'] );
		if ( $custom_fields )
			$lists[$the_list]['custom_fields'] = $custom_fields->data['entries'];
		
		update_option( $this->_optin_aweber_list_cache, $lists );

		return array( 'list_name' => $lists[$the_list]['name'] );

	}
	function initialize_aweber() {

		require_once( PREMISE_LIB_DIR . 'aweber_api/aweber_api.php' );

	}
	function get_aweber_account() {

		$this->initialize_aweber();
		$settings = $this->get_settings();

		$info = $settings['optin']['aweber-account-info'];
		$aweber = new AWeberAPI($info['consumer_key'], $info['consumer_secret']);

		try {
			$account = $aweber->getAccount($info['access_key'], $info['access_secret']);
		} catch (AWeberException $e) {
			$account = null;
		}

		return $account;	
	
	}
	function get_aweber_list( $list_id ) {

		foreach( $this->get_aweber_lists() as $list ) {

			if ( $list['id'] == $list_id )
				return $list;

		}

		return false;

	}

	function get_aweber_authorization_url( $app_id ) {

		return $this->_optin_AweberAuthenticationUrl . ( $app_id ? $app_id : $this->_optin_AweberApplicationId );

	}

	/// LANDING PAGE TEMPLATE TAG DELEGATES

	function add_google_analytics_script() {

		$settings = $this->get_settings();
		if ( empty( $settings ) || empty( $settings['tracking'] ) || empty( $settings['tracking']['account-id'] ) )
			return;

		$accountId = $settings['tracking']['account-id'];
		
		$pattern = '/^([0-9]+)$/';
		$pattern2 = '/^(UA-)?([0-9]+)(-([0-9]{1-3}))?$/';
		if( preg_match( $pattern, $accountId, $matches ) )
			$accountId = "UA-{$accountId}-2";
		elseif( preg_match( $pattern2, $accountId, $matches ) && ! isset( $matches[3] ) )
				$accountId = "{$accountId}-2";

		if( $accountId )
			include( PREMISE_VIEWS_DIR . 'gce/tracking.php' );
	}

	function get_premise_meta_item( $post_id, $item_key, $meta_key = '' ) {
		$meta = $this->get_premise_meta( $post_id, $meta_key );

		if( isset( $meta[$item_key] ) )
			return $meta[$item_key];

		return null;
	}
	//// GENERAL

	function getFooterCopy( $post_id ) {
		$copy = $this->get_premise_meta_item( $post_id, 'footer-copy' );

		if( empty( $copy ) ) {
			$settings = $this->get_settings();
			$copy = trim( $settings['main']['default-footer-text'] );
		}

		return $copy;
	}

	function getHeaderCopy( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'header-copy' );
	}

	function getHeaderImage( $post_id ) {

		return $this->get_header_image_field( $post_id, 'header-image' );

	}
	function get_header_image_alt( $post_id ) {

		return $this->get_header_image_field( $post_id, 'header-image-alt' );

	}
	function get_header_image_new_window( $post_id ) {

		return (bool)$this->get_premise_meta_item( $post_id, 'header-image-new' );

	}
	function get_header_image_url( $post_id ) {

		return $this->get_header_image_field( $post_id, 'header-image-url' );

	}

	function get_header_image_field( $post_id, $field ) {

		$image_field = trim( $this->get_premise_meta_item( $post_id, $field ) );
		if( empty( $image_field ) ) {
			$settings = $this->get_settings();
			$image_field = trim( $settings['main']['default-' . $field] );
		}

		return $image_field;

	}

	function shouldHaveFooter( $post_id ) {
		return 1 != $this->get_premise_meta_item( $post_id, 'footer' );
	}

	function shouldHaveHeader( $post_id ) {
		return 1 != $this->get_premise_meta_item( $post_id, 'header' );
	}

	function shouldHaveHeaderImage( $post_id ) {
		return 1 != $this->get_premise_meta_item( $post_id, 'header-image-hide' );
	}

	function use_premise_theme() {
		return empty( $this->_premise_theme_support );
	}
	//// CONTENT SCROLLER

	function getContentScrollers( $post_id ) {
		$scrollers = $this->get_premise_meta_item( $post_id, 'content-scrollers' );

		if( empty( $scrollers ) || !is_array( $scrollers ) )
			$scrollers = array( array( 'title'=>__( 'Tab 1', 'premise' ), 'text'=>'' ) );

		return $scrollers;
	}

	function getContentScrollerShowTabs( $post_id ) {
		$meta = $this->get_premise_meta_item( $post_id, 'show-arrows' );

		return $meta == 'tabs' || $meta == '' || $meta == 'arrows-and-tabs';
	}

	function getContentScrollerShowArrows( $post_id ) {
		$meta = $this->get_premise_meta_item( $post_id, 'show-arrows' );

		return $meta == '' || $meta == 'arrows-and-tabs';
	}

	//// Sales Page

	function getSubhead( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'subhead' );
	}

	//// OPT-IN

	function getOptinAlign( $post_id ) {
		$meta = $this->get_premise_meta_item( $post_id, 'optin-placement' );

		if( empty( $meta ) )
			return 'left';

		return $meta;
	}

	function getOptinCopy( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'optin-copy' );
	}

	function getOptinBelowCopy( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'below-optin-copy' );
	}

	function getOptinFormCode( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'optin-form-code' );
	}

	//// VIDEO

	function getVideoAlign( $post_id ) {
		$meta = $this->get_premise_meta_item( $post_id, 'video-placement' );

		if( empty( $meta ) )
			return 'left';

		return $meta;
	}

	function getVideoCopy( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'video-copy' );
	}

	function getVideoBelowCopy( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'below-video-copy' );
	}

	function getVideoEmbedCode( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'video-embed-code' );
	}

	function getVideoImage( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'video-image' );
	}

	function getVideoImageTitle( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'video-image-title' );
	}

	//// PRICING

	function getAbovePricingTableContent( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'above-pricing-table-copy' );
	}

	function getBelowPricingTableContent( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'below-pricing-table-copy' );
	}

	function getPricingBulletMarker( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'pricing-bullets' );
	}

	function getPricingBulletColor( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'pricing-bullets-color' );
	}

	function getPricingColumns( $post_id ) {
		$columns = $this->get_premise_meta_item( $post_id, 'pricing-columns' );

		if( empty( $columns ) || !is_array( $columns ) )
			$columns = array( array( 'title' => __( 'Basic Plan', 'premise' ), 'attributes' => array( '' ) ) );

		return $columns;
	}

	/// SOCIAL SHARE

	function hasSharedPage($postId = null) {
		if(empty($postId)) {
			global $post;
			$postId = $post->ID;
		}

		return isset( $_COOKIE['premise-social-share-'.$postId] ) && $_COOKIE['premise-social-share-'.$postId] == base64_encode( md5( 'premise-social-share-check-' . $postId ) );
	}

	function getSocialShareMessage( $post_id ) {
		$shareMessage = $this->get_premise_meta_item( $post_id, 'sharing-message' );

		if( empty( $shareMessage ) )
			$shareMessage = __('Tweet or Share this page to see the rest of the content.', 'premise' );

		return $shareMessage;
	}

	function getSocialShareTeaserPage( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'teaser-page' );
	}

	function getSocialShareAfterSharePage( $post_id ) {
		return $this->get_premise_meta_item( $post_id, 'after-a-share-page' );
	}

	function getSocialShareTwitterText( $post_id, $link = false ) {
		$twitterShareText = $this->get_premise_meta_item( $post_id, 'twitter-share-text' );

		if( empty( $twitterShareText ) )
			$twitterShareText = get_the_title( $post_id );

		if( $link )
			$twitterShareText = "{$twitterShareText} - " . get_permalink( $post_id );

		return $twitterShareText;
	}

	function getSocialShareTwitterIcon( $post_id ) {
		return $this->get_social_share_icon( $post_id, 'twitter' );
	}

	function getSocialShareFacebookIcon( $post_id ) {
		return $this->get_social_share_icon( $post_id, 'facebook' );
	}

	function getSocialShareType() {
		$settings = $this->get_settings();
		$sharing = $settings['sharing'];
		return intval($sharing['type']);
	}
	function get_social_share_method( $post_id = null ) {
		return $this->get_premise_meta_item( $post_id, 'share-method' );
	}


	// update database function
	function update() {

		$settings = $this->get_settings();
		$version = isset( $settings['version'] ) ? $settings['version'] : '2.1';
		// stop if the version is already the current one 
		if ( $version == PREMISE_VERSION )
			return;

		// 2.2
		if ( version_compare( $version, '2.2', '<=' ) ) {

			$links = get_option( 'member-access-links' );
			if ( empty( $links ) ) 
				return $this->update_settings_version();

			foreach( $links as $hash => $link ) {

				$post = array(
					'post_name' => sanitize_title_with_dashes( $link['name'] ),
					'post_title' => $link['name'],
					'post_type' => 'acp-links',
					'post_status' => 'publish',
				);
				if ( ! empty ( $link['access-levels'] ) ) {

					$post['tax_input'] = array( 'acp-access-level' => array() );
					foreach( $link['access-levels'] as $level ) {

						$access_level = get_term( $level, 'acp-access-level' );
						if ( $access_level && ! is_wp_error( $access_level ) )
							$post['tax_input']['acp-access-level'][] = $access_level->slug;

					}
				}

				$post_id = wp_insert_post( $post );
				if ( $post_id ) {

					update_post_meta( $post_id, '_acp_link_id', $hash );
					update_post_meta( $post_id, '_acp_link_filename', $link['filename'] );
					update_post_meta( $post_id, '_acp_link_delay', $link['delay'] );

				}
			}
			delete_option( 'member-access-links' );
		} // 2.2

		if ( version_compare( $version, '2.4', '<=' ) )
			$this->create_stylesheets();

		$this->update_settings_version();

	}
	private function update_settings_version() {

		$settings = $this->get_settings();
		$settings['version'] = PREMISE_VERSION;
		$this->update_settings( $settings );

		return true;
		
	}

}
endif;

global $premise_base;

$premise_base = new Premise_Base();

register_activation_hook( __FILE__, array( 'Premise_Base', 'activate' ) );
