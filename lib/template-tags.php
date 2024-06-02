<?php
/**
 * This file contains all template tags necessary for the Premise landing pages plugin.
 * Template tags are generally used on the frontend or in the Landing Page templates.  There
 * are some template tags in this file designed for use on particular landing pages, as well
 * as some template tags needed for all the landing pages.
 *
 * Most of the names should be self explanatory.  For any template tag accepting a post ID, you can
 * pass nothing and it will automatically detect the global post.
 */

/**
 * This particular function is necessary because the callback to wp_iframe
 * must be a string or else warnings get thrown and you get crazy messages
 * in the WP admin.  It just delegates back to the Premise plugin object.
 * @return void
 */
function premise_thickbox() {
	global $Premise;
	return $Premise->displayPremiseResourcesThickboxOutput();
}

function premise_the_editor( $content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2, $deprecated = '' ) {
	global $Premise;
	$Premise->theEditor( $content, $id, $prev_id, $media_buttons, $tab_index );
}

function premise_the_media_buttons() {
	ob_start();
	do_action('media_buttons');
	$buttons = ob_get_clean();
	$buttons = preg_replace('/id=(\'|").*?(\'|")/', '', $buttons);
	echo $buttons;
}

function premise_get_media_upload_src($type, $optional = array()) {
	global $post_ID, $temp_ID;
	$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
	$upload_iframe_src = add_query_arg('post_id', $uploading_iframe_ID, 'media-upload.php');

	if ( 'media' != $type ) {
		$upload_iframe_src = add_query_arg('tab', $type, $upload_iframe_src);
		$upload_iframe_src = add_query_arg('type', $type, $upload_iframe_src);
	}
	if(!empty($optional) && is_array($optional)) {
		$upload_iframe_src = add_query_arg($optional, $upload_iframe_src);
	}

	$upload_iframe_src = apply_filters($type . '_upload_iframe_src', $upload_iframe_src);

	return add_query_arg('TB_iframe', true, $upload_iframe_src);
}

function premise_get_version() {
	return apply_filters( 'premise_get_version', PREMISE_VERSION );
}
function premise_the_version() {
	echo apply_filters( 'premise_the_version', premise_get_version() );
}

function premise_active_admin_tab($tab) {
	if($_GET['page'] == $tab) { echo 'nav-tab-active'; }
}

/// GENERAL TEMPLATE TAGS

function premise_get_header_copy($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_header_copy', $premise_base->getHeaderCopy($postId), $postId);
}
function premise_the_header_copy($postId = null) {
	echo apply_filters('premise_the_header_copy', premise_get_header_copy($postId), $postId);
}

function premise_should_have_header_image($postId = null) {
	global $premise_base;
	return apply_filters('premise_should_have_header_image', $premise_base->shouldHaveHeaderImage($postId));
}

function premise_get_header_image($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_header_image', $premise_base->getHeaderImage($postId), $postId);
}
function premise_the_header_image($postId = null) {
	echo apply_filters('premise_the_header_image', premise_get_header_image($postId), $postId);
}
function premise_get_header_image_url($postId = null) {
	global $premise_base;
	return apply_filters( 'premise_get_header_image_url', $premise_base->get_header_image_url( $postId ), $postId );
}
function premise_the_header_image_url( $postId = null ) {
	echo apply_filters( 'premise_the_header_image_url', premise_get_header_image_url( $postId ), $postId );
}
function premise_get_header_image_alt( $post_id = null) {
	global $premise_base;
	return apply_filters( 'premise_get_header_image_alt', $premise_base->get_header_image_alt( $post_id ), $post_id );
}
function premise_get_header_image_new_window( $post_id = null ) {
	global $premise_base;
	return apply_filters( 'premise_get_header_image_new_window', $premise_base->get_header_image_new_window( $post_id ), $post_id );
}

function premise_get_footer_copy($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_footer_copy', $premise_base->getFooterCopy($postId), $postId);
}
function premise_the_footer_copy($postId = null) {
	echo apply_filters('premise_the_footer_copy', premise_get_footer_copy($postId), $postId);
}

function premise_should_have_footer($postId = null) {
	global $premise_base;
	return apply_filters('premise_should_have_footer', $premise_base->shouldHaveFooter($postId), $postId);
}

