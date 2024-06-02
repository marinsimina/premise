<?php
/**
 * AccessPress Post/Page access management.
 *
 * @package AccessPress
 */


/**
 * Handles allowing/denying access to posts/pages based on AccessPress membership level.
 *
 * @since 0.1.0
 *
 */
class AccessPress_Access_Management {

	function __construct() {

		add_post_type_support( 'post', 'premise-member-access' );
		add_post_type_support( 'page', 'premise-member-access' );
		add_post_type_support( 'landing_page', 'premise-member-access' );

		add_action( 'do_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'metabox_save' ), 1, 2 );
		add_filter( 'the_content', array( $this, 'post_content_filter' ), 1 );
		add_filter( 'the_excerpt', array( $this, 'post_content_filter' ), 1 );

	}

	function add_metabox() {

		global $typenow;

		if ( ! empty( $typenow ) && post_type_supports( $typenow, 'premise-member-access' ) )
			add_meta_box( 'accesspress-access-management-metabox', __( 'Membership Access', 'premise' ), array( $this, 'access_metabox' ), $typenow, 'side' );

	}

	function access_metabox() {
		global $post;
	?>
		<p>
			<?php _e( 'Select Protection', 'premise' ); ?>:
			<br />
			<label><input type="radio" name="accesspress_post_meta[_acp_protection]" id="accesspress_post_meta[_acp_protection]" value="" <?php checked( '' == accesspress_get_custom_field( '_acp_protection' ) ); ?> /> <?php _e( 'No Membership Required', 'premise' ); ?></label>
			<br />
			<label><input type="radio" name="accesspress_post_meta[_acp_protection]" id="accesspress_post_meta[_acp_protection][member]" value="member" <?php checked( 'member', accesspress_get_custom_field( '_acp_protection' ) ); ?> /> <?php _e( 'Has Member Access to', 'premise' ); ?></label>
			<br />
			<label><input type="radio" name="accesspress_post_meta[_acp_protection]" id="accesspress_post_meta[_acp_protection][nonmember]" value="nonmember" <?php checked( 'nonmember' == accesspress_get_custom_field( '_acp_protection' ) ); ?> /> <?php _e( 'Does not have Member Access to', 'premise' ); ?></label>
		</p>

		<p><?php _e( 'Choose the access level(s):', 'premise' ); ?></p>
		<p>
			<?php
			echo accesspress_get_access_level_checklist( array( 'name' => 'accesspress_post_meta[_acp_access_levels][]', 'selected' => get_post_meta( $post->ID, '_acp_access_levels', true ), 'style' => 'style="width: auto;"' ) );
			?>
		</p>

	<?php
	}

	/**
	 * Save the form data from the metaboxes
	 */
	function metabox_save( $post_id, $post ) {

		/**	Don't try to save the data under autosave, ajax, or future post */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		if ( defined( 'DOING_CRON' ) && DOING_CRON )
			return;

		/**	Check if user is allowed to edit this */
		if ( ! current_user_can( 'edit_post', $post->ID ) )
			return;

		/** Don't try to store data during revision save */
		if ( 'revision' == $post->post_type )
			return;
			
		$accesspress_post_meta = isset( $_POST['accesspress_post_meta'] ) ? $_POST['accesspress_post_meta'] : array();

		/** Merge defaults with user submission */
		$values = wp_parse_args( $accesspress_post_meta, array(
			'_acp_protection'                 => '',
			'_acp_access_levels'           => array(),
		) );

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
	function post_content_filter( $content ) {

		if ( ! premise_is_protected_content() )
			return $content;

		// close comments on protected content
		add_filter( 'comments_open', '__return_false', 99 );

		if ( ! is_user_logged_in() )
			return sprintf( __( 'Please <a href="%s">Log in</a> to view this content.', 'premise' ), memberaccess_login_redirect( get_permalink() ) );

		if ( premise_has_content_access() )
			return $content;

		return '';

	}
//@todo: sanitization
	function sanitize( $values ) {
		return $values;
	}
}

new AccessPress_Access_Management;