<?php
/**
 * Premise Member Access Link Manager
 *
 * @package Premise
 */


/**
 * Handles the registration and management of links for the Link Manager.
 *
 * This class handles the registration of the 'acp-links' Custom Post Type, which stores
 * all links. It also allows you to manage, edit, and (if need be) delete links.
 *
 * It uses the Access Level taxonomy to restrict access to links.
 *
 * The Link Name is the post title.
 * The Product ID is the numerical post ID.
 * The Access Level(s) this link requires are stored as a custom taxonomy. Each Access Level is a term.
 *
 * @since 2.2.0
 *
 */
class Premise_Member_Access_Links {


	/** Constructor */
	function __construct() {

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'wp', array( $this, 'process_link' ) );

		add_filter( 'manage_edit-acp-links_columns', array( $this, 'columns_filter' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'columns_data' ) );
		add_action( 'save_post', array( $this, 'metabox_save' ), 1, 2 );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		// enqueue JS & CSS
		add_action( 'load-edit.php', array( $this, 'enqueue_admin_css' ) );
		add_action( 'load-post.php', array( $this, 'enqueue_admin_css' ) );
		add_action( 'load-post-new.php', array( $this, 'enqueue_admin_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

	}

	/**
	 * Register the Products post type
	 */
	function register_post_type() {

			$labels = array(
				'name'               => __( 'Links', 'premise' ),
				'singular_name'      => __( 'Link', 'premise' ),
				'add_new'            => __( 'Create New Link', 'premise' ),
				'add_new_item'       => __( 'Create New Link', 'premise' ),
				'edit'               => __( 'Edit Link', 'premise' ),
				'edit_item'          => __( 'Edit Link', 'premise' ),
				'new_item'           => __( 'New Link', 'premise' ),
				'view'               => __( 'View Link', 'premise' ),
				'view_item'          => __( 'View Link', 'premise' ),
				'search_items'       => __( 'Search Links', 'premise' ),
				'not_found'          => __( 'No Links found', 'premise' ),
				'not_found_in_trash' => __( 'No Links found in Trash', 'premise' ),
				'menu_name'          => __( 'Link Manager', 'premise' ),
			);

		if ( current_user_can( 'manage_options' ) ) {

			register_post_type( 'acp-links',
				array(
					'labels' => $labels,
					'show_in_menu'         => 'premise-member',
					'supports'             => array( 'title' ),
					'taxonomies'           => array( 'acp-access-level' ),
					'register_meta_box_cb' => array( $this, 'metaboxes' ),
					'public'               => false,
					'show_ui'              => true,
					'rewrite'              => false,
					'query_var'            => false
				)
			);

		} else {

			register_post_type( 'acp-links',
				array(
					'labels' => $labels,
					'public'               => false,
					'show_ui'              => false,
					'rewrite'              => false,
					'query_var'            => false
				)
			);

		}

	}

	/**
	 * Register the metaboxes
	 */
	function metaboxes() {

		add_meta_box( 'accesspress-product-details-metabox', __( 'Link Details', 'premise' ), array( $this, 'details_metabox' ), 'acp-links', 'normal' );
		add_meta_box( 'accesspress-product-status-metabox', __( 'Status', 'premise' ), 'premise_custom_post_status_metabox', 'acp-links', 'side', 'high' );
		
		remove_meta_box( 'slugdiv', 'acp-links', 'normal' );
		remove_meta_box( 'submitdiv', null, 'side' );

	}

	/**
	 * The metabox output
	 */
	function details_metabox( $post ) {

		// check to see if file is directly accessible
		$file_name = accesspress_get_custom_field( '_acp_link_filename' );
		$uploads = wp_upload_dir();

		if ( $file_name ) {

			$member_file = trailingslashit( $uploads['baseurl'] ) . trailingslashit( accesspress_get_option( 'uploads_dir' ) ) . $file_name;
			$request_result = wp_remote_request( $member_file, array( 'method' => 'HEAD' ) );
			if ( isset( $request_result['response']['code'] ) && $request_result['response']['code'] == 200 && isset( $_SERVER['SERVER_SOFTWARE'] ) ) {

				$server = strtolower( $_SERVER['SERVER_SOFTWARE'] );

	?>
	<div class="error">
	<?php
				echo '<p><strong>' . __( 'Additional server configuration is required to protect Links from unauthorized download.', 'premise' ) . '</strong></p>';

				if ( strpos( $server, 'nginx' ) !== false ) {

					_e( 'Add the following block to your Nginx Server configuration', 'premise' );
	?><br />
		<pre>
location ~ <?php echo dirname( parse_url( $member_file, PHP_URL_PATH ) ); ?> {
	deny all;
}
		</pre>
	<?php
				} elseif ( strpos( $server, 'apache' ) !== false || strpos( $server, 'litespeed' ) !== false ) {

					printf( __( 'Add the following to <code>%s</code>', 'premise' ), trailingslashit( $uploads['basedir'] ) . trailingslashit( accesspress_get_option( 'uploads_dir' ) ) . '.htaccess' );
	?><br />
		<pre>
Order deny,allow
deny from all
		</pre>
	<?php
				}

	?>
	</div><!-- .error -->
	<?php

			}
		}
	?>

		<input type="hidden" name="accesspress-link-nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />

		<table class="form-table">
		<tbody>
			
			<tr>
				<th scope="row"><p><?php _e( 'Link URI', 'premise' ); ?></p></th>
				
				<td>
					<?php if ( ! accesspress_get_custom_field( '_acp_link_id' ) ) : ?>
					<p class="description"><?php _e( 'Save link to generate URI', 'premise' ); ?></p>
					<?php else : ?>
					<input type="hidden" name="accesspress_link_meta[_acp_link_id]" value="<?php accesspress_custom_field( '_acp_link_id' ); ?>" />
					<p class="description"><?php echo esc_url( sprintf( home_url( '/?download_id=%s' ), accesspress_get_custom_field( '_acp_link_id' ) ) ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="accesspress_link_meta[_acp_link_filename]"><?php _e( 'File', 'premise' ); ?></label>
				</th>
				
				<td>
				<div id="memberaccess_link_filename_wrap">
<script type="text/javascript">
<!--
var premise_member_file_list = Array(
<?php
		$comma = '';
		$member_path = trailingslashit( $uploads['basedir'] ) . accesspress_get_option( 'uploads_dir' );
		if ( is_dir( $member_path ) ) {

			$fh = opendir( $member_path );
			while( false !== ( $file = readdir( $fh ) ) ) {

				// do not show hidden files
				if ( substr( $file, 0, 1 ) == '.' )
					continue;

				printf( "%s '%s'\n", $comma, esc_js( basename( $file ) ) );
				$comma = ',';

			}
		}
?>
);
-->
jQuery(document).ready(function() {
	// html decode suggestion list
	jQuery(premise_member_file_list).each(function(index, value){
		premise_member_file_list[index] = jQuery('<div />').html(value).text();
	});

	jQuery('#accesspress_link_meta\\[_acp_link_filename\\]').autocomplete({
		appendTo: '#memberaccess_link_filename_wrap',
		delay: 150,
		html: true,
		minLength: 1,
		source: premise_member_file_list
	});
	
	var premise_file_list_autosuggest_index = 0;
	jQuery('#accesspress_link_meta\\[_acp_link_filename\\]').keyup(function(event){
		if (event.keyCode == jQuery.ui.keyCode.UP && premise_file_list_autosuggest_index > 0) {
			premise_file_list_autosuggest_index--;
		} else if (event.keyCode == jQuery.ui.keyCode.DOWN && premise_file_list_autosuggest_index < jQuery('#memberaccess_link_filename_wrap > ul.ui-autocomplete > li').length) {
			premise_file_list_autosuggest_index++;
		} else {
			premise_file_list_autosuggest_index = 0;
		}
		jQuery('#memberaccess_link_filename_wrap > ul.ui-autocomplete > li').removeClass('current');
		if (premise_file_list_autosuggest_index > 0)
			jQuery('#memberaccess_link_filename_wrap > ul.ui-autocomplete li:nth-child(' + premise_file_list_autosuggest_index + ')').addClass('current');
	});

	// file uploader
	jQuery('.premise_links_new_file_wrap').show();
	jQuery('#accesspress_link_meta\\[_acp_link_new_file\\]').click(function(){
		jQuery(this).parents('form').attr('enctype','multipart/form-data');
	});
});
</script>

					<input type="text" placeholder="" id="accesspress_link_meta[_acp_link_filename]" autocomplete="off" name="accesspress_link_meta[_acp_link_filename]" value="<?php accesspress_custom_field( '_acp_link_filename' ); ?>" class="large-text ui-autocomplete-input" role="textbox" aria-autocomplete="list" aria-haspopup="true"/>
					<br />
				</div>
				</td>
			</tr>

			<tr class="premise_links_new_file_wrap">
				<th scope="row">
					<label for="accesspress_link_meta[_acp_link_new_file]"><?php _e( 'Upload new file', 'premise' ); ?></label>
				</th>
				
				<td>
					<input type="file" name="accesspress_link_meta_new_file" id="accesspress_link_meta[_acp_link_new_file]" />
				</td>
			<tr>
				<th scope="row">
					<label for="accesspress_link_meta[_acp_link_delay]"><?php _e( 'Delay Access', 'premise' ); ?></label>
				</th>
				
				<td>
					<input type="text" placeholder="" id="accesspress_link_meta[_acp_link_delay]" autocomplete="off" name="accesspress_link_meta[_acp_link_delay]" value="<?php accesspress_custom_field( '_acp_link_delay' ); ?>" class="small-text ui-autocomplete-input" role="textbox" aria-autocomplete="list" aria-haspopup="true"/> <?php _e( 'Days', 'premise' ); ?>
					<p><span class="description"><?php _e( 'Delay access to this file by X days after signup.', 'premise' ); ?></span></p>
				</td>
			</tr>
			
			<?php do_action( 'premise_memberaccess_link_details_metabox_rows' ); ?>
			
		</tbody>
		</table>

	<?php
	}
	/**
	 * Save the form data from the metaboxes
	 */
	function metabox_save( $post_id, $post ) {

		/**	Verify the nonce */
		if ( ! isset( $_POST['accesspress-link-nonce'] ) || ! wp_verify_nonce( $_POST['accesspress-link-nonce'], plugin_basename( __FILE__ ) ) )
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
		if ( 'revision' == $post->post_type )
			return;

		/** Merge defaults with user submission */
		$defaults = apply_filters( 'premise_memberaccess_default_link_meta', array(
			'_acp_link_id'       => md5( time() ),
			'_acp_link_filename' => '',
			'_acp_link_delay'    => 0,
		) );

		$values = wp_parse_args( $_POST['accesspress_link_meta'], $defaults );

		/** Sanitize */
		$values = $this->sanitize( $values );

		// handle uploaded file
		if ( ! empty( $_FILES['accesspress_link_meta_new_file'] ) ) {

			$uploads = wp_upload_dir();
			$member_path = trailingslashit( $uploads['basedir'] ) . trailingslashit( accesspress_get_option( 'uploads_dir' ) );
			$real_file_name = $_FILES['accesspress_link_meta_new_file']['name'];

			// check for & avoid collision with existing file
			$count = 2;
			while( is_file( $member_path . $real_file_name ) ) {

				if ( ! isset( $reverse_name ) ) {

					$reverse_name = strrev( $real_file_name );
					$extension = '';
					$base_name = $real_file_name;
					$index = strpos( $reverse_name, '.' );

					if ( $index !== false ) {

						$extension = strrev( substr( $reverse_name, 0, $index + 1 ) );
						$base_name = strrev( substr( $reverse_name, $index + 1 ) );
					}
				}

				$real_file_name = $base_name . '-' . $count++ . $extension;

			}

			// move the uploaded file & set _acp_link_filename
			move_uploaded_file( $_FILES['accesspress_link_meta_new_file']['tmp_name'], $member_path . $real_file_name );
			$values['_acp_link_filename'] = $real_file_name;

		}

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
			'filename'     => __( 'Filename', 'premise' ),
			'access_level' => __( 'Access Levels', 'premise' ),
			'uri'          => __( 'Link URI' )
		);

		return array_merge( $columns, $new_columns );

	}

	/**
	 * Filter the data that shows up in the columns in the "Orders" screen, define our own.
	 */
	function columns_data( $column ) {

		global $post;

		if ( 'acp-links' != $post->post_type )
			return;

		switch( $column ) {
			case 'filename':
				echo get_post_meta( $post->ID, '_acp_link_filename', true );
				break;
			case 'uri' :
				echo make_clickable( esc_url_raw( sprintf( home_url( '/?download_id=%s' ), get_post_meta( $post->ID, '_acp_link_id', true ) ) ) );
				break;
			case "access_level":
				echo memberaccess_get_accesslevel_list( $post->ID );
				break;
		}

	}
	function enqueue_admin_css() {

		global $typenow;

		if( $typenow == 'acp-links' )
			wp_enqueue_style( 'premise-admin', PREMISE_RESOURCES_URL . 'premise-admin.css', array( 'thickbox' ), PREMISE_VERSION );

	}
	/**
	 * custom messages for the coupon post type
	 *
	 * @since 2.2.0
	 *
	 * @returns array
	 */
	function post_updated_messages( $messages ) {
		$messages['acp-links'] = array(
			 1 => __( 'Link updated.', 'premise' ),
			 4 => __('Link updated.', 'premise' ),
			 6 => __( 'Link published.', 'premise' ),
			 7 => __( 'Link saved.', 'premise' ),
		);
		return $messages;
	}
	/**
	 * Use this function to sanitize an array of values before storing.
	 *
	 * @todo a bit more thorough sanitization
	 */
	function sanitize( $values = array() ) {

		return (array) $values;

	}

	function scripts( $hook ) {

		global $post;

		// only enqueue scripts on the order edit screen
		if ( ! isset( $post ) || $post->post_type != 'acp-links' || ( $hook != 'post.php' && $hook != 'post-new.php' ) )
			return;

		wp_enqueue_script( 'accesspress-editor', PREMISE_RESOURCES_URL . 'editor.js', array( 'jquery', ), PREMISE_VERSION, true );
		
		// jQuery Autocomplete - Prevent collision with WordPress SEO & Scribe
		if ( ! wp_script_is( 'jquery-ui-autocomplete' ) )
			wp_enqueue_script( 'jquery-ui-autocomplete', PREMISE_RESOURCES_URL . 'jquery-ui-autocomplete.min.js', array( 'jquery', 'jquery-ui-core' ), PREMISE_VERSION, true );

		wp_enqueue_script( 'jquery-ui-autocomplete-html', PREMISE_RESOURCES_URL . 'jquery-ui-autocomplete-html.js', array( 'jquery-ui-autocomplete' ), PREMISE_VERSION, true );

	}
	/**
	 * 
	 */
	public function process_link() {
		
		if ( ! isset( $_REQUEST['download_id'] ) )
			return;
		
		$links = get_posts( array( 'post_type' => 'acp-links', 'meta_key' => '_acp_link_id', 'meta_value' => $_REQUEST['download_id'] ) );
		
		/** If invalid link ID, die. */
		if ( ! $links )
			$this->process_link_error( 0, '404 File not found', __( 'Not a valid Download ID.', 'premise' ) );
		
		/** If user isn't logged in, redirect to login page */
		if ( ! is_user_logged_in() ) {
	
			$redirect_back_to = esc_url_raw( home_url( sprintf( '/?download_id=%s', $_REQUEST['download_id'] ) ) );
			$redirect = add_query_arg( 'redirect_to', $redirect_back_to, get_permalink( accesspress_get_option( 'login_page' ) ) );

			do_action( 'premise_member_link_redirect', $links, $redirect );
			wp_redirect( $redirect );
			exit;
	
		}
		
		/** First link in the array */	
		$link = $links[0];
		
		/** Start with determining if they are a site admin */
		$access = current_user_can( 'manage_options' );

		if ( ! $access ) {

			/** Get all access levels assigned to this link */
			$access_levels = wp_get_post_terms( $link->ID, 'acp-access-level' );
		
			foreach ( (array) $access_levels as $access_level ) {
	
				if ( member_has_access_level( $access_level->slug, 0, get_post_meta( $link->ID, '_acp_link_delay', true ) ) ) {
	
					$access = true;
					break;

				}
			}
		}
		
		/** If they don't have access, deny. */
		if ( ! $access )
			$this->process_link_error( $link, '403 Forbidden', __( 'You do not have access to that file.', 'premise' ) );
		
		/** Upload directory location */
		$upload_dir = wp_upload_dir();
		
		/** Build the full path to the file */
		$file = trailingslashit( $upload_dir['basedir'] . '/' . trim( accesspress_get_option( 'uploads_dir' ), '/' ) ) . get_post_meta( $link->ID, '_acp_link_filename', true );
	
		/** If file doesn't exist, die */
		if ( ! file_exists( $file ) )
			$this->process_link_error( $link, '404 File not found', __( 'File not found.', 'premise' ) );

		do_action( 'premise_member_link_before', $link, $file );

		/** Deliver the file */
		$stream    = isset( $_GET['stream'] ) ? true : '';
		$file_name = basename( $file );
		$file_size = filesize( $file );
		$mime_info = wp_check_filetype_and_ext( $file, $file_name );

		if ( isset( $mime_info['type'] ) )
			header( 'Content-Type: ' . $mime_info['type'] );
		else
			header( 'Content-Type: application/octet-stream' );

		if ( $file_size )
			header( 'Content-Length: ' . $file_size );

		if ( ! isset( $mime_info['type'] ) || ! $this->is_streaming_media( $mime_info['type'] ) || ! $stream ) {

			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );

		} else {

			header('Accept-Ranges: bytes' );
			header( sprintf( 'Content-Range: bytes 0-%d/%d', $file_size - 1, $file_size ) );
			header('Content-Disposition: filename="' . $file_name . '"' );
			header('X-Pad: avoid browser bug');
			header('Cache-Control: no-cache');

		}

		readfile( $file );

		do_action( 'premise_member_link_after', $link, $file );

		exit;
		
	}

	function process_link_error( $link_id, $status, $message ) {

		do_action( 'premise_member_link_error', $link_id, $status );

		header( 'HTTP/1.1 ' . $status );
		die( $message );

	}

	function is_streaming_media( $mime_type ) {

		$streaming_mime_types = array( 
			'audio/midi',
			'audio/mpeg',
			'audio/ogg',
			'audio/x-realaudio',
			'audio/webm',
			'video/3gpp',
			'video/mp4',
			'video/mpeg',
			'video/ogg',
			'video/quicktime',
			'video/webm',
			'video/x-flv',
			'video/x-mng',
			'video/x-ms-asf',
			'video/x-ms-wmv',
			'video/x-msvideo',
		);

		$streaming_mime_types = apply_filters( 'premise_streaming_mime_types', $streaming_mime_types );

		return in_array( $mime_type, $streaming_mime_types );

	}	
}