function premise_should_have_header($postId = null) {
	global $premise_base;
	return apply_filters('premise_should_have_header', $premise_base->shouldHaveHeader($postId), $postId);
}

/// VIDEO TEMPLATE TAGS

function premise_get_video_embed_code($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_video_embed_code', do_shortcode( $premise_base->getVideoEmbedCode( $postId ) ), $postId);
}
function premise_the_video_embed_code($postId = null) {
	echo apply_filters('premise_the_video_embed_code', premise_get_video_embed_code($postId), $postId);
}

function premise_get_video_copy($postId = null) {
	global $premise_base ;
	return apply_filters('premise_get_video_copy', $premise_base->getVideoCopy($postId), $postId);
}
function premise_the_video_copy($postId = null) {
	echo apply_filters('the_content', premise_get_video_copy($postId), $postId);
}

function premise_get_video_below_copy($postId = null) {
	global $premise_base ;
	return apply_filters('premise_get_video_below_copy', $premise_base->getVideoBelowCopy($postId), $postId);
}
function premise_the_video_below_copy($postId = null) {
	echo apply_filters('the_content', premise_get_video_below_copy($postId), $postId);
}

function premise_get_video_align($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_video_align', $premise_base->getVideoAlign($postId), $postId);
}
function premise_the_video_align($postId = null) {
	echo apply_filters('premise_the_video_align', premise_get_video_align($postId), $postId);
}

function premise_has_video_image($postId = null) {
	$value = trim(premise_get_video_image($postId));
	return apply_filters('premise_had_video_image', !empty($value), $postId);
}
function premise_get_video_image($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_video_image', $premise_base->getVideoImage($postId), $postId);
}
function premise_the_video_image($postId = null) {
	echo apply_filters('premise_the_video_image', premise_get_video_image($postId), $postId);
}

function premise_get_video_image_title($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_video_image_title', $premise_base->getVideoImageTitle($postId), $postId);
}
function premise_the_video_image_title($postId = null) {
	echo apply_filters('premise_the_video_image_title', premise_get_video_image_title($postId), $postId);
}

/// CONTENT SCROLLER

/**
 * This function returns an array of arrays.  Each inner array is associative
 * and has data 'title', and 'text'
 * @return array
 */
function premise_get_content_tabs($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_content_tabs', $premise_base->getContentScrollers($postId));
}
function premise_the_content_tabs($postId = null, $before = '', $after = '', $beforeTitle = '', $afterTitle = '', $beforeContent = '', $afterContent = '') {
	$tabs = premise_get_content_tabs($postId);

	$output = '';
	if(!empty($tabs)) {
		$output .= $before;
		foreach($tabs as $key => $tab) {
			$output .= $beforeTitle.$tab['title'].$afterTitle.$beforeContent.$tab['text'].$afterContent;
		}
		$output .= $after;
	}

	echo apply_filters('premise_the_content_tabs', $output);
}

function premise_should_show_content_scroller_tabs($postId = null) {
	global $premise_base;
	return apply_filters('premise_should_show_content_scroller_tabs', $premise_base->getContentScrollerShowTabs($postId), $postId);
}
function premise_should_show_content_scroller_arrows($postId = null) {
	global $premise_base;
	return apply_filters('premise_should_show_content_scroller_arrows', $premise_base->getContentScrollerShowArrows($postId), $postId);
}

/// PRICING

function premise_get_pricing_columns($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_pricing_columns', $premise_base->getPricingColumns($postId), $postId);
}

function premise_the_above_pricing_table_content($postId = null) {
	echo apply_filters('the_content', premise_get_above_pricing_table_content($postId), $postId);
}
function premise_get_above_pricing_table_content($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_above_pricing_table_content', $premise_base->getAbovePricingTableContent($postId), $postId);
}

function premise_the_below_pricing_table_content($postId = null) {
	echo apply_filters('the_content', premise_get_below_pricing_table_content($postId), $postId);
}
function premise_get_below_pricing_table_content($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_below_pricing_table_content', $premise_base->getBelowPricingTableContent($postId), $postId);
}

function premise_get_pricing_bullet_marker($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_pricing_bullet_marker', $premise_base->getPricingBulletMarker($postId), $postId);
}
function premise_get_pricing_bullet_color($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_pricing_bullet_color', $premise_base->getPricingBulletColor($postId), $postId);
}

