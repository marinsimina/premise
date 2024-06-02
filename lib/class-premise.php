<?php
/** Credits
 *
 * Some TinyMCE icons adapted from the Led Icon set – http://led24.de/iconset/
 */

class Premise {

	var $_cached_DesignPageSlug = '';
	var $_cached_FrontPageId = null;
	var $_cached_NumberPluginColumns = 1;

	var $_data_EditorNumber = 0;
	var $_data_PremiseApi = null;
	var $_data_TinyMCESelectors = array();
	var $_data_RegisteredLandingPages = array();
	var $_data_LandingPageIds = array(
		'long-copy' => 1,
		'content-landing-page'=> 4,
		'pricing' => 5,
		'opt-in' => 6,
		'video'=> 7,
		'content-scroller' => 9,
		'thank-you' => 12,
		'social-share' => 13,
	);
	var $_data_PluginSlug = 'getpremise';
	var $_data_PluginInfoURL = 'http://api.getpremise.com/plugin/';
	var $_data_PluginURL = 'http://getpremise.com/';
	var $_data_VersionTransientName = 'premise_version_info';
	var $_data_VersionURL = 'http://api.getpremise.com/version/';

	var $_metakey_Duplicated = '_premise_landing_page_duplicated';
	var $_metakey_LandingPageType = '_premise_landing_page_type';
	var $_metakey_LandingPageAdviceType = '_premise_landing_page_advice_type';
	var $_metakey_SeoSettings = '_premise_seo_settings';
	var $_metakey_Settings = '_premise_settings';
	var $_metakey_Skin = '_premise_skin';
	var $_metakey_TrackingSettings = '_premise_tracking_settings';

	var $_optin_AweberApplicationId = '3ca8152d';
	var $_optin_AweberAuthenticationUrl = 'https://auth.aweber.com/1.0/oauth/authorize_app/';
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

	var $_option_ConfiguredButtons = '_premise_configured_buttons';
	var $_option_PinnedStatus = '_premise_pinned_status';
	var $_option_DesignSettings = '_premise_design_settings';
	var $_option_SettingsTransient = '_premise_settings_transient';
	var $_option_CurrentVersion = '_premise_current_version';

	var $_type_LandingPage = 'landing_page';

	var $_is_premise_beta;

	/// INITIALIZERS

	function Premise() {
		$this->addActions();
		$this->addFilters();
		
		$this->initialize();
		$this->initializeApi();
		$this->initializeLandingPages();

		$this->_is_premise_beta = preg_match( '|[a-zA-Z]+|', PREMISE_VERSION );
	}

	function addActions()  {
		add_action('admin_head',			array( $this, 'processDuplicateDisplay' ), 100 );
		add_action('admin_init',			array( $this, 'addTinyMCEButtons' ) );
		add_action('admin_init',			array( $this, 'processSubmissions' ) );
		add_action('admin_menu',			array( $this, 'addAdministrativeInterfaceItems' ) );
		add_action('admin_menu',			array( $this, 'modifyAdministrativeInterfaceItems' ), 1010 );
		add_action('admin_notices',			array( $this, 'addNoticeIfPremiseCannotGenerateCSS' ) );
		add_action('load-update-core.php',		array( $this, 'deleteUpdateTransient' ) );
		add_action('manage_posts_custom_column',	array( $this, 'outputLandingPageTypeColumn' ), 10, 2 );
		add_action('save_post',				array( $this, 'saveLandingPageMeta' ), 10, 2 );
		add_action('switch_theme',			array( $this, 'switch_theme' ) );
		
		add_action('wp_ajax_premise_save_button',	array( $this, 'saveButtonViaAjax' ) );
		add_action('wp_ajax_premise_use_graphic',	array( $this, 'useGraphic' ) );
		add_action('wp_ajax_premise_sample_copy',	array( $this, 'retrievePageTypeSampleCopy' ) );
		add_action('wp_ajax_premise_optin',		array( $this, 'retrieveOptInCode' ) );
		add_action('wp_ajax_premise_pinned',		array( $this, 'modifyPinnedStatus' ) );
		add_action('wp_ajax_premise_get_lists',		array( $this, 'ajaxOptinGetLists' ) );
		add_action('wp_ajax_premise_save_optin_manual',	array( $this, 'ajaxOptinSaveFormCode' ) );
		add_action('wp_ajax_premise_refresh_aweber_list', array( $this, 'refresh_aweber_list' ) );

		/**
		 * The following actions control the popup thickbox that allows for searching
		 * and using premise resources.
		 */
		add_action('media_upload_premise-button-create',	array( $this, 'displayPremiseResourcesThickbox' ) );
		add_action('media_upload_premise-button-usage',		array( $this, 'displayPremiseResourcesThickbox' ) );
		add_action('media_upload_premise-resources-education',	array( $this, 'displayPremiseResourcesThickbox' ) );
		add_action('media_upload_premise-resources-graphics',	array( $this, 'displayPremiseResourcesThickbox' ) );
		add_action('media_upload_premise-resources-optin',	array( $this, 'displayPremiseResourcesThickbox' ) );

		/**
		 * This next little part is totally hacky so that we can
		 * make sure people select a Landing Page Type before a
		 * landing page is actually created.
		 *
		 * This is a limitation of the WordPress plugin system, but keeps
		 * it relatively tucked away so that we can replace it in the
		 * future if necessary.
		 */
		add_action('admin_notices',	array( $this, 'possiblyStartOutputBuffering' ) );
		add_filter('wp_comment_reply',	array( $this, 'possiblyEndOutputBuffering' ) );

		add_action('after_plugin_row',	array( $this, 'addPluginSupportRow' ) );
	}

	function addFilters() {

		/// PLUGINS
		add_filter('transient_update_plugins',					array( $this, 'checkForUpdate' ) );
		add_filter('site_transient_update_plugins',				array( $this, 'checkForUpdate' ) );
		add_filter('manage_plugins_columns',					array( $this, 'saveNumberPluginColumns' ), 100000 );
		add_filter('manage_plugins-network_columns',				array( $this, 'saveNumberPluginColumns' ), 100000 );
		add_filter('plugin_action_links_' . PREMISE_BASENAME,			array( $this, 'addSupportLinkForPremise' ) );
		add_filter('network_admin_plugin_action_links_' . PREMISE_BASENAME,	array( $this, 'addSupportLinkForPremise' ) );
		add_filter('plugins_api',						array( $this, 'overridePluginsAPIResults'), 10, 3 );

		// EDITOR
		add_filter('attachment_fields_to_edit',				array( $this, 'modifyAttachmentFields' ), 11, 2 );
		add_filter('manage_edit-'.$this->_type_LandingPage.'_columns',	array( $this, 'addColumnHeaderForPageType' ) );
		add_filter('screen_layout_columns',				array( $this, 'modifyLayoutColumns' ), 10, 2 );
		add_filter('tiny_mce_version',					array( $this, 'registerTinyMCEScriptOutput' ) );

		// MISC
		add_filter('premise_get_optin_form_code', 'do_shortcode');
		add_filter('pre_update_option_'.$this->_option_DesignSettings,		array( $this, 'filterDesignSettings' ), 10, 2 );
		add_filter('pre_update_option_'.$this->_option_ConfiguredButtons,	array( $this, 'saveConfiguredButtonsStylesheet' ), 10, 2);
		add_filter('site_transient_theme_roots',				array( $this, 'modifyThemeRootsTransient' ) );
		add_filter('wp_dropdown_pages',						array( $this, 'addPageToReadingStaticPageDropdown' ) );

	}

	function initialize() {
		$currentVersion = get_option($this->_option_CurrentVersion);
		if($currentVersion != PREMISE_VERSION) {
			if(empty($currentVersion)) {
				// We need to delete transients for the mailchimp merge vars
				global $wpdb;
				$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%mailchimp_merge_vars_%'");
			}
			
			update_option($this->_option_CurrentVersion, PREMISE_VERSION);
		}
	}

	function initializeApi( $key = null ) {
		if ( ! $key ) {

			$settings = $this->getSettings();
			$key = isset( $settings['main']['api-key'] ) ? $settings['main']['api-key'] : '';

		}
		$this->_data_PremiseApi = new Premise_API( $key );
	}

