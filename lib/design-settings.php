<?php
/**
 * This file is a fork of the Prose design settings code.  It is customized for the Premise landing page plugin to ensure that
 * it only applies settings which make sense for landing page.
 */

function premise_get_mapping() {
	// Format:
	// '#selector' => array(
	//      'property1' => 'value1',
	//      'property2' => 'value2',
	//      'property-with-multiple-values-or-units' => array(
	//		  array('value', 'unit'),
	//		  array('value', 'string'),
	//		  array('value', 'unit')
	//      ),
	//      'property4' => 'value4'
	// ),

	$mapping = array (
		'body.premise-theme' => array(
			'background-color' => 'body_background_color',
			'background-color_select' => 'body_background_color_select',
			'background-position' => array(
				array('top','fixed_string'),
				array('body_background_align','string'),
			),
			'background-image' => array(
				array("url(",'fixed_string'),
				array('body_background_image','string'),
				array(")", 'fixed_string'),
			),
			'background-repeat' => 'body_background_repeat',
			'color' => 'body_font_color',
			'font-family' => 'body_font_family',
			'font-size' => array( array('body_font_size', 'px') ),
			'line-height' => array( array('body_line_height', 'px') ),
		),
		'.premise-theme a, .premise-theme a:visited' => array(
			'color' => 'body_link_color',
			'text-decoration' => 'body_link_decoration'
		),
		'.premise-theme a:hover' => array(
			'background-color' => 'body_link_hover_background',
			'background-color_select' => 'body_link_hover_background_select',
			'color' => 'body_link_hover',
			'text-decoration' => 'body_link_hover_decoration'
		),
		'.premise-theme #wrap' => array(
			'width' => array( array('wrap_width','px') ),
			'background-color' => 'wrap_background_color',
			'background-color_select' => 'wrap_background_color_select',
			'margin' => array(
			array('wrap_margin_top', 'px'),
			array('auto', 'fixed_string'),
			array('wrap_margin_bottom', 'px')
			),
			'border' => array(
			array('wrap_border','px'),
			array('wrap_border_style', 'string'),
			array('wrap_border_color', 'string')
			),
			'-moz-border-radius' => array( array('wrap_corner_radius','px') ),
			'-khtml-border-radius' => array( array('wrap_corner_radius','px') ),
			'-webkit-border-radius' => array( array('wrap_corner_radius','px') ),
			'border-radius' => array( array('wrap_corner_radius','px') ),
			'-moz-box-shadow' => 'wrap_background_shadow',
			'-webkit-box-shadow' => 'wrap_background_shadow'
		),
		'.premise-theme #inner' => array(
			'padding' => array( array('wrap_padding','px') ),
		),
		'.premise-theme #header' => array(
			'background-color' => array( array('header_background_color', 'string') ),
			'background-color_select' => 'header_background_color_select',
		),
		'#image-area' => array(
			'max-height' => array( array('header_image_height', 'px'), ),
			'width' => array( array('wrap_width','px'), ),
		),
		'.premise-theme #content .headline-area' => array(
			'background-color' => 'headline_area_background_color',
			'background-color_select' => 'headline_area_background_color_select',
			'background-position' => array(
				array('string','fixed_string'),
				array('headline_area_background_align','string'),
			),
			'background-image' => array(
				array("url(",'fixed_string'),
				array('headline_area_background_image','string'),
				array(")", 'fixed_string'),
			),
			'background-repeat' => 'headline_area_background_repeat',
			'margin-top' => array( array('headline_area_margin_top','px') ),
			'margin-bottom' => array(array('headline_area_margin_bottom','px') ),
			'padding' => array( array('headline_area_padding','px') ),
		),
		'#content .headline-area .entry-title' => array(
			'color' => 'headline_title_font_color',
			'font-family' => 'headline_title_font_family',
			'font-size' => array( array('headline_title_font_size','px') ),
			'font-style' => 'headline_title_font_style',
			'font-weight' => 'headline_title_font_weight',
			'text-align' => 'headline_area_align',
		),
		'#content .headline-area .entry-subtitle' => array(
			'color' => 'headline_subtitle_font_color',
			'font-family' => 'headline_subtitle_font_family',
			'font-size' => array( array('headline_subtitle_font_size','px') ),
			'font-style' => 'headline_subtitle_font_style',
			'font-weight' => 'headline_subtitle_font_weight',
			'text-align' => 'headline_area_align',
		),
		'.premise-theme #content blockquote' => array(
			'color' => 'blockquotes_font_color',
			'font-family' => 'blockquotes_font_family',
			'font-style' => 'blockquotes_font_style',
			'font-size' => array( array('blockquotes_font_size','px') ),
			'background-color' => 'blockquotes_background_color',
			'background-color_select' => 'blockquotes_background_color_select',
			'border' => array(
				array('blockquotes_border','px'),
				array('blockquotes_border_style','string'),
				array('blockquotes_border_color','string')
			),
			'padding' => array(
				array('blockquotes_padding_topbottom','px'),
				array('blockquotes_padding_leftright','px'),
				array('blockquotes_padding_topbottom','px'),
				array('blockquotes_padding_leftright','px'),
			),
		),
		'.premise-theme #content .notice' => array(
			'color' => 'notice_font_color',
			'font-family' => 'notice_font_family',
			'font-size' => array(array('notice_font_size','px')),
			'font-style' => 'notice_font_style',
			'background-color' => 'notice_background_color',
			'background-color_select' => 'notice_background_color_select',
			'border' => array(
				array('notice_border','px'),
				array('notice_border_style','string'),
				array('notice_border_color','string')
			),
			'padding' => array(
				array('notice_padding_topbottom','px'),
				array('notice_padding_leftright','px'),
				array('notice_padding_topbottom','px'),
				array('notice_padding_leftright','px'),
			),
		),
		'.premise-theme #content h1, .premise-theme #content h2, .premise-theme #content h3, .premise-theme #content h4, .premise-theme #content h5, .premise-theme #content h6' => array(
			'font-family' => 'headline_font_family',
			'font-style' => 'headline_font_style',
			'font-weight' => 'headline_font_weight',
			'text-align' => 'headline_text_align',
			'text-transform' => 'headline_text_transform'
		),
		'.premise-theme #content h1' => array(
			'font-size' => array( array('h1_font_size','px') ),
			'color' => 'h1_font_color',
		),
		'.premise-theme #content h2' => array(
			'font-size' => array( array('h2_font_size','px') ),
			'color' => 'h2_font_color',
		),
		'.premise-theme #content h3' => array(
			'font-size' => array( array('h3_font_size','px') ),
			'color' => 'h3_font_color',
		),
		'.premise-theme #content h4' => array(
			'font-size' => array( array('h4_font_size','px') ),
			'color' => 'h4_font_color',
		),
		'.premise-theme #footer' => array(
			'color' => 'footer_font_color',
			'font-size' => array( array('footer_font_size','px') ),
			'font-weight' => 'footer_font_weight',
			'text-transform' => 'footer_text_transform',
			'width' => array( array('wrap_width','px') ),
			),
		'.premise-theme #footer .creds' => array(
			'text-align' => 'footer_text_align',
			),
		'.premise-theme #footer a, .premise-theme #footer a:visited' => array(
			'color' => 'footer_link_color',
			'text-decoration' => 'footer_link_decoration'
			),
		'.premise-theme #footer a:hover' => array(
			'color' => 'footer_link_hover',
			'text-decoration' => 'footer_link_hover_decoration'
			),
		'#content input[type=text], #content input.text' => array(
			'background-color' => 'input_background_color',
			'background-color_select' => 'input_background_color_select',
			'border' => array(
				array('input_border','px'),
				array('input_border_style','string'),
				array('input_border_color','string')
			),
			'color' => array(
				array('input_font_color','string'),
				array('!important','fixed_string')
			),
			'font-family' => array(
				array('input_font_family','string'),
				array('!important','fixed_string')
			),
			'font-style' => 'input_font_style',
		),
		'.premise-theme #content .gform_footer .button, .premise-theme #content .button, .premise-theme #content input[type=submit], .premise-theme #content input.submit' => array(
			'background-color' => 'button_background_color',
			'background-color_select' => 'button_background_color_select',
			'border' => array(
				array('button_border','px'),
				array('button_border_style','string'),
				array('button_border_color','string'),
				array('!important', 'fixed_string'),
			),
			'-moz-border-radius' => array( array('button_border_radius','px') ),
			'-khtml-border-radius' => array( array('button_border_radius','px') ),
			'-webkit-border-radius' => array( array('button_border_radius','px') ),
			'border-radius' => array( array('button_border_radius','px') ),
			'color' => array(
				array('button_font_color','string'),
				array('!important','fixed_string')
			),
			'font-family' => array(
				array('button_font_family','string'),
				array('!important','fixed_string')
			),
			'font-size' => array(
				array('button_font_size','px'),
				array('!important','fixed_string')
			),
			'padding' => array(
				array('button_padding','px'),
				array('!important','fixed_string'),
			),
			'text-transform' => 'button_text_transform',
		),
		'.premise-theme #content .gform_footer .button:hover, .premise-theme #content .button:hover, .premise-theme #content input[type=submit]:hover, .premise-theme #content input.submit:hover' => array(
			'background-color' => 'button_background_hover_color',
			'background-color_select' => 'button_background_hover_color_select',
		),
		'.premise-theme .coda-slider' => array(
			'background-color' => 'content_scroller_background_color',
			'background-color_select' => 'content_scroller_background_color_select',
			'border' =>array(
				array('content_scroller_holder_border','px'),
				array('content_scroller_holder_border_style','string'),
				array('content_scroller_holder_border_color','string'),
			),
			'-moz-border-radius' => array( array('content_scroller_holder_radius','px') ),
			'-khtml-border-radius' => array( array('content_scroller_holder_radius','px') ),
			'-webkit-border-radius' => array( array('content_scroller_holder_radius','px') ),
			'border-radius' => array( array('content_scroller_holder_radius','px') ),
			'color' => 'content_scroller_color',
			'padding' => array( array('content_scoller_padding','px') ),
		),
		'.premise-theme .coda-nav-left a, .premise-theme .coda-nav-right a' => array(
			'background-color' => 'content_scroller_arrows_background',
			'background-color_select' => 'content_scroller_arrows_background_select',
			'color' => 'content_scroller_arrows_color'
		),
		'.premise-theme .coda-nav ul li a' => array(
			'background-color' => 'content_scroller_tabs_background_color',
			'background-color_select' => 'content_scroller_tabs_background_color_select',
			'-moz-border-radius-topleft' => array( array('content_scroller_tabs_radius','px') ),
			'-moz-border-radius-topright' => array( array('content_scroller_tabs_radius','px') ),
			'-khtml-border-top-left-radius' => array( array('content_scroller_tabs_radius','px') ),
			'-khtml-border-top-right-radius' => array( array('content_scroller_tabs_radius','px') ),
			'-webkit-border-top-left-radius' => array( array('content_scroller_tabs_radius','px') ),
			'-webkit-border-top-right-radius' => array( array('content_scroller_tabs_radius','px') ),
			'color' => 'content_scroller_tabs_color',
		),
		'.premise-theme .coda-nav ul li a.current' => array(
			'background-color' => 'content_scroller_tabs_active_background_color',
			'background-color_select' => 'content_scroller_tabs_active_background_color_select',
			'color' => 'content_scroller_tabs_active_color',
		),
		'.premise-theme .entry-video' => array(
			'background-color' => 'video_holder_background',
			'background-color_select' => 'video_holder_background_select',
			'border' =>array(
				array('video_holder_border','px'),
				array('video_holder_border_style','string'),
				array('video_holder_border_color','string'),
			),
			'-moz-border-radius' => array( array('video_holder_radius','px') ),
			'-khtml-border-radius' => array( array('video_holder_radius','px') ),
			'-webkit-border-radius' => array( array('video_holder_radius','px') ),
			'border-radius' => array( array('video_holder_radius','px') ),
			'color' => 'video_holder_color',
		),
		'.premise-theme .entry-video .container-border' => array(
			'padding' => array(array('video_holder_padding','px')),
		),
		'#content .entry-video h1, #content .entry-video h2, #content .entry-video h3, #content .entry-video h4, #content .entry-video h5, #content .entry-video h6' => array(
			'color' => 'video_holder_color',
		),
		'.premise-theme #content .entry-optin' => array(
			'background-color' => 'optin_holder_background',
			'background-color_select' => 'optin_holder_background_select',
			'border' => array(
				array('optin_holder_border','px'),
				array('optin_holder_border_style','string'),
				array('optin_holder_border_color','string')
			),
			'-moz-border-radius' => array( array('optin_holder_radius','px') ),
			'-khtml-border-radius' => array( array('optin_holder_radius','px') ),
			'-webkit-border-radius' => array( array('optin_holder_radius','px') ),
			'border-radius' => array( array('optin_holder_radius','px') ),
			'color' => 'optin_holder_color',
			'padding' => array( array('optin_holder_padding','px') ),
		),
		'#content .entry-optin h1, #content .entry-optin h2, #content .entry-optin h3, #content .entry-optin h4, #content .entry-optin h5, #content .entry-optin h6' => array(
			'color' => 'optin_holder_color',
		),
		'#content .entry-optin-optin' => array(
			'background-color' => 'optin_form_background',
			'background-color_select' => 'optin_form_background_select',
			'border' => array(
				array('optin_form_border','px'),
				array('optin_form_border_style','string'),
				array('optin_form_border_color','string')
			),
			'-moz-border-radius' => array( array('optin_form_radius','px') ),
			'-khtml-border-radius' => array( array('optin_form_radius','px') ),
			'-webkit-border-radius' => array( array('optin_form_radius','px') ),
			'color' => 'optin_form_color',
			'border-radius' => array( array('optin_form_radius','px') ),
			'padding' => array( array('optin_form_padding','px') ),
		),
		'#content .optin-header' => array(
			'background-color' => 'optin_form_header_background',
			'background-color_select' => 'optin_form_header_background_select',
			'color' => 'optin_form_header_color',
			'font-family' => 'optin_form_header_font',
			'font-size' => array( array('optin_form_header_font_size','px') ),
			'padding' => array( array('optin_form_header_padding','px') ),
		),
		'#content .optin-box' => array(
			'background-color' => 'optin_form_contents_background',
			'background-color_select' => 'optin_form_contents_background_select',
			'color' => 'optin_form_contents_color',
			'padding' => array( array('optin_form_contents_padding','px') ),
		),
		'#content .optin-box input[type=submit], #content .optin-box input.submit' => array(
			'background-color' => 'optin_form_contents_submit_background',
			'background-color_select' => 'optin_form_contents_submit_background_select',
			'color' => 'optin_form_contents_submit_color',
			'padding' => array( array('optin_form_contents_submit_padding','px') ),
		),
		'#content .optin-box input[type=submit]:hover, #content .optin-box input.submit:hover' => array(
			'background-color' => 'optin_form_contents_submit_background_hover',
			'background-color_select' => 'optin_form_contents_submit_background_hover_select',
		),
		'#content .pricing-table-container' => array( ),
		'#content .pricing-table-column' => array(
			'background-color' => 'pricing_tier_background',
			'background-color_select' => 'pricing_tier_background_select',
			'border' => array(
				array('pricing_tier_border','px'),
				array('pricing_tier_border_style','string'),
				array('pricing_tier_border_color','string')
			),
			'-moz-border-radius' => array( array('pricing_tier_border_radius','px') ),
			'-khtml-border-radius' => array( array('pricing_tier_border_radius','px') ),
			'-webkit-border-radius' => array( array('pricing_tier_border_radius','px') ),
			'border-radius' => array( array('pricing_tier_border_radius','px') ),
			'padding' => array( array('pricing_tier_padding','px') ),
		),
		'#content .pricing-table-column-header' => array(
			'background-color' => 'pricing_tier_header_background',
			'background-color_select' => 'pricing_tier_header_background_select',
			'color' => 'pricing_tier_header_color',
			'font-family' => 'pricing_tier_header_font',
			'font-size' => array( array('pricing_tier_header_font_size','px')),
			'font-style' => 'pricing_tier_header_font_style',
			'font-weight' => 'pricing_tier_header_font_weight',
			'padding' => array( array('pricing_tier_header_padding','px') ),
			'text-align' => 'pricing_tier_header_align',
		),
		'#content .pricing-table-column-features' => array(
			'background-color' => 'pricing_tier_features_background',
			'background-color_select' => 'pricing_tier_features_background_select',
			'color' => 'pricing_tier_features_color',
			'padding' => array( array('pricing_tier_features_padding','px') ),
		),
		'#content .pricing-table-call-to-action' => array(
			'text-align' => 'pricing_tier_cta_align',
		),
		'#content .pricing-table-call-to-action a' => array(
			'background-color' => 'pricing_tier_cta_background',
			'background-color_select' => 'pricing_tier_cta_background_select',
			'color' => 'pricing_tier_cta_color',
			'padding' => array( array('pricing_tier_cta_padding','px') ),
			'text-align' => 'pricing_tier_cta_align',
			'-moz-border-radius' => array( array('button_border_radius','px') ),
			'-khtml-border-radius' => array( array('button_border_radius','px') ),
			'-webkit-border-radius' => array( array('button_border_radius','px') ),
			'border-radius' => array( array('button_border_radius','px') ),
		),
		'#content .pricing-table-call-to-action a:hover' => array(
			'background-color' => 'pricing_tier_cta_background_hover',
			'background-color_select' => 'pricing_tier_cta_background_hover_select'
		),
		'.premise-theme .teaser-share-box' => array(
			'border' => array(
				array('teaser_share_box_border', 'px'),
				array('teaser_share_box_border_style', 'string'),
				array('teaser_share_box_border_color', 'string'),
			),
			'-moz-border-radius' => array(array('teaser_share_box_border_radius', 'px')),
			'-khtml-border-radius' => array(array('teaser_share_box_border_radius', 'px')),
			'-webkit-border-radius' => array(array('teaser_share_box_border_radius', 'px')),
			'border-radius' => array(array('teaser_share_box_border_radius', 'px')),
			'color' => 'teaser_share_box_color',
		),
		'.premise-theme .teaser-share-box-inside' => array(
			'background-color' => 'teaser_share_box_inside_background',
			'background-color_select' => 'teaser_share_box_inside_background_select',
			'border' => array(
				array('teaser_share_box_inside_border', 'px'),
				array('teaser_share_box_inside_border_style', 'string'),
				array('teaser_share_box_inside_border_color', 'string'),
			),
		),
		'.premise-theme .teaser-share-icons a' => array(
			'background' => 'teaser_share_icons_background',
			'background_select' => 'teaser_share_icons_background_select',
			'border-color' => 'teaser_share_icons_border_color',
			'color' => 'teaser_share_icons_color',
		),
		'.premise-theme .teaser-share-icons a:hover' => array(
			'background' => 'teaser_share_icons_hover_background',
			'background_select' => 'teaser_share_icons_hover_background_select',
			'border-color' => 'teaser_share_icons_hover_border_color',
			'color' => 'teaser_share_icons_hover_color',
		),
		'minify_css' => 'minify_css',
		'premise_custom_css' => 'premise_custom_css'
	);
	return apply_filters('premise_get_mapping',$mapping);
}