/// OPT-IN TEMPLATE TAGS

function premise_get_optin_copy($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_optin_copy', $premise_base->getOptinCopy($postId), $postId);
}
function premise_the_optin_copy($postId = null) {
	echo apply_filters('premise_the_optin_copy', premise_get_optin_copy($postId), $postId);
}

function premise_get_optin_below_copy($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_optin_below_copy', $premise_base->getOptinBelowCopy($postId), $postId);
}
function premise_the_optin_below_copy($postId = null) {
	echo apply_filters('the_content', premise_get_optin_below_copy($postId), $postId);
}

function premise_get_optin_form_code($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_optin_form_code', $premise_base->getOptinFormCode($postId), $postId);
}
function premise_the_optin_form_code($postId = null) {
	echo apply_filters('premise_the_optin_form_code', premise_get_optin_form_code($postId), $postId);
}


function premise_get_optin_align($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_optin_align', $premise_base->getOptinAlign($postId), $postId);
}
function premise_the_optin_align($postId = null) {
	echo apply_filters('premise_the_optin_align', premise_get_optin_align($postId), $postId);
}

/// LONG COPY
function premise_get_subhead($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_subhead', $premise_base->getSubhead($postId), $postId);
}
function premise_the_subhead($postId = null) {
	echo apply_filters('premise_the_subhead', premise_get_subhead($postId), $postId);
}

/// SOCIAL SHARE
function premise_has_social_share_shared_page($postId = null) {
	global $premise_base;
	return apply_filters('premise_has_social_share_shared_page', $premise_base->hasSharedPage($postId), $postId);
}

function premise_get_social_share_share_message($postId = null) {
	global $premise_base;
	return apply_filters('the_content', apply_filters('premise_get_social_share_share_message', $premise_base->getSocialShareMessage($postId), $postId));
}
function premise_the_social_share_share_message($postId = null) {
	echo apply_filters('premise_the_social_share_share_message', premise_get_social_share_share_message($postId), $postId);
}

function premise_get_social_share_teaser_page($postId = null) {
	global $premise_base;
	return '<div class="teaser-content">' . apply_filters('premise_get_social_share_teaser_page', $premise_base->getSocialShareTeaserPage($postId), $postId) . '</div>';
}
function premise_the_social_share_teaser_page($postId = null) {
	echo apply_filters('premise_the_social_share_teaser_page', premise_get_social_share_teaser_page($postId), $postId);
}

function premise_get_social_share_after_share_page($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_social_share_after_share_page', $premise_base->getSocialShareAfterSharePage($postId), $postId);
}
function premise_the_social_share_after_share_page($postId = null) {
	echo apply_filters('premise_the_social_share_after_share_page', premise_get_social_share_after_share_page($postId), $postId);
}

function premise_get_social_share_twitter_text($postId = null, $link = false) {
	global $premise_base;
	return apply_filters('premise_get_social_share_twitter_text', $premise_base->getSocialShareTwitterText($postId, $link), $postId, $link);
}
function premise_the_social_share_twitter_text($postId = null, $link = false) {
	echo apply_filters('premise_the_social_share_twitter_text', premise_get_social_share_twitter_text($postId, $link), $postId, $link);
}

function premise_get_social_share_twitter_icon($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_social_share_twitter_icon', $premise_base->getSocialShareTwitterIcon($postId), $postId);
}
function premise_the_social_share_twitter_icon($postId = null) {
	echo apply_filters('premise_the_social_share_twitter_icon', premise_get_social_share_twitter_icon($postId), $postId);
}

function premise_get_social_share_facebook_icon($postId = null) {
	global $premise_base;
	return apply_filters('premise_get_social_share_facebook_icon', $premise_base->getSocialShareFacebookIcon($postId), $postId);
}
function premise_the_social_share_facebook_icon($postId = null) {
	echo apply_filters('premise_the_social_share_facebook_icon', premise_get_social_share_facebook_icon($postId), $postId);
}

