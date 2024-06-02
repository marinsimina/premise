<?php
/**
 * Premise Member Access Order Summary
 *
 * @package Premise
 */


/**
 * Maintains a real-time daily summary of orders
 *
 * This class handles the registration of the 'acp-order-summary' Custom Post Type, which stores
 * the order summary with one post per day.
 *
 * @since 2.2.2
 *
 */
class Premise_Member_Access_Order_Summary {
	/**
	 * The summary post ID for this request
	 *
	 * @since 0.1.0
	 *
	 * @var int post ID
	 */
	private $_summary_post = null;

	/** Constructor */
	function __construct() {

		add_action( 'init',				array( $this, 'register_post_type' ) );
		add_action( 'wp_dashboard_setup',		array( $this, 'add_meta_box' ) );
		add_action( 'transition_post_status',		array( $this, 'update_order_summary' ), 10, 3 );
		add_action( 'premise_membership_refund_order',	array( $this, 'refund_order' ) );
		add_action( 'premise_membership_create_order',	array( $this, 'add_new_order' ), 10, 4 );
		
	}

	/**
	 * Register the Products post type
	 */
	function register_post_type() {

		register_post_type( 'acp-order-summary',
			array(
				'labels' => array(),
				'supports'             => array( '' ),
				'public'               => false,
				'show_ui'              => false,
				'rewrite'              => false,
				'query_var'            => true
			)
		);

	}

	function add_meta_box() {

		if ( apply_filters( 'premise_hide_order_dashboard_widget', false ) || ! current_user_can( 'manage_options' ) )
			return;
	
		$screen = get_current_screen();
		add_meta_box( 'premise_order_summary_dashboard', __( 'Member Access Order History', 'premise' ), array( $this, 'dashboard_meta_box' ), $screen, 'side', 'high' );
	
	}

	function dashboard_meta_box() {

		$posts_per_page = get_user_option( 'acp_order_summary_days' );
		$posts_per_page = $posts_per_page ? $posts_per_page : 8;
		$summary_query = new WP_Query( array( 'post_type' => 'acp-order-summary', 'posts_per_page' => $posts_per_page, 'post_status' => 'publish' ) );

		if ( ! $summary_query->have_posts() ) {

			_e( 'No statistics collected yet.', 'premise' );
			return;

		}

		$summary_table = array( 'header' => $this->get_column_titles() );
		foreach( $this->get_summary_rows( $summary_query ) as $id => $row )
			$summary_table[$id] = $row;

?>
		<table class="form-table">
		<?php
			$alt = 0;
			foreach( $summary_table as $id => $row ) {

				$class = '';
				if ( ! is_numeric( $id ) ) {

					$class = 'class="total"';

				} else {

					if ( $alt % 2 )
						$class = 'class="alt"';

					$title = array_shift( $row );
					$id_column_url = $this->get_detail_report_url( $title );

					array_unshift( $row, sprintf( '<a href="%s">%s</a>', $id_column_url, $title ) );
				}

				printf( '<tr %s><td>%s</td><tr>', $class, implode( '</td><td>', $row ) );
				$alt++;

			}
		?>
		</table>
<?php
		wp_reset_query();

	}

	function refund_order( $post_id ) {

		if ( ! $this->_summary_post )
			$this->find_summary_post( $post_id );

		if ( ! $this->_summary_post )
			return;

		$refunded = (int)get_post_meta( $this->_summary_post, '_acp_summary_refunded_count', true );
		$refunded_amt = (float)get_post_meta( $this->_summary_post, '_acp_summary_refunded_amount', true );
		$new_amt = (float)get_post_meta( $post_id, '_acp_order_price', true );

		update_post_meta( $this->_summary_post, '_acp_summary_refunded_count', $refunded + 1 );
		update_post_meta( $this->_summary_post, '_acp_summary_refunded_amount', sprintf( '%.2f', $refunded_amt + $new_amt ) );

	}

