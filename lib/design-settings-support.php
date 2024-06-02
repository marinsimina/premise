<?php
/**
 * This file deals with the importing of design settings.
 *
 * @package premise
 * @author StudioPress & Gary Jones
 * @since 0.9.7
 */

/**
 * This function pulls the design settings from the DB
 * for use/return. Does not cache, so always up to date.
 *
 * @author Gary Jones
 * @return mixed
 * @since 0.9.5
 * @version 0.9.8
 */
function premise_get_fresh_design_option( $opt, $key = null ) {
	global $premise_design_settings;
	$setting = $premise_design_settings->get_configured_style( $key );
	if( isset( $setting[$opt] ) )
		return $setting[$opt];

	return false;
}

/**
 * This function pulls the design settings from the DB
 * for use/return. Uses cache to minimise repeat lookups.
 *
 * @author StudioPress
 * @return mixed
 * @uses genesis_get_option()
 */
function premise_get_design_option( $opt, $key = null ) {
	return premise_get_fresh_design_option($opt, $key);
}

/**
 * Pull the option from the database to know if we're wanting minified CSS.
 *
 * @author Gary Jones
 * @since 0.9.7
 * @version 0.9.8
 */
function premise_is_minified($key = null) {
	return premise_get_fresh_design_option('minify_css', $key);
}

/**
 * Adds a dropdown setting - label and select.
 *
 * @author Gary Jones
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param string $type One of the types allowed in {@link premise_create_options()}
 * @since 0.9.5
 * @return string HTML markup
 * @version 0.9.8
 */
function premise_add_select_setting($id, $label, $type) {
	return premise_add_label($id, $label) . '<select id="' . $id . '" name="' . 'premise-design' . '[' . $id . ']" class="' . $type . '-option-types">' . premise_create_options(premise_get_fresh_design_option($id), $type) . '</select>';
}

/**
 * Adds a color setting - label and input.
 *
 * @author Gary Jones
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @since 0.9.5
 * @return string HTML markup
 */
function premise_add_color_setting($id, $label) {
	return premise_add_label($id, $label) . '<input type="text" id="' . $id . '" name="' . 'premise-design' . '[' . $id . ']" size="8" maxsize="7" value="' . esc_attr( premise_get_fresh_design_option($id) ) . '" class="premise-color-picker" />';
}

/**
 * Adds a background color setting - label, select and input.
 *
 * @author Gary Jones
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @since 0.9.8
 * @return string HTML markup
 */
function premise_add_background_color_setting($id, $label) {
	return premise_add_select_setting($id.'_select', $label, 'background') . '<input type="text" id="' . $id . '_hex" name="' . 'premise-design' . '[' . $id . ']" size="8" maxsize="7" value="' . esc_attr( premise_get_fresh_design_option($id) ) . '" class="premise-color-picker" />';
}


/**
 * Adds a size setting - label and input.
 *
 * @author Gary Jones
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param int $size Value for the size attribute (default = 1)
 * @since 0.9.5
 * @return string HTML markup
 */
function premise_add_size_setting($id, $label, $size = 1) {
	return premise_add_label($id, $label, false) . '<input class="numeric" type="text" id="' . $id . '" name="' . 'premise-design' . '[' . $id . ']" size="' . $size . '" value="' . esc_attr( premise_get_fresh_design_option($id) ) . '" /><abbr title="pixels">px</abbr></label>';
}

/**
 * Adds a text setting - label and input.
 *
 * @author Gary Jones
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param int $size Value for the size attribute (default = 25)
 * @since 0.9.5
 * @return string HTML markup
 */
function premise_add_text_setting($id, $label, $size = 25) {
	return premise_add_label($id, $label) . '<input type="text" id="' . $id . '" name="' . 'premise-design' . '[' . $id . ']" size="' . $size . '" value="' . esc_attr( premise_get_fresh_design_option($id) ) . '" />';
}

/**
 * Adds a checkbox setting - input and label.
 *
 * @author Gary Jones
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @since 0.9.6
 * @return string HTML markup
 */
function premise_add_checkbox_setting($id, $label) {
	return '<input type="checkbox" id="' . $id . '" name="' . 'premise-design' . '[' . $id . ']" value="true" class="checkbox" ' . checked(premise_get_fresh_design_option($id), 'true', false) . '/>' . premise_add_label($id, $label, true, false);
}

/**
 * Adds a textarea setting - label and textarea.
 *
 * @author Gary Jones
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @param integer cols Value for the cols attribute (default = 25)
 * @param integer rows Value for the rows attribute (default = 10)
 * @since 0.9.5
 * @return string HTML markup
 */
function premise_add_textarea_setting($id, $label, $cols = 25, $rows = 10) {
	return premise_add_label($id, $label) . '<br /><textarea id="' . $id . '" name="' . 'premise-design' . '[' . $id . ']" cols="39" rows="10">' . premise_get_fresh_design_option($id) . '</textarea>';
}

/**
 * Adds border width, color and style settings on one line.
 *
 * @author Gary Jones
 * @param string $id ID of the element
 * @param string $label Displayed label
 * @return string HTML markup
 * @since 1.0
 */
function premise_add_border_setting($id, $label) {
	return array(premise_add_size_setting($id, $label), premise_add_color_setting($id .'_color', ''), premise_add_select_setting($id . '_style', '', 'border'));
}

/**
 * Adds a NOTE.
 *
 * @author Gary Jones
 * @param string $note Text to display as the note
 * @return string HTML markup
 * @since 0.9.5
 */
function premise_add_note( $note ) {
	return '<span class="description"><strong>' . __( 'Note', 'premise' ) . ':</strong> ' . $note . '</span>';
}


/**
 * Adds the paragraph tags around a setting line and echos the result.
 *
 * @author Gary Jones
 * @param array|string $args
 * @since 0.9.5
 */
function premise_setting_line($args) {
	if ( is_array($args) ) {
		$output = '';
		foreach ($args as $arg) {
			$output .= ' ' .$arg;
		}
		premise_setting_line($output);
	} else {?>
<p><?php echo $args; ?></p>
	<?php
	}
}

/**
 * Adds the opening label tag, the for attribute, and the label text itself.
 *
 * If the label text is at least 1 character long, then it's wrapped as a label element.
 * @author Gary Jones
 * @param string $id
 * @param string $label
 * @param boolean $add_end_tag Optionaly add closing label tag (default = true)
 * @param boolean $add_colon Optionaly add colon after the label (default = true)
 * @return string HTML markup for a label
 * @since 1.0
 */
function premise_add_label( $id, $label, $add_end_tag = true, $add_colon = true ) {
	if ( strlen( $label ) <= 0 )
		return '';

	$return = sprintf('<label for="%s">%s', $id, __( $label, 'premise' ) );
	if ( $add_colon )
		$return .= ':';
	if ( $add_end_tag )
		$return .= '</label>';
		
	return $return;
}