	function convertLandingPageToId($advice) {
		return $this->_data_LandingPageIds[$advice];
	}

	function initializeLandingPages() {
		$this->addLandingPageType('long-copy', __('Sales Page', 'premise' ), __('A simple sales page.', 'premise' ), 'long-copy');
		$this->addLandingPageType('long-copy', __('Content Landing Page', 'premise' ), __('Content based landing page.', 'premise' ), 'content-landing-page');
		$this->addLandingPageType('pricing', __('Pricing', 'premise' ), __('Pricing table landing page.', 'premise' ), 'pricing');
		$this->addLandingPageType('opt-in', __('Opt In', 'premise' ), __('Landing page with opt-in form.', 'premise' ), 'opt-in');
		$this->addLandingPageType('video', __('Video', 'premise' ), __('Landing page with video.', 'premise' ), 'video');
		$this->addLandingPageType('content-scroller', __('Tab Scroller', 'premise' ), __('Landing page with multiple tabs on content scroller.', 'premise' ), 'content-scroller');
		$this->addLandingPageType('long-copy', __('Thank You', 'premise' ), __('Say thanks to your customers after they take action.', 'premise' ), 'thank-you');
		$this->addLandingPageType('social-share', __('Social Share', 'premise' ), __('Prompt your customers to share this page before allowing them to have access to all the content.', 'premise' ));
	}

	function addLandingPageType($template, $name, $description, $advice = '') {
		if(empty($advice)) {
			$advice = $template;
		}

		if(!isset($this->_data_RegisteredLandingPages[$advice])) {
			$data = array('template' => $template, 'name' => $name, 'description' => $description, 'advice' => $advice);
			$this->_data_RegisteredLandingPages[$advice] = $data;
		}
	}

	function get_landing_page_type( $type, $field = '' ) {

		if ( ! isset( $this->_data_RegisteredLandingPages[$type] ) )
			return false;

		if ( ! $field || ! isset( $this->_data_RegisteredLandingPages[$type][$field] ) )
			return $this->_data_RegisteredLandingPages[$type];

		return $this->_data_RegisteredLandingPages[$type][$field];
	}

	/// FUNCTIONAL CALLBACKS

	//// OPT IN AJAX

	function ajaxOptinGetLists() {
		$data = stripslashes_deep($_POST);

		$lists = array();
		switch($data['provider']) {
			case 'aweber':
				$lists = $this->getAweberLists();
				break;
			case 'mailchimp':
				$lists = $this->getMailChimpLists();
				break;
			case 'constant-contact':
				$lists = $this->getConstantContactLists();
				break;
		}

		echo json_encode($lists);
		exit();
	}

	function ajaxOptinSaveFormCode() {
		$data = stripslashes_deep($_POST);

		$id = time();
		$key = $this->_optin_ManualBase.$id;

		if(!get_option($key, false)) {
			add_option($key, $data['code']);
			echo json_encode(array('error' => false, 'id' => $id));
		} else {
			echo json_encode(array('error' => true));
		}

		exit();
	}

	//// OTHER
	
	function addPluginSupportRow( $pluginFile ) {

		if( $pluginFile != PREMISE_BASENAME )
			return;

		$count = $this->_cached_NumberPluginColumns;

		if ( ( $valid = $this->has_valid_premise_api_key() ) )
			include( PREMISE_VIEWS_DIR . 'misc/plugin-support-row.php' );

	}
	
	function addSupportLinkForPremise( $actions ) {

//@todo: add translation support
		if ( $this->_is_premise_beta )
			$actions['support'] = '<a href="#" id="premise-support-dropdown">Report Bug</a>';
		else
			$actions['support'] = '<a href="#" id="premise-support-dropdown">Get Support</a>';

		return $actions;
	}
	
	function saveNumberPluginColumns($columns) {
		$this->_cached_NumberPluginColumns = count($columns);
		return $columns;
	}

	function has_valid_premise_api_key() {

		global $premise_base;

		$settings = $premise_base->get_settings();
		$valid = false;
		if( isset( $settings['main'] ) && !empty( $settings['main']['api-key'] ) ) {
			$version = $this->getVersionInfo();
			$valid = ( $version && isset( $version->is_valid ) && $version->is_valid );
		}

		return $valid;

	}

	function switch_theme() {

		global $premise_base;

		// don't do anything if theme support not enabled
		if ( $premise_base->use_premise_theme() )
			return;

		$settings = $premise_base->get_settings();
		$settings['main']['theme-support'] = '';
		$premise_base->update_settings( $settings );
		
	}
	/**
	 * This callback adds the appropriate settings menu and submenus for Premise
	 * plugin administration.
	 * @return void
	 */
	function addAdministrativeInterfaceItems() {

		global $premise_base;
		
		$hooks = array('post-new.php','post.php');

		if ( $premise_base->use_premise_theme() ) {

			$hooks[] = add_submenu_page('premise-main', __('Style Settings', 'premise' ), __('Style Settings', 'premise' ), 'manage_options', 'premise-styles', array( $this, 'displayPremiseSettingsPage'));

			$styleTitle = __('Add Style', 'premise' );
			if( isset( $_GET['page'] ) && 'premise-style-settings' == $_GET['page'] && isset( $_GET['premise-design-key'] ) && $this->isValidStyleKey( $_GET['premise-design-key'] ) )
				$styleTitle = __('Edit Style', 'premise' );

				$design = add_submenu_page('premise-main', $styleTitle, $styleTitle, 'manage_options', 'premise-style-settings', array(&$this, 'displayPremiseSettingsPage'));

			$this->_cached_DesignPageSlug = $design;
			$hooks[] = $design;
	
			add_action("load-{$design}", array(&$this, 'settingsBoxes'));
			add_action("admin_print_styles-{$design}", array(&$this, 'enqueueAdministrativeResourcesForDesignPage'));

		}

		$hooks[] = add_submenu_page('premise-main', __('Button Settings', 'premise' ), __('Button Settings', 'premise' ), 'manage_options', 'premise-buttons', array( $this, 'displayPremiseSettingsPage'));

		$help_title = $this->_is_premise_beta ? __('Report Bug', 'premise' ) :__('Premise Help', 'premise' );
		$hooks[] = $premiseHelp = add_submenu_page('premise-main', $help_title,  $help_title, 'manage_options', 'premise-help', array(&$this, 'displayPremiseSettingsPage'));
		add_action("load-{$premiseHelp}", array(&$this, 'redirectToPremiseHelpPage'));

		foreach($hooks as $hook) {
			add_action("admin_print_styles-{$hook}", array(&$this, 'enqueueAdministrativeResourcesForAdminPages'));
		}

		add_action('admin_enqueue_scripts', array(&$this, 'possiblyEnqueueAdministrativeResourcesForAdminPages'));
	}

	function addColumnHeaderForPageType($columns) {
		$new = array('landing-page-type'=>__('Landing Page Type', 'premise' ));
		$columns = array_slice($columns, 0, 2, true) + $new + array_slice($columns, 2, count($columns), true);
		return $columns;
	}

	function addNoticeIfPremiseCannotGenerateCSS() {
		$directory = premise_get_stylesheet_location( 'path' );
		if( !file_exists( $directory ) )
			@mkdir($directory, 0755, true);

		if( isset( $_GET['page'] ) && $_GET['page'] == 'premise-style-settings' && !is_writeable( $directory ) )
			include( PREMISE_VIEWS_DIR . 'misc/not-writeable-notice.php' );
		
		if( isset( $_GET['premise-sent-support-request'] ) && $_GET['premise-sent-support-request'] == 'yes' )
			include( PREMISE_VIEWS_DIR . 'misc/sent-support-request-admin-notice.php' );
	}

