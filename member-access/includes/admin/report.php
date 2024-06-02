<?php
class Memberaccess_Report_Settings {

	var $settings_field = 'premise-reports';

	var $summary_where = '';

	function __construct() {

		add_action( 'admin_init', array( $this, 'process_export' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ), 20 );

	}

	function add_menu() {

		$hook = add_submenu_page( 'premise-member', __( 'Reports', 'premise' ), __( 'Reports', 'premise' ), 'manage_options', 'premise-reports', array( $this, 'admin_page' ) );
		add_action("admin_print_styles-{$hook}", array( $this, 'enqueue_admin_css' ) );
		add_action( "load-{$hook}", array( $this, 'scripts' ) );

	}

	function admin_page() {

		global $wpdb;

		$args = $this->parse_args();

		?>
		<div class="wrap">
			<form method="post" action="<?php menu_page_url( 'premise-reports' ); ?>">

			<?php 
			wp_nonce_field( $this->settings_field );
			screen_icon( $this->settings_field );
			?>
			<h2>
				<?php
				echo esc_html( get_admin_page_title() );
				submit_button( __( 'Submit', 'premise' ), 'button-primary accesspress-h2-button', 'premise-report[submit]', false );
				submit_button( __( 'Download', 'premise' ), 'button-primary accesspress-h2-button', 'premise-report[export]', false );
				?>
			</h2>

			<div class="premise-date-range">
				<label for="premise-report[start]"><?php _e( 'Start Date:', 'premise' ); ?></label>
				<input type="text" name="premise-report[start]" id="premise-report[start]" value="<?php echo esc_attr( $args['start'] ); ?>" />
				<br />

				<label for="premise-report[end]"><?php _e( 'End Date:', 'premise' ); ?></label>
				<input type="text" name="premise-report[end]" id="premise-report[end]" value="<?php echo esc_attr( $args['end'] ); ?>" />
<script type="text/javascript">
//<!--
jQuery(document).ready(function(){
	jQuery('#premise-report\\[start\\], #premise-report\\[end\\]').datepicker();
});
//-->
</script>
				<?php
				$report_types = array( 'sales' => __( 'Sales', 'premise' ) );
				if ( current_user_can( 'manage_options' ) )
					$report_types['summary'] = __( 'Sales Summary', 'premise' );
				if ( accesspress_get_option( 'authorize_net_recurring' ) )
					$report_types['subscription'] = __( 'Subscription Renewals', 'premise' );

				if ( count( $report_types ) > 1 ) {
				?>
				<br /><br />

				<strong><?php _e( 'Report', 'premise' ); ?>:</strong>
				<br />
				<label for="premise-report-type"><?php _e( 'Select Report:', 'premise' ); ?></label>

				<select name="premise-report[type]" id="premise-report-type">
					<?php
					foreach ( $report_types as $type => $desc ) {
						printf( '<option value="%s" %s>%s</option>', $type, selected( $type, $args['type'], false ), $desc );
					}
					?>
				</select>
				<?php
				} else {
					echo '<input type="hidden" name="premise-report[type]" value="sales" />';
				}
				?>
				<br /><br />

				<div class="premise-report-select-row premise-preport-select-product">
					<strong><?php _e( 'Product', 'premise' ); ?>:</strong>
					<?php 
					$product_query = new WP_Query( array( 'post_type' => 'acp-products', 'post_status' => 'publish', 'posts_per_page' => -1, 'order' => 'ASC', 'orderby' => 'title' ) );
					if ( $product_query->have_posts() ) {	
					?>
					<br />
					<label for="premise-report[product]"><?php _e( 'Select Product:', 'premise' ); ?></label>
	
					<select name="premise-report[product]" id="premise-report[product]">
						<option value=""><?php _e( '-- Product --', 'premise' ); ?></option>
						<?php
						while( $product_query->have_posts() ) {
		
							$product_query->the_post();
							$title = get_the_title();
							if ( empty( $title ) )
								continue;
		
							printf( '<option value="%d" %s>%s</option>', get_the_ID(), selected( get_the_ID(), $args['product'], false ), $title );
		
						}
		
						wp_reset_query();
						?>
					</select>
				<?php
				}
				?>
				<br />
				</div>
				<div class="premise-report-select-row premise-preport-select-coupon">
					<?php
					$coupon_query = new WP_Query( array( 'post_type' => 'acp-coupons', 'post_status' => 'publish', 'posts_per_page' => -1, 'order' => 'ASC', 'orderby' => 'title' ) );
					if ( $coupon_query->have_posts() ) {
						?>
					<br />
	
					<strong><?php _e( 'Coupon', 'premise' ); ?>:</strong>
					<br />
					<label for="premise-report[coupon]"><?php _e( 'Select Coupon:', 'premise' ); ?></label>
	
					<select name="premise-report[coupon]" id="premise-report[coupon]">
						<option value=""><?php _e( '-- Coupon --', 'premise' ); ?></option>
						<?php
						while( $coupon_query->have_posts() ) {
		
							$coupon_query->the_post();
							$title = get_the_title();
							if ( empty( $title ) )
								continue;
		
							printf( '<option value="%d" %s>%s</option>', get_the_ID(), selected( get_the_ID(), $args['coupon'], false ), $title );
		
						}
		
						wp_reset_query();
						?>
					</select>
					<?php
					}
					?>
				</div>
			</div>
		</form>
			<hr />
	<?php
		if ( isset( $report_types['summary'] ) ) {
?>
<script type="text/javascript">
//<!--
jQuery(document).ready(function(){
	jQuery('#premise-report-type').change(function(){
		if (jQuery('#premise-report-type').val() != 'summary')
			jQuery('.premise-report-select-row').slideDown();
		else
			jQuery('.premise-report-select-row').slideUp();
	});
	setTimeout(function(){ jQuery('#premise-report-type').change(); }, 250);
});
//-->
</script>
<?php
		}

		if ( ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], $this->settings_field ) ) {

			$order_rows = $this->get_report_data( false );

			echo '<table class="premise-report-table">';

			if ( ! empty( $order_rows ) ) {

				$alt = 0;
				foreach( $order_rows as $key => $order ) {

					$class = '';
					if ( ! is_numeric( $key ) ) {

						$class = 'class="total"';

					} else {

						if ( $alt % 2 )
							$class = 'class="alt"';

						$title = array_shift( $order );
						if ( $args['type'] != 'summary' )
							$id_column_url = add_query_arg( array( 'post' => $key, 'action' => 'edit' ), admin_url( 'post.php' ) );
						else
							$id_column_url = Premise_Member_Access_Order_Summary::get_detail_report_url( $title );

						array_unshift( $order, sprintf( '<a href="%s">%s</a>', $id_column_url, $title ) );
					}

					printf( '<tr %s><td>%s</td><tr>', $class, implode( '</td><td>', $order ) );
					$alt++;

				}

			} else {

				printf( '<tr><td>%s</td></tr>', __( 'No Orders matched the criteria.', 'premise' ) );

			}

			echo '</table>';
		}
	?>
		</div>
	
	<?php
	}

	function build_order_table( $start_date, $end_date, $product_id, $coupon_id, $export ) {

		global $wpdb;

		$meta_where = '';
		if ( $product_id )
			$meta_where .= $wpdb->prepare( " AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_acp_order_product_id' AND meta_value = %d)", $product_id );
		if ( $coupon_id )
			$meta_where .= $wpdb->prepare( " AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_acp_order_coupon_id' AND meta_value = %d)", $coupon_id );

		$order_rows = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_title FROM {$wpdb->posts} WHERE post_type = 'acp-orders' AND post_status = 'publish' AND post_date BETWEEN %s AND %s{$meta_where} ORDER BY post_date DESC", $start_date, $end_date ) );
		if ( empty( $order_rows ) )
			return array();

		$orders = array();
		foreach( $order_rows as $row )
			$orders[$row->ID] = $row->post_title;

		$details = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE post_id IN (" . implode( ',', array_keys( $orders ) ) . ')' );
		if ( empty( $details ) )
			return array();

		$order_details = array();
		$order_table = array( 'header' => array(
			__( 'ID', 'premise' ),
			__( 'Email', 'premise' ),
			$export ? __( 'First","Last', 'premise' ) : __( 'Member', 'premise' ),
			__( 'Product', 'premise' ),
			__( 'Date', 'premise' ),
			__( 'Price', 'premise' ),
			__( 'Refund', 'premise' ),
			__( 'Status', 'premise' ),
		) );

		if ( $export ) {

			$order_table['header'] = array_merge( $order_table['header'], array(
				__( 'Subscription', 'premise' ),
				__( 'Renewal Date', 'premise' ),
				__( 'Trial Price', 'premise' ),
				__( 'Coupon', 'premise' ),
				__( 'Gateway', 'premise' ),
				__( 'Transaction ID', 'premise' ),
			) );
		}

		$date_format = get_option( 'date_format' );
		$order_total = 0;
		$refund_total = 0;

		foreach( $details as $meta ) {

			if ( ! isset( $order_details[$meta->post_id] ) )
				$order_details[$meta->post_id] = array();

			$order_details[$meta->post_id][$meta->meta_key] = $meta->meta_value;
			if ( $meta->meta_key == '_acp_order_price' )
				$order_total += $meta->meta_value;

		}

		$email_as_username = accesspress_get_option( 'email_as_username' );
		$name_format = $export ? '%1$s","%2$s' : '%1$s %2$s';
		$user_format = $email_as_username || $export ? $name_format : '%3$s - ' . $name_format;
		$refund_status = __( 'refund', 'premise' );

		foreach( $orders as $order => $title ) {

			$row = array( $title );

			$user = isset( $order_details[$order]['_acp_order_member_id'] ) ? get_user_by( 'id', $order_details[$order]['_acp_order_member_id'] ) : null;
			$row[] = $user ? $user->user_email : '';
			$row[] = $user ? sprintf( $user_format, $user->first_name, $user->last_name, $user->user_login ) : '';

			$product = isset( $order_details[$order]['_acp_order_product_id'] ) ? get_post( $order_details[$order]['_acp_order_product_id'] ) : null;
			$row[] = $product ? $product->post_title : '';

			$row[] = isset( $order_details[$order]['_acp_order_time'] ) ? date( $date_format, $order_details[$order]['_acp_order_time'] ) : '';
			$row[] = $price = isset( $order_details[$order]['_acp_order_price'] ) ? $order_details[$order]['_acp_order_price'] : '';
			$status = isset( $order_details[$order]['_acp_order_status'] ) ? $order_details[$order]['_acp_order_status'] : '';

			if ( $status == $refund_status ) {

				$row[] = $price;
				$refund_total += $price;
				
			} else {

				$row[] = '';

			}

			$row[] = $status;

			if ( $export ) {

				$row[] = isset( $order_details[$order]['_acp_order_renewal_time'] ) ? __( 'Y', 'premise' ) : ''; 
				$row[] = isset( $order_details[$order]['_acp_order_renewal_time'] ) ? date( $date_format, $order_details[$order]['_acp_order_renewal_time'] ) : '';
				$row[] = isset( $order_details[$order]['_acp_order_trial_price'] ) ? $order_details[$order]['_acp_order_trial_price'] : '';

				$coupon = isset( $order_details[$order]['_acp_order_coupon_id'] ) ? get_post( $order_details[$order]['_acp_order_coupon_id'] ) : null;
				$row[] = $coupon ? $coupon->post_title : '';

				$gateway = AccessPress_Orders::find_gateway( $order );
				$row[] = $gateway ? $gateway->payment_method : '';
				$row[] = $gateway && $gateway->has_transaction_meta( $order ) ? $gateway->has_transaction_meta( $order ) : '';

			}
			$order_table[$order] = $row;

		}

		if ( ! $export )
			$order_table['total'] = array( __( 'Total Sales', 'premise' ), '', '', count( $orders ), '', $order_total, $refund_total );

		return $order_table;

	}

	function build_renewal_table( $start_date_ts, $end_date_ts, $product_id, $coupon_id, $export ) {

		global $wpdb;

		$meta_where = '';
		if ( $product_id )
			$meta_where .= $wpdb->prepare( " AND post_id IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_acp_order_product_id' AND meta_value = %d)", $product_id );
		if ( $coupon_id )
			$meta_where .= $wpdb->prepare( " AND post_id IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_acp_order_coupon_id' AND meta_value = %d)", $coupon_id );

		$subscriptions = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_acp_order_renewal_time' AND meta_value BETWEEN %s AND %s{$meta_where}", $start_date_ts, $end_date_ts ) );

		if ( empty( $subscriptions ) )
			return array();

		$order_rows = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'acp-orders' AND post_status = 'publish' AND ID IN (" . implode( ',', $subscriptions ) . ')' );
		$orders = array();
		foreach( $order_rows as $row )
			$orders[$row->ID] = $row->post_title;


		$details = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE post_id IN (" . implode( ',', array_keys( $orders ) ) . ')' );

		if ( empty( $orders ) || empty( $details ) )
			return array();

		$order_details = array();
		$order_table = array( 'header' => array(
			__( 'ID', 'premise' ),
			__( 'Email', 'premise' ),
			__( 'Member', 'premise' ),
			__( 'Product', 'premise' ),
			__( 'Purchase Date', 'premise' ),
			__( 'Renewal Date', 'premise' ),
			__( 'Subscription Price', 'premise' ),
			__( 'Status', 'premise' ),
		) );

		if ( $export ) {

			$order_table['header'] = array_merge( $order_table['header'], array(
				__( 'Trial Price', 'premise' ),
				__( 'Coupon', 'premise' ),
				__( 'Gateway', 'premise' ),
				__( 'Transaction ID', 'premise' ),
			) );
		} else {

			$order_table['header'][] = __( 'Action', 'premise' );

		}

		$date_format = get_option( 'date_format' );
		$order_total = 0;

		foreach( $details as $meta ) {

			if ( ! isset( $order_details[$meta->post_id] ) )
				$order_details[$meta->post_id] = array();

			$order_details[$meta->post_id][$meta->meta_key] = $meta->meta_value;
			if ( $meta->meta_key == '_acp_order_price' )
				$order_total += $meta->meta_value;

		}

		if ( $export )
			return $order_details;

		$order_sort = array();
		foreach( $orders as $order => $title ) {

			$key = $order_details[$order]['_acp_order_renewal_time'];
			while( isset( $order_sort[$key] ) )
				$key++;

			$order_sort[$key] = $order;

		}
		ksort( $order_sort );

		foreach( $order_sort as $order ) {

			$row = array( $title );

			$user = isset( $order_details[$order]['_acp_order_member_id'] ) ? get_user_by( 'id', $order_details[$order]['_acp_order_member_id'] ) : null;
			$row[] = $user ? $user->user_email : '';
			$row[] = $user ? sprintf( '%s - %s %s', $user->user_login, $user->first_name, $user->last_name ) : '';

			$product = isset( $order_details[$order]['_acp_order_product_id'] ) ? get_post( $order_details[$order]['_acp_order_product_id'] ) : null;
			$row[] = $product ? $product->post_title : '';

			$row[] = isset( $order_details[$order]['_acp_order_time'] ) ? date( $date_format, $order_details[$order]['_acp_order_time'] ) : '';
			$row[] = isset( $order_details[$order]['_acp_order_renewal_time'] ) ? date( $date_format, $order_details[$order]['_acp_order_renewal_time'] ) : '';
			$row[] = isset( $order_details[$order]['_acp_order_price'] ) ? $order_details[$order]['_acp_order_price'] : '';
			$row[] = isset( $order_details[$order]['_acp_order_status'] ) ? $order_details[$order]['_acp_order_status'] : '';

			if ( $export ) {

				$row[] = isset( $order_details[$order]['_acp_order_trial_price'] ) ? $order_details[$order]['_acp_order_trial_price'] : '';

				$coupon = isset( $order_details[$order]['_acp_order_coupon_id'] ) ? get_post( $order_details[$order]['_acp_order_coupon_id'] ) : null;
				$row[] = $coupon ? $coupon->post_title : '';

				$gateway = AccessPress_Orders::find_gateway( $order );
				$row[] = $gateway ? $gateway->payment_method : '';
				$row[] = $gateway && $gateway->has_transaction_meta( $order ) ? $gateway->has_transaction_meta( $order ) : '';

			} else {

				$renew_url = add_query_arg( array( 'action' => 'renew', 'subscription' => $order, 'key' => wp_create_nonce( 'renew-subscription-' . $order ) ), menu_page_url( 'premise-reports', false ) );
				$row[] = sprintf( __( '<a href="%s" %s>Renew</a>', 'premise' ), $renew_url, 'target="_blank"' );

			}
			$order_table[$order] = $row;

		}

		if ( ! $export )
			$order_table['total'] = array( __( 'Total Sales', 'premise' ), '', '', count( $orders ), $order_total );

		return $order_table;

	}

	function build_summary_table( $start_date, $end_date, $export ) {

		global $wpdb;

		$this->summary_where = $wpdb->prepare( " AND {$wpdb->posts}.post_date BETWEEN %s AND %s", $start_date, $end_date );

		add_filter( 'posts_where', array( $this, 'summary_where' ) );
		$summary_query = new WP_Query( array( 'post_type' => 'acp-order-summary', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		remove_filter( 'posts_where', array( $this, 'summary_where' ) );

		$summary_table = array();
		if ( $summary_query->have_posts() ) {

			$total_row = array( __( 'Total', 'premise' ) );
			$summary_table['header'] = Premise_Member_Access_Order_Summary::get_column_titles();
			for( $i = 1; $i < count( $summary_table['header'] ); $i++ )
				$total_row[$i] = 0;

			foreach( Premise_Member_Access_Order_Summary::get_summary_rows( $summary_query ) as $id => $row ) {

				$summary_table[$id] = $row;
				for( $i = 1; $i < count( $row ); $i++ )
					$total_row[$i] += $row[$i];

			}

			$summary_table['total'] = $total_row;

		}

		wp_reset_query();

		return $summary_table;

	}
	
	function summary_where( $where ) {

		return $where . $this->summary_where;

	}
	function process_export() {

		if ( empty( $_POST['premise-report']['export'] ) || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], $this->settings_field ) )
			return;

		$args = $this->parse_args();

		$order_rows = $this->get_report_data( true );

		if ( ! empty( $order_rows ) ) {

			$export_name = '';
			if ( $args['start'] )
				$export_name .= str_replace( '/', '.', $args['start'] ) . '-';
			if ( $args['end'] )
				$export_name .= str_replace( '/', '.', $args['end'] ) . '-';

			$export_name .= $args['type'];
			header('Content-Description: File Transfer');
			header("Content-Disposition: attachment; filename={$export_name}.csv");
			header('Content-Type: application/csv; charset=' . get_option('blog_charset'), true);

			foreach( $order_rows as $key => $order )
				echo '"' . implode( '","', $order ) . "\"\n";


			flush();
			exit;

		}
	}

	function get_report_data( $export ) {

		$args = $this->parse_args();
		$end_date_ts = strtotime( $args['end'] ) + 86400;
		$start_date_ts = strtotime( $args['start'] );
		$end_date = date( 'Y-m-d', $end_date_ts );
		$start_date = date( 'Y-m-d', $start_date_ts );

		if ( $args['type'] == 'subscription' )
			return $this->build_renewal_table( $start_date_ts, $end_date_ts, $args['product'], $args['coupon'], $export );
		else if ( $args['type'] == 'summary' )
			return $this->build_summary_table( $start_date, $end_date, $export );

		return $this->build_order_table( $start_date, $end_date, $args['product'], $args['coupon'], $export );
	}

	function parse_args() {

		$args = ! empty( $_POST ) ? $_POST['premise-report'] : ( empty( $_GET ) ? array() : $_GET );
		$default_end = time();
		$default_start = $default_end - ( 86400 * 7 );

		return wp_parse_args( $args, array(
			'start'		=> date( 'n/j/Y', $default_start ),
			'end'		=> date( 'n/j/Y', $default_end ),
			'type'		=> 'sales',
			'product'	=> 0,
			'coupon'	=> 0,
		) );
		
	}
	function enqueue_admin_css() {

		wp_enqueue_style( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.css', array( 'thickbox' ), PREMISE_VERSION );

	}
	function scripts() {

		wp_enqueue_script( 'jquery-ui-timepicker-addon', PREMISE_RESOURCES_URL . 'jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.0.1' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( 'premise-date-picker', PREMISE_RESOURCES_URL . 'premise-date-picker.css', PREMISE_VERSION );

	}
}

add_action( 'init', 'memberaccess_report_settings_init' );
/**
 * 
 */
function memberaccess_report_settings_init() {

	new Memberaccess_Report_Settings;

}