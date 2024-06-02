<?php
/*
Meta boxes for the Premise post type
*/
class Premise_Meta_Boxes {
	function __construct( $post ) {
		global $premise_base;

		add_action( 'edit_form_advanced', array( &$this, 'content_scroller_tabs' ) );
		add_action( 'edit_form_advanced', array( &$this, 'pricing_columns' ) );

		$type = $premise_base->get_page_type( $post->ID );
		$post_type = $premise_base->get_post_type();
		remove_post_type_support( $post_type, 'editor' );

		switch( $type ) {
			case 'content-scroller':
				add_meta_box( 'premise-content-scroller-controls', __( 'Tab/Panel Settings', 'premise' ), array( $this, 'content_scroller' ), $post_type, 'side', 'core' );
				break;
			case 'opt-in':
				add_meta_box( 'premise-opt-in', __( 'Opt In Copy', 'premise' ), array( $this, 'opt_in' ), $post_type, 'normal', 'high' );
				add_meta_box( 'premise-opt-in-below-copy', __( 'Copy Below the Opt In Form', 'premise' ), array( $this, 'opt_in_below_copy' ), $post_type, 'normal', 'high' );
				break;
			case 'pricing':
				add_meta_box( 'premise-pricing-content', __( 'Pricing Content', 'premise' ), array( $this, 'pricing_content' ), $post_type, 'normal', 'high' );
				add_meta_box( 'premise-pricing-controls', __( 'Re-order Price Settings', 'premise' ), array( $this, 'pricing' ), $post_type, 'side', 'core' );
				break;
			case 'social-share':
				add_meta_box( 'premise-social-share-teaser', __( 'Teaser Page', 'premise' ), array( $this, 'social_share_teaser' ), $post_type, 'normal', 'high' );
				add_meta_box( 'premise-social-share-sharing-options', __( 'Sharing Options', 'premise' ), array( $this, 'social_share_sharing_options' ), $post_type, 'normal', 'high' );
				add_meta_box( 'premise-social-share-after-a-share', __( 'After a Share Page', 'premise' ), array( $this, 'social_share_after_share' ), $post_type, 'normal', 'high' );
				break;
			case 'video':
				add_meta_box( 'premise-video-box-copy', __( 'Video Box Copy', 'premise' ), array( $this, 'video_copy' ), $post_type, 'normal', 'high' );
				add_meta_box( 'premise-video-box-below-copy', __( 'Copy Below the Video Box', 'premise' ), array( $this, 'video_below_copy' ), $post_type, 'normal', 'high' );
				break;
			default:
				add_meta_box( 'premise-long-copy', __( 'Content', 'premise' ), array( $this, 'long_copy' ), $post_type, 'normal', 'high' );
				break;
		}		

		add_meta_box( 'premise-assistant', __( 'Landing Page Assistant', 'premise' ), array( $this, 'education' ), $post_type, 'side', 'low' );
		add_meta_box( 'premise-options', __( 'Premise: Settings For This Page', 'premise' ), array( $this, 'base_options' ), $post_type, 'normal', 'high' );

		if ( $premise_base->have_premise_seo() )
			add_meta_box( 'premise-seo', __( 'Premise: SEO Options For This Page', 'premise' ), array( $this, 'seo_options' ), $post_type, 'normal', 'high' );

		add_meta_box( 'premise-tracking', __( 'Premise: Testing For This Page', 'premise' ), array( $this, 'tracking_options' ), $post_type, 'normal', 'high' );
	}

	/* meta box display */
	function content_scroller( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		$scrollers = $premise_base->getContentScrollers( $post->ID );
		include( PREMISE_VIEWS_DIR . 'meta-boxes/page-types/content-scroller-controls.php' );
	}

	function content_scroller_tabs() {
		global $post, $premise_base, $Premise;
		
		$type = $premise_base->get_page_type( $post->ID );
		if( 'content-scroller' != $type )
			return;

		echo '<div id="premise-content-scroller-tabs">';
		$scrollers = $premise_base->getContentScrollers( $post->ID );
		foreach( $scrollers as $key => $scroller ) {
			if( is_array( $scroller ) )
				include( PREMISE_VIEWS_DIR . 'meta-boxes/premise-content-tab.php' );
		}
		echo '</div>';
		echo '<script type="text/javascript">blockSave = true;</script>';
	}

	function education( $post ) {
		global $premise_base;
		
		$type = $premise_base->get_page_type( $post->ID );
		$image = PREMISE_RESOURCES_URL . 'images/icon.gif';

		include( PREMISE_VIEWS_DIR . 'meta-boxes/premise-education.php' );
	}

	function base_options( $post ) {
		global $premise_base, $Premise;
		
		$type = $premise_base->get_advice_type( $post->ID );
		$type_name = $Premise->get_landing_page_type( $type, 'name' );
		if ( $type_name )
			$type = $type_name;

		$meta = $this->get_page_meta( $post->ID );
		$resourcesUrl = PREMISE_RESOURCES_URL;

		include( PREMISE_VIEWS_DIR . 'meta-boxes/premise-options.php' );
	}

	function pricing_content( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		include( PREMISE_VIEWS_DIR . 'meta-boxes/page-types/pricing-content.php' );
	}

	function pricing( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		$columns = $premise_base->getPricingColumns( $post->ID );
		include( PREMISE_VIEWS_DIR . 'meta-boxes/page-types/pricing-controls.php' );
	}
	
