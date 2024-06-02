<?php
/*
Shortcodes for Premise
*/
class Premise_Shortcodes {
	var $parent = null;
	var $_optin_ManualBase = '_premise_optin_manual_form_';
	var $_has_errors = false;
	
	function __construct() {

		// leave gwo-section shortcode for existing landing pages that have the shortcode
		add_shortcode( 'gwo-section', array( &$this, 'gwo_section' ) );
		add_shortcode( 'aweber-optin-form', array( &$this, 'aweber_form' ) );
		add_shortcode( 'constant-contact-optin-form', array( &$this, 'constant_contact_form' ) );
		add_shortcode( 'mailchimp-optin-form', array( &$this, 'mailchimp_form' ) );
		add_shortcode( 'manual-optin-form', array( &$this, 'manual_opt_in_form' ) );
		add_shortcode( 'premise-internal', array( &$this, 'premise_internal' ) );
		add_shortcode( 'premise-button', array( &$this, 'premise_button' ) );

		add_shortcode( 'slider-tab', array( &$this, 'slider_tab' ) );
		add_shortcode( 'next-slider-tab', array( &$this, 'next_slider_tab' ) );
		add_shortcode( 'previous-slider-tab', array( &$this, 'previous_slider_tab' ) );
		add_filter( 'premise_get_optin_form_code', 'do_shortcode' );

	}
	/*
	Aweber
	*/
	function aweber_form( $atts, $content = null ) {

		global $premise_base;

		if ( isset( $atts['localform'] ) )
			$localform = $atts['localform'];

		$atts = $this->shortcode_atts( $atts );
		extract( $atts );
		
		if( empty( $id ) )
			return '';

		$lists = $premise_base->get_aweber_lists();
		$the_list = false;
		foreach( $lists as $list ) {

			foreach( $list['forms'] as $form ) {

				if ( $form['id'] == $id ) {

					$the_list = $list;
					break;
				}

			}
			if ( $the_list )
				break;

		}
		if ( ! $the_list )
			return '';

		$list_id = $the_list['id'];
		$settings = $premise_base->get_settings();
		if ( ! isset( $localform ) )
			$localform = ( isset( $settings['optin']['aweber-api'] ) && $settings['optin']['aweber-api'] );
		
		if ( $localform ) {

			$custom_fields = isset( $the_list['custom_fields'] ) ? $the_list['custom_fields'] : array();
			$meta = $premise_base->get_premise_meta( get_the_ID() );
			$is_checkout = isset( $meta['member-merge-gateway'] ) && $meta['member-merge-gateway'] == 'aweber';
			$aweber_form = 'misc/aweber-contact-form.php';

		} else {

			$scheme = is_ssl() ? 'https://' : 'http://'; 
			$script = sprintf( '<script type="text/javascript" src="%sforms.aweber.com/form/%02d/%d.js"></script>', $scheme, $id % 100, (int)$id );
			$aweber_form = 'misc/aweber-hosted-form.php';

		}

		$this->_has_errors = isset( $_REQUEST['error'] ) ? (int) $_REQUEST['error'] : 0;
		$complete = ! $this->_has_errors && isset( $_REQUEST['confirm'] );
		$message = $this->_has_errors ? (array)$premise_base->get_transient( '_premise_optin_aweber_messages', null ) : false;

		ob_start();
		include( PREMISE_VIEWS_DIR . $aweber_form );
		return ob_get_clean();
	}
	/*
	Constant Contact
	*/
	function constant_contact_form( $atts, $content = null ) {
		global $premise_base;
		
		$atts = $this->shortcode_atts( $atts );
		extract( $atts );
		
		if( !isset( $id ) )
			return '';

		$messages = (array)$premise_base->get_transient( '_optin_ConstantContactMessages', '_optin_ConstantContactCachedMessages' );
		$ccnumber = $premise_base->get_autonumber( '_optin_ConstantContactNumber' );

		ob_start();
		include( PREMISE_VIEWS_DIR . 'misc/constant-contact-form.php' );
		return ob_get_clean();
	}
	/*
	Google Web Optimizer
	*/
	function gwo_section( $atts, $content = null ) {
		if( empty( $content ) )
			return '';

		$atts = shortcode_atts( array( 'id'=>'' ), $atts );
		if( !empty( $atts['id'] ) )
			return '<script>utmx_section("' . esc_js( $atts['id'] ) . '")</script>' . $content . '</noscript>';

		return $content;
	}
	/*
	MailChimp
	*/
	function mailchimp_form( $atts, $content = null ) {
		global $premise_base;
		
		$atts = $this->shortcode_atts( $atts );
		extract( $atts );
		
		if( !isset( $id ) )
			return '';

		$this->_mc_has_errors = isset( $_REQUEST['error'] ) ? (int) $_REQUEST['error'] : 0;
		$complete = ! $this->_mc_has_errors && isset( $_REQUEST['confirm'] );
		$mcnumber = '';
		$messages = (array)$premise_base->get_transient( '_optin_MailChimpMessages', '_optin_MailChimpCachedMessages' );

		if ( ! $complete ) {

			$mcnumber = $premise_base->get_autonumber( '_optin_MailChimpNumber' );

			$mv = $premise_base->get_mailchimp_merge_vars( $id );

			if( !$mv )
				return '';

		}

		ob_start();
		include( PREMISE_VIEWS_DIR . 'misc/mailchimp-form.php' );
		return ob_get_clean();
	}