	function addPageToReadingStaticPageDropdown( $page_html ) {

		global $typenow;

		$is_page_on_front = 'page_on_front' == substr( $page_html, 14, 13 );
		$is_member_access_settings = 'member-access-settings' == substr( $page_html, 14, 22 );
		$is_product_settings = isset( $typenow ) && $typenow == 'acp-products';

		if (! $is_page_on_front && ! $is_member_access_settings && ! $is_product_settings )
			return $page_html;				

		$selected = false;
		$page_html = str_replace( '</select>', '', $page_html );
		$selected = 0;
		if ( $is_page_on_front )
			$selected = get_option( 'page_on_front' );
		elseif ( $is_product_settings )
			$selected = accesspress_get_custom_field( '_acp_product_thank_you' );
		elseif ( preg_match( '|member-access-settings\[([a-z_]+)\]|', $page_html, $m ) )
			$selected = accesspress_get_option( $m[1] );
		
		$landings = get_posts( array( 'post_type' => $this->_type_LandingPage, 'nopaging' => true, 'post_status' => 'publish' ) );
		foreach($landings as $landing) {
			$page_html .= sprintf( "<option %s class=\"level-0\" value=\"%d\">%s</option>\n",
				selected( $selected, $landing->ID, false ),
				$landing->ID,
				apply_filters( 'the_title', $landing->post_title )
			);
		}
		$page_html .= '</select>';
		return $page_html;
	}

	function addTinyMCEButtons() {
		if(user_can_richedit()) {
			add_filter("mce_external_plugins", array(&$this, 'addPremiseTinyMCEPlugin'));
			add_filter('mce_buttons_3', array(&$this, 'addPremiseTinyMCEPluginButtons'));
		}
	}

	function addPremiseTinyMCEPlugin($plugin_array) {
		global $pagenow, $post;
		if(in_array($pagenow, array('post.php','post-new.php')) && $post->post_type == $this->_type_LandingPage) {
			$plugin_array['PremiseInfo'] = PREMISE_RESOURCES_URL . 'tmce/editor_plugin.js';
		}
		return $plugin_array;
	}

	function addPremiseTinyMCEPluginButtons($buttons) {
		global $pagenow, $post;
		if(in_array($pagenow, array('post.php','post-new.php')) && $post->post_type == $this->_type_LandingPage) {
			array_push($buttons, 'PremiseSampleCopy');
			array_push($buttons, 'PremiseInsertGraphic');
			array_push($buttons, 'PremiseInsertOptIn');
			array_push($buttons, 'PremiseInsertNoticeBox');
			array_push($buttons, 'PremiseInsertButton');
			array_push($buttons, '|');

		}
		return $buttons;
	}

	function checkForUpdate( $option ) {
		$info = $this->getVersionInfo();

		if( !$info )
			return $option;

		if( !isset( $option->response[PREMISE_BASENAME] ) || !is_object( $option->response[PREMISE_BASENAME] ) )
			$option->response[PREMISE_BASENAME] = new stdClass();

		//Empty response means that the key is invalid. Do not queue for upgrade
		if( !is_object( $info ) || !$info->is_valid || version_compare( PREMISE_VERSION, $info->version, '>=' ) ) {
			unset( $option->response[PREMISE_BASENAME] );
		} else {
			$settings = $this->getSettings();
			$option->response[PREMISE_BASENAME]->url = $this->_data_PluginURL;
			$option->response[PREMISE_BASENAME]->slug = $this->_data_PluginSlug;
			$option->response[PREMISE_BASENAME]->package = add_query_arg( array( 'apikey' => $settings['main']['api-key'] ), $info->url );
			$option->response[PREMISE_BASENAME]->new_version = $info->version;
			$option->response[PREMISE_BASENAME]->id = "0";
		}

		return $option;
	}
	
	function deleteUpdateTransient() {
		delete_transient($this->_data_VersionTransientName);
	}

