<?php

if( !class_exists( 'Premise_API' ) ) :

class Premise_API {

	var $_key = '';

	/**
	 * @var Premise_API_Education_Provider
	 */
	var $_provider_Education = null;

	/**
	 * @var Premise_API_Graphics_Provider
	 */
	var $_provider_Graphics = null;

	function Premise_API( $key ) {
		$this->_key = $key;

		$this->_provider_Education = new Premise_API_Education_Provider( $key );
		$this->_provider_Graphics = new Premise_API_Graphics_Provider( $key );
	}

	// Graphics Requests

	function getGraphicCategories() {
		return $this->_provider_Graphics->getGraphicCategories();
	}

	function getGraphics( $limit = 10, $page = 1, $category = '', $search = '' ) {
		$images = $this->_provider_Graphics->getGraphics( $limit, $page, $category, $search );
		if( is_wp_error( $images ) )
			return $images;
		
		foreach( $images['images'] as $key => $image ) {
			$images['images'][$key]['thumbnail_url'] = add_query_arg( array( 'apikey' => $this->_key ), trailingslashit( $image['thumbnail_url'] ) );
			$images['images'][$key]['full_url'] = add_query_arg( array( 'apikey' => $this->_key ), trailingslashit( $image['full_url'] ) );
		}
		return $images;
	}

	function getGraphic( $slug ) {
		return $this->_provider_Graphics->getGraphic( $slug );
	}

	// Education Requests

	function getAdvice( $type ) {
		return $this->_provider_Education->getAdvice( $type );
	}

	function getAdviceSections( $type ) {
		return $this->_provider_Education->getAdviceSections( $type );
	}

	function getSampleContent( $type ) {
		return $this->_provider_Education->getSampleContent( $type );
	}

	function getSingleAdvice( $section ) {
		return $this->_provider_Education->getSingleAdvice( $section );
	}
}
endif;