function premise_get_social_share_enhanced_twitter_share_url($postId = null) {
	global $post;
	if( empty( $postId ) )
		$postId = $post->ID;
	
	return apply_filters('premise_get_social_share_enhanced_twitter_share_url', add_query_arg(array('social-share-ID' => $postId, 'social-share-type' => 'twitter'), get_permalink($postId)), $postId);
}
function premise_the_social_share_enhanced_twitter_share_url($postId = null) {
	echo apply_filters('premise_the_social_share_enhanced_twitter_share_url', premise_get_social_share_enhanced_twitter_share_url($postId), $postId);
}

function premise_get_social_share_twitter_share_url($postId = null) {
	$base = 'https://twitter.com/share/';
	$args = array(
		'url' => urlencode(get_permalink($postId)),
		'text' => urlencode(premise_get_social_share_twitter_text()),
	);
	
	return apply_filters('premise_get_social_share_twitter_share_url', add_query_arg($args, $base), $postId);
}
function premise_the_social_share_twitter_share_url($postId = null) {
	echo apply_filters('premise_the_social_share_twitter_share_url', premise_get_social_share_twitter_share_url($postId), $postId);
}

function premise_get_social_share_enhanced_facebook_share_url($postId = null) {
	if(empty($postId)) {
		global $post;
		$postId = $post->ID;
	}
	
	return apply_filters('premise_get_social_share_enhanced_facebook_share_url', add_query_arg(array('social-share-ID' => $postId, 'social-share-type' => 'facebook'), get_permalink($postId)), $postId);
}
function premise_the_social_share_enhanced_facebook_share_url($postId = null) {
	echo apply_filters('premise_the_social_share_enhanced_facebook_share_url', premise_get_social_share_enhanced_facebook_share_url($postId), $postId);
}

function premise_get_social_share_facebook_share_url($postId = null) {
	$base = 'http://www.facebook.com/sharer.php';
	$args = array(
		'u' => urlencode(get_permalink($postId)),
		't' => urlencode(get_the_title()),
	);
	
	return apply_filters('premise_get_social_share_facebook_share_url', add_query_arg($args, $base), $postId);
}
function premise_the_social_share_facebook_share_url($postId = null) {
	echo apply_filters('premise_the_social_share_facebook_share_url', premise_get_social_share_facebook_share_url($postId), $postId);
}

function premise_social_share_get_shared_page_url($postId = null) {
	if(empty($postId)) {
		global $post;
		$postId = $post->ID;
	}
	
	return apply_filters('premise_social_share_get_shared_page_url', wp_nonce_url(add_query_arg('social-share-ID', $postId, get_permalink($postId)), 'premise-shared-content-'.$postId), $postId);
}
function premise_social_share_the_shared_page_url($postId = null) {
	echo apply_filters('premise_social_share_the_shared_page_url', premise_social_share_get_shared_page_url($postId), $postId);
}

function premise_social_share_get_social_share_type() {
	global $premise_base;
	return apply_filters('premise_social_share_get_social_share_type', $premise_base->getSocialShareType());
}
function premise_social_share_get_social_share_method() {
	global $premise_base;
	return apply_filters('premise_social_share_get_social_share_method', $premise_base->get_social_share_method() );
}

