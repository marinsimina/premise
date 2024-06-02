<?php
/*
Design settings class for the premise theme
*/
class Premise_Design_Settings {
	var $_option_DesignSettings = '_premise_design_settings';
	
	function __construct() {}

	function get_default_settings() {
		$defaults = array( // define our defaults
		'premise_style_title' => __('My Style', 'premise' ),

		##################### globals
		'body_background_color' => '#f3f3f3',
		'body_background_image' => '',
		'body_background_repeat' => 'no-repeat',
		'body_background_align' => 'center',
		'body_font_color' => '#222222',
		'body_font_family' => "Arial, Helvetica, sans-serif",
		'body_font_size' => '14',
		'body_line_height' => '24',

		##################### global links
		'body_link_color' => '#035492',
		'body_link_decoration' => 'underline',
		'body_link_hover_background' => '#ffffff',
		'body_link_hover_background_select' => 'hex',
		'body_link_hover' => '#035492',
		'body_link_hover_decoration' => 'none',

		##################### wrap
		'wrap_width' => '800',
		'wrap_background_color' => '#ffffff',
        	'wrap_background_color_select' => 'hex',
		'wrap_margin_top' => "15",
		'wrap_margin_bottom' => "0",
		'wrap_padding' => "20",
		'wrap_border' => "5",
		'wrap_border_color' => '#eeeeee',
		'wrap_border_style' => 'solid',
		'wrap_corner_radius' => "5",
		'wrap_background_shadow' => "none",

		##################### header
		'header_image_height' => '230',
		'header_background_color' => '#ffffff',
		'header_background_color_select' => 'hex',

		##################### headline area
		'headline_area_background_color' => '#ffffff',
		'headline_area_background_color_select' => 'hex',
		'headline_area_background_image' => '',
		'headline_area_background_repeat' => 'no-repeat',
		'headline_area_background_align' => 'center',
		'headline_area_margin_top' => '1',
		'headline_area_margin_bottom' => '30',
		'headline_area_padding' => '0',
		'headline_area_align' => 'center',

		##################### headline area title
		'headline_title_font_color' => '#222222',
		'headline_title_font_family' => "Arial, Helvetica, sans-serif",
		'headline_title_font_size' => '40',
		'headline_title_font_style' => 'normal',
		'headline_title_font_weight' => 'bold',

		##################### headline area subtitle
		'headline_subtitle_font_color' => '#555555',
		'headline_subtitle_font_family' => "Arial, Helvetica, sans-serif",
		'headline_subtitle_font_size' => '22',
		'headline_subtitle_font_style' => 'normal',
		'headline_subtitle_font_weight' => 'normal',

		##################### blockquotes
		'blockquotes_background_color' => '#f3f3f3',
		'blockquotes_background_color_select' => 'hex',
		'blockquotes_padding_topbottom' => '30',
		'blockquotes_padding_leftright' => '15',
  		'blockquotes_font_color' => '#333333',
		'blockquotes_font_family' => "Georgia, serif",
		'blockquotes_font_size' => '16',
		'blockquotes_font_style' => 'italic',
		'blockquotes_border' => "1",
		'blockquotes_border_color' => '#dddddd',
		'blockquotes_border_style' => 'solid',

		##################### notice box
		'notice_background_color' => '#f9f2d6',
		'notice_background_color_select' => 'hex',
  		'notice_font_color' => '#48310e',
		'notice_font_family' => "Georgia, serif",
		'notice_font_size' => '16',
		'notice_font_style' => 'italic',
		'notice_border' => "1",
		'notice_border_color' => '#eada9c',
		'notice_border_style' => 'solid',
		'notice_padding_topbottom' => '15',
		'notice_padding_leftright' => '25',

		##################### headlines
		'headline_font_family' => "Georgia, serif",
		'h1_font_size' => '26',
		'h2_font_size' => '22',
		'h3_font_size' => '20',
		'h4_font_size' => '18',
		'h1_font_color' => '#222222',
		'h2_font_color' => '#222222',
		'h3_font_color' => '#222222',
		'h4_font_color' => '#222222',
		'headline_font_style' => 'normal',
		'headline_font_weight' => 'normal',
		'headline_text_align' => 'left',
		'headline_text_transform' => 'none',

		##################### headline links
		'h2_link_color' => '#222222',
		'h2_link_decoration' => 'none',
		'h2_link_hover' => '#444444',
		'h2_link_hover_decoration' => 'none',

		##################### footer
		'footer_font_color' => '#555555',
		'footer_font_size' => '11',
		'footer_font_weight' => 'normal',
		'footer_text_align' => 'center',
		'footer_text_transform' => 'none',

		##################### footer links
		'footer_link_color' => '#555555',
		'footer_link_decoration' => 'none',
		'footer_link_hover' => '#555555',
		'footer_link_hover_decoration' => 'underline',

		##################### input box
		'input_background_color' => '#f3f3f3',
		'input_background_color_select' => 'hex',
  		'input_font_color' => '#666666',
		'input_font_family' => "Arial, Helvetica, sans-serif",
		'input_font_style' => 'normal',
		'input_border' => "1",
		'input_border_color' => '#dddddd',
		'input_border_style' => 'solid',

		##################### buttons
		'button_background_color' => '#444444',
		'button_background_color_select' => 'hex',
		'button_background_hover_color' => '#222222',
		'button_background_hover_color_select' => 'hex',
		'button_font_color' => '#ffffff',
		'button_font_family' => "Arial, Helvetica, sans-serif",
		'button_font_size' => '13',
		'button_border' => "1",
		'button_border_color' => '#dddddd',
		'button_border_style' => 'solid',
		'button_border_radius' => '5',
		'button_padding' => '5',
		'button_text_transform' => 'uppercase',

		##################### Content Scroller

		'content_scroller_background_color' => '#f3f3f3',
		'content_scroller_background_color_select' => 'hex',
		'content_scroller_holder_border' => "1",
		'content_scroller_holder_border_color' => '#dddddd',
		'content_scroller_holder_border_style' => 'solid',
		'content_scroller_holder_radius' => '0',
		'content_scroller_color' => '#222222',
		'content_scroller_padding' => '15',
		'content_scroller_arrows_background' => '#035492' ,
		'content_scroller_arrows_background_select' => 'hex',
		'content_scroller_arrows_color' => '#ffffff',
		'content_scroller_tabs_background_color' => '#e5e5e5',
		'content_scroller_tabs_background_color_select' => 'hex',
		'content_scroller_tabs_color' => '#222222',
		'content_scroller_tabs_active_background_color' => '#035492',
		'content_scroller_tabs_active_background_color_select' => 'hex',
		'content_scroller_tabs_active_color' => '#ffffff',
		'content_scroller_tabs_radius' => '10',

		##################### Video Holder
		'video_holder_background' => '#f3f3f3',
		'video_holder_background_select' => 'hex',
		'video_holder_color' => '#222222',
		'video_holder_border' => "1",
		'video_holder_border_color' => '#dddddd',
		'video_holder_border_style' => 'solid',
		'video_holder_radius' => '0',
		'video_holder_padding' => '20',

		##################### Opt In Holder
		'optin_holder_background' => '#f3f3f3',
		'optin_holder_background_select' => 'hex',
		'optin_holder_color' => '#222222',
        	'optin_holder_border' => "1",
		'optin_holder_border_color' => '#dddddd',
		'optin_holder_border_style' => 'solid',
		'optin_holder_radius' => '0',
		'optin_holder_padding' => '20',

		##################### Opt In Container
		'optin_form_background' => '#ffffff',
		'optin_form_background_select' => 'hex',
		'optin_form_padding' => '1',
		'optin_form_radius' => '0',
		'optin_form_border' => "1",
		'optin_form_border_color' => '#dddddd',
		'optin_form_border_style' => 'solid',

		'optin_form_header_background' => '#035492',
		'optin_form_header_background_select' => 'hex',
		'optin_form_header_font' => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
		'optin_form_header_font_size' => '24',
		'optin_form_header_color' => '#ffffff',
		'optin_form_header_padding' => '10',

		'optin_form_contents_background' => '#ffffff',
		'optin_form_contents_background_select' => 'hex',
		'optin_form_contents_color' => '#222222',
		'optin_form_contents_padding' => '15',

		'optin_form_contents_submit_background' => '#4d8a05',
		'optin_form_contents_submit_background_select' => 'hex',
		'optin_form_contents_submit_background_hover' => '#b82300',
		'optin_form_contents_submit_background_hover_select' => 'hex',
		'optin_form_contents_submit_color' => '#ffffff',
		'optin_form_contents_submit_padding' => '9',

		##################### Pricing Table
		'pricing_tier_background' => '#ffffff',
		'pricing_tier_background_select' => 'hex',
		'pricing_tier_border' => '1',
		'pricing_tier_border_color' => '#dddddd',
		'pricing_tier_border_style' => 'solid',
		'pricing_tier_border_radius' => '0',
		'pricing_tier_padding' => '1',

		'pricing_tier_header_background' => '#035492',
		'pricing_tier_header_background_select' => 'hex',
		'pricing_tier_header_color' => '#ffffff',
		'pricing_tier_header_align' => 'center',
		'pricing_tier_header_padding' => '15',
		'pricing_tier_header_font' => "Georgia, serif",
		'pricing_tier_header_font_size' => '22',
		'pricing_tier_header_font_style' => 'normal',
		'pricing_tier_header_font_weight' => 'normal',

		'pricing_tier_features_background' => '#f3f3f3',
		'pricing_tier_features_background_select' => 'hex',
		'pricing_tier_features_color' => '#222222',
		'pricing_tier_features_padding' => '15',

		'pricing_tier_cta_background' => '#4d8a05',
		'pricing_tier_cta_background_select' => 'hex',
		'pricing_tier_cta_background_hover' => '#eb7028',
		'pricing_tier_cta_background_hover_select' => 'hex',
		'pricing_tier_cta_color' => '#ffffff',
		'pricing_tier_cta_padding' => '9',
		'pricing_tier_cta_align' => 'center',

		##################### Social Share
		'teaser_share_box_inside_background' => '#eef8fb',
		'teaser_share_box_inside_background_select' => 'hex',
		'teaser_share_box_border' => '3',
		'teaser_share_box_border_color' => '#deedf1',
		'teaser_share_box_border_style' => 'solid',
		'teaser_share_box_inside_border' => '1',
		'teaser_share_box_inside_border_color' => '#ffffff',
		'teaser_share_box_inside_border_style' => 'solid',
		'teaser_share_box_color' => '#222222',
		'teaser_share_box_border_radius' => '3',
		
		'teaser_share_icons_background' => '#ffffff',
		'teaser_share_icons_border_color' => '#cddbdf',
		'teaser_share_icons_color' => '#222222',
		
		'teaser_share_icons_hover_background' => '#efefef',
		'teaser_share_icons_hover_border_color' => '#cddbdf',
		'teaser_share_icons_hover_color' => '#222222',

		##################### install flag (do not edit)
		'installed' => 'true',

		##################### general settings
		'minify_css' => ''
		);

		return apply_filters( 'premise_settings_defaults', $defaults );
	}

