<?php
/**
 * Optin gateway management class to implement registered opt in gateways.
 *
 * @since 2.5
 */

class Premise_Optin_Payments {

	function __construct() {

		add_action( 'premise_optin_metabox_after_placement', array( $this, 'optin_metabox' ) );
		add_action( 'premise_optin_signup_extra_fields', array( $this, 'optin_extra_fields' ) );
		add_filter( 'premise_optin_extra_fields_errors', array( $this, 'validate_optin_extra_fields' ), 10, 2 );

	}


	public function optin_metabox( $meta ) {

		global $memberaccess_registered_gateways;

		$meta = wp_parse_args( $meta,
			array(
				'member-product' => 0,
				'member-merge-gateway' => '',
			)
		);
		$merge_format = '%1$s : <input class="regular-text" type="text" name="premise[%2$s]" id="premise-main-%2$s" value="%3$s" /> %4$s';

		$product = get_post( $meta['member-product'] );
		$title = ( empty( $product->post_title ) || empty( $product->post_type ) || $product->post_type != 'acp-products' ) ? '' : $product->post_title;
?>
<div class="premise-option-box">
		<h4><label for="premise-optin-member-access"><?php _e('Member Access', 'premise' ); ?></label></h4>
		<p><?php _e( 'Give access to a product to those who opt in by entering the Product ID.', 'premise' ); ?></p>
		<p>
			<?php printf( $merge_format, __( 'Product ID', 'premise' ), 'member-product', esc_attr( $meta['member-product'] ), $title ); ?></li>
		</p><br />
<?php
		$optin_gateways = array();
		foreach( $memberaccess_registered_gateways as $key => $gateway ) {

			if ( $gateway->get_optin_name() )
				$optin_gateways[$key] = $gateway;

		}

		if ( empty( $optin_gateways ) ) {

			_e( 'No opt in gateways configured.', 'premise' );
			return;

		}

		// add the gateways to the dropdown sorted alphabetically
		ksort( $optin_gateways );

?>
		<p><label><?php _e( 'Opt In Gateway', 'premise' ); ?> :
			<select  name="premise[member-merge-gateway]" id="premise-main-member-merge-gateway">
				<option value=""><?php _e( '-- None --', 'premise' ); ?></option>
<?php
		foreach( $optin_gateways as $key => $gateway )
			printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $meta['member-merge-gateway'], false ), $gateway->get_optin_name() );

?>
			</select>
		</label></p>
<script type="text/javascript">
//<!--
jQuery(document).ready(function(){
	jQuery('#premise-main-member-merge-gateway').change(function(){
		var $selected = '.premise-' + jQuery(this).val() + '-gateway-meta-wrap';
		jQuery('.premise-optin-gateway-meta-wrap:not(' + $selected + ')').slideUp();
		jQuery($selected).slideDown();		
	}).change();
});
//-->
</script>
<?php
		foreach( $optin_gateways as $key => $gateway ) {

			printf( '<div class="premise-option-box premise-optin-gateway-meta-wrap premise-%s-gateway-meta-wrap">', sanitize_html_class( $key ) );
			$gateway->optin_metabox( $meta );
			echo '</div>';
		}
?>
</div>
<?php
	}

	public function optin_extra_fields() {

		global $premise_base, $post;
		$meta = $premise_base->get_premise_meta( $post->ID );

		if ( empty( $meta['member-product'] ) || empty( $meta['member-merge-gateway'] ) )
			return;

		$gateway = memberaccess_get_payment_gateway( $meta['member-merge-gateway'], true );
		if ( ! $gateway )
			return;

		$gateway->optin_extra_fields( $meta );

		printf( '<input type="hidden" name="premise-product-id" value="%d" />', $meta['member-product'] );
		printf( '<input type="hidden" name="premise-landing-id" value="%d" />', $post->ID );
		printf( '<input type="hidden" name="premise-product-key" value="%s" />', wp_create_nonce( 'premise-product-key-' . $meta['member-product'] . '-' . $post->ID ) );

	}

	function validate_optin_extra_fields( $errors, $args = array() ) {

		global $premise_base;

		if ( empty( $_POST['premise-product-id'] ) || empty( $_POST['premise-product-key'] ) || empty( $_POST['premise-landing-id'] ) )
			return $errors;

		if ( ! wp_verify_nonce( $_POST['premise-product-key'], 'premise-product-key-' . $_POST['premise-product-id'] . '-' . $_POST['premise-landing-id'] ) )
			return $errors;

		$meta = $premise_base->get_premise_meta( $_POST['premise-landing-id'] );

		if ( empty( $meta['member-product'] ) || empty( $meta['member-merge-gateway'] ) )
			return $errors;

		if ( empty( $meta['member-product'] ) || $meta['member-product'] != (int) $_POST['premise-product-id'] )
			return $errors;

		$gateway = memberaccess_get_payment_gateway( $meta['member-merge-gateway'], true );
		if ( ! $gateway )
			return $errors;

		return $gateway->validate_optin_extra_fields( $errors, $args, $meta );

	}
}

new Premise_Optin_Payments();