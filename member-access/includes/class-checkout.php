<?php
/**
 * Class for handling the checkout process for the Member Access module
 *
 * This class processes & stores Checkout dara.
 *
 * @since 2.2.2
 */

class Premise_Checkout {
	/**
	 * This will be a singleton class
	 *
	 * @since 2.2.2
	 *
	 * @var object reference to the single instance of this class
	 */
	static $_instance = null;
	/**
	 * The argument array passed to the checkout form function
	 *
	 * @since 2.2.2
	 *
	 * @var array arguments from the checkout form shortcode
	 */
	var $_args;
	/**
	 * The argument array passed to the checkout function
	 *
	 * @since 2.2.2
	 *
	 * @var array merge arguments from the checkout form shortcode, request query string, posted form & filters
	 */
	var $_checkout_args = null;
	/**
	 * Holds the result of the accesspress_checkout call
	 *
	 * @since 2.2.2
	 *
	 * @var array or error object
	 */
	var $_checkout_complete = null;
	/**
	 * Flag indicating whether a checkout form was submitted
	 *
	 * @since 2.2.2
	 *
	 * @var bool
	 */
	var $_form_submitted = null;
	/**
	 * Output buffer for payment gateways that send output while processing checkout
	 *
	 * @since 2.2.2
	 *
	 * @var string payment gateway output
	 */
	var $_gateway_output_buffer = '';

	/**
	 * Class constructor.
	 *
	 * @since 2.2.2
	 */
	function __construct( $args = array() ) {

		$this->_args = $args;
		if ( isset( $_POST['accesspress-checkout'] ) )
			$this->_checkout_args = $_POST['accesspress-checkout'];

	}
	/**
	 * Get only instance of this class
	 *
	 * @since 2.2.2
	 */
	static function get_instance( $args = array() ) {

		$instance = self::$_instance === null ? new Premise_Checkout( $args ) : self::$_instance;
		self::$_instance =& $instance;

		if ( empty( $instance->_args ) && ! empty( $args ) )
		 	$instance->_args = $args;

		if ( null === $instance->_form_submitted )
			$instance->_form_submitted = apply_filters( 'premise_checkout_form_submitted', isset( $_POST['accesspress-checkout'] ) || isset( $_REQUEST['action'] ), $args );

		return $instance;

	}
	/**
	 * Get the checkout arguments
	 *
	 * @since 2.2.2
	 */
	function get_checkout_args() {

		return apply_filters( 'premise_checkout_args', $this->_checkout_args !== null ? $this->_checkout_args : $this->_args );

	}
	/**
	 * Get the original arguments
	 *
	 * @since 2.3
	 */
	function get_args() {

		return $this->_args;

	}
	/**
	 * Process the checkout
	 *
	 * @since 2.2.2
	 */
	function process_checkout() {

		if ( $this->_form_submitted && $this->_checkout_complete === null ) {

			ob_start();
			$this->_checkout_complete = accesspress_checkout( $this->get_checkout_args() );
			$this->_gateway_output_buffer = ob_get_clean();

		}

		return $this->_checkout_complete;

	}
	/**
	 * Get the checkout arguments
	 *
	 * @since 2.2.2
	 */
	function get_output_buffer() {

		return $this->_gateway_output_buffer;

	}
	/**
	 * Return flag indicating whether a form was submitted
	 *
	 * @since 2.2.2
	 */
	function has_submitted_form() {

		return $this->_form_submitted;

	}
}
