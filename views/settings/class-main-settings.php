<?php
class Premise_Main_Settings extends Premise_Admin_Boxes {
	/**
	 * Flag indicating show Aweber lists for possible refresh
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	var $_aweber_custom_field_refresh = false;

	function __construct() {

		global $premise_base;

		$settings_field = PREMISE_SETTINGS_FIELD;
		$default_settings = $premise_base->get_default_settings();

		$menu_ops = array(
			'main_menu' => array(
				'page_title'	=> __( 'Main Settings', 'premise' ),
				'menu_title'	=> __( 'Premise', 'premise' ),
				'capability'	=> 'manage_options',
				'icon_url'		=> PREMISE_RESOURCES_URL . 'images/icon-16x16.png',
				'position'		=> '55.99'
			),
			'first_submenu' => array( /** Do not use without 'main_menu' */
				'page_title'	=> __( 'Main Settings', 'premise' ),
				'menu_title'	=> __( 'Main Settings', 'premise' ),
				'capability'	=> 'manage_options'
			),
		);

		$page_ops = array(); /** @todo: change the screen icon */

		$this->create( 'premise-main', $menu_ops, $page_ops, $settings_field, $default_settings );

		add_filter( 'sanitize_option_' . $settings_field, array( $this, 'sanitize' ), 10, 2 );
	}

	function metaboxes() {

		add_meta_box( 'premise-main-settings', __( 'General', 'premise' ), array( $this, 'main_settings_metabox' ), $this->pagehook, 'main' );
		add_meta_box( 'premise-aweber-settings', __( 'AWeber', 'premise' ), array( $this, 'aweber_settings_metabox' ), $this->pagehook, 'main' );
		add_meta_box( 'premise-constant-contact-settings', __( 'Constant Contact', 'premise' ), array( $this, 'constant_contact_settings_metabox' ), $this->pagehook, 'main' );
		add_meta_box( 'premise-mailchimp-settings', __( 'MailChimp', 'premise' ), array( $this, 'mailchimp_settings_metabox' ), $this->pagehook, 'main' );
		add_meta_box( 'premise-content-settings', __( 'Content', 'premise' ), array( $this, 'content_settings_metabox' ), $this->pagehook, 'main' );
		add_meta_box( 'premise-seo-settings', __( 'SEO', 'premise' ), array( $this, 'seo_settings_metabox' ), $this->pagehook, 'main' );
		add_meta_box( 'premise-sharing-settings', __( 'Sharing', 'premise' ), array( $this, 'sharing_settings_metabox' ), $this->pagehook, 'main' );
		add_meta_box( 'premise-testing-settings', __( 'Testing', 'premise' ), array( $this, 'testing_settings_metabox' ), $this->pagehook, 'main' );

		if ( current_user_can( 'unfiltered_html' ) )
			add_meta_box( 'premise-script-settings', __( 'Scripts', 'premise' ), array( $this, 'script_settings_metabox' ), $this->pagehook, 'main' );

	}

	function main_settings_metabox() {

		global $Premise;
	?>

		<p class="premise-option-box">
			<strong><?php _e( 'API Key', 'premise' ); ?></strong><br /><br /><input type="text" name="<?php echo $this->get_field_name( 'main', 'api-key' ); ?>" id="<?php $this->get_field_id( 'main', 'api-key' ); ?>" value="<?php echo $this->get_field_value( 'main', 'api-key' ); ?>" style="min-width:50%" />
		</p>

		<p class="premise-option-box">
			<strong><?php _e('Landing Page URLs', 'premise' ); ?></strong><br /><br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'main', 'rewrite-root' ); ?>" id="<?php echo $this->get_field_id( 'main', 'rewrite-root' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'main', 'rewrite-root' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'main', 'rewrite-root' ); ?>"><?php printf(__('Landing pages should have URLs off the site root (like <code>%slanding-page-slug/</code>)', 'premise' ), home_url('/')); ?></label>
		</p>

		<p id="premise-main-rewrite-container" class="premise-option-box">
			<strong><?php _e( 'Rewrite Structure', 'premise' ); ?></strong>:<br /><code><?php echo esc_html( site_url( '/' ) ); ?></code><input type="text" class="code" name="<?php echo $this->get_field_name( 'main', 'rewrite' ); ?>" id="<?php $this->get_field_id( 'main', 'rewrite' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'main', 'rewrite' ) ); ?>" /><code>/landing-page-title/</code>
		</p>

		<p class="premise-option-box">
			<strong><?php _e('Membership', 'premise' ); ?></strong><br /><br />
