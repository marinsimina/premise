<?php
/**
 * MemberAccess Coupons registration and management.
 *
 * @package MemberAccess
 */


/**
 * Handles the registration and management of coupons.
 *
 * This class handles the registration of the 'acp-coupons' Custom Post Type, which stores
 * all coupons created with Premise. It also allows you to manage, edit, and (if need be) delete
 * coupons.
 *
 * @since 2.2
 *
 */
class MemberAccess_Coupons {


	/** Constructor */
	function __construct() {

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'wp', array( $this, 'process_coupon' ) );
		add_filter( 'premise_product_trial_price', array( $this, 'product_price_filter' ), 10, 4 );
		add_filter( 'premise_product_price', array( $this, 'product_price_filter' ), 10, 4 );
		
		add_filter( 'manage_edit-acp-coupons_columns', array( $this, 'columns_filter' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'columns_data' ) );
		add_action( 'save_post', array( $this, 'metabox_save' ), 1, 2 );
		add_filter( 'acp-coupons_rewrite_rules', array( $this, 'acp_coupons_rewrite_rules' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_action( 'load-post.php', array( $this, 'scripts' ) );
		add_action( 'load-post-new.php', array( $this, 'scripts' ) );
		// enqueue CSS
		add_action( 'load-edit.php', array( $this, 'enqueue_admin_css' ) );
		add_action( 'load-post.php', array( $this, 'enqueue_admin_css' ) );
		add_action( 'load-post-new.php', array( $this, 'enqueue_admin_css' ) );

	}

	/**
	 * Register the Products post type
	 */
	function register_post_type() {

			$labels = array(
				'name'               => __( 'Coupons', 'premise' ),
				'singular_name'      => __( 'Coupon', 'premise' ),
				'add_new'            => __( 'Create New Coupon', 'premise' ),
				'add_new_item'       => __( 'Create New Coupon', 'premise' ),
				'edit'               => __( 'Edit Coupon', 'premise' ),
				'edit_item'          => __( 'Edit Coupon', 'premise' ),
				'new_item'           => __( 'New Coupon', 'premise' ),
				'view'               => __( 'View Coupon', 'premise' ),
				'view_item'          => __( 'View Coupon', 'premise' ),
				'search_items'       => __( 'Search Coupons', 'premise' ),
				'not_found'          => __( 'No Coupons found', 'premise' ),
				'not_found_in_trash' => __( 'No Coupons found in Trash', 'premise' )
			);

		if ( current_user_can( 'manage_options' ) ) {

			register_post_type( 'acp-coupons',
				array(
					'labels' => $labels,
					'show_in_menu'         => 'premise-member',
					'supports'             => array( 'title' ),
					'register_meta_box_cb' => array( $this, 'metaboxes' ),
					'public'               => true,
					'show_ui'              => true,
					'rewrite'              => array( 'slug' => 'coupon' ),
					'query_var'            => true
				)
			);

		} else {

			register_post_type( 'acp-coupons',
				array(
					'labels' => $labels,
					'public'               => true,
					'show_ui'              => false,
					'rewrite'              => array( 'slug' => 'coupon' ),
					'query_var'            => true
				)
			);

		}
	}
	/**
	 * Register the metaboxes
	 */
	function metaboxes() {

		add_meta_box( 'memberaccess-coupon-details-metabox', __( 'Coupon Details', 'premise' ), array( $this, 'details_metabox' ), 'acp-coupons', 'normal' );
		add_meta_box( 'memberaccess-coupon-status-metabox', __( 'Status', 'premise' ), 'premise_custom_post_status_metabox', 'acp-coupons', 'side', 'high' );
		remove_meta_box( 'submitdiv', null, 'side' );

	}

	function details_metabox( $post ) {

		global $pagenow;

		$auto_check = $pagenow == 'post-new.php' ? 1 : 0;
		$sdate = accesspress_get_custom_field( '_acp_coupon_start_date' );
		$edate = accesspress_get_custom_field( '_acp_coupon_end_date' );
		$start_date = ! empty( $sdate ) ? date( 'n/j/Y G:i', $sdate ) : '';
		$end_date = ! empty( $edate ) ? date( 'n/j/Y G:i', $edate ) : '';

	?>

		<input type="hidden" name="memberaccess-coupons-nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

		<p>
			<strong><?php _e( 'Discount Type', 'premise' ); ?>:</strong>
			<br />
			<br />
			<label><input type="radio" name="memberaccess_coupon_meta[_acp_coupon_type]" value="percent"<?php checked( 'percent', accesspress_get_custom_field( '_acp_coupon_type' ) ); ?> /> <?php _e( 'Percentage', 'premise' ); ?></label>
			<input type="text" name="memberaccess_coupon_meta[_acp_coupon_percent]" value="<?php echo esc_attr( accesspress_get_custom_field( '_acp_coupon_percent' ) ); ?>" size="3" /> %
			<br />
			<label><input type="radio" name="memberaccess_coupon_meta[_acp_coupon_type]" value="flat"<?php checked( 'flat', accesspress_get_custom_field( '_acp_coupon_type' ) ); ?> /> <?php _e( 'Flat', 'premise' ); ?></label>
			<input type="text" name="memberaccess_coupon_meta[_acp_coupon_flat]" value="<?php echo esc_attr( accesspress_get_custom_field( '_acp_coupon_flat' ) ); ?>" size="3" /> $
		</p>

		<p>		
			<strong><?php _e( 'Eligibility', 'premise' ); ?>:</strong>
			<br />
			<input type="checkbox" name="memberaccess_coupon_meta[_acp_coupon_new_customer]" id="memberaccess_coupon_meta[_acp_coupon_new_customer]" value="1" <?php checked( '1', accesspress_get_custom_field( '_acp_coupon_new_customer' ) || $auto_check ); ?> />
			<label for="memberaccess_coupon_meta[_acp_coupon_new_customer]"><?php _e( 'New Customers', 'premise' ); ?></label>
			<br />
			<input type="checkbox" name="memberaccess_coupon_meta[_acp_coupon_member]" id="memberaccess_coupon_meta[_acp_coupon_member]" value="1" <?php checked( '1', accesspress_get_custom_field( '_acp_coupon_member' ) || $auto_check ); ?> />
			<label for="memberaccess_coupon_meta[_acp_coupon_member]"><?php _e( 'Existing Members', 'premise' ); ?></label>
		</p>

	<?php
		if ( accesspress_get_option( 'authorize_net_recurring' ) ) {
	?>

		<p>		
			<strong><?php _e( 'Subscriptions', 'premise' ); ?>:</strong>
			<br />
			<input type="checkbox" name="memberaccess_coupon_meta[_acp_coupon_trial]" id="memberaccess_coupon_meta[_acp_coupon_trial]" value="1" <?php checked( '1', accesspress_get_custom_field( '_acp_coupon_trial' ) || $auto_check ); ?> />
			<label for="memberaccess_coupon_meta[_acp_coupon_trial]"><?php _e( 'Apply To Trial Payment', 'premise' ); ?></label>
			<br />
			<input type="checkbox" name="memberaccess_coupon_meta[_acp_coupon_subscription]" id="memberaccess_coupon_meta[_acp_coupon_subscription]" value="1" <?php checked( '1', accesspress_get_custom_field( '_acp_coupon_subscription' ) || $auto_check ); ?> />
			<label for="memberaccess_coupon_meta[_acp_coupon_subscription]"><?php _e( 'Apply To Subscription Payment', 'premise' ); ?></label>
		</p>
		<?php
		}
		?>
		<p>
			<strong><?php _e( 'Date Range', 'premise' ); ?>:</strong>
			<br />
			<div class="premise-date-range">
				<label for="memberaccess_coupon_meta[start]"><?php _e( 'Start Date/Time:', 'premise' ); ?></label>
				<input type="text" name="memberaccess_coupon_meta[start]" id="memberaccess_coupon_meta[start]" value="<?php echo esc_attr( $start_date ); ?>" />
				<br />

				<label for="memberaccess_coupon_meta[end]"><?php _e( 'End Date/Time:', 'premise' ); ?></label>
				<input type="text" name="memberaccess_coupon_meta[end]" id="memberaccess_coupon_meta[end]" value="<?php echo esc_attr( $end_date ); ?>" />
		</p>
<script type="text/javascript">
//<!--
jQuery(document).ready(function(){
	jQuery('#memberaccess_coupon_meta\\[start\\], #memberaccess_coupon_meta\\[end\\]').datetimepicker();
});
//-->
</script>
<!--
		<p>
			<strong><?php _e( 'Limit Usage', 'premise' ); ?>:</strong>
			<br />
			<label for="memberaccess_coupon_meta[_acp_coupon_max_uses]"><?php _e( 'Maximum Uses:', 'premise' ); ?></label>
			<input type="text" name="memberaccess_coupon_meta[_acp_coupon_max_uses]" value="<?php echo esc_attr( accesspress_get_custom_field( '_acp_coupon_max_uses' ) ); ?>" size="3" /><br />
			<span class="description"><?php _e( 'Leave blank for indefinite', 'premise' ); ?></span>
		</p>
-->
		<p>		
			<strong><?php _e( 'Limit Access', 'premise' ); ?>:</strong>
			<br />
			<input type="checkbox" name="memberaccess_coupon_meta[_acp_coupon_auth_key]" id="memberaccess_coupon_meta[_acp_coupon_auth_key]" value="1" <?php checked( '1', accesspress_get_custom_field( '_acp_coupon_auth_key' ) ); ?> />
			<label for="memberaccess_coupon_meta[_acp_coupon_auth_key]"><?php _e( 'Require Authorization Key', 'premise' ); ?></label>
			<input type="hidden" name="memberaccess_coupon_meta[_acp_coupon_auth_key_base]" value="<?php echo esc_attr( accesspress_get_custom_field( '_acp_coupon_auth_key_base' ) ); ?>" />
			<br />
			<label for="memberaccess_coupon_meta[_acp_coupon_lifetime]"><?php _e( 'Cookie Lifetime:', 'premise' ); ?></label>
			<input type="text" name="memberaccess_coupon_meta[_acp_coupon_lifetime]" value="<?php echo esc_attr( accesspress_get_custom_field( '_acp_coupon_lifetime' ) ); ?>" size="3" /> <?php _e( 'Days', 'premise' ); ?><br />
			<span class="description"><?php _e( 'Leave blank to expire at end of current visit.', 'premise' ); ?></span>
		</p>

		<p>
			<strong><?php _e( 'Limit Products', 'premise' ); ?>:</strong>
			<?php 
			$permalink = get_permalink( $post->ID );
			$coupon_url_format = untrailingslashit( $permalink ) . '/product/%d' . ( $permalink != untrailingslashit( $permalink ) ? '/' : '' );
			$protected = accesspress_get_custom_field( '_acp_coupon_auth_key' ) && accesspress_get_custom_field( '_acp_coupon_auth_key_base' );
			if ( $protected ) {

				$permalink .= '?auth=%s';
				$coupon_url_format .= '?auth=%s';

			}

			$products = accesspress_get_custom_field( '_acp_coupon_products' ) ? explode( ',', accesspress_get_custom_field( '_acp_coupon_products' ) ) : array();
			$product_query = new WP_Query( array( 'post_type' => 'acp-products', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
			if ( $product_query->have_posts() ) {

				echo '<br />';

				printf( '<br /><label><input type="radio" name="memberaccess_coupon_meta[_acp_coupon_products_all]" value="1"%s /> %s</label> - %s', checked( 1, empty( $products ), false ), __( 'All Products', 'premise' ), make_clickable( sprintf( $permalink, $this->get_authorization_key( $post->ID ) ) ) );

				?>
				<br /><br />
				<label for="memberaccess_coupon_meta[_acp_coupon_redirect]"><?php _e( 'Redirect:', 'premise' ); ?></label>
				<input type="text" name="memberaccess_coupon_meta[_acp_coupon_redirect]" value="<?php echo esc_attr( accesspress_get_custom_field( '_acp_coupon_redirect' ) ); ?>" /><br />
				<span class="description"><?php _e( 'Enter the URL you want the coupon link to redirect to.', 'premise' ); ?></span><br />
				<?php

				printf( '<br /><label><input type="radio" name="memberaccess_coupon_meta[_acp_coupon_products_all]" value="0"%s /> %s</label>', checked( 1, ! empty( $products ), false ), __( 'Select From The Products Below:', 'premise' ) );

				echo '<br />';

				while( $product_query->have_posts() ) {

					$product_query->the_post();
					$title = get_the_title();
					if ( empty( $title ) )
						continue;

					$coupon_url = sprintf( $coupon_url_format, get_the_ID(), $this->get_authorization_key( $post->ID, get_the_ID() ) );
					if ( in_array( get_the_ID(), $products ) )
						printf( '<br /><label><input type="checkbox" name="memberaccess_coupon_meta[_acp_coupon_products][%d]" value="1"%s /> %s</label> - %s', get_the_ID(), checked( 1, 1, false ), $title, make_clickable( $coupon_url ) );
					else
						printf( '<br /><label><input type="checkbox" name="memberaccess_coupon_meta[_acp_coupon_products][%d]" value="1" /> %s</label>', get_the_ID(), $title );

				}

				wp_reset_query();
			?>
			<?php
			} else {

				echo '<br />';
				printf( 'You don\'t have any products created yet. <a href="%s">Create a product</a> to add a coupon to.', admin_url( 'post-new.php?post_type=acp-products' ) );

			}
			?>
		</p>
	<?php

	}
	/**
	 * Save the form data from the metaboxes
	 */
	function metabox_save( $post_id, $post ) {

		/**	Verify the nonce */
		if ( ! isset( $_POST['memberaccess-coupons-nonce'] ) || ! wp_verify_nonce( $_POST['memberaccess-coupons-nonce'], plugin_basename( __FILE__ ) ) )
			return $post->ID;

		/**	Don't try to save the data under autosave, ajax, or future post */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		if ( defined( 'DOING_CRON' ) && DOING_CRON )
			return;

		/**	Check if user is allowed to edit this */
		if ( ! current_user_can( 'edit_post', $post->ID ) )
			return $post->ID;

		/** Don't try to store data during revision save */
		if ( 'acp-coupons' != $post->post_type )
			return;

		/** Merge defaults with user submission */
		$defaults = apply_filters( 'memberaccess_default_coupon_meta', array(
			'_acp_coupon_type'				=> 'percent',
			'_acp_coupon_percent'			=> '',
			'_acp_coupon_flat'			=> '',
			'_acp_coupon_start_date'		=> '',
			'_acp_coupon_end_date'			=> '',
			'_acp_coupon_max_uses'			=> '',
			'_acp_coupon_products'			=> array(),
			'_acp_coupon_products_all'		=> false,
			'_acp_coupon_new_customer'		=> '',
			'_acp_coupon_member'			=> '',
			'_acp_coupon_trial'			=> '',
			'_acp_coupon_subscription'		=> '',
			'_acp_coupon_auth_key'			=> '',
			'_acp_coupon_auth_key_base'		=> '',
			'_acp_coupon_lifetime'			=> '',
			'_acp_coupon_redirect'			=> '',
		) );

		$values = wp_parse_args( $_POST['memberaccess_coupon_meta'], $defaults );

		if ( $values['_acp_coupon_percent'] ) {

			$values['_acp_coupon_percent'] = (int) $values['_acp_coupon_percent'];
			$values['_acp_coupon_percent'] = $values['_acp_coupon_percent'] > 0 && $values['_acp_coupon_percent'] <= 100 ? $values['_acp_coupon_percent'] : 0;

		}

		if ( $values['_acp_coupon_flat'] ) {

			$values['_acp_coupon_flat'] = (float) $values['_acp_coupon_flat'];
			$values['_acp_coupon_flat'] = $values['_acp_coupon_flat'] > 0 ? sprintf( '%.2f', $values['_acp_coupon_flat'] ) : 0;

		}

		if ( $values['_acp_coupon_auth_key'] && ! $values['_acp_coupon_auth_key_base'] )
			$values['_acp_coupon_auth_key_base'] = substr( md5( serialize( $values ) . time() ), 0, 12 );
 
		if ( is_array( $values['_acp_coupon_products'] ) ) {

			if ( $values['_acp_coupon_products_all'] ) {

				$values['_acp_coupon_products'] = false;

			} else {

				$product_list = array();
				foreach( array_keys( $values['_acp_coupon_products'] ) as $product_id ) {
	
					if ( (int) $product_id ) {
		
						$product = get_post( $product_id );
						if ( ! empty( $product->post_type ) && 'acp-products' == $product->post_type )
							$product_list[] = $product_id;
	
					}
		
				}

				sort( $product_list );
				$values['_acp_coupon_products'] = implode( ',', $product_list );

			}
		}

		if ( $values['start'] )
			$values['_acp_coupon_start_date'] = strtotime( $values['start'] ) > 0 ? strtotime( $values['start'] ) : 0;
		if ( $values['end'] )
			$values['_acp_coupon_end_date'] = strtotime( $values['end'] ) > 0 ? strtotime( $values['end'] ) : 0;

		unset( $values['start'], $values['end'], $values['_acp_coupon_products_all'] );
		/** Sanitize */
		$values = $this->sanitize( $values );

		/** Loop through values, to potentially store or delete as custom field */
		foreach ( (array) $values as $key => $value ) {
			/** Save, or delete if the value is empty */
			if ( $value )
				update_post_meta( $post->ID, $key, $value );
			else
				delete_post_meta( $post->ID, $key );
		}

	}

	/**
	 * Filter the columns in the "Orders" screen, define our own.
	 */
	function columns_filter ( $columns ) {

		unset( $columns['date'] );
		$new_columns = array(
			'discount_type'		=> __( 'Discount', 'premise' ),
			'date_range'		=> __( 'Date Range', 'premise' ),
			'products'			=> __( 'Products', 'premise' ),
		);

		return array_merge( $columns, $new_columns );

	}

	/**
	 * Filter the data that shows up in the columns in the "Orders" screen, define our own.
	 */
	function columns_data( $column ) {

		global $post;

		if ( 'acp-coupons' != $post->post_type )
			return;

		switch( $column ) {
			case 'discount_type':
				$percentage = 'percent' == accesspress_get_custom_field( '_acp_coupon_type' );
				$format = $percentage ? __( '%d%%', 'premise' ) : __( '$ %.2f', 'premise' );
				$discount = accesspress_get_custom_field( $percentage ? '_acp_coupon_percent' : '_acp_coupon_flat' );

				printf( $format, $discount );
				break;
			case "date_range":
				$start_date = accesspress_get_custom_field( '_acp_coupon_start_date' );
				$end_date = accesspress_get_custom_field( '_acp_coupon_end_date' );
				if ( ! $start_date && ! $end_date ) {

					echo '--';
					break;
					
				}

				$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
				if ( $start_date )
					printf( __( 'From %s<br />', 'premise' ), date( $date_format, $start_date ) );

				if ( $end_date )
					printf( __( 'To %s', 'premise' ), date( $date_format, $end_date ) );

				break;
			case 'products':
				$products = accesspress_get_custom_field( '_acp_coupon_products' );
				if ( empty( $products ) ) {

					_e( 'All Products', 'premise' );
					break;

				}

				$query_args = array( 'post_type' => 'acp-products', 'post_status' => 'publish', 'posts_per_page' => 9999, 'nopaging' => true, 'post__in' => explode( ',', $products ) );

				$product_query = new WP_Query( $query_args );
				if ( $product_query->have_posts() ) {
	
					while( $product_query->have_posts() ) {
	
						$product_query->the_post();
						$title = get_the_title();
						if ( empty( $title ) )
							continue;
	
						printf( '<a href="%s">%s</a><br />', add_query_arg( array( 'post' => get_the_ID(), 'action' => 'edit' ), admin_url( 'post.php' ) ), $title );
	
					}
					wp_reset_query();
				}
				break;
		}

	}
	/**
	 * Use this function to sanitize an array of values before storing.
	 *
	 * @todo a bit more thorough sanitization
	 */
	function sanitize( $values = array() ) {

		return (array) $values;

	}
	/*
	override the defualt rewrite rules for the coupon post type
	*/
	function acp_coupons_rewrite_rules( $rules ) {
		global $wp_rewrite;

		$base_struct = ltrim( str_replace( '%acp-coupons%', '', $wp_rewrite->get_extra_permastruct( 'acp-coupons' ) ), '/' );
		
		return array( 
			$base_struct . '([^/]+)(/[0-9]+)?/?$' =>  'index.php?acp-coupons=$matches[1]&page=$matches[2]',
			$base_struct . '([^/]+)/product(/[0-9]+)?/?$' =>  'index.php?acp-coupons=$matches[1]&attachment=$matches[2]' 
		);
	}
	/*
	handle coupon permalink request
	*/
	function process_coupon() {

		if( is_admin() || ! is_singular( 'acp-coupons' ) )
			return;
	
		// check for authorization
		$object = get_queried_object();
		$product_id = str_replace( '/', '', get_query_var( 'attachment' ) );

		if ( ! accesspress_get_custom_field( '_acp_coupon_auth_key' ) || ! accesspress_get_custom_field( '_acp_coupon_auth_key_base' ) || ( isset( $_REQUEST['auth'] ) && $this->get_authorization_key( $object->ID, $product_id ) == $_REQUEST['auth'] ) ) {

			$coupon_products = accesspress_get_custom_field( '_acp_coupon_products' );
			$cookie_name = 'premise-coupon-' . $object->ID . ( $product_id && ! empty( $coupon_products ) ? '-' . $product_id : '' );
			$value = isset( $_REQUEST['auth'] ) ? $_REQUEST['auth'] : md5( $cookie_name );
			$now = strtotime( current_time( 'mysql' ) );
			$expiry = accesspress_get_custom_field( '_acp_coupon_lifetime' ) > 0 ? $now + accesspress_get_custom_field( '_acp_coupon_lifetime' ) * 86400 : 0;

			// clear conflicting coupons
			foreach( self::get_member_coupons() as $key => $coupon ) {

				if ( $key != $cookie_name && ( empty( $coupon_products ) || ! isset( $coupon[2] ) || $coupon[2] == $product_id ) ) {

					do_action( 'premise_member_coupon_clear', $object->ID, $key );
					setcookie( $key, '', $now, COOKIEPATH, COOKIE_DOMAIN );

				}
			}

			do_action( 'premise_member_coupon_before', $object->ID, $cookie_name );

			// store the coupon in a cookie
			setcookie( $cookie_name, $value, $expiry, COOKIEPATH, COOKIE_DOMAIN );

			do_action( 'premise_member_coupon_after', $object->ID, $cookie_name );

		}

		if ( isset( $_REQUEST['redir'] ) ) {

			$redir = urldecode( $_REQUEST['redir'] );
			do_action( 'premise_member_coupon_redirect', $object->ID, $redir );
			wp_redirect( $redir );
			exit;

		}
		$checkout_page = accesspress_get_option( 'checkout_page' );
		if ( ! $product_id || ! $checkout_page ) {

			$redir = accesspress_get_custom_field( '_acp_coupon_redirect' );
			if ( $redir ) {

				do_action( 'premise_member_coupon_redirect', $object->ID, $redir );
				wp_redirect( $redir );

			} else {

				$redir = home_url( '/' );
				do_action( 'premise_member_coupon_redirect', $object->ID, $redir );
				wp_safe_redirect( $redir );

			}
			exit;

		}

		$redir = add_query_arg( array( 'product_id' => $product_id ), get_permalink( $checkout_page ) );
		do_action( 'premise_member_coupon_redirect', $object->ID, $redir );
		wp_safe_redirect( $redir );
		exit;
	}

	function get_authorization_key( $coupon_id, $product_id = null ) {

		$auth_base = get_post_meta( $coupon_id, '_acp_coupon_auth_key_base', true );
		if ( $product_id )
			return md5( $auth_base . '-' . $coupon_id . '-' . $product_id );

		return md5( $auth_base . '-' . $coupon_id );

	}

	static function get_product_coupon( $product_id ) {

		$cookies = self::get_member_coupons();

		// look for a coupon specific to this product
		foreach( $cookies as $name => $match ) {

			if ( ! isset( $match[2] ) )
				continue;

			if ( $match[2] != $product_id ) {

				unset( $cookies[$name] );
				continue;

			}

			$coupon_products = get_post_meta( $match[1], '_acp_coupon_products', true );
			if ( ! empty( $coupon_products ) ) {

				$coupon_products = explode( ',', $coupon_products );
				if ( ! empty( $coupon_products ) && ! in_array( $match[2], $coupon_products ) )
					continue;

			}

			$auth = get_post_meta( $match[1], '_acp_coupon_auth_key' ) ? self::get_authorization_key( $match[1], $match[2] ) : md5( $name );

			if ( $auth == $_COOKIE[$name] )
				return $match[1];

			unset( $cookies[$name] );

		}

		foreach( $cookies as $name => $match ) {

			$coupon_products = get_post_meta( $match[1], '_acp_coupon_products', true );
			if ( ! empty( $coupon_products ) ) {

				$coupon_products = explode( ',', $coupon_products );
				if ( ! empty( $coupon_products ) && ! in_array( $product_id, $coupon_products ) )
					continue;

			}

			$auth = get_post_meta( $match[1], '_acp_coupon_auth_key' ) ? self::get_authorization_key( $match[1] ) : md5( $name );
			if ( $auth == $_COOKIE[$name] )
				return $match[1];

		}

		return false;

	}

	static public function get_member_coupons() {

		$cookies = array();
		foreach( (array) $_COOKIE as $name => $contents ) {

			if ( preg_match( '|^premise-coupon-([0-9]+)-?([0-9]+)?$|', $name, $m ) )
				$cookies[$name] = $m;

		}

		return $cookies;
		
	} 

	public function product_price_filter( $price, $coupon_id, $subscription, $original_price ) {

		// don't process unless the price is greater than $0 & unaltered & there is a coupon
		if ( $price <= 0 || $price != $original_price || ! $coupon_id )
			return $price;

		// match coupon paramters to product parameters
		if ( $subscription && ! get_post_meta( $coupon_id, '_acp_coupon_subscription', true ) )
			return $price;

		if ( current_filter() == 'premise_product_trial_price' && ! get_post_meta( $coupon_id, '_acp_coupon_trial', true ) )
			return $price;

		if ( is_user_logged_in() ) {

			if ( ! get_post_meta( $coupon_id, '_acp_coupon_member', true ) )
				return $price;
			
		} elseif ( ! get_post_meta( $coupon_id, '_acp_coupon_new_customer', true ) ) {
			
			return $price;
			
		}

		$now = strtotime( current_time( 'mysql' ) );
		$start_date = get_post_meta( $coupon_id, '_acp_coupon_start_date', true );

		if ( ! empty( $start_date ) && $start_date > $now )
			return $price;

		$end_date = get_post_meta( $coupon_id, '_acp_coupon_end_date', true );
		if ( ! empty( $end_date ) && $end_date < $now )
			return $price;

		// matches all the criteria
		$percentage = 'percent' == get_post_meta( $coupon_id, '_acp_coupon_type', true );
		$discount = get_post_meta( $coupon_id, $percentage ? '_acp_coupon_percent' : '_acp_coupon_flat', true );

		$new_price = $percentage ? sprintf( '%.2f', $price * ( 100 - $discount ) / 100 ) : ( $price - $discount );

		return $new_price > 0 ? $new_price : 0;

	}
	/**
	 * custom messages for the coupon post type
	 *
	 * @since 2.2.0
	 *
	 * @returns array
	 */
	function post_updated_messages( $messages ) {
		$messages['acp-coupons'] = array(
			 1 => __( 'Coupon updated.', 'premise' ),
			 4 => __('Coupon updated.', 'premise' ),
			 6 => __( 'Coupon published.', 'premise' ),
			 7 => __( 'Coupon saved.', 'premise' ),
		);
		return $messages;
	}

	function scripts() {

		global $typenow;

		if ( 'acp-coupons' != $typenow )
			return;
		
		wp_enqueue_script( 'accesspress-editor', PREMISE_RESOURCES_URL . 'editor.js', array( 'jquery', ), PREMISE_VERSION, true );
		wp_enqueue_script( 'jquery-ui-timepicker-addon', PREMISE_RESOURCES_URL . 'jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.0.1' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( 'premise-date-picker', PREMISE_RESOURCES_URL . 'premise-date-picker.css', PREMISE_VERSION );

	}
	function enqueue_admin_css() {

		global $typenow;

		if( $typenow == 'acp-coupons' )
			wp_enqueue_style( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.css', array( 'thickbox' ), PREMISE_VERSION );

	}
}