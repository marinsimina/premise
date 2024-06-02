<?php
/**
 * AccessPress Template Tags for displaying front-end content
 *
 * @package AccessPress
 */

function accesspress_checkout_form( $args = array() ) {

	global $product_member;

	$premise_checkout = Premise_Checkout::get_instance( $args );
	$checkout_args = $premise_checkout->get_checkout_args();
	$form_submitted = $premise_checkout->has_submitted_form();

	/** If form submitted */
	if ( $form_submitted ) {

		$checkout_complete = $premise_checkout->process_checkout();
		echo $premise_checkout->get_output_buffer();

		/** If there was an error in the submission show the error to the user */
		if ( is_wp_error( $checkout_complete ) ) {

			do_action( 'premise_checkout_incomplete_before', $checkout_args, $checkout_args['product_id'], $args, $checkout_complete );

			printf( '<div class="acp-error">%s</div>', $checkout_complete->get_error_message() );

			do_action( 'premise_checkout_incomplete_after', $checkout_args, $checkout_args['product_id'], $args, $checkout_complete );

		} else {

			/** Show the comlpete message when the transaction is complete */
			if ( $checkout_complete ) {

				do_action( 'premise_checkout_complete_before', $checkout_args, $checkout_args['product_id'], $args, $checkout_complete );

				if ( ! empty( $checkout_complete['member_id'] ) )
					$product_member = new WP_User( $checkout_complete['member_id'] );

				$receipt_body = get_post_meta( $checkout_args['product_id'], '_acp_product_email_receipt_intro', true );
				if( ! empty( $receipt_body ) )
					echo do_shortcode( str_replace( "\n", '<br />', $receipt_body ) );
					
				do_action( 'premise_checkout_complete_after', $checkout_args, $checkout_args['product_id'], $args, $checkout_complete );

			}
			/** don't show the checkout form */
			return;
		}
		
	}
	
	/** don't show the checkout form unless there is a product or checkout is in progress */
	$product_id = ! empty( $args['product_id'] ) ? $args['product_id'] : '';
	$product_id = isset( $_GET['product_id'] ) ? $_GET['product_id'] : $product_id;
	if( ! $form_submitted && ! memberaccess_is_valid_product( $product_id ) )
		return;

	$checkout_button = '<input type="submit" value="%s" class="input-submit" />';

	if ( ! isset( $_REQUEST['renew'] ) && memberaccess_member_has_product( $product_id ) ) {

			echo '<h3>' . $args['already_purchased_text'] . '</h3>';
			$checkout_button = '';

	}

	$checkout_args['product_id'] = $args['product_id'] = $product_id;
	do_action( 'premise_checkout_form_before', $checkout_args, $product_id, $args );

	echo '<div class="premise-checkout-wrap"><form method="post" action="">';

		printf( '<input type="hidden" name="accesspress-checkout[product_id]" id="accesspress-checkout-product-id" value="%s" />', $product_id );
		if ( isset( $_GET['renew'] ) && 'true' == $_GET['renew'] )
			echo '<input type="hidden" name="accesspress-checkout[renew]" value="true" />';

		do_action( 'premise_checkout_form', $checkout_args, $product_id, $args );

		if ( get_post_meta( $checkout_args['product_id'], '_acp_product_free_product', true ) )
			$checkout_text = trim( is_user_logged_in() ? $args['member_text_free'] : $args['nonmember_text_free'] );
		else
			$checkout_text = trim( is_user_logged_in() ? $args['member_text'] : $args['nonmember_text'] );

		$checkout_button = apply_filters( 'premise_checkout_button', $checkout_button, $checkout_args, $product_id, $args );
		if ( $checkout_button ) {

			if ( empty( $checkout_text ) )
				$checkout_text = apply_filters( 'premise_checkout_button_text', is_user_logged_in() ? __( 'Submit Order', 'premise' ) : __( 'Submit Order and Create My Account', 'premise' ), is_user_logged_in() );

			printf( $checkout_button, $checkout_text );

		}

	echo '</form></div>';
	
	do_action( 'premise_checkout_form_after', $checkout_args, $product_id, $args );

}

add_action( 'premise_checkout_form', 'accesspress_checkout_form_account' );