	function update_order_summary( $new_status, $old_status, $post ) {
	
		// only interested in order posts
		if ( empty( $post ) || empty( $post->post_type ) || $post->post_type != 'acp-orders' )
			return;
	
		// ignore orders that don't change status 
		if ( $new_status == $old_status )
			return;
	
		// ignore orders that don't involve a publish status
		if ( $new_status != 'publish' && $old_status != 'publish' )
			return;

		$this->find_summary_post( $post->ID );

		if ( ! $this->_summary_post )
			return;

		// restoring from trash or putting on draft (in quick edit)
		if ( $old_status == 'trash' || $new_status == 'draft' ) {

			$refunded = (int)get_post_meta( $this->_summary_post, '_acp_summary_refunded_count', true );
			if ( $refunded > 0 ) {

				$refunded--;
				$refunded_amt = (float)get_post_meta( $this->_summary_post, '_acp_summary_refunded_amount', true );
				$refunded_amt =- (float)get_post_meta( $post->ID, '_acp_order_price', true );
				if ( $refunded_amt < 0 )
					$refunded_amt = 0;

				update_post_meta( $this->_summary_post, '_acp_summary_refunded_count', $refunded );
				update_post_meta( $this->_summary_post, '_acp_summary_refunded_amount', sprintf( '%.2f', $refunded_amt ) );

			}

		} else if ( $old_status == 'draft' ) {

			$this->add_new_order_to_summary( $post->ID );

		} else if ( $new_status == 'trash' ) {

			$this->refund_order( $post->ID );

		}

	}

	function find_summary_post( $order_id ) {

		if ( $this->_summary_post !== null )
			return;

		$today = current_time( 'mysql' );
		$today = substr( $today, 0, strpos( $today, ' ' ) );
		$order_date_ts = get_post_meta( $order_id, '_acp_order_time', true );
		$order_date = $order_date_ts ? date( 'Y-m-d', (int)$order_date_ts ) : $today;
		$date_fields = explode( '-', $order_date );
	
		$summary_query = new WP_Query( array( 'post_type' => 'acp-order-summary', 'year' => $date_fields[0], 'monthnum' => $date_fields[1], 'day' => $date_fields[2] ) );
		$this->_summary_post = 0;

		// only add a new summary post if the order is from today
		if ( $summary_query->have_posts() ) {
	
			$summary_query->the_post();
			$this->_summary_post = get_the_ID();
	
		} else if ( $order_date == $today ) {
	
			$new_summary_post = array(
				'post_type' => 'acp-order-summary',
				'post_title' => $today,
				'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( $today ) ),
				'post_status' => 'publish'
			);
			$this->_summary_post = wp_insert_post( $new_summary_post );

		}

		wp_reset_query();

	}

	function add_new_order_to_summary( $order_id, $new_amt = false ) {

		$this->find_summary_post( $order_id );

		if ( ! $this->_summary_post )
			return;

		$new_amt = $new_amt !== false ? $new_amt : get_post_meta( $order_id, '_acp_order_amount', true );
		$orders = (int)get_post_meta( $this->_summary_post, '_acp_summary_order_count', true );
		$orders_amt = (float)get_post_meta( $this->_summary_post, '_acp_summary_order_amount', true );

		update_post_meta( $this->_summary_post, '_acp_summary_order_count', $orders + 1 );
		update_post_meta( $this->_summary_post, '_acp_summary_order_amount', sprintf( '%.2f', $orders_amt + $new_amt ) );

	}

	function add_new_order( $member_id, $values, $renewal, $order_id ) {

		if ( $renewal )
			return;

		$this->add_new_order_to_summary( $order_id, isset( $values['_acp_order_price'] ) ?  $values['_acp_order_price'] : 0 );

	}

	function get_column_titles() {

		return array(
			__( 'Date', 'premise' ),
			__( 'Orders', 'premise' ),
			__( 'Refunded', 'premise' ),
			__( 'Total Amt', 'premise' ),
			__( 'Refunded Amt', 'premise' ),
			__( 'Net Amt', 'premise' )
		);
		
	}

	function get_summary_rows( $summary_query ) {

		$summary_rows = array();
		if ( ! $summary_query->have_posts() )
			return $summary_rows;

		while( $summary_query->have_posts() ) {

			$summary_query->the_post();

			$the_id = get_the_id();
			$order_total = get_post_meta( $the_id, '_acp_summary_order_amount', true );
			$refunded_total = get_post_meta( $the_id, '_acp_summary_refunded_amount', true );

			$row = array(
				get_the_title(),
				esc_html( get_post_meta( $the_id, '_acp_summary_order_count', true ) ),
				esc_html( get_post_meta( $the_id, '_acp_summary_refunded_count', true ) ),
				esc_html( $order_total ),
				esc_html( $refunded_total ),
				sprintf( '%.2f', $order_total - $refunded_total )
			);

			$summary_rows[$the_id] = $row;
		}

		return $summary_rows;

	}

	function get_detail_report_url( $date ) {

		$start_ts = strtotime( $date );
		$start_date = urlencode( date( 'n/j/Y', $start_ts ) );
		$end_date = urlencode( date( 'n/j/Y', $start_ts + 86400 ) );

		return wp_nonce_url( add_query_arg( array( 'start' => $start_date, 'end' => $end_date ), menu_page_url( 'premise-reports', false ) ), 'premise-reports' );

	}
}