	function social_share_teaser( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		include( PREMISE_VIEWS_DIR . 'meta-boxes/page-types/social-share-teaser.php' );
	}
	
	function social_share_sharing_options( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		include( PREMISE_VIEWS_DIR . 'meta-boxes/page-types/social-share-sharing-options.php' );
	}
	
	function social_share_after_share( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		include( PREMISE_VIEWS_DIR . 'meta-boxes/page-types/social-share-after-a-share.php' );
	}

	function pricing_columns() {
		global $premise_base, $post;
		
		$type = $premise_base->get_page_type( $post->ID );
		if( 'pricing' != $type )
			return;

		echo '<div id="premise-pricing-columns">';
		$columns = $premise_base->getPricingColumns( $post->ID );
		foreach( $columns as $key => $column ) {
			if( is_array( $column ) )
				include( PREMISE_VIEWS_DIR . 'meta-boxes/premise-pricing-column.php' );
		}
		echo '</div>';
		echo '<script type="text/javascript">blockSave = true;</script>';
	}

	function seo_options( $post ) {
		global $premise_base, $Premise;
		
		$type = $premise_base->get_page_type( $post->ID );
		$seo = $this->get_page_meta( $post->ID, 'seo' );
		include( PREMISE_VIEWS_DIR . 'meta-boxes/premise-seo.php' );
	}

	function tracking_options( $post ) {
		global $premise_base, $Premise;
		
		$type = $premise_base->get_page_type( $post->ID );
		$tracking = $this->get_page_meta( $post->ID, 'tracking' );
		$settings = $premise_base->get_settings();
		include( PREMISE_VIEWS_DIR . 'meta-boxes/premise-tracking.php' );
	}

	function opt_in( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		$resourcesUrl = PREMISE_RESOURCES_URL;
		include( PREMISE_VIEWS_DIR . 'meta-boxes/page-types/opt-in.php' );
	}

	function opt_in_below_copy( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		premise_the_editor( $meta['below-optin-copy'], 'premise[below-optin-copy]', '', true, 6 );
	}

	function video_copy( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		$resourcesUrl = PREMISE_RESOURCES_URL;
		include( PREMISE_VIEWS_DIR . 'meta-boxes/page-types/video.php' );
	}

	function video_below_copy( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		premise_the_editor( $meta['below-video-copy'], 'premise[below-video-copy]', '', true, 6 );
	}

	function long_copy( $post ) {
		global $premise_base, $Premise;
		
		$meta = $this->get_page_meta( $post->ID );
		premise_the_editor( $post->post_content, 'content', '', true, 6 );
	}

	/* wrappers */
	function getAdviceSections($type) {
		global $Premise;
		return $Premise->_data_PremiseApi->getAdviceSections($type);
	}

	function getAdvice($type) {
		global $Premise;
		return $Premise->_data_PremiseApi->getAdvice($type);
	}

	function getSampleContent($type) {
		global $Premise;
		return $Premise->_data_PremiseApi->getSampleContent($type);
	}

	function getSingleAdvice($section) {
		global $Premise;
		return $Premise->_data_PremiseApi->getSingleAdvice($section);
	}
	function convertLandingPageToId($advice) {
		global $Premise;
		return $Premise->_data_LandingPageIds[$advice];
	}
	function getLandingPageAdviceType( $post_id ) {
		global $premise_base;
		return $premise_base->get_advice_type( $post_id );
	}
	function get_page_meta( $post_id, $meta_key = '' ) {
		global $premise_base;

		$default_meta = array(
			'subhead' => '',
			'style' => '',
			'favicon' => '',
			'header' => '',
			'header-image-hide' => '',
			'header-image-alt' => '',
			'header-image-new' => false,
			'header-image-url' => '',
			'footer' => '',
			'footer-copy' => '',
			'header-scripts' => '',
			'footer-scripts' => '',
			'video-copy' => '',
			'video-placement' => '',
			'video-image' => '',
			'video-image-title' => '',
			'video-embed-code' => '',
			'below-video-copy' => '',
			'optin-copy' => '',
			'optin-placement' => '',
			'optin-form-code' => '',
			'below-optin-copy' => '',
			'above-pricing-table-copy' => '',
			'below-pricing-table-copy' => '',
			'pricing-bullets' => '',
			'pricing-bullets-color' => '',
			'show-arrows' => '',
			'teaser-page' => '',
			'sharing-message' => '',
			'share-method' => '',
			'twitter-share-text' => '',
			'twitter-share-button' => '',
			'facebook-share-button' => '',
			'after-a-share-page' => '',
		);

		$default_key_meta = array(
			'seo' => array(
				'title' => '',
				'description' => '',
				'keywords' => '',
				'canonical' => '',
				'noindex' => '',
				'nofollow' => '',
				'noarchive' => '',
				'disable-feed' => '',
			),
			'tracking' => array(
				'account-id' => '',
				'enable-gce' => '',
				'ab' => '',
				'ab-original' => '',
				'test-id' => '',
				'page-type' => '',
				'link-click-conversion' => '',
			)
		);

		$meta = $premise_base->get_premise_meta( $post_id, $meta_key );
		return wp_parse_args( $meta, isset( $default_key_meta[$meta_key] ) ? $default_key_meta[$meta_key] : $default_meta );

	}
}