function accesspress_checkout_form_account( $args = array() ) {

	global $accesspress_checkout_member;

	if ( accesspress_get_option( 'email_as_username' ) )
		$args['show_username'] = false;

	if ( is_user_logged_in() ) {

		$user = wp_get_current_user();
		$accesspress_checkout_member = $user->ID;
		$args['first-name']	= $user->first_name;
		$args['last-name']	= $user->last_name;
		$args['email']		= $user->user_email;
		$args['username']	= $user->user_login;

	}

	$disabled_username = $disabled_user = '';
	if ( ! empty( $accesspress_checkout_member ) && $accesspress_checkout_member > 0 ) {

		$disabled_username = 'disabled="disabled" ';
		$disabled_user = ! isset( $args['disabled'] ) || $args['disabled'] ? $disabled_username : '';
		if ( ! empty( $args['account_box_heading_member'] ) )
			$args['account_box_heading'] =  $args['account_box_heading_member'];

		printf( '<input type="hidden" name="accesspress-checkout[member]" value="%d" />', $accesspress_checkout_member );
		wp_nonce_field( isset( $args['nonce_key'] ) ? $args['nonce_key'] : 'checkout-member-' . $accesspress_checkout_member, 'accesspress-checkout[member-key]' );

	}

	$args = wp_parse_args( $args, array(
		'account_box_heading'	=> is_user_logged_in() ? __( 'Your Account', 'premise' ) : __( '1. Create Your Account', 'premise' ),
		'first-name'		=> '',
		'last-name'		=> '',
		'email'			=> '',
		'username'		=> '',
		'wrap_before'		=> '<div class="accesspress-checkout-form-account">',
		'wrap_after'		=> '</div>',
		'before_item'		=> '<div class="accesspress-checkout-form-row %s-wrap">',
		'after_item'		=> '</div>',
		'show_first_name'	=> true,
		'show_last_name'	=> true,
		'show_email_address'	=> true,
		'show_username'		=> true,
		'label_separator'	=> ':',
		'script_username_id'	=> '#accesspress-checkout-username',
		'script_email_id'	=> '#accesspress-checkout-email',
		'script_submit_control'	=> '.input-submit',
	) );

	// generate a login link that redirects back to this checkout
	$login_page = accesspress_get_option( 'login_page' );
	$login_url = $login_page ? get_permalink( $login_page ) : '';
	$sign_in_fmt = $login_url ? '<a href="%2$s" title="%1$s">%1$s</a>' : '%s';
	$sign_in_url = sprintf( $sign_in_fmt, __( 'sign in', 'premise' ), esc_url( add_query_arg( array( 'redirect_to' => urlencode( $_SERVER['REQUEST_URI'] ) ), $login_url ) ) );

	do_action( 'premise_checkout_account_before_wrap' );
	echo $args['wrap_before'];
	do_action( 'premise_checkout_account_before' );
?>
		<?php if ( $args['account_box_heading'] ) : ?>
			<div class="accesspress-checkout-heading"><?php echo esc_html( $args['account_box_heading'] ); ?></div>
		<?php endif; ?>

		<?php if ( $args['show_first_name'] ) : ?>
			<?php printf( $args['before_item'], 'accesspress-checkout-first-name' ); ?>
				<label for="accesspress-checkout-first-name" class="checkout-text-label"><?php echo __( 'First Name', 'premise' ) . $args['label_separator']; ?></label>
				<input type="text" name="accesspress-checkout[first-name]" id="accesspress-checkout-first-name" class="input-text" value="<?php echo esc_attr( $args['first-name'] ); ?>" <?php echo $disabled_user; ?>/>
			<?php echo $args['after_item']; ?>
		<?php endif; ?>

		<?php if ( $args['show_last_name'] ) : ?>
			<?php printf( $args['before_item'], 'accesspress-checkout-last-name' ); ?>
				<label for="accesspress-checkout-last-name" class="checkout-text-label"><?php echo __( 'Last Name', 'premise' ). $args['label_separator']; ?></label>
				<input type="text" name="accesspress-checkout[last-name]" id="accesspress-checkout-last-name" class="input-text" value="<?php echo esc_attr( $args['last-name'] ); ?>" <?php echo $disabled_user; ?>/>
			<?php echo $args['after_item']; ?>
		<?php endif; ?>

		<?php if ( ! is_user_logged_in() ) : ?>
		<span class="premise-checkout-lookup accesspress-checkout-form-row" style="display:none"><?php printf( __( 'Your user name and/or email address is already in use. If you already have an account with us please %s.', 'premise' ), $sign_in_url ); ?></span>
		<?php endif; ?>

		<?php if ( $args['show_email_address'] ) : ?>
			<?php printf( $args['before_item'], 'accesspress-checkout-email' ); ?>
				<label for="accesspress-checkout-email" class="checkout-text-label"><?php echo __( 'Email Address', 'premise' ). $args['label_separator']; ?></label>
				<input type="text" name="accesspress-checkout[email]" id="accesspress-checkout-email" class="input-text" autocomplete="off" value="<?php echo esc_attr( $args['email'] ); ?>" <?php echo $disabled_user; ?>/>
			<?php echo $args['after_item']; ?>
			<?php if ( accesspress_get_option( 'email_as_username' ) && is_user_logged_in() ) : ?>
				<input type="hidden" id="accesspress-checkout-email-original" value="<?php echo esc_attr( $args['email'] ); ?>" />
				<span class="premise-profile-email-change accesspress-checkout-form-row premise-message" style="display:none"><?php _e( 'Changing your email address will log you out and you have to log in to continue to access your profile.', 'premise' ); ?></span>
<script type="text/javascript">
//<!--
jQuery(document).ready(function(){
	jQuery('#accesspress-checkout-email').keyup(function(){
		if (jQuery('#accesspress-checkout-email').val() != jQuery('#accesspress-checkout-email-original').val())
			jQuery('.premise-profile-email-change').show();
		else
			jQuery('.premise-profile-email-change').hide();
	});
});
//-->
</script>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $args['show_username'] ) : ?>
			<?php printf( $args['before_item'], 'accesspress-checkout-username' ); ?>
				<label for="accesspress-checkout-username" class="checkout-text-label"><?php echo __( 'Username', 'premise' ). $args['label_separator']; ?></label>
				<input type="text" name="accesspress-checkout[username]" id="accesspress-checkout-username" class="input-text" value="<?php echo esc_attr( $args['username'] ); ?>" <?php echo $disabled_username; ?>/>
			<?php echo $args['after_item']; ?>
		<?php endif; ?>
		
		<?php if ( ! $disabled_user ) : ?>
			<?php printf( $args['before_item'], 'accesspress-checkout-password' ); ?>
				<label for="accesspress-checkout-password" class="checkout-text-label"><?php echo __( 'Password', 'premise' ). $args['label_separator']; ?></label>
				<input type="password" name="accesspress-checkout[password]" id="accesspress-checkout-password" class="input-text" value="" />
			<?php echo $args['after_item']; ?>

			<?php printf( $args['before_item'], 'accesspress-checkout-password-repeat' ); ?>
				<label for="accesspress-checkout-password-repeat" class="checkout-text-label"><?php echo __( 'Re-enter Password', 'premise' ). $args['label_separator']; ?></label>
				<input type="password" name="accesspress-checkout[password-repeat]" id="accesspress-checkout-password-repeat" class="input-text" value="" />
			<?php echo $args['after_item']; ?>
		<?php endif; 

	if ( ! is_user_logged_in() ) {
		
		wp_nonce_field( 'checkout-lookup-' . $args['product_id'], 'premise-checkout-lookup' );
		?>
<script type="text/javascript">
//<!--
var	premise_email_id = '<?php echo esc_js( $args['script_email_id'] ); ?>',
	premise_username_id = '<?php echo esc_js( $args['script_username_id'] ); ?>',
	premise_submit_control = '<?php echo esc_js( $args['script_submit_control'] ); ?>';

jQuery(document).ready(function(){

<?php	if ( empty( $args['username'] ) || empty( $args['email'] ) ) { ?>						
	// disable the checkout button
	jQuery(premise_submit_control).attr('disabled','disabled');
<?php	} ?>

	jQuery(premise_username_id + ', ' + premise_email_id).blur(function(){
		ajaxurl='<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
		username = <?php echo accesspress_get_option( 'email_as_username' ) ? 1 : 'jQuery(premise_username_id).val()'; ?>;
		email = jQuery(premise_email_id).val();
		if (!username || !email)
			return;

		jQuery.post(
			ajaxurl,
			{
				action: 'premise_checkout_lookup',
				username: username,
				email: email,
				product: jQuery('#accesspress-checkout-product-id').val(),
				auth: jQuery('#premise-checkout-lookup').val()
			},
			function(data, status) {
				if (data == '1') {
					jQuery('.premise-checkout-lookup').show();
					jQuery(premise_submit_control).attr('disabled','disabled');
				} else {
					jQuery('.premise-checkout-lookup').hide();
					jQuery(premise_submit_control).removeAttr('disabled');
				}
			},
			'text'
		);
	});
	// trigger an initial check after the browser has autofilled any fields
	setTimeout(function(){ jQuery(premise_email_id).blur() }, 250);
});
//-->
</script>
		<?php
	}

	do_action( 'premise_checkout_account_after', $args );
	echo $args['wrap_after'];
	do_action( 'premise_checkout_account_after_wrap', $args );

}