/**
 * Used to create the actual markup of options.
 *
 * @author Gary Jones
 * @param string Used as comparison to see which option should be selected.
 * @param string $type One of 'border', 'family', 'style', 'variant', 'weight', 'align', 'decoration', 'transform'.
 * @since 0.9.5
 * @return string HTML markup of dropdown <option>s
 * @version 0.9.8
 */
function premise_create_options($compare, $type) {

	switch($type) {
		case "border":
			// border styles
			$options = array(
			array('None', 'none'),
			array('Solid', 'solid'),
			array('Dashed', 'dashed'),
			array('Dotted', 'dotted'),
			array('Double', 'double'),
			array('Groove', 'groove'),
			array('Ridge', 'ridge'),
			array('Inset', 'inset'),
			array('Outset', 'outset')
			);
			break;
		case "family":
			//font-family sets
			$options = array(
			array('Arial', 'Arial, Helvetica, sans-serif'),
			array('Arial Black', "'Arial Black', Gadget, sans-serif"),
			array('Century Gothic', "'Century Gothic', sans-serif"),
			array('Courier New', "'Courier New', Courier, monospace"),
			array('Georgia', 'Georgia, serif'),
			array('Lucida Console', "'Lucida Console', Monaco, monospace"),
			array('Lucida Sans Unicode', "'Lucida Sans Unicode', 'Lucida Grande', sans-serif"),
			array('Palatino Linotype', "'Palatino Linotype', 'Book Antiqua', Palatino, serif"),
			array('Tahoma', 'Tahoma, Geneva, sans-serif'),
			array('Times New Roman', "'Times New Roman', serif"),
			array('Trebuchet MS', "'Trebuchet MS', Helvetica, sans-serif"),
			array('Verdana', 'Verdana, Geneva, sans-serif')
			);
			$options = apply_filters('premise_font_family_options', $options);
			sort($options);
			array_unshift($options, array('Inherit', 'inherit')); // Adds Inherit option as first option.
			break;
		case "style":
			// font-style options
			$options = array(
			array('Normal', 'normal'),
			array('Italic', 'italic')
			);
			break;
		case "variant":
			// font-variant options
			$options = array(
			array('Normal', 'normal'),
			array('Small-Caps', 'small-caps')
			);
			break;
		case "weight":
			// font-weight options
			$options = array(
			array('Normal', 'normal'),
			array('Bold', 'bold')
			);
			break;
		case "align":
			// text-align options
			$options = array(
			array('Left', 'left'),
			array('Center', 'center'),
			array('Right', 'right'),
			array('Justify', 'justify')
			);
			break;
		case "decoration":
			// text-decoration options
			$options = array(
			array('None', 'none'),
			array('Underline', 'underline'),
			array('Overline', 'overline')
			// Include line-through?
			);
			break;
		case "transform":
			// text-transform options
			$options = array(
			array('None', 'none'),
			array('Capitalize', 'capitalize'),
			array('Lowercase', 'lowercase'),
			array('Uppercase', 'uppercase')
			);
			break;
		case "background":
			// background color options
			$options = array(
			array('Color (Hex)', 'hex'),
			array('Inherit', 'inherit'),
			array('Transparent', 'transparent')
			);
			break;
		case "color":
			// font color options
			$options = array(
			array('Color (Hex)', 'hex'),
			array('Inherit', 'inherit')
			);
			break;
		case 'repeat':
			$options = array(
			array('None', 'no-repeat'),
			array('Horizontal', 'repeat-x'),
			array('Vertical', 'repeat-y'),
			array('All', 'repeat')
			);
			break;
		case 'image_align':
			$options = array(
			array('Left','left'),
			array('Center', 'center'),
			array('Right','right')
			);
			break;
		default:
			$options = '';
	}
	if ( is_array($options) ) {
		$output = '';
		foreach ($options as $option) {
			$output .= '<option value="'. esc_attr($option[1]) . '" title="' . esc_attr($option[1]) . '" ' . selected(esc_attr($option[1]), esc_attr($compare), false) . '>' . __($option[0], 'premise' ) . '</option>';
		}
	} else {
		$output = '<option>Select type was not valid.</option>';
	}
	return $output;
}

