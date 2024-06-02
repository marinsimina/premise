<?php

if( !class_exists( 'Premise_API_Education_Provider' ) ) :

class Premise_API_Education_Provider extends Premise_API_Provider {

	function Premise_API_Education_Provider( $key ) {
		$this->_key = $key;
	}

	/**
	 * @param int $type The numeric ID of the type to return long-form advice for.
	 * @return WP_Error|array A WP_Error object if some type of error ocurrs, otherwise return an associative array with keys 'advice' and 'type'.
	 */
	function getAdvice( $type = null ) {
		$sections = $this->getAdviceSections( $type );

		if( is_wp_error( $sections ) )
			return $sections;

		$text = '';
		foreach( (array)$sections as $section )
			$text .= "<h2>{$section['name']}</h2>{$section['advice']}";

		return array( 'advice' => $text, 'type' => $type );
	}

	/**
	 * @param $section The numeric ID of a single advice section.
	 * @return WP_Error|array A WP_Error object if some type of error ocurrs, otherwise return an associative array with keys 'advice', 'name' and 'section'
	 */
	function getSingleAdvice( $section = null ) {
		if( !is_numeric( $section ) )
			return new WP_Error('section_parameter', __('The section parameter must be an integer when requesting an advice section.', 'premise' ));

		$type = absint( $section );
		$path = "/advice/{$section}/";

		$result = $this->makeRequest( $path );
		if( is_wp_error( $result ) )
			return $result;

		return array( 'advice' => $result->body, 'name' => $result->title, 'section' => $result->id );
	}

	/**
	 * @param int $type The numeric ID of the type to return long-form advice for.
	 * @return WP_Error|array A WP_Error object if some type of error ocurrs, otherwise return an array of associative arrays with keys 'advice', 'id' and 'name'
	 */
	function getAdviceSections( $type = null ) {
		if( !is_numeric( $type ) )
			return new WP_Error('type_parameter', __('The type parameter must be an integer when requesting advice.', 'premise' ));

		$type = absint($type);
		$path = "/advices/{$type}/";

		$result = $this->makeRequest( $path );
		if( is_wp_error( $result ) )
			return $result;

		$sections = array();
		foreach( (array)$result->advices as $advice )
			$sections[] = array( 'advice' => $advice->body, 'id' => $advice->id, 'name' => $advice->title );

		return $sections;
	}

	/**
	 * Returns a string of sample content for a particular landing page type.
	 *
	 * @param string $type The type of landing page to retrieve sample content for.
	 * @return array|bool False if the page type is invalid or an error occurs.  Otherwise returns an array with the keys content and type.
	 */
	function getSampleContent( $type = null ) {
		if( !is_numeric( $type ) )
			return new WP_Error('type_parameter', __('The type parameter must be an integer when requesting sample content.', 'premise' ));

		$type = absint( $type );
		$path = "/sample/{$type}/";

		$result = $this->makeRequest( $path );
		if( is_wp_error( $result ) )
			return $result;

		return array( 'content' => $result->body, 'type' => $result->id );
	}
}
endif;