add_action( 'premise_checkout_form', 'accesspress_checkout_form_choose_payment', 11 );

function accesspress_checkout_form_choose_payment( $args = array() ) {

	global $memberaccess_payment_gateways;

	reset( $memberaccess_payment_gateways );
	$default_method = count( $memberaccess_payment_gateways ) > 1 ? '' : key( $memberaccess_payment_gateways );

	$args = wp_parse_args( $args, array(
		'payment_box_heading' => $default_method ? __( '2. Payment Method', 'premise' ) : __( '2. Choose Payment Method', 'premise' ),
		'payment-method' => $default_method,
	) );

?>
	<div class="accesspress-checkout-form-payment-method">

		<?php if ( $args['payment_box_heading'] ) : ?>
			<div class="accesspress-checkout-heading"><?php echo esc_html( $args['payment_box_heading'] ); ?></div>
		<?php endif; ?>

		<?php
		foreach( $memberaccess_payment_gateways as $key => $gateway ) {

			// if there are multiple gateways, only show gateways that support members canceling the subscription
			if ( ! $default_method ) {

				if ( ! isset( $duration ) )
					$duration = $gateway->get_subscription_duration( $args['product_id'] );

				if ( $duration && ! $gateway->member_can_cancel() )
					continue;

			}
			do_action( 'premise_checkout_payment_method_before_row', $key, $args['payment-method'] );
		?>
		<div class="accesspress-checkout-form-row accesspress-checkout-payment-method-wrap accesspress-checkout-payment-method-<?php echo esc_attr( $key ); ?>-wrap">

			<?php do_action( 'premise_checkout_payment_method_before', $key, $args['payment-method'] ); ?>

			<input type="radio" name="accesspress-checkout[payment-method]" id="accesspress-checkout-payment-method-<?php echo esc_attr( $key ); ?>" class="input-text checkout-radio checkout-payment-method-radio" value="<?php echo $key; ?>" <?php checked( $key, $args['payment-method'] ); ?> />
			<label for="accesspress-checkout-payment-method-<?php echo $key; ?>"><?php echo $gateway->checkout_label; ?></label>

			<?php do_action( 'premise_checkout_payment_method_after', $key, $args['payment-method'] ); ?>

		</div>
		<?php
			do_action( 'premise_checkout_payment_method_after_row', $key, $args['payment-method'] );

			if ( $key == 'cc' ) { 
		?>
<script type="text/javascript">
//<!--
jQuery(document).ready(function(){
	jQuery('.checkout-payment-method-radio').change(function(){
		if (jQuery('#accesspress-checkout-payment-method-cc').attr('checked'))
			jQuery('.accesspress-checkout-form-cc').slideDown(1000);
		else
			jQuery('.accesspress-checkout-form-cc').slideUp(1000);
	});
	setTimeout(function(){ jQuery('.checkout-payment-method-radio').change(); }, 250);
});
//-->
</script>
		<?php
			}
		} 
		?>

	</div>
<?php	
	
}