/**
 * This next section defines functions that contain the content of the "boxes" that will be
 * output by default on the "Design Settings" page. There's a bunch of them.
 */

/**
 * Add settings to the Global Styles box. Does premise_settings_global action hook.
 *
 * @author Gary Jones
 * @version 0.9.5
 */
function premise_settings_global() {
	global $premise_style_configuration_key;
	if ( null !== $premise_style_configuration_key ) {
		?>
		<input type="hidden" name="premise-design-key" value="<?php echo esc_attr( $premise_style_configuration_key ); ?>" />
		<?php
	}
	
	premise_setting_line(premise_add_text_setting('premise_style_title', __('Style Title', 'premise' )));
	premise_setting_line(premise_add_background_color_setting('body_background_color', 'Background'));
	premise_setting_line(array(premise_add_text_setting('body_background_image', 'Background Image', 25), premise_add_select_setting('body_background_repeat', '', 'repeat'), premise_add_select_setting('body_background_align', '', 'image_align')));
	$url = esc_attr(add_query_arg(array('send_to_premise_field_id'=>'body_background_image', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image'))));
	premise_setting_line(premise_add_note(sprintf('<a class="thickbox" href="%s">Upload</a> your background image.', $url)));

	premise_setting_line(premise_add_color_setting('body_font_color', 'Color'));
	premise_setting_line(premise_add_select_setting('body_font_family', 'Font', 'family'));
	premise_setting_line(premise_add_size_setting('body_font_size', 'Font Size'));
	premise_setting_line(premise_add_size_setting('body_line_height', 'Line Height'));
	do_action('premise_settings_global');
	premise_setting_line(premise_add_note(__('All fonts listed are considered web-safe', 'premise' )));
}

/**
 * Add settings to the Global Links box. Does premise_settings_global_links action hook.
 *
 * @author Gary Jones
 * @version 0.9.5
 */
function premise_settings_global_links() {
	premise_setting_line(premise_add_color_setting('body_link_color', 'Color'));
	premise_setting_line(premise_add_select_setting('body_link_decoration', 'Decoration', 'decoration'));
	premise_setting_line(premise_add_background_color_setting('body_link_hover_background', 'Hover Background'));
	premise_setting_line(premise_add_color_setting('body_link_hover', 'Hover Color'));
	premise_setting_line(premise_add_select_setting('body_link_hover_decoration', 'Hover Decoration', 'decoration'));
	do_action('premise_settings_global_links');
}

/**
 * Add settings to the Wrap box. Does premise_settings_wrap action hook.
 *
 * @author Gary Jones
 * @version 1.0
 */
function premise_settings_wrap() {
	premise_setting_line(premise_add_background_color_setting('wrap_background_color', 'Background'));
	premise_setting_line(premise_add_text_setting('wrap_background_shadow', 'Background Shadow'));
	premise_setting_line(premise_add_border_setting('wrap_border', 'Border'));
	premise_setting_line(premise_add_size_setting('wrap_margin_bottom', 'Margin Bottom'));
	premise_setting_line(premise_add_size_setting('wrap_margin_top', 'Margin Top'));
	premise_setting_line(premise_add_size_setting('wrap_padding', 'Padding'));
	premise_setting_line(premise_add_size_setting('wrap_corner_radius', 'Rounded Corner Radius'));
	premise_setting_line(premise_add_size_setting('wrap_width', 'Width'));

	premise_setting_line(premise_add_note(__('Enter <code>0 1px 1px #999999</code> for a subtle background shadow.', 'premise' )));
	do_action('premise_settings_wrap');
}

/**
 * Add settings to the Header box. Does premise_settings_header action hook.
 *
 * @author Gary Jones
 * @version 0.9.6
 */
function premise_settings_header() {
	premise_setting_line(premise_add_background_color_setting('header_background_color', 'Background'));
	premise_setting_line(premise_add_size_setting('header_image_height', 'Height', 2));
	do_action('premise_settings_header');
}

/**
 * Add settings to the Headline Area box.  Does premise_settings_headline_area hook.
 */
function premise_settings_headline_area() {
	premise_setting_line(premise_add_background_color_setting('headline_area_background_color', 'Background'));
	premise_setting_line(array(premise_add_text_setting('headline_area_background_image', 'Background Image', 25), premise_add_select_setting('headline_area_background_repeat', '', 'repeat'), premise_add_select_setting('headline_area_background_align', '', 'image_align')));
	$url = esc_attr(add_query_arg(array('send_to_premise_field_id'=>'headline_area_background_image', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image'))));
	premise_setting_line(premise_add_note(sprintf('<a class="thickbox" href="%s">Upload</a> your background image.', $url)));

	premise_setting_line(premise_add_size_setting('headline_area_margin_bottom', 'Margin Bottom'));
	premise_setting_line(premise_add_size_setting('headline_area_margin_top', 'Margin Top'));
	premise_setting_line(premise_add_size_setting('headline_area_padding', 'Padding'));
	premise_setting_line(premise_add_select_setting('headline_area_align', 'Text Align', 'align'));

	echo '<br />';
	premise_setting_line(premise_add_note(__('The following apply to the landing page headline.', 'premise' )));
	premise_setting_line(premise_add_color_setting('headline_title_font_color', 'Title Color'));
	premise_setting_line(premise_add_select_setting('headline_title_font_family', 'Title Font', 'family'));
	premise_setting_line(premise_add_size_setting('headline_title_font_size', 'Title Font Size'));
	premise_setting_line(premise_add_select_setting('headline_title_font_style', 'Title Font Style', 'style'));
	premise_setting_line(premise_add_select_setting('headline_title_font_weight', 'Title Font Weight', 'weight'));

	echo '<br />';
	premise_setting_line(premise_add_note(__('The following apply to the landing page sub-headline.', 'premise' )));
	premise_setting_line(premise_add_color_setting('headline_subtitle_font_color', 'Subtitle Color'));
	premise_setting_line(premise_add_select_setting('headline_subtitle_font_family', 'Subtitle Font', 'family'));
	premise_setting_line(premise_add_size_setting('headline_subtitle_font_size', 'Subtitle Font Size'));
	premise_setting_line(premise_add_select_setting('headline_subtitle_font_style', 'Subtitle Font Style', 'style'));
	premise_setting_line(premise_add_select_setting('headline_subtitle_font_weight', 'Subtitle Font Weight', 'weight'));

	do_action('premise_settings_headline_area');
}

/**
 * Add settings to the Blockquotes box. Does premise_settings_blockquotes action hook.
 *
 * @author Brian Gardner
 * @version 1.0
 */
function premise_settings_blockquotes() {
	premise_setting_line(premise_add_background_color_setting('blockquotes_background_color', 'Background'));
	premise_setting_line(premise_add_border_setting('blockquotes_border', 'Border'));
	premise_setting_line(premise_add_color_setting('blockquotes_font_color', 'Color'));
	premise_setting_line(premise_add_select_setting('blockquotes_font_family', 'Font', 'family'));
	premise_setting_line(premise_add_size_setting('blockquotes_font_size', 'Font Size'));
	premise_setting_line(premise_add_select_setting('blockquotes_font_style', 'Font Style', 'style'));
	premise_setting_line(premise_add_size_setting('blockquotes_padding_topbottom', 'Padding Top/Bottom'));
	premise_setting_line(premise_add_size_setting('blockquotes_padding_leftright', 'Padding Left/Right'));

	do_action('premise_settings_blockquotes');
}

/**
 * Add settings to the Notice box. Does premise_settings_notices_box action hook.
 *
 * @author Brian Gardner
 * @version 1.0
 */
function premise_settings_notice_box() {
	premise_setting_line(premise_add_background_color_setting('notice_background_color', 'Background'));
	premise_setting_line(premise_add_border_setting('notice_border', 'Border'));
	premise_setting_line(premise_add_color_setting('notice_font_color', 'Color'));
	premise_setting_line(premise_add_select_setting('notice_font_family', 'Font', 'family'));
	premise_setting_line(premise_add_size_setting('notice_font_size', 'Font Size'));
	premise_setting_line(premise_add_select_setting('notice_font_style', 'Font Style', 'style'));
	premise_setting_line(premise_add_size_setting('notice_padding_topbottom', 'Padding Top/Bottom'));
	premise_setting_line(premise_add_size_setting('notice_padding_leftright', 'Padding Left/Right'));
	do_action('premise_settings_notice_box');
}

function premise_settings_pricing() {
	premise_setting_line(premise_add_note(__('The following apply to the containers for each pricing tier.', 'premise' )));
	premise_setting_line(premise_add_background_color_setting('pricing_tier_background', 'Container Background'));
	premise_setting_line(premise_add_border_setting('pricing_tier_border', 'Container Border'));
	premise_setting_line(premise_add_size_setting('pricing_tier_padding', 'Container Padding'));
	premise_setting_line(premise_add_size_setting('pricing_tier_border_radius', 'Container Rounded Corner Radius'));

	echo '<br />';
	premise_setting_line(premise_add_note(__('The following apply to the header area for each pricing tier.', 'premise' )));
	premise_setting_line(premise_add_background_color_setting('pricing_tier_header_background', 'Header Background'));
	premise_setting_line(premise_add_color_setting('pricing_tier_header_color', 'Header Color'));
	premise_setting_line(premise_add_select_setting('pricing_tier_header_font', 'Header Font', 'family'));
	premise_setting_line(premise_add_size_setting('pricing_tier_header_font_size', 'Header Font Size'));
	premise_setting_line(premise_add_select_setting('pricing_tier_header_font_style', 'Header Style', 'style'));
	premise_setting_line(premise_add_select_setting('pricing_tier_header_font_weight', 'Header Weight', 'weight'));
	premise_setting_line(premise_add_select_setting('pricing_tier_header_align', 'Header Text Align', 'align'));
	premise_setting_line(premise_add_size_setting('pricing_tier_header_padding', 'Header Padding'));

	echo '<br />';
	premise_setting_line(premise_add_note(__('The following apply to the feature area for each pricing tier.', 'premise' )));
	premise_setting_line(premise_add_background_color_setting('pricing_tier_features_background', 'Features Background'));
	premise_setting_line(premise_add_color_setting('pricing_tier_features_color', 'Features Color'));
	premise_setting_line(premise_add_size_setting('pricing_tier_features_padding', 'Features Padding'));

	echo '<br />';
	premise_setting_line(premise_add_note(__('The following apply to the call to action links for each pricing tier.', 'premise' )));
	premise_setting_line(premise_add_background_color_setting('pricing_tier_cta_background', 'Call to Action Background'));
	premise_setting_line(premise_add_background_color_setting('pricing_tier_cta_background_hover', 'Call to Action Background Hover'));
	premise_setting_line(premise_add_color_setting('pricing_tier_cta_color', 'Call to Action Color'));
	premise_setting_line(premise_add_select_setting('pricing_tier_cta_align', 'Call to Action Text Align', 'align'));
	premise_setting_line(premise_add_size_setting('pricing_tier_cta_padding', 'Call to Action Padding'));

	do_action('premise_settings_pricing');
}

/**
 * Add settings to the Headlines box. Does premise_settings_headline action hook.
 *
 * @author Gary Jones
 * @version 0.9.5
 */
function premise_settings_headline() {
	premise_setting_line(premise_add_select_setting('headline_font_family', 'Font', 'family'));
	premise_setting_line(premise_add_select_setting('headline_font_style', 'Font Style', 'style'));
	premise_setting_line(premise_add_select_setting('headline_font_weight', 'Font Weight', 'weight'));
	premise_setting_line(premise_add_select_setting('headline_text_align', 'Text Align', 'align'));
	premise_setting_line(premise_add_select_setting('headline_text_transform', 'Text Transform', 'transform'));
	premise_setting_line(array(premise_add_size_setting('h1_font_size', 'H1 Font Size'), premise_add_color_setting('h1_font_color', 'Color')));
	premise_setting_line(array(premise_add_size_setting('h2_font_size', 'H2 Font Size'), premise_add_color_setting('h2_font_color', 'Color')));
	premise_setting_line(array(premise_add_size_setting('h3_font_size', 'H3 Font Size'), premise_add_color_setting('h3_font_color', 'Color')));
	premise_setting_line(array(premise_add_size_setting('h4_font_size', 'H4 Font Size'), premise_add_color_setting('h4_font_color', 'Color')));
	do_action('premise_settings_headline');
}


/**
 * Add settings to the Footer box. Does premise_settings_footer action hook.
 *
 * @author Gary Jones
 * @version 0.9.5
 */
function premise_settings_footer() {
	premise_setting_line(premise_add_color_setting('footer_font_color', 'Color'));
	premise_setting_line(premise_add_size_setting('footer_font_size', 'Font Size'));
	premise_setting_line(premise_add_select_setting('footer_font_weight', 'Font Weight', 'weight'));
	premise_setting_line(premise_add_select_setting('footer_text_align', 'Text Align', 'align'));
	premise_setting_line(premise_add_select_setting('footer_text_transform', 'Text Transform', 'transform'));
	do_action('premise_settings_footer');
}

/**
 * Add settings to the Input box. Does premise_settings_input_box action hook.
 *
 * @author Brian Gardner
 * @version 1.0
 */
function premise_settings_input_box() {
	premise_setting_line(premise_add_background_color_setting('input_background_color', 'Background'));
	premise_setting_line(premise_add_border_setting('input_border', 'Border'));
	premise_setting_line(premise_add_color_setting('input_font_color', 'Color'));
	premise_setting_line(premise_add_select_setting('input_font_family', 'Font', 'family'));
	premise_setting_line(premise_add_select_setting('input_font_style', 'Font Style', 'style'));
	do_action('premise_settings_input_box');
}

/**
 * Add settings to the Submit Buttons box. Does premise_settings_buttons action hook.
 *
 * @author Brian Gardner
 * @since 0.9.7.2
 * @version 0.9.8
 */
function premise_settings_buttons() {
	premise_setting_line(premise_add_background_color_setting('button_background_color', 'Background'));
	premise_setting_line(premise_add_background_color_setting('button_background_hover_color', 'Background Hover'));
	premise_setting_line(premise_add_border_setting('button_border', 'Border'));
	premise_setting_line(premise_add_color_setting('button_font_color', 'Color'));
	premise_setting_line(premise_add_select_setting('button_font_family', 'Font', 'family'));
	premise_setting_line(premise_add_size_setting('button_font_size', 'Font Size'));
	premise_setting_line(premise_add_size_setting('button_padding', 'Padding'));
	premise_setting_line(premise_add_size_setting('button_border_radius', 'Rounded Corner Radius'));
	premise_setting_line(premise_add_select_setting('button_text_transform', 'Text Transform', 'transform'));
	do_action('premise_settings_buttons');
}


function premise_settings_content_scroller() {
	premise_setting_line(premise_add_background_color_setting('content_scroller_background_color', 'Container Background'));
	premise_setting_line(premise_add_border_setting('content_scroller_holder_border', 'Border'));
	premise_setting_line(premise_add_size_setting('content_scroller_holder_radius', 'Rounded Corner Radius'));
	premise_setting_line(premise_add_color_setting('content_scroller_color', 'Container Text Color'));
	premise_setting_line(premise_add_size_setting('content_scroller_padding', 'Container Padding'));

	premise_setting_line(premise_add_background_color_setting('content_scroller_arrows_background', 'Arrows Background'));
	premise_setting_line(premise_add_color_setting('content_scroller_arrows_color', 'Arrows Color'));

	premise_setting_line(premise_add_background_color_setting('content_scroller_tabs_background_color', 'Tabs Background'));
	premise_setting_line(premise_add_color_setting('content_scroller_tabs_color', 'Tabs Color'));
	premise_setting_line(premise_add_size_setting('content_scroller_tabs_radius', 'Tabs Top Corner Radius'));

	premise_setting_line(premise_add_background_color_setting('content_scroller_tabs_active_background_color', 'Active Tab Background'));
	premise_setting_line(premise_add_color_setting('content_scroller_tabs_active_color', 'Active Tab Color'));


	do_action('premise_settings_content_scroller');
}


function premise_settings_video() {
	premise_setting_line(premise_add_background_color_setting('video_holder_background', 'Background'));
	premise_setting_line(premise_add_border_setting('video_holder_border', 'Border'));
	premise_setting_line(premise_add_color_setting('video_holder_color', 'Color'));
	premise_setting_line(premise_add_size_setting('video_holder_padding', 'Padding'));
	premise_setting_line(premise_add_size_setting('video_holder_radius', 'Rounded Corner Radius'));

	do_action('premise_settings_video');
}

function premise_settings_optin() {
	premise_setting_line(premise_add_background_color_setting('optin_holder_background', 'Container Background'));
	premise_setting_line(premise_add_color_setting('optin_holder_color', 'Container Color'));
	premise_setting_line(premise_add_border_setting('optin_holder_border', 'Container Border'));
	premise_setting_line(premise_add_size_setting('optin_holder_padding', 'Container Padding'));
	premise_setting_line(premise_add_size_setting('optin_holder_radius', 'Container Rounded Corner Radius'));
	echo '<br />';

	premise_setting_line(premise_add_background_color_setting('optin_form_background', 'Form Background'));
	premise_setting_line(premise_add_border_setting('optin_form_border', 'Form Border'));
	premise_setting_line(premise_add_size_setting('optin_form_padding', 'Form Padding'));
	premise_setting_line(premise_add_size_setting('optin_form_radius', 'Form Rounded Corner Radius'));
	echo '<br />';

	premise_setting_line(premise_add_background_color_setting('optin_form_header_background', 'Form Header Background'));
	premise_setting_line(premise_add_color_setting('optin_form_header_color', 'Form Header Color'));
	premise_setting_line(premise_add_select_setting('optin_form_header_font', 'Form Header Font', 'family'));
	premise_setting_line(premise_add_size_setting('optin_form_header_font_size', 'Form Header Font Size'));
	premise_setting_line(premise_add_size_setting('optin_form_header_padding', 'Form Header Padding'));
	echo '<br />';

	premise_setting_line(premise_add_background_color_setting('optin_form_contents_background', 'Form Contents Background'));
	premise_setting_line(premise_add_color_setting('optin_form_contents_color', 'Form Contents Color'));
	premise_setting_line(premise_add_size_setting('optin_form_contents_padding', 'Form Contents Padding'));
	echo '<br />';

	premise_setting_line(premise_add_background_color_setting('optin_form_contents_submit_background', 'Form Submit Background'));
	premise_setting_line(premise_add_background_color_setting('optin_form_contents_submit_background_hover', 'Form Submit Background Hover'));
	premise_setting_line(premise_add_color_setting('optin_form_contents_submit_color', 'Form Submit Color'));
	premise_setting_line(premise_add_size_setting('optin_form_contents_submit_padding', 'Form Submit Padding'));

	do_action('premise_settings_optin');
}

function premise_settings_social_share() {
	premise_setting_line(premise_add_background_color_setting('teaser_share_box_inside_background', 'Share Box Background'));
	premise_setting_line(premise_add_border_setting('teaser_share_box_border', 'Share Box Border'));
	premise_setting_line(premise_add_border_setting('teaser_share_box_inside_border', 'Share Box Inner Border'));
	premise_setting_line(premise_add_color_setting('teaser_share_box_color', 'Share Box Color'));
	premise_setting_line(premise_add_size_setting('teaser_share_box_border_radius', 'Share Box Rounded Corner Radius'));
	echo '<br />';

	premise_setting_line(premise_add_background_color_setting('teaser_share_icons_background', 'Share Icons Background'));
	premise_setting_line(premise_add_color_setting('teaser_share_icons_border_color', 'Share Icons Border Color'));
	premise_setting_line(premise_add_color_setting('teaser_share_icons_color', 'Share Icons Color'));
	
	premise_setting_line(premise_add_background_color_setting('teaser_share_icons_hover_background', 'Share Icons Hover Background'));
	premise_setting_line(premise_add_color_setting('teaser_share_icons_hover_border_color', 'Share Icons Hover Border Color'));
	premise_setting_line(premise_add_color_setting('teaser_share_icons_hover_color', 'Share Icons Hover Color'));

	do_action('premise_settings_optin');
}


/**
 * Add settings to the General Settings box. Does premise_settings_general action hook.
 *
 * @author Gary Jones
 * @since 0.9.6
 * @version 1.0
 */
function premise_settings_general() {

	$design_key = isset( $_GET['premise-design-key'] ) ? $_GET['premise-design-key'] : '';
	premise_setting_line(premise_add_note( sprintf( __( 'Use the new <a href="%s">Custom Code Editor</a> to add/edit custom CSS and functions. ', 'premise' ), menu_page_url( 'premise-custom', false ) ) ) );
	premise_setting_line(premise_add_checkbox_setting('minify_css', 'Minify CSS?'));
	premise_setting_line(premise_add_note( __( 'Check this box for a live site, uncheck for testing.', 'premise' ) ) );

	echo '<hr />';
	if ( $design_key ) {
		premise_setting_line('<a class="button" href="' . wp_nonce_url(admin_url('admin.php?page=premise-style-settings&amp;premise=export&amp;premise-design-key='.$_GET['premise-design-key']), 'premise-export') . '">'.__('Export Premise Settings', 'premise' ) . '</a>');
	}
	
	$title = premise_get_design_option( 'premise_style_title', $design_key );
	if(empty($title)) {
		$title = __('My Style', 'premise' );
	}

	premise_setting_line('</form><form id="premise-settings-import" method="post" enctype="multipart/form-data" action="">' . wp_nonce_field('premise-import', '_wpnonce-premise-import') . premise_add_label('import-file', 'Import premise Settings File') . '<br /><input type="hidden" name="premise" value="import" /><input type="file" class="text_input" name="file" id="import-file" /><input class="button" type="submit" value="Upload" /><input type="hidden" name="premise-design-key" value="'.esc_attr( $design_key ).'" /><input type="hidden" name="premise_style_title" value="'.esc_attr($title).'" /></form>');
	do_action('premise_settings_general');
}