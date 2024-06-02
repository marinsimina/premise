<div class="premise-option-box">
<?php if( $premise_base->hasSharedPage( $post->ID ) ) { ?>
	<h4><?php _e('You have shared this page', 'premise' ); ?></h4>
	<p><?php printf( __( '<a href="%s">Clear my share history</a> for this page.', 'premise' ), wp_nonce_url( add_query_arg( array( 'clear-share-ID' => $post->ID ), admin_url( 'post.php' ) ), 'clear-share-id' ) ); ?></p>
<?php } else { ?>
	<h4><?php _e('You have not shared this page', 'premise' ); ?></h4>
<?php } ?>
</div>

<div class="premise-option-box">
	<h4><label for="premise-sharing-message"><?php _e('Sharing Message', 'premise' ); ?></label></h4>
	<p><?php _e('Enter a message that your users should see that prompts them to tweet or share your page to see the rest of the content. If you don\'t enter anything, a default message will be used.', 'premise' ); ?></p>

	<input type="text" class="large-text" name="premise[sharing-message]" id="premise-sharing-message" value="<?php echo esc_attr($meta['sharing-message']); ?>" />
</div>

<div class="premise-option-box">
	<h4><label for="premise-twitter-share-text"><?php _e('Share Text', 'premise' ); ?></label></h4>
	<p><?php _e('Enter the text you would like to be used when sharing your page. If you don\'t enter anything, the title of the page will be used.  The URL to the page will always be appended where appropriate.', 'premise' ); ?></p>

	<input type="text" class="large-text" name="premise[twitter-share-text]" id="premise-twitter-share-text" value="<?php echo esc_attr($meta['twitter-share-text']); ?>" />
</div>

<div class="premise-option-box">
	<h4><label for="premise-share-method"><?php _e('Share Method', 'premise' ); ?></label></h4>
	<select name="premise[share-method]" id="premise-share-method">
		<option value=""><?php _e( 'Twitter or Facebook', 'premise' ); ?></option>
		<option value="facebook" <?php selected( $meta['share-method'], 'facebook' ); ?>><?php _e( 'Facebook', 'premise' ); ?></option>
		<option value="twitter" <?php selected( $meta['share-method'], 'twitter' ); ?>><?php _e( 'Twitter', 'premise' ); ?></option>
	</select>
</div>

<div class="premise-option-box">
	<h4><label for="premise-twitter-share-button"><?php _e('Twitter Icon', 'premise' ); ?></label></h4>
	<p><?php printf(__('Enter a URL that points at the share with Twitter button you want to use.  Leave this blank to use the default button. You can upload one via the <a class="thickbox" href="%s">WordPress uploader</a>.', 'premise' ), esc_url(add_query_arg(array('post_id' => 0, 'send_to_premise_field_id'=>'premise-twitter-share-button', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image'))))); ?></p>

	<input type="text" class="large-text" name="premise[twitter-share-button]" id="premise-twitter-share-button" value="<?php echo esc_attr($meta['twitter-share-button']); ?>" />
</div>

<div class="premise-option-box">
	<h4><label for="premise-facebook-share-button"><?php _e('Facebook Icon', 'premise' ); ?></label></h4>
	<p><?php printf(__('Enter a URL that points at the share with Facebook button you want to use.  Leave this blank to use the default button. You can upload one via the <a class="thickbox" href="%s">WordPress uploader</a>.', 'premise' ), esc_url(add_query_arg(array('post_id' => 0, 'send_to_premise_field_id'=>'premise-twitter-share-button', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image'))))); ?></p>

	<input type="text" class="large-text" name="premise[facebook-share-button]" id="premise-facebook-share-button" value="<?php echo esc_attr($meta['facebook-share-button']); ?>" />
</div>