	function optin_confirm() {
		if( $this->_has_errors || ! isset( $_REQUEST['confirm'] ) )
			return;

		echo '<div class="optin-confirm">';

		if ( $_REQUEST['confirm'] == '1' )
			_e( 'Almost finished... We need to confirm your email address. To complete the subscription process, please click the link in the email we just sent you.', 'premise' );
		else
			_e( 'You have been subscribed to the list.', 'premise' );

		echo "</div>\n";
	}
	/*
	Manual contact form
	*/
	function manual_opt_in_form( $atts, $content = null ) {
		$atts = $this->shortcode_atts( $atts );
		extract( $atts );
		
		if( !isset( $id ) )
			return '';

		$script = get_option( $this->_optin_ManualBase . $id );
		$script = do_shortcode( $script );
		
		ob_start();
		include( PREMISE_VIEWS_DIR . 'misc/manual-contact-form.php' );
		return ob_get_clean();
	}
	/*
	tabs
	*/
	function slider_tab( $atts, $content = null ) {
		if( is_array( $atts ) )
			extract( $atts );
		else
			$number = (int)$atts;
		
		$tabNumber = is_numeric( $number ) && $number > 1 ? $number : 1;
		
		return '<a class="xtrig" rel="coda-slider-1" href="#' . $tabNumber . '">' . $content . '</a>';
	}
	function next_slider_tab( $atts, $content = null ) {
		global $current_slider_tab, $current_slider_total_tabs;
		
		if( $current_slider_tab == $current_slider_total_tabs )
			$next = 1;
		else
			$next = $current_slider_tab + 1;
		
		$content = trim( $content );
		if( empty( $content ) )
			$content = __( 'Next', 'premise' );
		
		return $this->slider_tab( $next, $content );
	}	
	function previous_slider_tab( $atts, $content = null ) {
		global $current_slider_tab, $current_slider_total_tabs;
		
		if( $current_slider_tab == 1 )
			$previous = $current_slider_total_tabs;
		else
			$previous = $current_slider_tab - 1;
		
		$content = trim( $content );
		if( empty( $content ) )
			$content = __( 'Previous', 'premise' );
		
		return $this->slider_tab( $previous, $content );
	}	
	/*
	internal shortcode
	*/
	function premise_internal( $atts, $content = null ) {
		if( empty( $content ) || empty( $atts['id'] ) )
			return '';
			
		return sprintf('<a href="%s">%s</a>', add_query_arg( array( 'section' => $atts['id'] ) ), $content );
	}
	/*
	Premise button
	*/
	function premise_button( $atts, $content = null ) {
		$atts = array_map( 'trim', $atts );
		
		if( !empty( $atts['href'] ) )
			$code = '<a href="' . esc_url( $atts['href'] ) . '" class="premise-button-%s">%s</a>';
		else
			$code = '<span class="premise-button-%s">%s</span>';
		
		return sprintf( $code, sanitize_html_class( $atts['id'] ), $content );
	}
	/*
	common function used to parse shortcode attributes
	*/
	function shortcode_atts( $atts ) {
		if( empty( $atts['id'] ) )
			return array();
			
		$atts = shortcode_atts( array( 'id' => '', 'align' => 'none', 'title' => __( 'Sign Up', 'premise' ), 'button_text' => __( 'Sign Up', 'premise' ) ), $atts );

		if( !in_array( $atts['align'], array( 'center', 'left', 'right' ) ) )
			$atts['align'] = 'none';
		
		return $atts;
	}
	/*
	mailchimp form fields
	*/
	function mailchimp_label( $merge_var ) {
		extract( $merge_var );

		echo '<label for="mailchimp-' . esc_attr( $tag ) . '">' . esc_html( $name ); 
		if( isset( $dateformat ) )
			echo ' (' . esc_html( $dateformat ) . ')';

		echo ( $req == 1 ? '*' : '' ) . "</label>\n";
	}
	function mailchimp_field( $merge_var ) {

		$choices = $dateformat = null;
		extract( $merge_var );
		$posted = '';
		if ( isset( $_POST['mailchimp'][$tag] ) )
			$posted = stripslashes_deep( $_POST['mailchimp'][$tag] );
		elseif ( ! empty( $merge_var['default'] ) )
			$posted = $merge_var['default'];
		elseif ( $field_type == 'address' )
			$posted = array(
				'addr1' => '',
				'addr2' => '',
				'city' => '',
				'state' => '',
				'zip' => '',
				'country' => ! empty( $merge_var['defaultcountry'] ) ? $merge_var['defaultcountry'] : 164
			);
		
		$callback = 'mailchimp_field_' . $field_type;
		if( $choices === null )
			$choices = $dateformat;

		if( is_callable( array( $this, $callback ) ) )
			$this->$callback( $tag, $name, $posted, $choices );
		else
			echo '<input type="text" id="mailchimp-' . esc_attr( $tag ) . '" name="mailchimp[' . esc_attr( $tag ) . ']" value="' . esc_attr( $posted ) .'" />';
	}
	// birthday
	function mailchimp_field_birthday( $tag, $name, $posted, $dateformat ) {
		$this->mailchimp_field_date( $tag, $name, $posted, $dateformat, 'date birthday' );
	}
	// date
	function mailchimp_field_date( $tag, $name, $posted, $dateformat, $type = 'date' ) {
		$posted = (array)$posted;
		$dateformat = strtoupper( $dateformat );

		foreach( array( 'DD' => ' D ', 'MM' => ' M ', 'YYYY' => ' Y ', 'YY' => ' Y ' ) as $search => $replace )
			$dateformat = str_replace( $search, $replace, $dateformat );

		$format = '<span class="subfield %2$s"><input type="text" pattern="[0-9]*" id="mailchimp-%1$s-%2$s" name="mailchimp[%1$s][%2$s]" maxlength="%3$s" value="%4$s"></span>';
		foreach( array( 'M' => 'month', 'D' => 'day', 'Y' => 'year' ) as $field => $index ) {
			$input = sprintf( $format, esc_attr( $tag ), $index, $field == 'Y' ? 4 : 2, esc_attr( $posted[$index] ) );
			$dateformat = str_replace( $field, $input, $dateformat );
		}

		echo '<div class="premise-' . esc_attr( $type ) . '">' . $dateformat . "</div>\n";
	}
	// radio
	function mailchimp_field_radio( $tag, $name, $posted, $choices ) {
		echo '<div class="premise-radio"><ul class="interestgroup_field" id="mailchimp-' . esc_attr( $tag ) . '">';
		foreach( $choices as $key => $choice ) {
			$id = esc_attr( $tag . '-' . $key ); 
			echo '<li><input type="radio" name="mailchimp[' . esc_attr( $tag ) . ']" id="mailchimp-' . $id . '" value="' . esc_attr( $choice ) . '"' . checked( $choice, $posted, false ) . '>&nbsp;<label for="' . $id . '">' . esc_html( $choice ) . '</label></li>';
		}			

		echo "</ul></div>\n";
	}
	// dropdown
	function mailchimp_field_dropdown( $tag, $name, $posted, $choices ) {
		echo '<select id="mailchimp-' . esc_attr( $tag ) . '" name="mailchimp[' . esc_attr( $tag ) . ']">';
		echo '<option value="">' . esc_html( $name ) . '</option>';
		foreach( $choices as $choice ) 
			echo '<option value="' . esc_attr( $choice ) . '"' . selected( $choice, $posted, false ) . '>' . esc_html( $choice ) . '</option>';
			
		echo "</select>\n";
	}
	// address
	function mailchimp_field_address( $tag, $name, $posted ) {
		$posted = (array)$posted;
			?>
<div class="premise-address"> 
	<div class="addressfield">
		<span class="subfield addr1field"><label for="mailchimp-<?php echo esc_attr( $tag ); ?>-addr1"><?php _e( 'Street Address', 'premise' ); ?></label><input type="text" id="mailchimp-<?php echo esc_attr( $tag ); ?>-addr1" name="mailchimp[<?php echo esc_attr( $tag ); ?>][addr1]" maxlength="70" value="<?php echo esc_attr( $posted['addr1'] );?>"></span>
		<span class="subfield addr2field"><label for="mailchimp-<?php echo esc_attr( $tag ); ?>-addr2"><?php _e( 'Address Line 2', 'premise' ); ?></label><input type="text" id="mailchimp-<?php echo esc_attr( $tag ); ?>-addr2" name="mailchimp[<?php echo esc_attr( $tag ); ?>][addr2]" maxlength="70" value="<?php echo esc_attr( $posted['addr2'] );?>"></span>
		<span class="subfield cityfield"><label for="mailchimp-<?php echo esc_attr( $tag ); ?>-city"><?php _e( 'City', 'premise' ); ?></label><input type="text" id="mailchimp-<?php echo esc_attr( $tag ); ?>-city" name="mailchimp[<?php echo esc_attr( $tag ); ?>][city]" maxlength="40" value="<?php echo esc_attr( $posted['city'] );?>"></span>
		<span class="subfield statefield"><label for="mailchimp-<?php echo esc_attr( $tag ); ?>-state"><?php _e( 'State/Province/Region', 'premise' ); ?></label><input type="text" id="mailchimp-<?php echo esc_attr( $tag ); ?>-state" name="mailchimp[<?php echo esc_attr( $tag ); ?>][state]" maxlength="20" value="<?php echo esc_attr( $posted['state'] );?>"></span>
		<span class="subfield zipfield"><label for="mailchimp-<?php echo esc_attr( $tag ); ?>-zip"><?php _e( 'Postal / Zip Code', 'premise' ); ?></label><input type="text" id="mailchimp-<?php echo esc_attr( $tag ); ?>-zip" name="mailchimp[<?php echo esc_attr( $tag ); ?>][zip]" maxlength="10" value="<?php echo esc_attr( $posted['zip'] );?>"></span>
		<span class="subfield countryfield"><label for="mailchimp-<?php echo esc_attr( $tag ); ?>-country"><?php _e( 'Country', 'premise' ); ?></label><select id="mailchimp-<?php echo esc_attr( $tag ); ?>-country" name="mailchimp[<?php echo esc_attr( $tag ); ?>][country]">
			<?php
			$country = array(
				164 => 'USA',
				286 => 'Aaland Islands',
				274 => 'Afghanistan',
				2 => 'Albania',
				3 => 'Algeria',
				178 => 'American Samoa',
				4 => 'Andorra',
				5 => 'Angola',
				176 => 'Anguilla',
				175 => 'Antigua And Barbuda',
				6 => 'Argentina',
				7 => 'Armenia',
				179 => 'Aruba',
				8 => 'Australia',
				9 => 'Austria',
				10 => 'Azerbaijan',
				11 => 'Bahamas',
				12 => 'Bahrain',
				13 => 'Bangladesh',
				14 => 'Barbados',
				15 => 'Belarus',
				16 => 'Belgium',
				17 => 'Belize',
				18 => 'Benin',
				19 => 'Bermuda',
				20 => 'Bhutan',
				21 => 'Bolivia',
				22 => 'Bosnia and Herzegovina',
				23 => 'Botswana',
				181 => 'Bouvet Island',
				24 => 'Brazil',
				180 => 'Brunei Darussalam',
				25 => 'Bulgaria',
				26 => 'Burkina Faso',
				27 => 'Burundi',
				28 => 'Cambodia',
				29 => 'Cameroon',
				30 => 'Canada',
				31 => 'Cape Verde',
				32 => 'Cayman Islands',
				33 => 'Central African Republic',
				34 => 'Chad',
				35 => 'Chile',
				36 => 'China',
				185 => 'Christmas Island',
				37 => 'Colombia',
				204 => 'Comoros',
				38 => 'Congo',
				183 => 'Cook Islands',
				268 => 'Costa Rica',
				275 => 'Cote D\'Ivoire',
				40 => 'Croatia',
				276 => 'Cuba',
				41 => 'Cyprus',
				42 => 'Czech Republic',
				43 => 'Denmark',
				44 => 'Djibouti',
				289 => 'Dominica',
				187 => 'Dominican Republic',
				233 => 'East Timor',
				45 => 'Ecuador',
				46 => 'Egypt',
				47 => 'El Salvador',
				48 => 'Equatorial Guinea',
				49 => 'Eritrea',
				50 => 'Estonia',
				51 => 'Ethiopia',
				189 => 'Falkland Islands',
				191 => 'Faroe Islands',
				52 => 'Fiji',
				53 => 'Finland',
				54 => 'France',
				193 => 'French Guiana',
				277 => 'French Polynesia',
				57 => 'Gambia',
				59 => 'Germany',
				60 => 'Ghana',
				194 => 'Gibraltar',
				61 => 'Greece',
				195 => 'Greenland',
				192 => 'Grenada',
				196 => 'Guadeloupe',
				62 => 'Guam',
				198 => 'Guatemala',
				270 => 'Guernsey',
				63 => 'Guinea',
				65 => 'Guyana',
				200 => 'Haiti',
				66 => 'Honduras',
				67 => 'Hong Kong',
				68 => 'Hungary',
				69 => 'Iceland',
				70 => 'India',
				71 => 'Indonesia',
				278 => 'Iran',
				279 => 'Iraq',
				74 => 'Ireland',
				75 => 'Israel',
				76 => 'Italy',
				202 => 'Jamaica',
				78 => 'Japan',
				288 => 'Jersey  (Channel Islands)',
				79 => 'Jordan',
				80 => 'Kazakhstan',
				81 => 'Kenya',
				82 => 'Kuwait',
				83 => 'Kyrgyzstan',
				84 => 'Lao People\'s Democratic Republic',
				85 => 'Latvia',
				86 => 'Lebanon',
				88 => 'Liberia',
				281 => 'Libya',
				90 => 'Liechtenstein',
				91 => 'Lithuania',
				92 => 'Luxembourg',
				208 => 'Macau',
				93 => 'Macedonia',
				94 => 'Madagascar',
				95 => 'Malawi',
				96 => 'Malaysia',
				97 => 'Maldives',
				98 => 'Mali',
				99 => 'Malta',
				210 => 'Martinique',
				100 => 'Mauritania',
				212 => 'Mauritius',
				241 => 'Mayotte',
				101 => 'Mexico',
				102 => 'Moldova, Republic of',
				103 => 'Monaco',
				104 => 'Mongolia',
				290 => 'Montenegro',
				294 => 'Montserrat',
				105 => 'Morocco',
				106 => 'Mozambique',
				242 => 'Myanmar',
				107 => 'Namibia',
				108 => 'Nepal',
				109 => 'Netherlands',
				110 => 'Netherlands Antilles',
				213 => 'New Caledonia',
				111 => 'New Zealand',
				112 => 'Nicaragua',
				113 => 'Niger',
				114 => 'Nigeria',
				217 => 'Niue',
				214 => 'Norfolk Island',
				272 => 'North Korea',
				116 => 'Norway',
				117 => 'Oman',
				118 => 'Pakistan',
				222 => 'Palau',
				282 => 'Palestine',
				119 => 'Panama',
				219 => 'Papua New Guinea',
				120 => 'Paraguay',
				121 => 'Peru',
				122 => 'Philippines',
				221 => 'Pitcairn',
				123 => 'Poland',
				124 => 'Portugal',
				126 => 'Qatar',
				58 => 'Republic of Georgia',
				127 => 'Reunion',
				128 => 'Romania',
				129 => 'Russia',
				130 => 'Rwanda',
				205 => 'Saint Kitts and Nevis',
				206 => 'Saint Lucia',
				237 => 'Saint Vincent and the Grenadines',
				132 => 'Samoa (Independent)',
				227 => 'San Marino',
				133 => 'Saudi Arabia',
				134 => 'Senegal',
				266 => 'Serbia',
				135 => 'Seychelles',
				136 => 'Sierra Leone',
				137 => 'Singapore',
				138 => 'Slovakia',
				139 => 'Slovenia',
				223 => 'Solomon Islands',
				141 => 'South Africa',
				257 => 'South Georgia and the South Sandwich Islands',
				142 => 'South Korea',
				143 => 'Spain',
				144 => 'Sri Lanka',
				293 => 'Sudan',
				146 => 'Suriname',
				225 => 'Svalbard and Jan Mayen Islands',
				147 => 'Swaziland',
				148 => 'Sweden',
				149 => 'Switzerland',
				152 => 'Taiwan',
				153 => 'Tanzania',
				154 => 'Thailand',
				155 => 'Togo',
				232 => 'Tonga',
				234 => 'Trinidad and Tobago',
				156 => 'Tunisia',
				157 => 'Turkey',
				287 => 'Turks &amp; Caicos Islands',
				159 => 'Uganda',
				161 => 'Ukraine',
				162 => 'United Arab Emirates',
				262 => 'United Kingdom',
				163 => 'Uruguay',
				165 => 'Uzbekistan',
				239 => 'Vanuatu',
				166 => 'Vatican City State (Holy See)',
				167 => 'Venezuela',
				168 => 'Vietnam',
				169 => 'Virgin Islands (British)',
				238 => 'Virgin Islands (U.S.)',
				188 => 'Western Sahara',
				173 => 'Zambia',
				174 => 'Zimbabwe',
			);
			foreach( $country as $k => $v )
				echo '<option value="' . $k . '"' . selected( $posted['country'], $k, false ) . '>' . $v . "</option>\n";
			?>
			</select>
		</span>
	</div> 
</div>
			<?php
	}
}