	function get_settings() {
		$settings = get_option( $this->_option_DesignSettings );
		if( !is_array( $settings ) )
			$settings = $this->get_default_settings();
			
		if( isset( $settings['body_background_color'] ) ) {
			$settings['premise_style_timesaved'] = current_time( 'timestamp' );
			$settings['premise_style_title'] = __( 'Default', 'premise' );
			$settings = array( 0 => $settings );
			update_option( $this->_option_DesignSettings, $settings );
		}
		
		return $settings;
	}

	function update_settings( $settings, $key = 'all' ) {
		if( !is_array( $settings ) )
			return $key;

		if( $key == 'all' ) {
			update_option( $this->_option_DesignSettings, $settings );
			return '';
		}

		$existing = $this->get_settings();
		$settings['premise_style_timesaved'] = current_time('timestamp');
		if( isset( $existing[$key] ) ) {
			$existing[$key] = $settings;
		} else {
			$existing[] = $settings;
			$keys = array_keys( $existing );
			$key = array_pop( $keys );
		}
		
		update_option( $this->_option_DesignSettings, $existing );
		return $key;
	}
	function get_configured_style( $key = null ) {
		global $premise_style_configuration_key, $premise_style_configuration_should_use_defaults;
		
		$styles = $this->get_settings();
		
		if( null === $key )
			$key = $premise_style_configuration_key;
		
		if( isset( $styles[$key] ) )
			return $styles[$key];
		elseif( $premise_style_configuration_should_use_defaults )
			return $this->get_default_settings();

		return array_shift( $styles );
	}
}
global $premise_design_settings;
$premise_design_settings = new Premise_Design_Settings();