function premise_the_after_social_share_tease() {

	$enhanced_share = premise_social_share_get_social_share_type() == 1;
	$facebook_url = $enhanced_share ? premise_get_social_share_enhanced_facebook_share_url() : premise_get_social_share_facebook_share_url();
	$twitter_url = $enhanced_share ? premise_get_social_share_enhanced_twitter_share_url() : premise_get_social_share_twitter_share_url();
	$target = $enhanced_share ? '' : 'target="_blank"';
	$share_method = premise_social_share_get_social_share_method();

?>
		<div class="teaser-share-box">
			<div class="teaser-share-box-inside">
				<div class="teaser-share-message"><?php premise_the_social_share_share_message(); ?></div>
				<div class="teaser-share-icons<?php echo $share_method ? ' teaser-share-single-icon' : ''; ?>">
				<?php 
				if ( ! $share_method || $share_method == 'twitter' ) {
				?>
					<a <?php echo $target; ?> id="twitter-share-icon" href="<?php echo esc_url( $twitter_url ); ?>">
						<img src="<?php premise_the_social_share_twitter_icon(); ?>" alt="Twitter Share Icon" />
						<span><?php _e('Tweet This', 'premise' ); ?></span>
					</a>
				<?php 
				}
				if ( ! $share_method || $share_method == 'facebook' ) { 
				?>
					<a <?php echo $target; ?> id="facebook-share-icon" href="<?php echo esc_url( $facebook_url ); ?>">
						<img src="<?php premise_the_social_share_facebook_icon(); ?>" alt="Facebook Share Icon" />
						<span><?php _e('Share This', 'premise' ); ?></span>
					</a>
				<?php
				}
				?>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>

				<?php if( ! $enhanced_share ) { ?>
				<p class="teaser-share-clickthrough"><a href="<?php premise_social_share_the_shared_page_url(); ?>"><?php _e('Click here when you have shared this page.', 'premise' ); ?></a></p>
				<?php } ?>
			</div>
		</div>

		<?php if( ! $enhanced_share ) { ?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.teaser-share-icons a').click(function(event) {
					event.preventDefault();
					var $this = $(this);
					var href = $this.attr('href');

					var height = 580;
					var width = 980;
					var left = (screen.width / 2) - (width / 2);
					var top = (screen.height / 2) - (height / 2);

					window.open(href, "premise_social_share", 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+width+', height='+height+', top='+top+', left='+left);

					setTimeout('jQuery(".teaser-share-clickthrough").show(); jQuery(".teaser-share-message,.teaser-share-icons").hide();', 5000);
				});
			});
		</script>
		<?php } ?>
<?php
}

function premise_get_pricing_columns_content() {

	global $content_width, $premise_base;

	$columns = premise_get_pricing_columns();
	$column_count = count( $columns );
	$landing_page_style = premise_get_landing_page_style();

	if ( ! isset( $content_width ) || ! $content_width )
		$width = intval( premise_get_fresh_design_option( 'wrap_width', $landing_page_style ) ) - 2 * intval( premise_get_fresh_design_option( 'wrap_padding', $landing_page_style ) );
	else
		$width = (int) $content_width;

	$ctaAlign = premise_get_fresh_design_option( 'pricing_tier_cta_align', $landing_page_style );
	$margins = ( 10 * ( $column_count - 1 ) ); // Account for margins
	 // container extraneous
	$extraneous = ( $column_count * 2 * ( premise_get_fresh_design_option( 'pricing_tier_border', $landing_page_style ) + premise_get_fresh_design_option( 'pricing_tier_padding', $landing_page_style ) ) );
	$available = $width - $margins - $extraneous;
	$max = 0;

	foreach($columns as $column) {
		if( count( $column['attributes'] ) > $max )
			$max = count( $column['attributes'] );
	}

	$marker = strtolower( premise_get_pricing_bullet_marker() );
	if ( ! in_array( $marker, array( 'none', 'default' ) ) )
		$marker .= '-' . strtolower( premise_get_pricing_bullet_color() );

	$column_width = $premise_base->use_premise_theme() ? sprintf( 'width: %dpx', $available / $column_count ) : '';

	$output = '<div class="pricing-table-container"><div class="pricing-table ' . sanitize_html_class( 'pricing-table-' . $marker ) . '">';

	$count = 0;
	foreach( $columns as $key => $column ) {

		$count++;
		$column_title = '<div class="pricing-table-column-header">' . apply_filters( 'the_title', $column['title'] ) . '</div>';

		$attributes = '';
		$atts = 0;
		foreach( $column['attributes'] as $attribute ) {

			$atts++;
			$attributes .= '<li>' . apply_filters( 'pricing_table_attribute', $attribute ) . '</li>';

		}
		for( $i = $atts; $i < $max; $i++ )
			$attributes .= '<li class="nothing">&nbsp;</li>';

		$target = ( isset( $column['newwindow'] ) && $column['newwindow'] == 'yes' ) ? 'target="_blank"' : '';
		$id = 'pricing-table-call-to-action-' . get_the_ID() . '-column-' . esc_attr( $key );
		$action = ! empty( $column['callurl'] ) && ! empty( $column['calltext'] ) ? '<a id="' . $id . '-link" ' . $target . ' class="cta-align' . $ctaAlign . '" href="' . esc_url( $column['callurl'] ) . '">' . esc_html( apply_filters( 'pricing_table_call_to_action', $column['calltext'] ) ) . '</a>' : '';
		$column_action = sprintf( '<div id="%s" class="pricing-table-call-to-action">%s<br class="clear" /></div>', $id, $action );
		$column_features = sprintf( '<div class="pricing-table-column-features"><ul class="pricing-table-column-properties">%s</ul>%s</div>', $attributes, $column_action );

		$output .= sprintf( '<div class="pricing-table-column pricing-column-%d%s" style="%s">%s</div>', $count, $column_count == $count ? ' last' : '', $column_width, $column_title . $column_features );

	}

	$output .= '<br class="clear" /></div></div>';
	return do_shortcode( $output );

}