<?php if ( $Premise->has_valid_premise_api_key() ) { ?>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'main', 'member-access' ); ?>" id="<?php echo $this->get_field_id( 'main', 'member-access' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'main', 'member-access' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'main', 'member-access' ); ?>"><?php _e( 'Enable the membership module', 'premise' ); ?></label>
<?php } else { ?>
			<input type="hidden" name="premise[main][member-access]" value="<?php echo isset( $main['member-access'] ) ? (int) $main['member-access'] : '0'; ?>" />
			<?php _e( 'Add your API key above to enable the membership module', 'premise' ); ?>
<?php } ?>
		</p>

<?php
	}

	function content_settings_metabox() {

		global $Premise;
//@todo: hook switch theme to check to disable
		$theme_support = get_theme_support( 'premise-landing-pages' );
		$theme_support = ! empty( $theme_support ) ? current( $theme_support ) : '';

		if ( ! $theme_support ) {
		?>
		<input type="hidden" name="premise[main][theme-support]" value="" />
		<?php
		} else {
		?>
		<p class="premise-option-box">
			<strong><?php _e('Theme Support', 'premise' ); ?></strong><br /><br />
			<?php if ( $Premise->has_valid_premise_api_key() ) { ?>
			<label>
				<input <?php checked( $theme_support, $this->get_field_value( 'main', 'theme-support' ) ); ?> type="checkbox" name="<?php echo $this->get_field_name( 'main', 'theme-support' ); ?>" id="premise-main-theme-support" value="<?php echo esc_attr( $theme_support ); ?>" />
				<?php printf( __( 'Use %s in my current theme for my landing page template', 'premise' ), $theme_support ); ?>
			</label>
		<?php } else { ?>
			<?php _e( 'Add your API key above to enable the site theme support', 'premise' ); ?>
		<?php } ?>
		</p>
		<?php 
		}
		?>
		<p><?php _e('The content settings below are defaults and may be overridden per landing page.', 'premise' ); ?></p>

		<div class="premise-option-box"><strong><?php _e('Default Favicon', 'premise' ); ?></strong><br />
			<p><?php _e('Enter the URL to a favicon you would like to use for your landing pages.  You can override this per landing page, but the value here will be used by default.', 'premise' ); ?></p>
			<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'main', 'default-favicon' ); ?>" id="premise-main-default-favicon" value="<?php echo esc_attr( $this->get_field_value( 'main', 'default-favicon' ) ); ?>" />
			<a class="thickbox" href="<?php echo esc_attr(add_query_arg(array('send_to_premise_field_id'=>'premise-main-default-favicon', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image')))); ?>"><?php _e('Upload', 'premise' ); ?></a><br />
		</div>

		<div class="premise-option-box"><strong><?php _e('Default Header Image', 'premise' ); ?></strong><br />
			<p><?php _e('Enter the URL to an image that you wish to use in the header of your landing pages by default.', 'premise' )?></p>
			<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'main', 'default-header-image' ); ?>" id="premise-main-default-header-image" value="<?php echo esc_attr( $this->get_field_value( 'main', 'default-header-image' ) ); ?>" />
			<a class="thickbox" href="<?php echo esc_attr(add_query_arg(array('send_to_premise_field_id'=>'premise-main-default-header-image', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image')))); ?>"><?php _e('Upload', 'premise' ); ?></a><br />
		</div>

		<div class="premise-option-box"><strong><?php _e('Default Header Image Alt Text', 'premise' ); ?></strong><br />
			<p><?php _e('(optional) Enter the alternate text you would like for the header image.', 'premise' )?></p>
			<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'main', 'default-header-image-alt' ); ?>" id="<?php echo $this->get_field_id( 'main', 'default-header-image-alt' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'main', 'default-header-image-alt' ) ); ?>" /><br />
		</div>

		<div class="premise-option-box"><strong><?php _e('Default Header Image Link', 'premise' ); ?></strong><br />
			<p><?php _e('(optional) Enter the URL you would like the header image to link to.', 'premise' )?></p>
			<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'main', 'default-header-image-url' ); ?>" id="<?php echo $this->get_field_id( 'main', 'default-header-image-url' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'main', 'default-header-image-url' ) ); ?>" /><br />
		</div>
		<div class="premise-option-box"><strong><?php _e('Default Footer Text', 'premise' ); ?></strong><br />
			<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'main', 'default-footer-text' ); ?>" id="<?php echo $this->get_field_id( 'main', 'default-footer-text' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'main', 'default-footer-text' ) ); ?>" />
		</div>
	<?php
	}

	function seo_settings_metabox() {

	?>
		<p><strong><?php _e('SEO Tool', 'premise' ); ?></strong><br /><br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'seo', 'indicator' ); ?>" id="<?php echo $this->get_field_id( 'seo', 'indicator' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'seo', 'indicator' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'seo', 'indicator' ); ?>"><?php _e( 'Use Premise SEO', 'premise' ); ?></label>
		</p>

		<p><?php _e('The SEO settings below are defaults and will be overridden per landing page.', 'premise' ); ?></p>

		<div class="premise-option-box"><strong><?php _e('Robots Meta Settings', 'premise' ); ?></strong><br />
			<p><?php _e( 'You can add these tags to tell robots not to index the content of a page, not scan it for links to follow, and/or remove your pages from the Google Cache.', 'premise' ); ?></p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'seo', 'noindex' ); ?>" id="<?php echo $this->get_field_id( 'seo', 'noindex' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'seo', 'noindex' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'seo', 'noindex' ); ?>"><?php _e('Apply <code>noindex</code> to page', 'premise' ); ?></label><br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'seo', 'nofollow' ); ?>" id="<?php echo $this->get_field_id( 'seo', 'nofollow' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'seo', 'nofollow' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'seo', 'nofollow' ); ?>"><?php _e('Apply <code>nofollow</code> to page', 'premise' ); ?></label><br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'seo', 'noarchive' ); ?>" id="<?php echo $this->get_field_id( 'seo', 'noarchive' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'seo', 'noarchive' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'seo', 'noarchive' ); ?>"><?php _e('Apply <code>noarchive</code> to page', 'premise' ); ?></label><br />
		</div>

		<div class="premise-option-box"><strong><?php _e('Feed Autodetect', 'premise' ); ?></strong><br />
			<p><?php _e( 'On by default &mdash; checking the box turns it off.  You may not want your feed autodetect to show up on a landing page, as it may deter from your call to action.  You can turn off the feed autodiscovery on landing pages and not the rest of your site.', 'premise' ); ?></p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'seo', 'disable-feed' ); ?>" id="<?php echo $this->get_field_id( 'seo', 'disable-feed' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'seo', 'disable-feed' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'seo', 'disable-feed' ); ?>"><?php _e( 'Turn off Feed Autodetect for landing pages?', 'premise' ); ?></label><br />
		</div>

	<?php
	}
	function aweber_settings_metabox() {
		global $premise_base;
//@todo: add function to get auth url

		$authorization_class = $this->get_field_value( 'optin', 'aweber-api' ) && $this->get_field_value( 'optin', 'aweber-enhanced' ) && $this->get_field_value( 'optin', 'aweber-app-id' ) ? 'enhanced' : 'basic';
	?>
		<p><strong><?php _e( 'AWeber Forms', 'premise' ); ?></strong><br /><br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'optin', 'aweber-api' ); ?>" id="<?php echo $this->get_field_id( 'optin', 'aweber-api' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'optin', 'aweber-api' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'optin', 'aweber-api' ); ?>"><?php _e( 'Use opt in forms hosted on my site?', 'premise' ); ?></label><br />
		</p>
		<p class="premise-aweber-enhanced"><strong><?php _e( 'AWeber Enhanced', 'premise' ); ?></strong><br /><br />
			<input type="checkbox" name="<?php echo $this->get_field_name( 'optin', 'aweber-enhanced' ); ?>" id="<?php echo $this->get_field_id( 'optin', 'aweber-enhanced' ); ?>" value="1" <?php checked( '1', $this->get_field_value( 'optin', 'aweber-enhanced' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'optin', 'aweber-enhanced' ); ?>"><?php _e( 'Use enhanced access which provides access to all AWeber API functions?', 'premise' ); ?></label><br />
		</p>
		<p class="premise-aweber-enhanced premise-aweber-app-id-wrap"><strong><?php _e( 'AWeber App ID', 'premise' ); ?></strong><br /><br />
			<input type="text" class="code" name="<?php echo $this->get_field_name( 'optin', 'aweber-app-id' ); ?>" id="<?php echo $this->get_field_id( 'optin', 'aweber-app-id' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'optin', 'aweber-app-id' ) ); ?>" /><br />
		<?php printf( __( 'Control over member email subscriptions requires an AWeber App ID. <a href="%s">Create an AWeber app</a> then paste the App ID in the field above to enable full AWeber API access.', 'premise' ), esc_url( 'https://labs.aweber.com/apps' ) ); ?>
		</p>
		<p class="premise-aweber-<?php echo $authorization_class; ?>"><strong><?php _e( 'AWeber Authorization Code', 'premise' ); ?></strong><br /><br />
			<input type="text" class="code large-text" name="<?php echo $this->get_field_name( 'optin', 'aweber-authorization' ); ?>" id="<?php echo $this->get_field_id( 'optin', 'aweber-authorization' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'optin', 'aweber-authorization' ) ); ?>" /><br />
			<a href="<?php echo $premise_base->get_aweber_authorization_url( $this->get_field_value( 'optin', 'aweber-app-id' ) ); ?>" target="_blank" class="premise-aweber-authorization-url"><?php _e('Click here to get your authorization code.', 'premise' ); ?></a>
		</p>
		<?php submit_button( __( 'Refresh Lists', 'premise' ), 'button secondary', $this->get_field_name( 'refresh-aweber' ) ); ?>

	<?php
		if ( ! $this->_aweber_custom_field_refresh  || ! $this->get_field_value( 'optin', 'aweber-api' ) )
			return;

		$lists = $premise_base->get_aweber_lists();
		if ( empty( $lists ) )
			return;

		$comma = '';
		wp_nonce_field( 'premise-refresh-aweber-list', 'premise-aweber-list-nonce' );
?>
<script type="text/javascript">
/* <![CDATA[ */
var premise_aweber_lists = Array(
<?php
		foreach( $lists as $list ) {

			printf( "%s '%d'\n", $comma, $list['id'] );
			$comma = ',';

		}
?>
);
function premise_refresh_aweber_custom_fields() {
	if (!premise_aweber_lists.length) {
		jQuery('.premise-aweber-ajax-feedback').hide();
		jQuery('#_premise_settings\\[refresh-aweber\\]').removeAttr('disabled');
		return;
	}

	list_data = {
		action: 'premise_refresh_aweber_list',
		list_id: premise_aweber_lists.pop(),
		nonce: jQuery('#premise-aweber-list-nonce').val()
	};
	jQuery.post(ajaxurl,
		list_data,
		function(data){
			if (data.list_name) {
				var $li = jQuery('<li></li>');
				$li.addClass('premise_refreshed_aweber_list');
				$li.html(data.list_name);
				jQuery('.premise_refreshed_aweber_lists').prepend($li);
			}
			setTimeout(premise_refresh_aweber_custom_fields, 900);
		},
		'json'
	).fail(function(){ 
		premise_refresh_aweber_custom_fields(); 
	});
}
jQuery(document).ready(function(){
	jQuery('#message').hide();
	jQuery('#_premise_settings\\[refresh-aweber\\]').attr('disabled','disabled');
	jQuery('#aweber-message').append(jQuery('.premise_refresh_aweber_list').detach());
	premise_refresh_aweber_custom_fields();
});
/* ]]> */
</script>
<div class="premise_refresh_aweber_list">
<h4 class="premise_refresh_aweber_list_header"><?php _e( 'Refreshing List Fields', 'premise' ); ?> <img alt="" title="" class="premise-aweber-ajax-feedback" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" /></h4>
<ul class="premise_refreshed_aweber_lists"></ul>
</div>
<?php
	}

	function constant_contact_settings_metabox() {

	?>

		<p><strong><?php _e('Constant Contact Username', 'premise' ); ?></strong><br /><br />
			<input type="text" class="code regular-text" name="<?php echo $this->get_field_name( 'optin', 'constant-contact-username' ); ?>" id="<?php echo $this->get_field_id( 'optin', 'constant-contact-username' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'optin', 'constant-contact-username' ) ); ?>" /><br />
		</p>
		<p><strong><?php _e('Constant Contact Password', 'premise' ); ?></strong><br /><br />
			<input type="text" class="code regular-text" name="<?php echo $this->get_field_name( 'optin', 'constant-contact-password' ); ?>" id="<?php echo $this->get_field_id( 'optin', 'constant-contact-password' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'optin', 'constant-contact-password' ) ); ?>" /><br />
		</p>

	<?php
	}
	function mailchimp_settings_metabox() {

	?>

		<p><strong><?php _e('MailChimp API Key', 'premise' ); ?></strong><br /><br />
			<input type="text" class="code large-text" name="<?php echo $this->get_field_name( 'optin', 'mailchimp-api' ); ?>" id="<?php echo $this->get_field_id( 'optin', 'mailchimp-api' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'optin', 'mailchimp-api' ) ); ?>" /><br />
			<a href="http://admin.mailchimp.com/account/api-key-popup" target="_blank"><?php _e('Get your API key.', 'premise' ); ?></a>
		</p>

		<p><strong><?php _e('Double Opt In', 'premise' ); ?></strong><br /><br />
			<input <?php checked( 1, $this->get_field_value( 'optin', 'mailchimp-single-optin' ) ); ?> type="checkbox" name="<?php echo $this->get_field_name( 'optin', 'mailchimp-single-optin' ); ?>" id="<?php echo $this->get_field_id( 'optin', 'mailchimp-single-optin' ); ?>" value="1" />
			<label for="<?php echo $this->get_field_id( 'optin', 'mailchimp-single-optin' ); ?>"><?php _e( 'Turn off the MailChimp double opt in confirmation email and confirmation page.', 'premise' ); ?></label>
		</p>

	<?php
	}
	function sharing_settings_metabox() {

		$sharing_type = $this->get_field_value( 'sharing', 'type' );
	?>

		<p><?php _e('Premise contains a landing page type that makes your visitors share your content before they can access the full page. You can pick from simple or enhanced sharing types below.', 'premise' ); ?></p>

		<p><strong><?php _e('Sharing Type', 'premise' ); ?></strong><br /><br />
			<input class="premise-sharing-type" type="radio" name="<?php echo $this->get_field_name( 'sharing', 'type' ); ?>" id="<?php echo $this->get_field_id( 'sharing', 'type' ); ?>" value="0" <?php checked( empty( $sharing_type ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'sharing', 'type' ); ?>"><?php _e( 'Simple - Sharing based on the "honor system." No technical knowledge required, but does not ensure the share is completed and provides no tracking of page shares.', 'premise' ); ?></label><br />
			<br />
			<input class="premise-sharing-type" type="radio" name="<?php echo $this->get_field_name( 'sharing', 'type' ); ?>" id="<?php echo $this->get_field_id( 'sharing', 'type' ); ?>" value="1" <?php checked( '1', $sharing_type ); ?> />
			<label for="<?php echo $this->get_field_id( 'sharing', 'type' ); ?>"><?php _e('Enhanced', 'premise' ); ?> - <?php printf( __( 'Only select this if you feel comfortable registering Twitter (<a href="%s" target="_blank">instructions</a>) and Facebook (<a href="%s" target="_blank">instructions</a>) applications.', 'premise' ), 'https://members.getpremise.com/help-social-share-twitter.aspx', 'https://members.getpremise.com/help-social-share-facebook.aspx' ); ?></label>
		</p>
		<p class="premise-sharing-type-enhanced-dependent"><strong><?php _e('Twitter Consumer Key', 'premise' ); ?></strong><br /><br />
			<input type="text" class="large-text" name="<?php echo $this->get_field_name( 'sharing', 'twitter-consumer-key' ); ?>" id="<?php echo $this->get_field_id( 'sharing', 'twitter-consumer-key' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'sharing', 'twitter-consumer-key' ) ); ?>" />
		</p>
		<p class="premise-sharing-type-enhanced-dependent"><strong><?php _e('Twitter Consumer Secret', 'premise' ); ?></strong><br /><br />
			<input type="text" class="large-text" name="<?php echo $this->get_field_name( 'sharing', 'twitter-consumer-secret' ); ?>" id="<?php echo $this->get_field_id( 'sharing', 'twitter-consumer-secret' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'sharing', 'twitter-consumer-secret' ) ); ?>" />
		</p>
		<p class="premise-sharing-type-enhanced-dependent"><strong><?php _e('Facebook App ID', 'premise' ); ?></strong><br /><br />
			<input type="text" class="large-text" name="<?php echo $this->get_field_name( 'sharing', 'facebook-app-id' ); ?>" id="<?php echo $this->get_field_id( 'sharing', 'facebook-app-id' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'sharing', 'facebook-app-id' ) ); ?>" />
		</p>
		<p class="premise-sharing-type-enhanced-dependent"><strong><?php _e('Facebook App Secret', 'premise' ); ?></strong><br /><br />
			<input type="text" class="large-text" name="<?php echo $this->get_field_name( 'sharing', 'facebook-app-secret' ); ?>" id="<?php echo $this->get_field_id( 'sharing', 'facebook-app-secret' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'sharing', 'facebook-app-secret' ) ); ?>" />
		</p>

	<?php	
	}

	function testing_settings_metabox() {

	?>
		<div class="premise-option-box"><strong><?php _e('Google Analytics Account ID', 'premise' ); ?></strong><br /><br />
				<p><?php _e( 'Leave this blank if you are using a Google Analytics plugin. Otherwise enter the entire account ID, including the UA string. If you do not know your account ID, please just paste your entire tracking script and Premise will extract it.', 'premise' ); ?></p>
				<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'tracking', 'account-id' ); ?>" id="<?php echo $this->get_field_id( 'tracking', 'account-id' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'tracking', 'account-id' ) ); ?>" /> <br />
		</div>
		<div class="premise-option-box"><strong><?php _e('Visual Website Optimizer Account ID', 'premise' ); ?></strong><br /><br />
				<input class="regular-text" type="text" name="<?php echo $this->get_field_name( 'tracking', 'vwo-account-id' ); ?>" id="<?php echo $this->get_field_id( 'tracking', 'vwo-account-id' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'tracking', 'vwo-account-id' ) ); ?>" />
		</div>

	<?php
	}

	function script_settings_metabox() {

	?>

		<p><?php _e('Premise allows you to add content to either the header or footer.  Insert some code into the textareas below to make it appear on all Premise landing pages. These fields are meant for adding JavaScript, tracking codes and CSS, not content.', 'premise' ); ?></p>

		<p class="premise-option-box"><strong><?php _e('Header Scripts', 'premise' ); ?></strong><br /><br />
			<textarea rows="6" class="large-text code" name="<?php echo $this->get_field_name( 'scripts', 'header' ); ?>" id="$this->get_field_id( 'scripts', 'header' )"><?php echo esc_html( $this->get_field_value( 'scripts', 'header' ) ); ?></textarea>
		</p>
		<p class="premise-option-box"><strong><?php _e('Footer Scripts', 'premise' ); ?></strong><br /><br />
			<textarea rows="6" class="large-text code" name="<?php echo $this->get_field_name( 'scripts', 'footer' ); ?>" id="$this->get_field_id( 'scripts', 'footer' )"><?php echo esc_html( $this->get_field_value( 'scripts', 'footer' ) ); ?></textarea>
		</p>

	<?php
	}

	function enqueue_admin_css() {

		wp_enqueue_style( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.css', array( 'thickbox' ), PREMISE_VERSION );

	}

	function scripts() {

		parent::scripts();
		wp_enqueue_script( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.js', array( 'jquery', 'jquery-ui-sortable', 'farbtastic', 'jquery-form', 'thickbox' ), PREMISE_VERSION );

	}
	function sanitize( $newvalue, $option ) {

		global $premise_base, $Premise;
		
		$seo_indicator = isset( $newvalue['seo']['indicator'] ) ? $newvalue['seo']['indicator'] : 0;
		$newvalue = array_merge( $premise_base->get_default_settings(), $newvalue );
		if ( $seo_indicator == 1 ) {

			$newvalue['seo']['noindex'] = isset( $newvalue['seo']['noindex'] ) && $newvalue['seo']['noindex'] == 1 ? 1 : 0;
			$newvalue['seo']['nofollow'] = isset( $newvalue['seo']['nofollow'] ) && $newvalue['seo']['nofollow'] == 1 ? 1 : 0;
			$newvalue['seo']['noarchive'] = isset( $newvalue['seo']['noarchive'] ) && $newvalue['seo']['noarchive'] == 1 ? 1 : 0;
			$newvalue['seo']['disable-feed'] = isset( $newvalue['seo']['disable-feed'] ) && $newvalue['seo']['disable-feed'] == 1 ? 1 : 0;

		} else {

			$newvalue['seo']['indicator'] = 0;

		}

		if ( isset( $newvalue['main']['rewrite'] ) ) {

			if ( isset( $newvalue['main']['rewrite-root'] ) && $newvalue['main']['rewrite-root'] == 1 )
				$newvalue['main']['rewrite'] = '';
			else
				$newvalue['main']['rewrite'] = empty( $newvalue['main']['rewrite'] ) ? 'landing' : $newvalue['main']['rewrite'];

		}

		$oldSettings = $premise_base->get_settings();
		unset( $oldSettings['reset'] );
		$oldSettings = array_merge( $premise_base->get_default_settings(), $oldSettings );

		$settings = array_merge( $oldSettings, $newvalue );
//@todo: switch error handling to admin class method
		$errors = array();
		$settings['main']['theme-support'] = ! empty( $settings['main']['theme-support'] ) ? $settings['main']['theme-support'] : '';
		$settings['main']['api-key'] = trim( $settings['main']['api-key'] );

		if ( $settings['main']['api-key'] != $oldSettings['main']['api-key'] ) {

			$Premise->initializeApi( $settings['main']['api-key'] );
			$check = $Premise->getGraphicsCategories();
			if ( is_wp_error( $check ) ) {

				$errors[] = __('Your new Premise API key could not be confirmed.  Please enter a valid key below.', 'premise' );
				$settings['main']['api-key'] = '';

			} else {

				$Premise->deleteUpdateTransient();
				if ( trim( $oldSettings['main']['api-key'] ) == '' )
					$settings['main']['member-access'] = '1';

			}
		}

		if ( $settings['optin']['aweber-authorization'] != $oldSettings['optin']['aweber-authorization'] ) {

			$aweberCheck = $Premise->validateAweberAuthorizationCode( $settings['optin']['aweber-authorization'] );
			if ( is_array( $aweberCheck ) && ! isset( $aweberCheck['error'] ) ) {

				$settings['optin']['allowed']['aweber'] = 1;
				$settings['optin']['aweber-account-info'] = $aweberCheck;

			} else {

				$settings['optin']['allowed']['aweber'] = 0;
				$errors[] = $aweberCheck['error'];
				$settings['optin']['aweber-authorization'] = '';

			}

		} else {

			$settings['optin']['aweber-account-info'] = $oldSettings['optin']['aweber-account-info'];
			$settings['optin']['allowed']['aweber'] = $oldSettings['optin']['allowed']['aweber'];

		}

		if ( $settings['optin']['mailchimp-api'] != $oldSettings['optin']['mailchimp-api'] ) {

			$mailchimpCheck = $Premise->validateMailChimpAPIKey( $settings['optin']['mailchimp-api'] );
			if ( true === $mailchimpCheck ) {

				$settings['optin']['allowed']['mailchimp'] = 1;

			} else {

				$settings['optin']['allowed']['mailchimp'] = 0;
				$errors[] = $mailchimpCheck['error'];
				$settings['optin']['mailchimp-api'] = '';

			}

		} else {

			$settings['optin']['allowed']['mailchimp'] = $oldSettings['optin']['allowed']['mailchimp'];

		}

		if ( $settings['optin']['constant-contact-username'] != $oldSettings['optin']['constant-contact-username'] || $settings['optin']['constant-contact-password'] != $oldSettings['optin']['constant-contact-password'] ) {

			$constantContactCheck = $premise_base->validate_constant_contact_credentials( $settings['optin']['constant-contact-username'], $settings['optin']['constant-contact-password'] );
			if(true === $constantContactCheck) {

				$settings['optin']['allowed']['constant-contact'] = 1;

			} else {

				$settings['optin']['allowed']['constant-contact'] = 0;
				$errors[] = $constantContactCheck['error'];
				$settings['optin']['constant-contact-username'] = '';
				$settings['optin']['constant-contact-password'] = '';
			}

		} else {

			$settings['optin']['allowed']['constant-contact'] = $oldSettings['optin']['allowed']['constant-contact'];

		}

		$settings['tracking']['account-id'] = $Premise->parseAccountIdentifierFromGoogleWebsiteOptimizerEmbedCode( $settings['tracking']['account-id'] );
		if ( ! empty( $errors ) )
			update_user_option( get_current_user_id(), 'premise_main_settings_errors', $errors );

		flush_rewrite_rules( false );

		return $settings;

	}
	public function notices() {

		global $premise_base;

		if ( ! accesspress_is_menu_page( $this->page_id ) )
			return;

		$settings = $premise_base->get_settings();
		if ( isset( $settings['refresh-aweber'] ) ) {

			unset( $settings['refresh-aweber'] );
			remove_filter( 'sanitize_option_' . $this->settings_field, array( $this, 'sanitize' ), 10, 2 );
			$premise_base->update_settings( $settings );
			add_filter( 'sanitize_option_' . $this->settings_field, array( $this, 'sanitize' ), 10, 2 );

			$premise_base->get_aweber_lists( true );
			$this->_aweber_custom_field_refresh = true;

			$message = __( 'AWeber lists refreshed.', 'premise' );

			echo '<div id="aweber-message" class="updated"><p><strong>' . $message . '</strong></p></div>';

		}

		$my_user_id = get_current_user_id();
		$errors = get_user_option( 'premise_main_settings_errors', $my_user_id );
		if ( ! empty( $errors ) ) {

			delete_user_option( $my_user_id, 'premise_main_settings_errors' );
			foreach( $errors as $error )
				printf( '<div class="error">%s</div>', $error );

		}

		return parent::notices();
	}
}

new Premise_Main_Settings;