	/**
	 * This callbacks ensures that the Premise specific CSS and JS are loaded on the appropriate pages.
	 * @return void
	 */
	function enqueueAdministrativeResourcesForAdminPages() {
		global $pagenow, $post;
		if(in_array($pagenow, array('post.php', 'post-new.php')) && $post->post_type != $this->_type_LandingPage) {
			return;
		}

		global $editor_styles;
		$editor_styles = array();

		wp_enqueue_style( 'premise_custom_buttons', premise_get_custom_buttons_stylesheet_url(), array(), time() );
		wp_enqueue_style( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.css', array( 'thickbox' ), PREMISE_VERSION );
		wp_enqueue_script( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.js', array( 'jquery', 'editor', 'thickbox' ), PREMISE_VERSION, true );
		wp_localize_script( 'premise-admin', 'Premise', array('tabs_warning'=>5, 'graphics_title' => __('Premise Graphics', 'premise' ), 'optin_title' => __('Premise Opt In', 'premise' ), 'button_title' => __('Premise Buttons', 'premise' )));
	}

	function enqueueAdministrativeResourcesForDesignPage() {
		wp_enqueue_style( 'farbtastic' );

		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'premise-design', PREMISE_RESOURCES_URL . 'premise-design.js', array( 'farbtastic', 'thickbox' ), PREMISE_VERSION );
		$params = array( 'pageHook' => $this->_cached_DesignPageSlug, 'firstTime' => !is_array(get_user_option('closedpostboxes_'.$this->_cached_DesignPageSlug)), 'toggleAll' => __('Toggle All', 'premise' ), 'warnUnsaved' => __('The changes you made will be lost if you navigate away from this page.', 'premise' ), 'warnReset' => __('Are you sure you want to reset?', 'premise' ) );
		wp_localize_script('premise-design', 'PremiseDesign', $params);
	}

	function filterDesignSettings( $newvalue, $oldvalue ) {
		$defaults = $this->getDefaultDesignSettings();
		foreach ( (array)$newvalue as $akey => $array ) {
			if( is_array( $array ) ) {
				foreach( $array as $key => $value ) {
					if ( '' == $value // Empty
					|| ( preg_match( '/_color$/', $key ) && !preg_match( '/^#(([a-fA-F0-9]{3}$)|([a-fA-F0-9]{6}$))/', $value ) ) // Invalid color code
					|| ( ( strpos( $key, '_size' ) || strpos( $key, '_height' ) || strpos( $key, '_margin' ) || strpos( $key, '_padding' ) || strpos( $key, '_radius' ) || strpos( $key, '_width' ) || preg_match( '/_border$/', $key ) )
					&& !ctype_digit( $value ) ) // Not a digit
					) {
						$newvalue[$akey][$key] = isset( $defaults[$key] ) ? $defaults[$key] : ''; // Save default value instead.
					}
				}
			}
		}
		return $newvalue;
	}

	function generateRewriteRules($wp_rewrite) {
		global $premise_base;
		$premise_base->generate_rewrite_rules();
	}

	function getPremiseThickboxTabs($vals) {
		return array('premise-resources-education'=>__('Education', 'premise' ), 'premise-resources-graphics'=>__('Graphics', 'premise' ));
	}

	function modifyAdministrativeInterfaceItems() {
		global $menu, $submenu;

		$menu['55.98'] = array( '', 'read', 'separator-premise', '', 'wp-menu-separator' );
		$menu['56.4'] = $menu[950];
		unset($menu[950]);
	}

	function modifyAttachmentFields($form_fields, $post) {
		$html = '<a href="'.$post->guid.'" class="send-to-premise-field">'.__('Use This Image', 'premise' ).'</a>';

		$form_fields = $form_fields + array('send-to-premise-field' => array('tr' => '<tr style="display: none;" class="send-to-premise-field"><td></td><td>'.$html.'</td></tr>'));

		return $form_fields;
	}

	function modifyLayoutColumns($columns, $screen) {
		if ($screen == $this->_cached_DesignPageSlug) {
			$columns[$this->_cached_DesignPageSlug] = 2;
		}
		return $columns;
	}

	function modifyPinnedStatus() {
		$this->savePinnedStatus($_POST['pinned'] == 'true');
		exit();
	}

	function modifyThemeRootsTransient( $roots ) {
		if( !isset( $roots['premise'] ) )
			return false;

		return $roots;
	}

	function notifyOfDuplicate() {
		global $post;
		$duplicate = get_post_meta($post->ID, $this->_metakey_Duplicated, true);
		include( PREMISE_VIEWS_DIR . 'misc/duplicate-notice.php' );
		delete_post_meta($post->ID, $this->_metakey_Duplicated);
	}


	function outputLandingPageTypeColumn( $column, $postId ) {
		global $premise_base;
		
		if( $column != 'landing-page-type' )
			return;

		$type = $premise_base->get_advice_type( $postId );
		$type_name = $this->get_landing_page_type( $type, 'name' );

		echo esc_html( $type_name ? $type_name : $type );
	}

	function outputMediaButton() {
		global $post, $premise_base;
		if( $premise_base->is_premise_post_type( $post->post_type ) )
			printf( '<a href="%s" class="%s thickbox" title="%s"><img src="%s" alt="%s" /></a>', esc_url( premise_get_media_upload_src( 'premise-resources-graphics' ) ), 'add_premise_resources', __('Premise Graphics', 'premise' ), PREMISE_RESOURCES_URL . 'images/icon-desat.gif', __( 'Premise Graphics', 'premise' ) );
	}

	function overridePluginsAPIResults($res, $action, $args) {
		if('plugin_information' == $action && $this->_data_PluginSlug == $args->slug) {
			$settings = $this->getSEttings();
			$url = add_query_arg(array('apikey' => $settings['main']['api-key']), $this->_data_PluginInfoURL);
			$response = wp_remote_get($url);

			if(!is_wp_error($response) && 200 == wp_remote_retrieve_response_code($response)) {
				$res = json_decode(wp_remote_retrieve_body($response));
				$sections = array();
				if(is_string($res->sections)) {
					$res->sections = array('Description' => $res->sections);
				} elseif(is_object($res->sections)) {
					foreach($res->sections as $name => $content) {
						$sections[$name] = $content;
					}

					$res->sections = $sections;
				}
			}
		}

		return $res;
	}

	function possiblyEnqueueAdministrativeResourcesForAdminPages($hook) {
		wp_enqueue_script( 'premise-menu', PREMISE_RESOURCES_URL . 'premise-menu.js', array( 'jquery' ), PREMISE_VERSION );
		if('media-upload-popup' == $hook) {
			$this->enqueueAdministrativeResourcesForAdminPages();
			include( PREMISE_VIEWS_DIR . 'misc/media-upload-popup-script.php' );
		}
	}

	function possiblyEndOutputBuffering() {
		global $pagenow;
		if($pagenow == 'post-new.php' && $_GET['post_type'] == $this->_type_LandingPage) {
			$result = ob_get_clean();
			remove_action( 'admin_print_footer_scripts', array( '_WP_Editors', 'editor_js'), 50 );
			include( PREMISE_VIEWS_DIR . 'interceptions/post-new.php' );
		}
	}

	function possiblyStartOutputBuffering() {
		global $pagenow;
		if($pagenow == 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == $this->_type_LandingPage) {
			ob_start();
			add_post_type_support( $this->_type_LandingPage, 'comments' );
		}
	}

	function premiseButtonOutput($atts, $content = null) {
		$atts = array_map('trim', $atts);
		$id = $atts['id'];
		
		if(!empty($atts['href'])) {
			$code = '<a href="'.$atts['href'].'" class="premise-button-%s">%s</a>';
		} else {
			$code = '<span class="premise-button-%s">%s</span>';
		}
		
		return sprintf($code, $id, $content);
	}

	/**
	 * Intercepts values in the administrative interface to do various actions such as:
	 * - Saving settings
	 * - Setting the landing page type for a particular landing page
	 *
	 * @return void
	 */
	function processSubmissions() {
		if(isset($_GET['landing_page_set']) && $_GET['landing_page_set'] == 1 && wp_verify_nonce($_GET['_wpnonce'], 'landing_page_set')) {
			$this->setDefaultLandingPageMeta($_GET['post']);
			$this->setLandingPageType($_GET['post'], $_GET['landing_page']);
			$this->setLandingPageAdviceType($_GET['post'], $_GET['landing_page_advice']);
			wp_redirect(get_edit_post_link($_GET['post'], 'raw'));
			exit();
		}
		if((isset($_POST['premise-design'])) && wp_verify_nonce($_POST['save-premise-design-settings-nonce'], 'save-premise-design-settings')) {
			$message = $this->processPremiseDesignSettings();
			wp_redirect(admin_url('admin.php?page=premise-style-settings&'.$message));
			exit();
		}
		if ( isset( $_GET['premise-design-key'] ) && isset( $_GET['premise-delete-style'] ) && $_GET['premise-delete-style'] == 'true' && check_admin_referer('premise-delete-style')) {
			$this->deleteConfiguredStyle($_GET['premise-design-key']); 
			wp_redirect(admin_url('admin.php?page=premise-styles&deleted=true'));
			exit();
		} elseif(isset($_GET['premise-duplicate-style']) && $_GET['premise-duplicate-style'] == 'true' && $this->isValidStyleKey($_GET['premise-design-key']) && check_admin_referer('premise-duplicate-style')) {
			$style = $this->getConfiguredStyle($_GET['premise-design-key']);
			$style['premise_style_title'] .= (' - '.__('Copy', 'premise' ));
			$style['premise_style_timesaved'] = current_time('timestamp');
			$this->saveDesignSettings($style, null);
			
			wp_redirect(admin_url('admin.php?page=premise-styles&duplicated=true'));
			exit();
		} elseif(isset($_GET['premise-button-id']) && isset( $_GET['premise-delete-button'] ) && $_GET['premise-delete-button'] == 'true' && check_admin_referer('premise-delete-button')) {
			$this->deleteConfiguredButton($_GET['premise-button-id']);
			wp_redirect(admin_url('admin.php?page=premise-buttons&deleted=true'));
			exit();
		} elseif(isset($_GET['premise-button-id']) && isset( $_GET['premise-duplicate-button'] ) && $_GET['premise-duplicate-button'] == 'true' && check_admin_referer('premise-duplicate-button')) {
			$this->duplicateConfiguredButton($_GET['premise-button-id']);
			wp_redirect(admin_url('admin.php?page=premise-buttons&duplicated=true'));
			exit();
		}

		$data = stripslashes_deep($_POST);
		if(isset($data['premise-support-submit-request']) && isset($data['premise-support-submit-request-nonce']) && wp_verify_nonce($data['premise-support-submit-request-nonce'], 'premise-support-submit-request')) {
				
			$support = $data['premise-support'];
			
			$PremiseSupport = new Premise_Support;
			$PremiseSupport->sendSupportRequest( $support );
			
			wp_redirect(add_query_arg(array('plugin_status' => $data['plugin_status'], 'paged' => $data['paged'], 'premise-sent-support-request' => 'yes'), admin_url('plugins.php')));
			exit();
		}
	}

	function processDuplicateDisplay() {
		global $pagenow, $post, $premise_base;
		if($pagenow == 'post.php' && $post->post_type == $this->_type_LandingPage) {
			$duplicated = get_post_meta($post->ID, $this->_metakey_Duplicated, true);
			if($duplicated) {
				add_action('admin_notices', array(&$this, 'notifyOfDuplicate'));
			}

			$use_premise_seo = $premise_base->have_premise_seo();
			include( PREMISE_VIEWS_DIR . 'misc/scribe-override.php' );
		}
	}

	function redirectToPremiseHelpPage() {
		wp_redirect('https://members.getpremise.com/help.aspx');
		exit;
	}

	function retrieveOptInCode() {
		$settings = $this->getSettings();
		$results = array('error' => true, 'code' => '');
		if(!empty($settings['main']['opt-in-code'])) {
			$results = array('error' => false, 'code' => $settings['main']['opt-in-code']);
		}
		echo json_encode($results);
		exit();
	}

	function refresh_aweber_list() {

		global $premise_base;

		$data = stripslashes_deep( $_REQUEST );
		$args = wp_parse_args( $data, array(
			'nonce' => '',
			'list_id' => ''
		) );

		if ( ! wp_verify_nonce( $args['nonce'], 'premise-refresh-aweber-list' ) || ! $args['list_id'] )
			exit( 0 );

		echo json_encode( $premise_base->refresh_aweber_list_custom_fields( $args['list_id'] ) );
		exit;

	}
	function retrievePageTypeSampleCopy() {
		$copy = $this->retrieveSampleCopyForPage($_POST['id']);

		if(empty($copy)) {
			$results = array('error' => true, 'copy' => '', 'error_message' => __('Could not retrieve sample copy for this page.  Please check your Premise API key.', 'premise' ));
		} else {
			$results = array('error' => false, 'copy' => $copy);
		}

		echo json_encode($results);
		exit();
	}

	function registerTinyMCEScriptOutput($version) {
		$this->_data_PreviouslyOutputTinyMCEScriptTags = true;

		return $version;
	}
	
	function saveButtonViaAjax() {
		$data = stripslashes_deep($_POST);
		
		if(isset($data['premise-save-button-nonce']) && wp_verify_nonce($data['premise-save-button-nonce'], 'premise-save-button')) {
			$key = $data['premise-button-id'];
			$button = $data['button-editing'];
			
			$this->saveConfiguredButton($button, $key);
		}
		
		exit;
	}

	function saveLandingPageMeta( $postId, $post ) {
		global $premise_base;

		if( ( false === wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) && $premise_base->is_premise_post_type( $post->post_type ) ) {
			$this->processLandingPageTypeSpecificMeta( $postId, $post );
			$this->processPremiseSeoMeta( $postId, $post );
			$this->processPremiseTrackingMeta( $postId, $post );
			$this->processDuplication( $postId, $post );

			$settings = $premise_base->get_settings();
			if( !isset( $settings['main'] ) || ( isset( $settings['main']['rewrite-root'] ) && $settings['main']['rewrite-root'] ) || empty( $settings['main']['rewrite'] ) )
				flush_rewrite_rules();
		}
	}
	
	function settingsBoxes() {
		add_meta_box('premise-settings-global', __('Global Styles', 'premise' ), 'premise_settings_global', $this->_cached_DesignPageSlug, 'column1');
		add_meta_box('premise-settings-global-links', __('Global Links', 'premise' ), 'premise_settings_global_links', $this->_cached_DesignPageSlug, 'column1');
		add_meta_box('premise-settings-wrap', __('Wrap (content area)', 'premise' ), 'premise_settings_wrap', $this->_cached_DesignPageSlug, 'column1');
		add_meta_box('premise-settings-header', __('Header', 'premise' ), 'premise_settings_header', $this->_cached_DesignPageSlug, 'column1');
		add_meta_box('premise-settings-headline-area', __('Main Headline Area', 'premise' ), 'premise_settings_headline_area', $this->_cached_DesignPageSlug, 'column1');
		add_meta_box('premise-settings-blockquotes', __('Blockquotes', 'premise' ), 'premise_settings_blockquotes', $this->_cached_DesignPageSlug, 'column1');
		add_meta_box('premise-settings-notice', __('Notice Box', 'premise' ), 'premise_settings_notice_box', $this->_cached_DesignPageSlug, 'column1');
		add_meta_box('premise-settings-pricing', __('Pricing', 'premise' ), 'premise_settings_pricing', $this->_cached_DesignPageSlug, 'column1');

		add_meta_box('premise-settings-headline', __('In-Page Headlines', 'premise' ), 'premise_settings_headline', $this->_cached_DesignPageSlug, 'column2');
		add_meta_box('premise-settings-footer', __('Footer', 'premise' ), 'premise_settings_footer', $this->_cached_DesignPageSlug, 'column2');
		add_meta_box('premise-settings-input', __('Input Boxes', 'premise' ), 'premise_settings_input_box', $this->_cached_DesignPageSlug, 'column2');
		add_meta_box('premise-settings-buttons', __('Submit Buttons', 'premise' ), 'premise_settings_buttons', $this->_cached_DesignPageSlug, 'column2');
		add_meta_box('premise-settings-content-scroller', __('Content Scroller', 'premise' ), 'premise_settings_content_scroller', $this->_cached_DesignPageSlug, 'column2');
		add_meta_box('premise-settings-video', __('Video', 'premise' ), 'premise_settings_video', $this->_cached_DesignPageSlug, 'column2');
		add_meta_box('premise-settings-optin', __('Opt In', 'premise' ), 'premise_settings_optin', $this->_cached_DesignPageSlug, 'column2');
		add_meta_box('premise-settings-social-share', __('Social Share', 'premise' ), 'premise_settings_social_share', $this->_cached_DesignPageSlug, 'column2');
		add_meta_box('premise-settings-general', __('General Settings', 'premise' ), 'premise_settings_general', $this->_cached_DesignPageSlug, 'column2');
	}

	function useGraphic() {
		$data = stripslashes_deep($_POST);

		$id = $data['slug'];
		$name = $data['name'];
		$filename = $data['filename'];
		$info = $this->getGraphic($id);

		if(is_wp_error($info)) {
			echo json_encode(array('error'=>true, 'error_message'=> $info->get_error_message()));
		} else {
			$upload = wp_upload_bits($filename, false, $info);
			if(is_wp_error($upload)) {
				echo json_encode(array('error'=>true,'error_message'=>$upload->get_error_message()));
			} elseif(is_array($upload) && isset($upload['error']) && $upload['error']) {
				echo json_encode(array('error' => true, 'error_message' => $upload['error']));
			} else {
				list($iinfo['width'], $iinfo['height']) = getimagesize($upload['file']);
				$iinfo['full_url'] = $upload['url'];
				$iinfo['error'] = false;
				$iinfo['html'] = sprintf('<img src="%s" alt="%s" width="%d" height="%d" />', $iinfo['full_url'], $name, $iinfo['width'], $iinfo['height']);
				echo json_encode($iinfo);
			}
		}
		exit();
	}

	/// OAUTH
	
	function includeTwitterOAuthLibrary() {
		require_once( PREMISE_LIB_DIR . 'twitteroauth/twitteroauth.php' );
	}
	
	/// PROCESSING

	function processDuplication($postId, $post) {
		global $premise_base;

		if( !$premise_base->is_premise_post_type( $post->post_type ) )
			return;

		$data = stripslashes_deep( $_POST );
		if( empty( $data['premise-duplicate-page'] ) )
			return;

		remove_action( 'save_post', array( &$this, 'saveLandingPageMeta' ), 10, 2 );

		$duplicate = $post;
		$duplicate->ID = '';
		$duplicate->post_title .= __(' Copy', 'premise' );
		$duplicate->post_status = 'draft';
		$duplicate_name = $post->post_name ? $post->post_name : $post->post_title;
		$duplicate->post_name = wp_unique_post_slug( $duplicate_name, 1, 'publish', $post->post_type, $post->post_parent );
		$duplicateId = wp_insert_post( $duplicate );

		update_post_meta($duplicateId, $premise_base->get_meta_key( 'advice' ), $premise_base->get_advice_type( $postId ) );
		update_post_meta($duplicateId, $premise_base->get_meta_key( 'type' ), $premise_base->get_page_type( $postId ) );
		update_post_meta($duplicateId, $premise_base->get_meta_key( 'seo' ), $premise_base->get_premise_meta( $postId, 'seo' ) );
		update_post_meta($duplicateId, $premise_base->get_meta_key(), $premise_base->get_premise_meta( $postId ) );
		update_post_meta($duplicateId, $premise_base->get_meta_key( 'tracking' ), $premise_base->get_premise_meta( $postId, 'tracking' ) );
		update_post_meta($postId, $premise_base->get_meta_key( 'duplicate' ), $duplicateId );
		
		add_action( 'save_post', array(&$this, 'saveLandingPageMeta' ), 10, 2 );
	}

	function processLandingPageTypeSpecificMeta( $postId, $post ) {
		global $premise_base;

		if( !$premise_base->is_premise_post_type( $post->post_type ) )
			return;

		$data = stripslashes_deep($_POST);
		if( ! isset( $data['premise'] ) || ! is_array( $data['premise'] ) || ! isset( $data['save-premise-settings-nonce'] ) || ! wp_verify_nonce( $data['save-premise-settings-nonce'], 'save-premise-settings' ) )
			return;

		$premise = $data['premise'];
		$premise['footer'] = isset( $premise['footer'] ) && $premise['footer'] == 1 ? 1 : 0;
		$premise['header'] = isset( $premise['header'] ) && $premise['header'] == 1 ? 1 : 0;

		if( isset( $premise['content-scrollers'] ) && is_array( $premise['content-scrollers'] ) ) {
			$temp = $premise['content-scrollers'];
			$premise['content-scrollers'] = array();
			foreach( $premise['content-scrollers-order'] as $key )
				$premise['content-scrollers'][] = $temp[$key];

			if( !empty( $premise['add-another-content-scroller-tab'] ) && $premise['add-another-content-scroller-tab'] == '1' ) {
				$number = count( $premise['content-scrollers'] );
				$new_scroller = array(
					'title' => 'Tab ' . ( $number + 1 ),
					'tooltip' => '',
					'icon' => '',
					'text' => '',
				);
				$premise['content-scrollers'][] = $new_scroller;
				$premise['content-scroller-order'][$number] = $number;
			}
		}

		if( isset( $premise['pricing-columns'] ) && is_array( $premise['pricing-columns'] ) ) {
			$temp = $premise['pricing-columns'];
			$premise['pricing-columns'] = array();
			foreach( $premise['pricing-order'] as $key ) {
				$attributes = $temp[$key]['attributes'];
				if( !is_array( $attributes ) )
					$attributes = array();

				$attributes = array_values( array_filter( array_map( 'trim', $attributes ) ) );

				$temp[$key]['attributes'] = $attributes;
				$premise['pricing-columns'][] = $temp[$key];
			}
		}

		$premise_base->update_premise_meta( $postId, $premise );

	}

	function processPremiseSeoMeta( $postId, $post ) {
		global $premise_base;

		if( !$premise_base->is_premise_post_type( $post->post_type ) )
			return;

		$data = stripslashes_deep( $_POST );
		if( isset( $data['premise-seo'] ) && is_array( $data['premise-seo'] ) && isset( $data['save-premise-seo-settings-nonce'] ) && wp_verify_nonce( $data['save-premise-seo-settings-nonce'], 'save-premise-seo-settings' ) ) {
			$seo = $data['premise-seo'];
			foreach( array( 'noindex', 'nofollow', 'noarchive', 'disable-feed' ) as $key )
				$seo[$key] = isset( $seo[$key] ) && $seo[$key] == 1 ? 1 : 0;

			$premise_base->update_premise_meta( $postId, $seo, 'seo' );
		} elseif( $post->post_status == 'auto-draft' ) {
			$settings = $premise_base->get_settings();
			$premise_base->update_premise_meta( $postId, $settings['seo'], 'seo' );
		}
	}

	function processPremiseTrackingMeta($postId, $post) {
		global $premise_base;

		if( !$premise_base->is_premise_post_type( $post->post_type ) )
			return;

		$data = stripslashes_deep( $_POST );
		if( isset( $data['premise-tracking'] ) && is_array( $data['premise-tracking'] ) && isset( $data['save-premise-tracking-settings-nonce'] ) && wp_verify_nonce( $data['save-premise-tracking-settings-nonce'], 'save-premise-tracking-settings' ) ) {
			$tracking = $data['premise-tracking'];
			if ( isset( $tracking['test-id'] ) )
				$tracking['test-id'] = $this->parseTestIdentifierFromGoogleContentExperimentEmbedCode( $tracking['test-id'] );

			$tracking['enable-gce'] = isset( $tracking['enable-gce'] ) && $tracking['enable-gce'] == 1 ? 1 : 0;

			$premise_base->update_premise_meta( $postId, $tracking, 'tracking' );
		}
	}

	function parseAccountIdentifierFromGoogleWebsiteOptimizerEmbedCode( $code ) {
		$code = trim( $code );
		if( empty( $code ) )
			return '';

		if( preg_match( '/UA-([0-9]+)-([0-9]{1,3})/', $code, $matches ) )
			return $matches[0];

		return '';
	}

	function parseTestIdentifierFromGoogleContentExperimentEmbedCode($code) {

		$code = trim( $code );
		if ( empty( $code ) )
			return '';

		// if it looks like a experiment code then assume it is one
		if ( preg_match( '/^[0-9\-]*$/', $code ) )
			return $code;

		if ( preg_match( "/var k='([0-9\-]*)'/", $code, $matches ) )
			return $matches[1];

		return '';
	}


	function processPremiseDesignSettings() {
		$data = stripslashes_deep($_POST);
		$key = isset( $data['premise-design-key'] ) ? $data['premise-design-key'] : null;
		$design = $data['premise-design'];

		if(isset($design['reset'])) {
			$settings = $this->getDefaultDesignSettings();
			$message = 'reset=true';
		} else {
			if(!empty($key)) {
				$settings = $this->getDefaultDesignSettings();
			} else {
				$settings = $this->getConfiguredStyle($key);
			}

			$settings = array_merge($settings, $design);

			if ( ! isset( $design['minify_css'] ) || $design['minify_css'] != 'true' || ! is_writeable( premise_get_stylesheet_location( 'file' ) ) )
				unset($settings['minify_css']);

			$message = 'updated=true';
		}

		$key = $this->saveDesignSettings($settings, $key);

		return "premise-design-key={$key}&{$message}";
	}

	/// DISPLAY THICKBOX CALLBACKS

	function displayPremiseResourcesThickbox() {
		wp_enqueue_style( 'media' );
		wp_enqueue_style( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.css', array( 'farbtastic' ), PREMISE_VERSION );

		wp_enqueue_script( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.js', array( 'jquery', 'jquery-ui-sortable', 'farbtastic', 'jquery-form' ), PREMISE_VERSION );

		add_filter( 'media_upload_tabs', array( &$this, 'getPremiseThickboxTabs' ) );
		return wp_iframe( 'premise_thickbox' );
	}

	function displayPremiseResourcesThickboxOutput() {
		$page = isset( $_GET['tab'] ) ? $_GET['tab'] : null;
		if( empty( $page ) )
			$page = $_GET['type'];


		switch($page) {
			case 'premise-resources-graphics':
				include( PREMISE_VIEWS_DIR . 'thickbox/graphics.php' );
				break;
			case 'premise-resources-education':
				include( PREMISE_VIEWS_DIR . 'thickbox/education.php' );
				break;
			case 'premise-resources-optin':
				include( PREMISE_VIEWS_DIR . 'thickbox/optin.php' );
				break;
			case 'premise-button-create':
				include( PREMISE_VIEWS_DIR . 'thickbox/button-creation.php' );
				break;
			case 'premise-button-usage':
				include( PREMISE_VIEWS_DIR . 'thickbox/button-usage.php' );
				break;
		}
	}

	function thickbox_button_color_picker( $id, $label_text, $current ) {
		$esc_id = esc_attr( $id );
		echo '<label class="descriptor" for="button-editing-' . $esc_id . '">' . esc_html( $label_text ) . '</label>';
		echo '<input type="text" size="7" class="premise-color-picker" name="button-editing[' . $esc_id . ']" id="button-editing-' . $esc_id . '" value="' . esc_attr( $current ) . '" />';
		echo "\n";
	}
	function thickbox_button_select( $id, $label_text, $current, $range_start, $range_end, $unit, $step = 1 ) {
		$esc_id = esc_attr( $id );
		if( !empty( $label_text ) )
			echo '<label class="descriptor" for="button-editing-' . $esc_id . '">' . esc_html( $label_text ) . '</label>';
			
		echo '<select name="button-editing[' . $esc_id . ']" id="button-editing-' . $esc_id . '">';
		foreach( range( $range_start, $range_end, $step ) as $value )
			echo '<option ' . selected( $value, $current, false ) . ' value="' . esc_attr( $value ) . '">' . esc_html( $value . $unit ) . '</option>';

		echo "</select>\n";
	}
	/// OPT IN PROVIDERS

	//// AWEBER

	/**
	 *
	 * @param $code
	 * @return AWeberAPI
	 */
	function initializeAweberApi() {
		global $premise_base;
		$premise_base->initialize_aweber();
	}

	function getAweberLists( $force_api = false ) {

		global $premise_base;

		return $premise_base->get_aweber_lists( $force_api );

	}

	function validateAweberAuthorizationCode($code) {
		$this->initializeAweberApi();

		try {
			list($consumer_key, $consumer_secret, $access_key, $access_secret) = AWeberAPI::getDataFromAweberID($code);
		} catch (AWeberException $e) {
			list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
		}

		if(!$access_secret) {
			return array('error' => __('Invalid Aweber authorization code.  Please make sure you entered it correctly.', 'premise' ));
		}

		$aweber = new AWeberAPI($consumer_key, $consumer_secret);

		try {
			$account = $aweber->getAccount($access_key, $access_secret);
		} catch (AWeberResponseError $e) {
			$account = null;
		}

		if(!$account) {
			return array('error' => __('Unable to connect to Aweber account.  Please try again.', 'premise' ));
		}

		return compact('consumer_key', 'consumer_secret', 'access_key', 'access_secret');
	}

	//// CONSTANT CONTACT

	function getConstantContactLists() {

		global $premise_base;

		require_once( PREMISE_LIB_DIR . 'constant_contact_api/constant_contact_api.php' );

		$settings = $premise_base->get_settings();
		$optin = $settings['optin'];

		$premise_base->setup_constant_contact( $this->_optin_ConstantContactKey, $optin['constant-contact-username'], $optin['constant-contact-password'] );

		$lists = new ListsCollection();
		list($items) = $lists->getLists();

		$return = array();
		foreach($items as $item) {
			$return[] = array('id' => preg_replace('/[^0-9]/', '', (string)$item->getId()), 'name' => (string)$item->getName());
		}
		return array_slice($return, 3);
	}

	//// MAILCHIMP

	function getMailChimpLists() {
		require_once( PREMISE_LIB_DIR . 'mailchimp_api/MCAPI.class.php' );

		$settings = $this->getSettings();
		$mailchimp = new MCAPI($settings['optin']['mailchimp-api']);
		$data = $mailchimp->lists(array(), 0, 100);

		$lists = array();
		if(is_array($data) && is_array($data['data'])) {
			foreach($data['data'] as $item) {
				$lists[] = array('id' => $item['id'], 'name' => $item['name']);
			}
		}
		return $lists;
	}

	function getMailChimpMergeVars( $id ) {
		global $premise_base;
		return $premise_base->get_mailchimp_merge_vars( $id );
	}

	function signupUserForMailChimp( $vars, $list ) {
		global $premise_base;
		return $premise_base->signup_user_mailchimp( $vars, $list );
	}

	function validateMailChimpAPIKey( $key ) {
		global $premise_base;
		return $premise_base->validate_mailchimp_key( $key );
	}

	/// API DELEGATES

	function retrieveAdviceForPage( $postId ) {
		global $post, $premise_base;
		if( empty( $postId ) )
			$postId = $post->ID;

		$type = $this->convertLandingPageToId( $premise_base->get_advice_type( $postId ) );
		return $this->getAdvice($type);
	}

	function retrieveAdviceSectionsForPage( $postId ) {
		global $post, $premise_base;
		if( empty( $postId ) )
			$postId = $post->ID;

		$advice = $premise_base->get_advice_type( $postId );
		$content = $this->getAdviceSections( $advice );

		return $content;
	}

	function retrieveSampleCopyForPage( $postId ) {
		global $post, $premise_base;
		if( empty( $postId ) )
			$postId = $post->ID;

		$content = $this->getSampleContent( $this->convertLandingPageToId( $premise_base->get_advice_type( $postId ) ) );
		if( !is_wp_error( $content ) )
			return $content['content'];

		return '';
	}

	//// ADVICE

	function getAdviceSections($type) {
		return $this->_data_PremiseApi->getAdviceSections($type);
	}

	function getAdvice($type) {
		return $this->_data_PremiseApi->getAdvice($type);
	}

	function getSampleContent($type) {
		return $this->_data_PremiseApi->getSampleContent($type);
	}

	function getSingleAdvice($section) {
		return $this->_data_PremiseApi->getSingleAdvice($section);
	}

	//// GRAPHICS

	function getGraphicsCategories() {
		return $this->_data_PremiseApi->getGraphicCategories();
	}

	function getGraphics($limit = 10, $page = 1, $category = '', $search = '') {
		return $this->_data_PremiseApi->getGraphics($limit, $page, $category, $search);
	}

	function getGraphic($id) {
		return $this->_data_PremiseApi->getGraphic($id);
	}

	/// DISPLAY SETTINGS CALLBACKS

	function displayPremiseSettingsPage() {

		global $Premise, $premise_style_configuration_key, $premise_style_configuration_should_use_defaults;

		$messages = get_transient($this->_option_SettingsTransient);
		$orderby = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'title';
		$order = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'ASC';

		delete_transient($this->_option_SettingsTransient);
		include( PREMISE_VIEWS_DIR . 'settings/header.php' );

		switch( $_GET['page'] ) {
			case 'premise-styles':
				include( PREMISE_VIEWS_DIR . 'settings/styles.php' );
				break;
			case 'premise-buttons':
				include( PREMISE_VIEWS_DIR . 'settings/buttons.php' );
				break;
			case 'premise-style-settings':
				$premise_style_configuration_key = null;
				if ( isset( $_GET['premise-design-key'] ) && $Premise->isValidStyleKey( $_GET['premise-design-key'] ) )
					$premise_style_configuration_key = intval( $_GET['premise-design-key'] );
				else
					$premise_style_configuration_should_use_defaults = true;

				include( PREMISE_VIEWS_DIR . 'settings/design.php' );
				break;
		}
		include( PREMISE_VIEWS_DIR . 'settings/footer.php' );
	}

	/// LOCATIONS

	/// UTILITY
	
	function getLandingPageUris() {
		global $premise_base;
		return $premise_base->get_landing_page_uris();
	}

	function getVersionInfo( $cache = true ) {
		global $premise_base;
		$raw = false;
		if( $cache )
			$raw = get_transient( $this->_data_VersionTransientName );

		if( !$raw ) {
			$settings = $premise_base->get_settings();
			$key = isset( $settings['main']['api-key'] ) ? $settings['main']['api-key'] : '';
			$url = add_query_arg( array( 'apikey' => $key ), $this->_data_VersionURL );
			$raw = wp_remote_get( $url );

			set_transient( $this->_data_VersionTransientName, $raw, 12*60*60 );
		}

		if ( is_wp_error( $raw ) )
			return false;
		elseif( 200 != wp_remote_retrieve_response_code( $raw ) )
			return array( 'is_valid' => 0, 'version' => '', 'url '=> '' );

		return json_decode( wp_remote_retrieve_body( $raw ) );

	}

	/// META

	function setDefaultLandingPageMeta($postId) {
		global $premise_base;
		
		$settings = $premise_base->get_settings();
		$header_image = isset( $settings['main']['default-header-image'] ) ? $settings['main']['default-header-image'] : '';
		$header_copy = isset( $settings['main']['default-header-tagline'] ) ? $settings['main']['default-header-tagline'] : '';
		$footer_copy = isset( $settings['main']['default-footer-text'] ) ? $settings['main']['default-footer-text'] : '';
		$defaults = array( 'header-image' => $header_image, 'header-copy' => $header_copy, 'footer-copy' => $footer_copy );
		$premise_base->update_premise_meta( $postId, $defaults );
	}

	function getPremiseMeta($postId) {
		global $premise_base;
		return $premise_base->get_premise_meta( $postId );
	}

	function savePremiseMeta($postId, $settings) {
		global $premise_base;
		return $premise_base->update_premise_meta( $postId, $settings );
	}

	function getPremiseSeoMeta($postId) {
		global $premise_base;
		return $premise_base->get_premise_meta( $postId, 'seo' );
	}

	function savePremiseSeoMeta($postId, $settings) {
		global $premise_base;
		return $premise_base->update_premise_meta( $postId, $settings, 'seo' );
	}

	function getPremiseTrackingMeta($postId) {
		global $premise_base;
		return $premise_base->get_premise_meta( $postId, 'tracking' );
	}

	function savePremiseTrackingMeta($postId, $settings) {
		global $premise_base;
		return $premise_base->update_premise_meta( $postId, $settings, 'tracking' );
	}

	/// SETTINGS

	function getSettings() {
		global $premise_base;
		return $premise_base->get_settings();
	}

	function saveSettings( $settings ) {
		global $premise_base;
		$premise_base->update_settings( $settings );
	}

	function getDefaultDesignSettings() {
		global $premise_design_settings;
		return $premise_design_settings->get_default_settings();
	}

	function getDesignSettings() {
		global $premise_design_settings;
		return $premise_design_settings->get_settings();
	}

	function saveDesignSettings( $settings, $key ) {
		global $premise_design_settings;
		return $premise_design_settings->update_settings( $settings, $key );
	}

	function getPinnedStatus() {
		$status = wp_cache_get($this->_option_PinnedStatus);
		if(false === $status) {
			$status = get_option($this->_option_PinnedStatus, 0);
			wp_cache_set($this->_option_PinnedStatus, $status);
		}
		return $status == 1;
	}

	function savePinnedStatus($status) {
		update_option($this->_option_PinnedStatus, $status);
		wp_cache_set($this->_option_PinnedStatus, $status, null, time() + 24*60*60);
	}

	/// LANDING PAGE UTILITY

	function getAvailableLandingPageTypes() {
		return $this->_data_RegisteredLandingPages;
	}

	function getLandingPageInformation($info) {
		if(empty($info)) {
			return false;
		}
		$nameMatches = preg_match('|Template Name:(.*)$|mi', $info, $name);
		$descriptionMatches = preg_match('|Template Description:(.*)$|mi', $info, $description);

		$name = $name[1];
		$description = $description[1];

		if(empty($name)) {
			return false;
		}

		return array('name'=>$name, 'description'=>$description);
	}

	function setLandingPageType($postId, $type) {
		global $premise_base;
		$post = get_post( $postId );
		if( !$premise_base->is_premise_post_type( $post->post_type ) )
			return;
			
		foreach( $this->getAvailableLandingPageTypes() as $atype ) {
			if($atype['template'] == $type) {
				update_post_meta( $postId, $premise_base->get_meta_key( 'type' ), $type );
				update_post_meta( $postId, '_wp_page_template', $type );
				break;
			}
		}
	}

	function setLandingPageAdviceType($postId, $type) {
		global $premise_base;
		$post = get_post( $postId );
		if( !$premise_base->is_premise_post_type( $post->post_type ) )
			return;
			
		$types = $this->getAvailableLandingPageTypes();
		$keys = array_keys($types);

		if( in_array( $type, $keys ) )
			update_post_meta( $postId, $premise_base->get_meta_key( 'advice' ), $type );
	}

	function getLandingPageTypeName($postId) {
		global $premise_base;
		$type = $premise_base->get_advice_type( $postId );
		$available = $this->getAvailableLandingPageTypes();
		if( isset( $available[$type] ) )
			return trim( $available[$type]['name'] );
			
		return _e('Unknown', 'premise' );
	}

	/// STYLESHEET UTILITY

	/// STYLES UTILITY

	function isValidStyleKey( $key ) {
		global $premise_design_settings;
		$settings = $premise_design_settings->get_settings();
		return isset( $settings[$key] );
	}
	
	function deleteConfiguredStyle($key) {
		global $premise_design_settings;
		$settings = $premise_design_settings->get_settings();
		unset($settings[$key]);
		$premise_design_settings->update_settings( $settings );
	}

	function getConfiguredStyle( $key = null ) {
		global $premise_design_settings;
		return $premise_design_settings->get_configured_style( $key );
	}
	
	/// BUTTONS UTILITY
	
	function deleteConfiguredButton($id) {
		$buttons = $this->getConfiguredButtons($id);
		unset($buttons[$id]);
		$this->saveConfiguredButtons($buttons);
	}
	
	function duplicateConfiguredButton($id) {

		$button = $this->getConfiguredButton($id);

		// make sure this isn't a duplicate name
		$buttons = $this->getConfiguredButtons();
		$unique = false;
		$count = 2;
		while( ! $unique ) {

			$title = $button['title'] . ' - '. $count++;
			$unique = true;
			foreach( $buttons as $existing ) {

				if ( $existing['title'] != $title )
					continue;

				$unique = false;
				break;

			}
			if ( $unique )
				$button['title'] = $title;

		}
		
		$this->saveConfiguredButton($button, null);

	}
	
	function getButtonCode( $button, $key = null ) {
		global $premise_base;
		return $premise_base->get_button_code( $button, $key = null );
	}
	
	function getConfiguredButtons() {
		global $premise_base;
		return $premise_base->get_configured_buttons();
	}
	
	function getConfiguredButton($key) {
		$buttons = $this->getConfiguredButtons();
		
		if(isset($buttons[$key])) {
			return $buttons[$key];
		} else {
			return $this->getDefaultButtonConfiguration();
		}
	}
	
	function getDefaultButtonConfiguration() {
		return array(
			'title' => __('Default', 'premise' ),
			'font-family' => 'inherit',
			'font-size' => 14,
			'font-color' => '#123d54',
			'padding-tb' => 10,
			'padding-lr' => 20,
			'background-color-1' => '#afd9fa',
			'background-color-2' => '#afd9fa',
			'background-color-2-position' => 25,
			'background-color-3' => '#ffffff',
			'background-color-3-position' => 50,
			'background-color-4' => '#588fad',
			'background-color-4-position' => 75,
			'background-color-5' => '#588fad',
			'background-color-hover-1' => '#588fad',
			'background-color-hover-2' => '#588fad',
			'background-color-hover-2-position' => 25,
			'background-color-hover-3' => '#ffffff',
			'background-color-hover-3-position' => 50,
			'background-color-hover-4' => '#afd9fa',
			'background-color-hover-4-position' => 75,
			'background-color-hover-5' => '#afd9fa',
			'border-radius' => 6,
			'border-width' => 1,
			'border-color' => '#003366',
			'drop-shadow-x' => 0,
			'drop-shadow-y' => 1,
			'drop-shadow-size' => 3,
			'drop-shadow-color' => '#000000',
			'drop-shadow-opacity' => 0.5,
			'inset-shadow-x' => 0,
			'inset-shadow-y' => 0,
			'inset-shadow-size' => 1,
			'inset-shadow-color' => '#ffffff',
			'inset-shadow-opacity' => 1,
			'text-shadow-1-x' => 0,
			'text-shadow-1-y' => -1,
			'text-shadow-1-size' => 0,
			'text-shadow-1-color' => '#000000',
			'text-shadow-1-opacity' => .7,
			'text-shadow-2-x' => 0,
			'text-shadow-2-y' => 1,
			'text-shadow-2-size' => 0,
			'text-shadow-2-color' => '#ffffff',
			'text-shadow-2-opacity' => .3,
		);
	}
	
	function getRgbForHex( $color ) {
		global $premise_base;
		return $premise_base->RGB2hex( $color );
	}
	
	function saveConfiguredButton($button, $key) {
		$button['lastsaved'] = current_time('timestamp');
		$buttons = $this->getConfiguredButtons();
		
		if(isset($key)) {
			$buttons[$key] = $button;
		} else {
			do {
				$key = wp_generate_password(12, false, false);
			} while(isset($buttons[$key]));
			$buttons[$key] = $button;
		}
		
		$this->saveConfiguredButtons($buttons);
	}
	
	function saveConfiguredButtons($buttons) {
		if(is_array($buttons)) {
			update_option($this->_option_ConfiguredButtons, $buttons);
		}
	}
	
	function saveConfiguredButtonsStylesheet( $newvalue, $oldvalue ) {
		global $premise_base;
		return $premise_base->save_configured_buttons_stylesheet( $newvalue, $oldvalue );
	}
	
	function saveCustomButtonsCss() {
		$buttons = $this->getConfiguredButtons();
	}
	
	/// TEMPLATE TAG DELEGATES

	function theEditor( $content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2 ) {
		global $wp_scripts, $wp_version;
		if( !in_array( 'quicktags', $wp_scripts->done ) )
			wp_print_scripts('quicktags');
		
		$editor_number = $this->_data_EditorNumber++;
		$editor_id = 'content_' . $editor_number;

		echo '<div  id="premise-editor-container-' . $editor_number . '" class="premise-editor-container premise-editor-area postarea">';

		if( substr( $wp_version, 0, 3 ) >= '3.3' ) {
			$quicktags_settings = array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,spell,close' );
			wp_editor( $content, $editor_id, array('dfw' => true, 'tabindex' => $editor_number, 'quicktags' => $quicktags_settings, 'textarea_name' => $id ) );
			premise_create_quicktags_script( $editor_id );

			if ( 'content' == $id ) {
// this is a short term fix to disable autosave
//@todo: diagnose wp-includes/js/autosave.js not picking up #content on save
?>
<script type="text/javascript">
//<!--
setTimeout(function(){
	autosave = function() {}
}, 1000);
//-->
</script>
<?php
			}

		} else {
			printf( __( '<h2>To use Premise %s you need to update to WordPress 3.3.</h2>', 'premise' ), PREMISE_VERSION );
		}

		echo '</div>';
	}

}

