<?php
if( !class_exists( 'Premise_API_Provider' ) ) :

class Premise_API_Provider {

	/**
	 * @var string The base URL for making request related to Premise education resources.
	 */
	var $_base = 'http://api.getpremise.com';

	/**
	 * @var string Holds the API key used for requests.
	 */
	var $_key = '';

	function makeRequest( $path = '/', $args = array(), $json = true ) {
		if( empty( $this->_key ) )
			return new WP_Error('empty_apikey', sprintf(__('Access denied.  Please <a target="_blank" href="%s">enter your Premise API key</a> and then attempt your request again.', 'premise' ), admin_url('admin.php?page=premise-main')));

		$args = wp_parse_args( $args, array( 'apikey' => $this->_key ) );

		$url = add_query_arg( $args, "{$this->_base}{$path}" );
		$response = wp_remote_get( $url );

		if( is_wp_error( $response ) )
			return $response;

		$body = wp_remote_retrieve_body( $response );
		$code = wp_remote_retrieve_response_code( $response );

		if( !in_array( $code, array( 200, 201 ) ) )
			return new WP_Error('access_denied', __('Access denied when attempting to access a Premise resource.  Please check your API key.', 'premise' ));

		return $json ? json_decode( $body ) : $body;
	}
}
endif;