function premise_get_pricing_content() {

	$content = '<div class="premise-above-pricing-table-content">' . premise_get_above_pricing_table_content() . '</div>';
	$content .= premise_get_pricing_columns_content();
	$content .= '<div class="premise-above-pricing-table-content">' . premise_get_below_pricing_table_content() . '</div>';

	return $content;
}

function premise_get_content_scroller_content() {

	$tabs = premise_get_content_tabs();
	$output = '';

	if ( ! empty( $tabs ) ) {

		$output .= '<div class="coda-slider-wrapper">';
		$content = '';

		if ( premise_should_show_content_scroller_tabs() ) {

			$output .= '<div id="coda-nav-1" class="coda-nav"><ul>';
			$current_slider_total_tabs = count( $tabs );
			$content .= '<div class="coda-slider preload" id="coda-slider-1">';

			foreach( $tabs as $key => $tab ) {

				$tab_title = apply_filters( 'the_title', $tab['title'] );
				$output .= sprintf( '<li id="coda-nav-tab-%1$d" class="tab%1$d"><a title="%2$s" href="#%1$d">%3$s</a></li>', $key + 1, esc_attr( __( $tab['tooltip'], 'premise' ) ), $tab_title );
				$content .= sprintf( '<div class="panel"><div class="container-border"><div class="panel-wrapper"><h2 class="title">%s</h2>%s</div></div></div>', $tab_title, $tab['text'] );

			}

			$output .= '</ul></div>';
			$content .= '</div>';

		}

		if ( premise_should_show_content_scroller_arrows() )
			$output .= '<div id="coda-nav-left-1" class="coda-nav-left"><a href="#">&laquo;</a></div>' . $content . '<div id="coda-nav-right-1" class="coda-nav-right"><a href="#">&raquo;</a></div>';
		else
			$output .= '<div class="coda-nav-left-blank"></div>' . $content . '<div class="coda-nav-right-blank"></div>';

	}

	$output .= '</div>';

	return apply_filters( 'premise_get_content_scroller_content', $output );
}

function premise_do_after_content_scroller_content() {
?>
<script type="text/javascript">
//<!--
	var $ = jQuery;
	jQuery(document).ready(function($) {
		jQuery('#coda-slider-1').codaSlider(
			{
				dynamicArrows: false,
				dynamicTabs: false
			}
		);

		jQuery('a.xtrig').click(function(event) {
			var codaSliderOffset = $('#coda-slider-1').offset();
			$(window).scrollTo( codaSliderOffset.top, 1000 );
		});
	});
//-->
</script>
<?php
}
function premise_do_before_video_content() {
?>
	<div class="entry-video entry-video-align-<?php premise_the_video_align(); ?>">
		<div class="container-border">
			<div class="entry-video-video">
				<?php if(premise_has_video_image()) { ?>
				<a id="inline" href="#entry-video-video-embed"><img src="<?php premise_the_video_image(); ?>" alt="<?php premise_the_video_image_title(); ?>" /></a>
				<?php } else { premise_the_video_embed_code(); } ?>
			</div>
			<?php if(premise_has_video_image()) { ?><div style="display:none"><div id="entry-video-video-embed"><?php premise_the_video_embed_code(); ?></div></div><?php } ?>
			<?php if(premise_get_video_align() != 'center') { ?>
			<div class="entry-video-content"><?php echo apply_filters('the_content', premise_get_video_copy()); ?></div>
			<?php } ?>
			<span class="clear"></span>
		</div>
		<span class="clear"></span>
	</div>
<?php
}
function premise_footer() {
	do_action( 'premise_before_footer' );
	wp_footer();
	do_action( 'premise_after_footer' );
}