if ( is_active_payment_method( 'authorize.net' ) )
	add_action( 'premise_checkout_form', 'accesspress_checkout_form_payment_cc', 12 );

function accesspress_checkout_form_payment_cc( $args = array() ) {
	
	$args = wp_parse_args( $args, array(
		'cc_box_heading' => __( '3. Enter Credit Card Information', 'premise' ),
		'card-name' => '',
		'card-month' => '',
		'card-year' => '',
		'card-country' => '',
		'card-postal' => '',
	) );

	do_action( 'premise_checkout_cc_before_wrap' );
	?>
	<div class="accesspress-checkout-form-cc">

		<?php do_action( 'premise_checkout_cc_before' ); ?>

		<?php if ( $args['cc_box_heading'] ) : ?>
			<div class="accesspress-checkout-heading"><?php echo esc_html( $args['cc_box_heading'] ); ?></div>
		<?php endif; ?>

		<div class="accesspress-checkout-form-row accesspress-checkout-card-name-wrap">
			<label for="accesspress-checkout-card-name" class="checkout-text-label"><?php _e( 'Name on Card', 'premise' ); ?>:</label>
			<input type="text" name="accesspress-checkout[card-name]" id="accesspress-checkout-card-name" class="input-text" autocomplete="off" value="<?php echo esc_attr( $args['card-name'] ); ?>" />
		</div>

		<div class="accesspress-checkout-form-row accesspress-checkout-card-number-wrap">
			<label for="accesspress-checkout-card-number" class="checkout-text-label"><?php _e( 'Card Number', 'premise' ); ?>:</label>
			<input type="text" name="accesspress-checkout[card-number]" id="accesspress-checkout-card-number" class="input-text" autocomplete="off" value="" />
		</div>

		<div class="accesspress-checkout-form-row accesspress-checkout-card-month-wrap">
			<label for="accesspress-checkout-card-month" class="checkout-text-label"><?php _e( 'Expiration Date', 'premise' ); ?></label>

			<select name="accesspress-checkout[card-month]" id="accesspress-checkout-card-month">
				<?php
				foreach ( range( 1, 12 ) as $month ) {
					printf( '<option value="%d" %s>%d</option>', $month, selected( $month, $args['card-month'], false ), $month );
				}
				?>
			</select>

			<select name="accesspress-checkout[card-year]" id="accesspress-checkout-card-year">
				<?php
				$thisyear = (int) date('Y');
				foreach ( range( $thisyear, $thisyear + 10 ) as $year ) {
					printf( '<option value="%d" %s>%d</option>', $year, selected( $year, $args['card-year'], false ), $year );
				}
				?>
			</select>
		</div>

		<div class="accesspress-checkout-form-row accesspress-checkout-card-security-wrap">
			<label for="accesspress-checkout-card-security" class="checkout-text-label"><?php _e( 'Security Code', 'premise' ); ?>:</label>
			<input type="password" name="accesspress-checkout[card-security]" id="accesspress-checkout-card-security" class="input-text input-text-short" size="3" autocomplete="off" value="" />
			<p><span class="description"><?php _e( 'The security code should be located on the back of your card.', 'premise' ) ?></span></p>
		</div>

		<div class="accesspress-checkout-form-row accesspress-checkout-card-country-wrap">
			<label for="accesspress-checkout-card-country" class="checkout-text-label"><?php _e( 'Country', 'premise' ); ?>:</label>
			<select name="accesspress-checkout[card-country]" id="accesspress-checkout-card-country">
				<?php
				foreach ( (array) accesspress_get_countries() as $code => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $code ), selected( $code, $args['card-country'], false ), esc_html( $label ) );
				}
				?>
			</select>
		</div>

		<div class="accesspress-checkout-form-row accesspress-checkout-card-postal-wrap">
			<label for="accesspress-checkout-card-postal" class="checkout-text-label"><?php _e( 'ZIP/Postal Code', 'premise' ); ?>:</label>
			<input type="text" name="accesspress-checkout[card-postal]" id="accesspress-checkout-card-postal" class="input-text input-text-short" size="12" autocomplete="off" value="<?php echo esc_attr( $args['card-postal'] ); ?>" />
		</div>
		
		<?php do_action( 'premise_checkout_cc_after' ); ?>

	</div>
