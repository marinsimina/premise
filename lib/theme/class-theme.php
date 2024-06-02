<?php
/*
Handling for the Premise theme
*/
class Premise_Theme {
	var $_original_stylesheet = '';
	var $_original_template = '';
	var $_original_front_page = '';
	var $_site_theme_template;
	var $_theme_root = null;
	var $_post_id = 0;

	var $_optin_MailChimpMessages = '_premise_optin_mailchimp_messages';
	var $_optin_aweber_messages = '_premise_optin_aweber_messages';
	var $_optin_ConstantContactMessages = '_premise_optin_constant_contact_messages';
	var $_optin_ConstantContactKey = 'a1a2e20e-e5e9-4879-8a20-0339affa0c6a';

	function __construct( $is_home, $page_id, $premise_theme_support ) {

		$this->_site_theme_template = $premise_theme_support;
		if ( ! $this->_site_theme_template ) {

			$this->_theme_root = untrailingslashit( PREMISE_THEMES_DIR );
			register_theme_directory( $this->_theme_root );
			add_filter( 'template', array( $this, 'theme_filter' ) );
			add_filter( 'stylesheet', array( $this, 'theme_filter' ) );
			add_filter( 'theme_root', array( $this, 'theme_root' ) );
			add_filter( 'stylesheet_directory_uri', array( $this, 'stylesheet_directory_uri' ) );
			add_action( 'after_setup_theme', array( $this, 'remove_init_hooks' ), 999 );
			define( 'BP_DISABLE_ADMIN_BAR', true );

			if( $is_home ) {
				$this->_original_front_page = $page_id;
				remove_action( 'template_redirect', 'redirect_canonical' );
				add_action( 'parse_request', array( $this, 'parse_request' ) );
				add_filter( 'post_type_link', array( $this, 'home_page_canonical_url' ), 10, 2 );
			}

		}

		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_action( 'parse_request', array( $this, 'process_mailing_list_submissions' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );

	}
	function theme_filter( $setting ) {
		$original = '_original_' . current_filter();
		$this->$original = $setting;
		
		return 'premise';
	}
	function theme_root( $root ) {
		return $this->_theme_root;
	}
	function parse_request( $wp ) {
		global $premise_base;

		if( empty( $this->_original_front_page ) )
			return;

		$wp->query_vars['p'] = $this->_original_front_page;
		$wp->query_vars['post_type'] = $premise_base->get_post_type();

	}
	function template_include( $template ) {

		global $premise_base;

		if ( ! $premise_base->is_premise_post_type() )
			return $template;

		if ( $this->_site_theme_template && ( $site_theme_template = locate_template( array( $this->_site_theme_template ), false ) ) ) {

			wp_enqueue_style( 'premise-landing-page', PREMISE_THEMES_URL . 'premise/style.css', array(), PREMISE_VERSION );
			add_action( 'premise_immediately_after_head', array( $this, 'header_image_width_style' ) );

			if ( get_template() == 'genesis' )
				require_once( PREMISE_LIB_DIR . 'theme/genesis-landing-page.php' );

			return $site_theme_template;
		}

		$post_id = $this->get_post_id();
		$premise_template = trailingslashit( PREMISE_THEMES_DIR . 'premise' ) . $premise_base->get_page_type( $post_id ) . '.php';

		if( is_file( $premise_template ) ) {
			$this->setup_wp_head();
			return $premise_template;
		}

		return $template;
	}
	function body_class( $classes ) {

		global $premise_base;

		if ( ! $premise_base->is_premise_post_type() )
			return $classes;

		$classes[] = 'premise-landing';
		$classes[] = 'premise-landing-' . sanitize_html_class( $this->get_post_id() );
		$classes[] = sanitize_html_class( premise_get_advice_type() . '-landing' );
		$classes[] = ( $this->_site_theme_template ? 'site' : 'premise' ) . '-theme';

		return $classes;

	}

	function stylesheet_directory_uri( $uri ) {
		return PREMISE_THEMES_URL . 'premise';
	}
	function setup_wp_head() {
		global $premise_base;

		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'index_rel_link' );
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		remove_action( 'wp_head', 'noindex', 1 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rel_canonical' );

		if( 1 == $premise_base->get_premise_meta_item( $this->get_post_id(), 'disable-feed', 'seo' ) ) {
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}

		add_action( 'wp_head', array( $this, 'wp_head_meta' ), -1 );
		add_action( 'wp_head', array( $this, 'enqueue_theme_scripts_css' ), 0 );
		add_action( 'wp_head', array( $this, 'content_slider_styles' ) );
		add_action( 'wp_head', array( $this, 'website_optimizer_scripts' ), 5 );
		add_action( 'wp_head', array( $this, 'landing_page_header_scripts'), 11 );
		add_filter( 'wp_title', array( $this, 'wp_title' ), 25 );
		add_action( 'wp_footer', array( $this, 'landing_page_footer_scripts' ) );

		$this->remove_content_filters();
	}
	function wp_head_meta() {

		global $premise_base, $post;

		$favicon = $premise_base->get_favicon( $this->get_post_id() );
		if( !empty( $favicon ) ) {
?>
<link rel="shortcut icon" type="image/ico" href="<?php echo esc_url( $favicon ); ?>" />
<?php
		}

		if ( ! $premise_base->have_premise_seo() )
			return;

		$seo = $premise_base->get_premise_meta( $this->get_post_id(), 'seo' );
		include( PREMISE_VIEWS_DIR . 'misc/meta-tags.php' );

	}
	function content_slider_styles() {
		global $premise_base;
		
		$post_id = $this->get_post_id();
		$meta = $premise_base->get_premise_meta( $post_id );
		$styleKey = $meta['style'];
		
		$tabs = premise_get_content_tabs( $post_id );
		if( !empty( $tabs ) ) {
			$uploadInfo = wp_upload_dir();
			$uploadDir = $uploadInfo['basedir'];
			$uploadUrl = $uploadInfo['baseurl'];
			include( PREMISE_VIEWS_DIR . 'misc/content-slider-styles.php' );
		}
	}
	function enqueue_theme_scripts_css() {
		global $premise_base;
		
		$post_id = $this->get_post_id();
		$meta = $premise_base->get_premise_meta( $post_id );
		$key = $meta['style'];
		
		if( !file_exists( premise_get_settings_stylesheet_path( $key ) ) || trim( premise_get_settings_stylesheet_contents( $key ) ) == '' )
			premise_create_stylesheets();
		
		if( !file_exists( premise_get_custom_buttons_stylesheet_path() ) )
			$premise_base->save_configured_buttons_stylesheet( array(), $premise_base->get_configured_buttons() );
		
		if ( ! $this->_site_theme_template && file_exists( premise_get_settings_stylesheet_path( $key ) ) ) {
			if ( !premise_is_minified( $key ) ) {
				wp_enqueue_style( 'premise', PREMISE_THEMES_URL . 'premise/style.css', array(), filemtime( PREMISE_DIR . 'themes/premise/style.css' ) );
				wp_enqueue_style( 'premise_settings_stylesheet', premise_get_settings_stylesheet_url( $key ), false, filemtime( premise_get_settings_stylesheet_path( $key ) ) );
				if ( is_file( premise_get_custom_stylesheet_path( $key ) ) )
					wp_enqueue_style( 'premise_custom_stylesheet', premise_get_custom_stylesheet_url( $key ), false, filemtime( premise_get_custom_stylesheet_path( $key ) ) );

			} else {
				// Otherwise, if minified, then add reference to minified stylesheet, and remove style.css reference
				wp_enqueue_style( 'premise_minified_stylesheet', premise_get_minified_stylesheet_url( $key ), false, filemtime( premise_get_minified_stylesheet_path( $key ) ) );
			}
		}
		
		wp_enqueue_style( 'premise_custom_buttons', premise_get_custom_buttons_stylesheet_url() );
		wp_enqueue_script( 'premise_easing', PREMISE_THEMES_URL . 'premise/js/jquery-easing.js', array( 'jquery' ), PREMISE_VERSION );
		wp_enqueue_script( 'premise_coda_slider', PREMISE_THEMES_URL . 'premise/js/jquery-coda.js', array( 'jquery', 'premise_easing' ), PREMISE_VERSION );
		wp_enqueue_script( 'premise_pretty_photo', PREMISE_THEMES_URL . 'premise/js/jquery-overlay.js', array( 'jquery' ), PREMISE_VERSION );
	}
	function enqueue_video_scripts_css() {

		if ( premise_get_advice_type() != 'video' )
			return;
?>

<link rel="stylesheet" href="<?php echo plugins_url('/themes/premise/js/colorbox/colorbox.css', PREMISE_THEMES_DIR ); ?>" />
<script src="<?php echo plugins_url('/themes/premise/js/colorbox/jquery.colorbox-min.js', PREMISE_THEMES_DIR ); ?>"></script>
<script type="text/javascript" charset="utf-8">

	$jQuery = jQuery.noConflict();
	$jQuery(document).ready(function(){

		$jQuery("#inline").colorbox({ inline:true });

	});

</script>

<?php
	}
	function wp_title( $title ) {

		global $premise_base;

		// trim the title which is prefixed with " $sep " by default
		$title = trim( $title );

		if ( ! $premise_base->have_premise_seo() )
			return $title;

		$seo = $premise_base->get_premise_meta( $this->get_post_id(), 'seo' );
		$stitle = trim( $seo['title'] );
		if( !empty( $stitle ) )
			return $stitle;

		return $title;
	}

	function header_image_width_style() {

		global $content_width;

		if ( ! isset( $content_width ) )
			return;

		$header_width = apply_filters( 'premise_landing_page_header_width', $content_width, $this->get_post_id() );
		if ( ! $header_width )
			return;

		// if it's a number treat it as pixels
		if ( is_numeric( $header_width ) )
			$header_width .= 'px';

		printf( "\n<style type='text/css'>\n.premise-landing-header .wrap { width: %s; }\n</style>\n", $header_width );

	}
	function website_optimizer_scripts() {
		global $premise_base;

		$tracking = $premise_base->get_premise_meta( $this->get_post_id(), 'tracking' );

		if( ! isset( $tracking['enable-gce'] ) || $tracking['enable-gce'] != 1 )
			return;

		$settings = $premise_base->get_settings();
		$trackingSettings = $settings['tracking'];

		$testId = isset( $tracking['test-id'] ) ? $tracking['test-id'] : false;
		if( $testId )
			include( PREMISE_VIEWS_DIR . 'gce/control.php' );

		if( isset( $settings['tracking']['vwo-account-id'] ) && ! empty( $settings['tracking']['vwo-account-id'] ) ) {

			$accountId = $settings['tracking']['vwo-account-id'];
			include( PREMISE_VIEWS_DIR . 'vwo/tracking.php' );

		}

		add_action( 'premise_immediately_after_head', array( &$this, 'web_optimizer_scripts_after_head' ), 9 );

	}
	function landing_page_header_scripts() {
		$this->landing_page_scripts( 'header' );
	}
	function landing_page_footer_scripts() {
		$this->landing_page_scripts( 'footer' );
	}
	function landing_page_scripts( $location ) {
		global $premise_base;
		$settings = $premise_base->get_settings();
			
		$meta = $premise_base->get_premise_meta( $this->get_post_id() );
		$script = trim( $meta[$location . '-scripts'] );
		if( !empty( $script ) )
			echo "\n{$script}\n";

		if( !empty($settings['scripts'][$location] ) )
			echo "\n{$settings['scripts'][$location]}\n";

		if ( $this->_site_theme_template && $location == 'header' ) {

?>

<script type="text/javascript" >
//<!--
var premise_theme_images_url = '<?php echo apply_filters( 'premise_js_theme_images_url', PREMISE_THEMES_URL . 'premise/images' ); ?>';
//-->
</script>
<?php

		}
	}
	function web_optimizer_scripts_after_head() {

		global $premise_base;

		$premise_base->add_google_analytics_script();

	}

	function get_post_id() {
		global $premise_base, $wp_query;

		if( !$this->_post_id && $premise_base->is_premise_post_type() ) {
			$post = $wp_query->get_queried_object();
			$this->_post_id = $post->ID;
		}
		
		return $this->_post_id;
	}
	function remove_init_hooks() {
		remove_action( 'init', 'dd_enable_required_js_in_wordpress' );
		remove_action( 'init', 'sharebar_init');
		remove_action( 'init', 'HelloBarForWordPress' );
		remove_action( 'init', 'viperbar_load' );
		remove_action( 'wp_print_styles', 'nrelate_related_styles' );
		remove_action( 'get_header', 'blogs_top' );
		remove_action( 'wp_print_styles', array( 'better_Author_box_red', 'pluginCss' ) );
		remove_action( 'wp_print_styles', array( 'xpandable_author_tab', 'pluginCss' ) );
		remove_action( 'init', 'twitter_facebook_share_init' );
		remove_action( 'init', 'addthis_init' );
		remove_action( 'init', 'yarpp_init' );
		remove_action( 'wp_head', 'fbgraphinfo' );
		remove_filter( 'language_attributes', 'schema' );
		remove_action( 'wp_print_styles', 'wp_about_author_style' );
		remove_action( 'wp_enqueue_scripts', array( 'dc_jqslicksocial', 'dcssb_scripts' ) );
		remove_action( 'wp_print_styles', 'sharing_css' );
		remove_action( 'wp_enqueue_scripts', 'nrelate_flyout_styles' );
		// post ender
		remove_action( 'init', array( 'PostEnder', 'init' ) );

		do_action( 'premise_init_remove_display_plugins' );
	}
	function remove_content_filters() {
		global $sociable, $wpfblike, $wpTweetButton, $cons_shareFollow, $gdsr;
		remove_filter( 'the_content', 'tm_update', 8 );
		remove_filter( 'the_content', 'shrsb_position_menu' );
		
		// SOCIABLE
		remove_filter( 'the_content', 'sociable_display_hook' );
		
		// LINK WITHIN
		remove_action( 'wp_footer', 'linkwithin_load_widget' );
		remove_filter( 'the_content', 'linkwithin_add_hook' );
		
		// SHARE THIS
		remove_filter( 'the_content', 'st_add_widget' );
		remove_filter( 'the_content', 'jw_share_this_links' );
		
		// SHARE BAR
		remove_filter( 'the_content', 'sharebar_auto' );
		remove_action( 'wp_head', 'sharebar_header' );

		// DIGG DIGG
		remove_action( 'wp_head', 'dd_output_css_to_html' );
		remove_action( 'wp_head', 'dd_get_thumbnails_for_fb' );
		remove_filter( 'the_excerpt', 'dd_hook_wp_content' );
		remove_filter( 'the_content', 'dd_hook_wp_content' );
		remove_filter( 'the_content', 'dd_content_hook' );
		remove_action( 'the_content', 'pf_show_link' );
		
		// YET ANOTHER RELATED POSTS
		remove_filter( 'the_content', 'yarpp_default', 1200 );

		// WP JETPACK				
		remove_filter( 'the_content', 'sharing_display', 19 );
		remove_filter( 'the_content', 'polldaddy_link', 1 );
		remove_filter( 'the_content_rss', 'polldaddy_link', 1 );
		remove_filter( 'the_content', 'youtube_link', 1 );
    		remove_filter( 'the_content_rss', 'youtube_link', 1 );
		
		// POST FOOTER
		remove_action( 'the_content', 'add_post_footer' );
		
		// OUTBRAIN DISPLAY
		remove_filter( 'the_content', 'outbrain_display' );
		
		// WHAT WOULD SETH GODIN DO
		remove_filter( 'the_content', 'wwsgd_filter_content' );
		remove_filter( 'get_the_excerpt', 'wwsgd_filter_excerpt', 1 );
		remove_action( 'wp_footer', 'wwsgd_js' );
		
		// ADD TO ANY
		remove_filter( 'the_content', 'A2A_SHARE_SAVE_add_to_content', 98 );
		
		// SOCIABLE
		if( is_object( $sociable ) )
			remove_filter( 'the_content', array( &$sociable, 'content_hook' ) );
		
		if( is_object( $wpfblike ) ) {
			remove_action( 'the_content', array( &$wpfblike, 'get_content_button_before' ), $wpfblike->buttonpriority );
			remove_action( 'the_content', array( &$wpfblike, 'get_content_button_after' ), $wpfblike->buttonpriority );					
		}
		
		// ShareBar
		remove_filter( 'the_content', 'sharebar_auto' );
		
		// Facebook Like
		remove_filter( 'the_content', 'Add_Like_Button' );
		
		// WP FB Like
		remove_filter( 'the_content', 'fblike_out' );
		
		// WP Tweet Button
		if( is_object( $wpTweetButton ) )
			remove_filter( 'the_content', array( &$wpTweetButton, 'tw_update' ), $wpTweetButton->tw_get_option( 'tw_hook_prio' ) );
		
		// Facebook Share (New)
		remove_filter( 'the_content', 'fb_share' );
		
		// Cool Author Box
		remove_action( 'the_content', 'author_BOX_display' );
		
		// Share and Follow
		if( is_object( $cons_shareFollow ) ) {
			remove_filter( 'the_content', array( &$cons_shareFollow, 'addContent' ), 10 );
			remove_action( 'wp_head', array( &$cons_shareFollow, 'getCDNcodes' ), 1 );
		        remove_action( 'wp_head', array( &$cons_shareFollow, 'addHeaderCode' ), 1 );
		        remove_action( 'wp_head', array( &$cons_shareFollow, 'addHeaderCodeEndBlock' ), 10 );
		        remove_action( 'wp_footer', array( &$cons_shareFollow, 'show_follow_links' ), 1 );
		        remove_action( 'wp_head', array( &$cons_shareFollow, 'doAnalytics' ), 10 );
		}
		// Nofollow Reciprocity
		remove_filter( 'the_content', 'wp_nofollow_reciprocity', 10 );
		remove_filter( 'get_footer', 'wp_nofollow_reciprocity_awareness', 10 );
		remove_action( 'wp_footer', 'wp_nofollow_reciprocity_awareness', 10 );
		
		// GD Star Rating
		if( is_object( $gdsr ) )
			remove_filter( 'the_content', array( $gdsr, 'display_article' ) );
		
		// WP Related Posts 2.3
		remove_filter( 'the_content', 'wp_related_posts_auto', 99 );

		// WP Insert
		remove_filter( 'the_content', 'wp_insert_filter_content' );
		
		// WP Twitter Button
		remove_filter( 'the_content', 'rk_add_twitter_button' );
		
		// Google Plus One
		remove_filter( 'the_content', 'googleone_share' );
		remove_action( 'wp_head', 'googleone_add_js' );
		// WP Plus 1
	        remove_action( 'wp_footer', 'wp_plus_one_script_async' );
		remove_action( 'wp_head', 'wp_plus_one_script' );

		// Add post footer
		remove_action( 'the_content', 'add_post_footer', 0 );
		// tweet button with shortening
		remove_filter( 'the_content', 'tbws_update' );
		// click retweet share
		remove_action ( 'wp_head', 'lacands_fb_meta' );
		remove_filter( 'the_content', 'lacands_wp_filter_post_content');
		// facebook like button plugin
		remove_action( 'the_content', 'facebook_like_button_plugin_output', 99 );
		remove_action( 'wp_head', 'facebook_like_button_plugin_wp_head' );
		remove_action( 'wp_footer', 'facebook_like_button_plugin_wp_footer' );
		// viperbar
		remove_action( 'wp_head', 'add_minify_location' , 99 );
		// tweet meme
		remove_filter( 'the_content', 'tm_update', 8 );
		remove_filter( 'get_the_excerpt', 'tm_remove_filter', 9 );
		remove_action( 'wp_head', 'tm_head' );
		remove_action( 'wp_footer', 'tm_footer' );
		remove_filter( 'the_content', 'twitter_update', 9 );
		// nrelated
		remove_filter( 'the_content', 'nrelate_related_inject', 10 );
		remove_filter( 'the_excerpt', 'nrelate_related_inject', 10 );
		// advertisement management
		remove_filter( 'the_content', 'advertising' );
		remove_action( 'wp_footer', 'adbelowfooter' );
		// better author bio
		remove_filter( 'the_content', array( 'better_Author_box_red', 'filterContent' ) );
		// twitter facebook social share
		remove_action( 'wp_head', 'fb_like_thumbnails' );
		remove_filter( 'the_content', 'kc_twitter_facebook_contents' );
		remove_filter( 'the_excerpt', 'kc_twitter_facebook_excerpt' );
		// addthis
		remove_filter( 'the_title', 'at_title_check' );
		remove_filter( 'language_attributes', 'addthis_language_attributes' );
		remove_action( 'wp_footer', 'addthis_output_script' );
		// scrolling social share
		remove_action( 'wp_head', 'ssharebar_css' );
		remove_filter( 'the_content', 'disp_ssharebar',1 );
		// sphere related content
		remove_action( 'the_content','sphere_content' );
		remove_action( 'wp_head', 'sphere_header' );
		// xpandable author tab
		remove_filter( 'the_content', array( 'xpandable_author_tab', 'filterContent' ) );
		// subscribe remind
		remove_filter( 'the_content', 'subscribe_remind' );
		// facebook comments plugin
		remove_action( 'wp_footer', 'fbmlsetup', 100 );
		remove_filter( 'the_content', 'fbcommentbox', 100 );
		// call to action
		remove_filter( 'the_content', 'cbox_show' );
		remove_filter( 'wp_head', 'cbox_style' );
		// WP about author
		remove_filter( 'the_content', 'insert_wp_about_author' );
		// quick adsense
		remove_filter( 'the_content', 'process_content' );
		// FB like button
		remove_filter( 'the_content', 'fblike_the_content' );
		// getsocial
		remove_filter( 'the_content', 'add_getsocial_div', 30 );
		remove_action( 'wp_footer','add_getsocial_scripts' );
		remove_action( 'wp_footer','add_getsocial_box' );
		remove_action( 'wp_head','add_getsocial_scripts' );
		remove_action( 'wp_head','add_getsocial_box' );
		// seo arlp
		remove_filter( 'the_content', 'seo_alrp_content', 10 );
		remove_filter( 'the_content', 'pk_seo_alrp_auto_link_content_filter', 0 );
		// slick social share
		remove_action( 'wp_footer', array( 'dc_jqslicksocial', 'footer' ) );
		// simple social sharing
		remove_filter( 'the_excerpt', 'add_sharing_excerpt', 25 );
		remove_filter( 'the_content', 'add_sharing_post', 25 );
		remove_filter( 'the_content', 'add_sharing_page', 25 );
		remove_filter( 'the_content', 'add_sharing_homep', 25 );
		// nrelate
		remove_filter( 'wp_footer', 'nrelate_flyout_inject', 10 );
		remove_filter( 'the_content', 'nrelate_flyout_wrap_post', 10 );
		// mp share center
		remove_action( 'wp_head', 'mp_share_center_head_includes' );
		remove_action( 'wp_head', 'mp_share_center_fb_like_thumbnails' );
		remove_action( 'wp_footer', 'mp_share_center_footer_includes' );
		remove_filter( 'the_content', 'mp_share_center_insert_content', 25 );
		remove_shortcode( 'mp_share_center' );

		do_action( 'premise_remove_display_plugins' );
	}
	function process_mailing_list_submissions( $wp ) {
		global $premise_base;
		$data = stripslashes_deep($_POST);

		if( isset($data['constant-contact'] ) && is_array( $data['constant-contact'] ) && is_numeric( $data['constant-contact']['list'] ) && wp_verify_nonce( $data['constant-contact-signup-nonce'], 'constant-contact-signup-'.$data['constant-contact']['list'] ) ) {
			$cc = array_map('trim', $data['constant-contact']);

			$errors = array();
			if( empty( $cc['first-name'] ) )
				$errors[] = __( 'Please enter your first name', 'premise' );

			if( empty( $cc['last-name'] ) )
				$errors[] = __( 'Please enter your last name', 'premise' );

			if( empty( $cc['email'] ) )
				$errors[] = __( 'Please enter your email', 'premise' );
			elseif( !is_email( $cc['email'] ) )
				$errors[] = __( 'Email address is invalid', 'premise' );

			$messages = array();
			if( !empty( $errors ) ) {
				foreach( $errors as $body )
					$messages[] = array( 'type' => 'error', 'body' => $body );
			} else {
				$result = $this->signup_user_constant_contact( $cc['first-name'], $cc['last-name'], $cc['email'], $cc['list'] );
			}

			if( true === $result )
				$messages[] = array( 'type' => 'update', 'body' => __( 'You have been successfully subscribed', 'premise' ) );
			elseif( is_array( $result ) && isset( $result['error'] ) )
				$messages[] = array( 'type' => 'error', 'body' => $result['error'] );

			set_transient( $this->_optin_ConstantContactMessages, $messages );
			wp_redirect( trailingslashit( home_url( $cc['currenturl'] ) ) . '#' . $cc['formkey'] );
			exit();
		}

		if( isset( $data['mailchimp'] ) && is_array( $data['mailchimp'] ) && isset( $data['mailchimp']['list'] ) && wp_verify_nonce( $data['mailchimp-signup-nonce'], 'mailchimp-signup-'.$data['mailchimp']['list'] ) ) {
			$mc = array();
			foreach( $data['mailchimp'] as $key => $value ) {
				if( is_array( $value ) )
					$mc[$key] = array_map( 'trim', $value );
				else
					$mc[$key] = trim( $value );
			}

			$mv = $premise_base->get_mailchimp_merge_vars( $mc['list'] );

			$newvars = array();
			$errors = array();
			foreach( $mv as $data ) {
				if( empty( $mc[$data['tag']] ) && $data['req'] == 1 )
					$errors[] = sprintf( __( '%s is required', 'premise' ), $data['name']);
				elseif( $data['field_type'] == 'email' && !is_email( $mc[$data['tag']] ) )
					$errors[] = sprintf( __( '%s must be a valid email address', 'premise' ), $data['name'] );
				else
					$newvars[$data['tag']] = $mc[$data['tag']];
			}

			$errors = (array) apply_filters( 'premise_optin_extra_fields_errors', $errors, $newvars );
			$messages = array();
			$result = false;
			$confirm = 0;
			$has_errors = 1;
			if( !empty( $errors ) ) {

				foreach( $errors as $body )
					$messages[] = array( 'type' => 'error', 'body' => $body );

			} else {

				$member = apply_filters( 'premise_optin_subscribe_user', true, $newvars );

				if ( ! is_wp_error( $member ) )
					$result = $this->signup_mailchimp_user( $newvars, $mc['list'] );
				else
					$messages[] = array( 'type' => 'error', 'body' => $member->get_error_message() );

			}

			if( true === $result ) {

				do_action( 'premise_optin_complete_order', $member );
				$messages[] = array( 'type' => 'update', 'body' => __( 'You have been successfully subscribed', 'premise' ) );
				$settings = $premise_base->get_settings();
				$confirm = empty( $settings['optin']['mailchimp-single-optin'] ) ? '1' : '0';
				$has_errors = 0;

			} elseif( is_array( $result ) && isset( $result['error'] ) ) {

				$messages[] = array( 'type' => 'error', 'body' => $result['error'] );

			}

			set_transient( $this->_optin_MailChimpMessages, $messages );
			wp_redirect( add_query_arg( array( 'confirm' => $confirm, 'error' => $has_errors ), $mc['currenturl'] ) . '#' . $mc['formkey'] );
			exit();
		}
		if ( isset( $data['aweber'] ) ) {

			$aweber = $data['aweber'];
			if ( ! isset( $aweber['signup-nonce'] ) || ! isset( $aweber['list-id'] ) || ! wp_verify_nonce( $aweber['signup-nonce'], 'aweber-signup-' . $aweber['list-id'] ) )
				return;

			$the_list = $premise_base->get_aweber_list( $aweber['list-id'] );
			if ( ! $the_list )
				return;

			$account = $premise_base->get_aweber_account();
			if ( ! $account )
				return;

			try {

				$list = $account->loadFromUrl( "/accounts/{$account->id}/lists/{$the_list['id']}" );

				# create a subscriber
				$params = array(
					'email' => $aweber['email'],
					'ip_address' => isset( $_SERVER['REMOTE_ADDR'] ) && strlen( $_SERVER['REMOTE_ADDR'] ) > 6 ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
					'ad_tracking' => '',
					'last_followup_message_number_sent' => 0,
					'misc_notes' => '',
					'name' => isset( $aweber['name'] ) ? $aweber['name'] : '',
				);

				// add custom fields only if the list has custom fields
				$custom_fields = array();
				foreach( $the_list['custom_fields'] as $field ) {

					$slug = sanitize_title_with_dashes( $field['name'] );
					if ( isset( $aweber[$slug] ) )
						$custom_fields[$field['name']] = $aweber[$slug];

				}
				if ( ! empty( $custom_fields ) )
					$params['custom_fields'] = $custom_fields;

				$errors = (array) apply_filters( 'premise_optin_extra_fields_errors', array(), $params );

				if ( ! empty( $errors ) ) {

					set_transient( $this->_optin_aweber_messages, array( 'type' => 'error', 'body' => current( $errors ) ) );
					$this->aweber_redirect( array( 'confirm' => '1', 'error' => '1' ), $aweber );

				}

				$member = apply_filters( 'premise_optin_subscribe_user', true, $params );
				if ( is_wp_error( $member ) ) {

					set_transient( $this->_optin_aweber_messages, array( 'type' => 'error', 'body' => $member->get_error_message() ) );
					$this->aweber_redirect( array( 'confirm' => '1', 'error' => '1' ), $aweber );

				}

				if ( empty( $params['name'] ) ) {

					$user = get_user_by( 'id', $member );
					if ( $user )
						$params['name'] = $user->first_name . ' ' . $user->last_name;

				}

				$new_subscriber = $list->subscribers->create( $params );
				do_action( 'premise_optin_complete_order', $member );

				$this->aweber_redirect( array( 'confirm' => '1' ), $aweber );

			} catch( AWeberAPIException $exc ) {

				set_transient( $this->_optin_aweber_messages, array( 'type' => 'error', 'body' => $exc->message ) );
				$this->aweber_redirect( array( 'confirm' => '1', 'error' => '1' ), $aweber );

			}

			exit;

		}
	}
	function signup_mailchimp_user( $vars, $list ) {
		global $premise_base;
		require_once( PREMISE_LIB_DIR . 'mailchimp_api/MCAPI.class.php' );

		$settings = $premise_base->get_settings();
		$mailchimp = new MCAPI( $settings['optin']['mailchimp-api'] );

		$double_opt_in = apply_filters( 'premise_mailchimp_subscribe_double_opt_in', empty( $settings['optin']['mailchimp-single-optin'] ), $list, $vars );
		$result = $mailchimp->listSubscribe( $list, $vars['EMAIL'], $vars, null, $double_opt_in );

		if( 1 != $result )
			return array( 'error' => __( 'Unable to subscribe you to the list.', 'premise' ) );

		return true;
	}

	function unsubscribe_mailchimp_user( $email_address, $list ) {

		global $premise_base;
		require_once( PREMISE_LIB_DIR . 'mailchimp_api/MCAPI.class.php' );

		$settings = $premise_base->get_settings();
		$mailchimp = new MCAPI( $settings['optin']['mailchimp-api'] );

		$mailchimp->listUnsubscribe( $list, $email_address, false, false, empty( $settings['optin']['mailchimp-single-optin'] ) );

	}

	function signup_user_constant_contact( $firstname, $lastname, $email, $list ) {
		global $premise_base;
		require_once( PREMISE_LIB_DIR . 'constant_contact_api/constant_contact_api.php' );

		$settings = $premise_base->get_settings();
		$optin = $settings['optin'];

		$premise_base->setup_constant_contact( $this->_optin_ConstantContactKey, $optin['constant-contact-username'], $optin['constant-contact-password'] );

		$collection = new ContactsCollection();
		list( $search ) = $collection->searchByEmail( $email );
		if( !empty( $search ) ) {
			foreach( $search as $possible ) {
				if( $email == $possible->getEmailAddress() ) {
					$contact = $collection->getContact( $possible->getLink() );
					break;
				}
			}
		}

		$listKey = 'http://api.constantcontact.com/ws/customers/'.$optin['constant-contact-username'].'/lists/'.$list;
		if( $contact ) {
			$existingLists = $contact->getLists();

			if( in_array( $listKey, $existingLists ) )
				return array( 'error' => __( 'You have already subscribed to this mailing list', 'premise' ) );
			else
				$contact->setLists( $listKey );

			$contact->setFirstName( $firstname );
			$contact->setLastName( $lastname );
			$result = $collection->updateContact( $contact->getId(), $contact );
		} else {
			$contact = new Contact();
			$contact->setFirstName( $firstname );
			$contact->setLastName( $lastname );
			$contact->setEmailAddress( $email );
			$contact->setLists( $listKey );

			$result = $collection->createContact( $contact );
		}

		$first = substr( (string)$result,0,1 );

		if( $first != 2 )
			return array( 'error' => __( 'Could not subscribe you to this mailing list', 'premise' ) );

		return true;
	}

	function aweber_redirect( $args, $aweber ) {

		$args_filter = isset( $args['error'] ) && $args['error'] ? 'premise_optin_error_args' : 'premise_optin_success_args';
		$query_args = (array) apply_filters( $args_filter, $args, 'aweber', $aweber );

		$redirect = apply_filters( 'premise_optin_redirect_url', isset( $aweber['currenturl'] ) ? $aweber['currenturl'] : '', 'aweber', $aweber );
		wp_redirect( add_query_arg( $query_args, $redirect ) );
		exit;

	}

	function home_page_canonical_url( $url, $post ) {

		if ( $post->ID == $this->_original_front_page )
			return site_url();

		return $url;

	}

}