function premise_create_quicktags_script( $number ) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	setTimeout(function(){ create_premise_quicktags('<?php echo $number; ?>'); }, 250 );
});

var premise_editor_canvas_<?php echo $number; ?> = document.getElementById('<?php echo $number; ?>');
</script>
<?php
}

function premise_get_landing_page_style() {
	global $premise_base;
	$meta = $premise_base->get_premise_meta( null );

	return isset( $meta['style'] ) ? $meta['style'] : 0;
}
/*
 * structural template functions
 */
function premise_do_header() {
?>

	<div id="image-area">
		<?php
		if( premise_get_header_image_url() ) {

			printf( '<a href="%s" %s><img src="%s" alt="%s" /></a>',
				premise_get_header_image_url(),
				premise_get_header_image_new_window() ? ' target="_blank"' : '',
				premise_get_header_image(),
				premise_get_header_image_alt()
			);

		} else {

			printf( '<img src="%s" alt="%s" />',
				premise_get_header_image(),
				premise_get_header_image_alt()
			);

		}
		?>

	</div><!-- #image-area -->
<?php
}

function premise_do_headline() {

	if ( ! premise_should_have_header() )
		return;

?>
	<div class="headline-area">
		<h1 class="entry-title"><?php the_title(); ?></h1>
<?php
		if ( premise_get_subhead() ) {
?>
		<h2 class="entry-subtitle"><?php premise_the_subhead(); ?></h2>
<?php
		}
?>
	</div>
<?php
}

add_action( 'premise_optin_form_code', 'premise_the_optin_form_code' );
function premise_do_before_post() {

	global $content_width, $premise_base;

	if ( $premise_base->use_premise_theme() || ! isset( $content_width ) || ! $content_width ) {

		$landing_page_style = premise_get_landing_page_style();
		$width = intval( premise_get_fresh_design_option( 'wrap_width', $landing_page_style ) ) - ( 2 * ( intval( premise_get_fresh_design_option( 'wrap_padding', $landing_page_style ) ) + intval( premise_get_fresh_design_option( 'optin_holder_padding', $landing_page_style ) +  premise_get_fresh_design_option( 'optin_holder_border', $landing_page_style ) ) ) );
		$optin_width = $width . 'px';

	} else {

		$width = (int) $content_width;
		$optin_width = '100%';

	}

	switch( premise_get_advice_type() ) {

		case 'opt-in':
?>
			<div style="width: <?php echo $optin_width; ?>" class="entry-optin entry-optin-align-<?php premise_the_optin_align(); ?>">
			<?php
				do_action( 'premise_optin_form_code' );
				if ( premise_get_optin_align() != 'center' )
					echo apply_filters( 'the_content', premise_get_optin_copy() );
			?>
				<span class="clear"></span>
			</div>
<?php
			add_filter( 'the_content', 'premise_get_optin_below_copy', 0 );
			break;

		case 'pricing':

			add_filter( 'the_content', 'premise_get_pricing_content', 0 );
			break;

		case 'social-share':

			add_filter( 'the_content', premise_has_social_share_shared_page() ? 'premise_get_social_share_after_share_page' : 'premise_get_social_share_teaser_page', 0 );
			break;

		case 'content-scroller':

?>

<script type="text/javascript">
//<!--
	var $ = jQuery;
	$(document).ready(function($) {
		$('.coda-slider, .coda-slider .panel').css({
			width: '<?php echo $width - 120; ?>px'
		});
		$('.coda-slider .container-border').css({
			width: '<?php echo $width - 122; ?>px'
		});
	});
//-->
</script>
<?php
			add_filter( 'the_content', 'premise_get_content_scroller_content', 0 );
			break;

		case 'video':

			premise_do_before_video_content();
			add_filter( 'the_content', 'premise_get_video_below_copy', 0 );
			break;

	}
}
function premise_do_after_post() {

	switch( premise_get_advice_type() ) {

		case 'social-share':

			if ( ! premise_has_social_share_shared_page() )
				premise_the_after_social_share_tease();
			break;

		case 'content-scroller':

			premise_do_after_content_scroller_content();
			break;
	}
}
/*
 * lookup functions
 */
function premise_get_advice_type() {

	global $premise_base;
	return $premise_base->get_advice_type();

}
