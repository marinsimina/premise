<?php
/*
Handles social sharing for Premise
*/
class Premise_Social_Share {

	function handle_social_share( $id ) {

		global $premise_base;
		
		if ( ! post_type_exists( $premise_base->get_post_type() ) )
			$premise_base->register_post_type();

		$data = stripslashes_deep( $_REQUEST );
		if( isset( $data['twitter-oauth-callback'] ) && 1 == $data['twitter-oauth-callback'] )
			self::authorize_twitter( $id );
			
		if( isset( $data['facebook-oauth-callback'] ) && 1 == $data['facebook-oauth-callback'] )
			self::authorize_facebook( $id );

		$settings = $premise_base->get_settings();
		if( $settings['sharing']['type'] == 1 ) {
			if( $data['social-share-type'] == 'facebook' )
				self::redirect_to_facebook_oauth( $id );

			self::redirect_to_twitter_oauth( $id );
		} 
		if( wp_verify_nonce( $data['_wpnonce'], 'premise-shared-content-' . $id ) )
			self::set_cookie( $id ); 
	}

	function clear_social_share( $id ) {
		$post_id = (int)$id;
		if( $post_id && wp_verify_nonce( $_REQUEST['_wpnonce'], 'clear-share-id' ) )
			self::set_cookie( $post_id, time() - 86400 );

		self::redirect( add_query_arg( array( 'post' => $post_id, 'action' => 'edit' ), admin_url( 'post.php' ) ) );
	}

	function redirect_to_twitter_oauth( $postId ) {
		$connection = self::get_twitter_oauth();
		$callbackUrl = add_query_arg( array( 'social-share-ID' => $postId, 'twitter-oauth-callback' => 1 ), get_permalink( $postId ) );
		$temporary = $connection->getRequestToken( $callbackUrl );
		
		$_SESSION['oauth_token'] = $temporary['oauth_token'];
		$_SESSION['oauth_token_secret'] = $temporary['oauth_token_secret'];
		
		$redirect = $connection->getAuthorizeURL( $temporary );
		
		self::redirect( $redirect );
	}
	
	function redirect_to_facebook_oauth( $postId ) {
		global $premise_base;

		$settings = $premise_base->get_settings();
		$key = $secret = '';
		if( isset( $settings['sharing']['facebook-app-id'] ) )
			$key = $settings['sharing']['facebook-app-id'];

		$callbackUrl = add_query_arg( array( 'social-share-ID' => $postId, 'facebook-oauth-callback' => 1 ), get_permalink( $postId ) );

		$url = add_query_arg( array( 'client_id' => $key, 'redirect_uri' => urlencode( $callbackUrl ), 'scope' => 'publish_stream' ), 'https://www.facebook.com/dialog/oauth' );
		self::redirect( $url );
	}
	
	function authorize_twitter( $postId ) {
		$connection = self::get_twitter_oauth( true );
		$token_credentials = $connection->getAccessToken( $_REQUEST['oauth_verifier'] );

		if( !empty( $token_credentials['oauth_token'] ) && !empty( $token_credentials['oauth_token_secret'] ) ) {
			$key_secret = self::get_twitter_key_secret();
			extract( $key_secret );
			$connection_for_post = new TwitterOAuth( $key, $secret, $token_credentials['oauth_token'], $token_credentials['oauth_token_secret'] );
			$status = premise_get_social_share_twitter_text( $postId, true );
			$connection_for_post->post( 'statuses/update', array( 'status' => $status ) );
			self::set_cookie( $postId );
		} 

		self::redirect( get_permalink( $postId ) );
	}
	
	function authorize_facebook( $postId ) {
		global $premise_base;

		$data = stripslashes_deep( $_REQUEST );
		$code = isset( $data['code'] ) ? $data['code'] : false;
		$permalink = get_permalink( $postId );
		if( !$code )
			self::redirect( $permalink );

		$settings = $premise_base->get_settings();
		$key = $secret = '';
		if( isset( $settings['sharing']['facebook-app-id'] ) && isset( $settings['sharing']['facebook-app-secret'] ) ) {
			$key = $settings['sharing']['facebook-app-id'];
			$secret = $settings['sharing']['facebook-app-secret'];
		}
		$callbackUrl = add_query_arg( array( 'social-share-ID' => $postId, 'facebook-oauth-callback' => 1 ), $permalink );
			
		$accessUrl = 'https://graph.facebook.com/oauth/access_token';
		$accessUrl = add_query_arg( array( 'client_id' => $key, 'redirect_uri' => urlencode( $callbackUrl ), 'client_secret' => $secret, 'code' => $code ), $accessUrl );
		$response = wp_remote_get( $accessUrl, array( 'sslverify' => false ) );
		if( is_wp_error( $response ) )
			self::redirect( $permalink );

		$body = wp_remote_retrieve_body( $response );
		$httpCode = wp_remote_retrieve_response_code( $response );
		if( $httpCode == 400 )
			self::redirect( $permalink );

		$params = null;
		parse_str( $body, $params );
		$accessToken = $params['access_token'];
		
		$postArguments = array(
			'access_token' => $accessToken,
			'name' => get_the_title( $postId ),
			'message' => premise_get_social_share_twitter_text( $postId ),
			'link' => $permalink,
			'privacy' => json_encode( array( 'value' => 'EVERYONE' ) ),
		);
		$postResponse = wp_remote_post( 'https://graph.facebook.com/me/feed', array( 'body' => $postArguments, 'sslverify' => false ) );
		if( is_wp_error( $postResponse ) )
			self::redirect( $permalink );

		$body = wp_remote_retrieve_body( $postResponse );
		$postHttpCode = wp_remote_retrieve_response_code( $postResponse );
		if( 200 != $postHttpCode )
			self::redirect( $permalink );

		$object = json_decode( $body );
		if( isset( $object->id ) )
			self::set_cookie( $postId );
		
		self::redirect( $permalink );
	}
	
	function set_cookie( $postId, $time = null ) {
		$redirect = false;

		if( $time === null ) {
			$time = time() + 2592000; // 30 days
			$redirect = true;
		}
		setcookie( 'premise-social-share-' . $postId, base64_encode( md5( 'premise-social-share-check-' . $postId ) ), $time, COOKIEPATH, COOKIE_DOMAIN );

		if( $redirect )
			self::redirect( get_permalink( $postId ) );
	}

	function get_twitter_oauth( $session_tokens = false ) {
		session_start();
		require_once( PREMISE_LIB_DIR . 'twitteroauth/twitteroauth.php' );
		
		$key_secret = self::get_twitter_key_secret();
		extract( $key_secret );
		if( !$session_tokens )
			return new TwitterOAuth( $key, $secret );

		return new TwitterOAuth( $key, $secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret'] );
	}
	function get_twitter_key_secret() {
		global $premise_base;

		$settings = $premise_base->get_settings();
		$key = $secret = '';
		if( isset( $settings['sharing']['twitter-consumer-key'] ) && isset( $settings['sharing']['twitter-consumer-secret'] ) ) {
			$key = $settings['sharing']['twitter-consumer-key'];
			$secret = $settings['sharing']['twitter-consumer-secret'];
		}
		return compact( 'key', 'secret' );
	}
	function redirect( $url ) {
		wp_redirect( $url );
		exit;
	}
}
