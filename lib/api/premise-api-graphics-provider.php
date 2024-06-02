<?php

if( !class_exists( 'Premise_API_Graphics_Provider' ) ) :

class Premise_API_Graphics_Provider extends Premise_API_Provider {

	function Premise_API_Graphics_Provider( $key ) {
		$this->_key = $key;
	}

	/**
	 * @return WP_Error|array A WP_Error object if some type of error ocurrs, otherwise return an array of associative arrays with keys 'count', 'id' and 'name'
	 */
	function getGraphicCategories() {
		$path = "/graphics/";

		$result = $this->makeRequest( $path );
		if( is_wp_error( $result ) )
			return $result;

		$cats = array();
		foreach( (array)$result->categories as $item )
			$cats[] = array( 'count' => $item->graphicCount, 'id' => $item->id, 'name' => $item->name );

		return $cats;
	}

	/**
	 * @param int $limit The maximum number of graphics.
	 * @param int $category The category from which graphics should be retrieved.
	 * @param string $search A search term to use when looking for graphics.
	 * @return WP_Error|array A WP_Error object if some type of error ocurrs, otherwise return an array of associative arrays with keys 'width', 'height', 'thumbnail_url', 'name', 'full_url', 'id' and 'type'
	 */
	function getGraphics( $limit = 10, $page = 1, $category = 0, $search = null ) {
		if( empty( $page ) || !is_numeric( $page ) )
			$page = 1;

		$page = absint( $page );

		if( empty( $limit ) || !is_numeric( $limit ) )
			$limit = 10;

		$limit = absint( $limit );

		if( empty( $category ) || !is_numeric( $category ) )
			$category = 0;

		$category = absint( $category );

		if( empty( $search ) )
			$search = null;

		$result = $this->makeRequest( '/graphics/', array( 'limit' => $limit, 'page' => $page, 'type' => $category, 'search' => urlencode( $search ) ) );
		if( is_wp_error( $result ) )
			return $result;

		$images = array();
		foreach( (array)$result->graphics as $graphic ) {
			$images[] = array(
				'filename' => $graphic->fileName,
				'width' => $graphic->width,
				'height' => $graphic->height,
				'thumbnail_url' => $graphic->webAddress,
				'name' => $graphic->title,
				'full_url' => $graphic->webAddress,
				'id' => $graphic->id,
				'type' => $graphic->fileType
			);
		}
		return array( 'images' => $images, 'total' => (integer)$result->totalCount, 'current' => (integer)$result->currentPage );
	}

	/**
	 * @param int $id The url to fetch the graphic bits for.  The
	 * @return WP_Error|bits A WP_Error object if some type of error ocurrs, otherwise return data for the image.
	 */
	function getGraphic( $id ) {
		if( !is_numeric( $id ) )
			return new WP_Error( 'id_parameter', __( 'Image id must be numeric.', 'premise' ) );

		$path = "/graphic/{$id}/";
		return $this->makeRequest( $path, array(), false );
	}
}
endif;