<?php 
	do_action( 'premise_checkout_cc_after_wrap' );
	
}

function memberaccess_has_coupon( $coupon_id ) {

	$cookies = MemberAccess_Coupons::get_member_coupons();
	if ( empty( $cookies ) )
		return false;

	if ( ! (int) $coupon_id ) {

		$coupons = new WP_query( array( 'post_type' => 'acp-coupons', 'name' => sanitize_title_with_dashes( $coupon_id ) ) );
		if ( ! $coupons->have_posts() )
			return false;

		$coupons->the_post();
		$coupon_id = get_the_ID();

		wp_reset_query();

	}

	foreach( $cookies as $name => $cookie ) {

		if ( $cookie[1] != $coupon_id )
			continue;

		$auth = get_post_meta( $cookie[1], '_acp_coupon_auth_key' ) ? MemberAccess_Coupons::get_authorization_key( $cookie[1], ! empty( $cookie[2] ) ? $cookie[2] : null ) : md5( $name );

		if ( $auth == $_COOKIE[$name] )
			return true;

	}

	return false;
}
function memberaccess_member_has_product( $product_id, $member_id = null ) {

	if ( ! (int)$product_id )
		return false;

	if ( ! $member_id ) {
	
		if ( ! is_user_logged_in() )
			return false;

		$member_id = get_current_user_id();

	}

	$products = memberaccess_get_member_products( $member_id, 0, true );

	return in_array( $product_id, $products );

}