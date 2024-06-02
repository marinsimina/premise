<p><?php _e('Copy that is displayed next to the video (or leave blank for a centered video with no copy.', 'premise' ); ?></p>
<?php premise_the_editor($meta['video-copy'], 'premise[video-copy]', '', true, 3); ?>

<div class="premise-option-box">
	<h4><?php _e('Placement', 'premise' ); ?></h4>
	<p><?php _e('Where do you want your video in relation to your copy?', 'premise' ); ?></p>
	<ul id="premise-video-placement-choices">
		<li>
			<label for="premise-video-placement-left">
				<img src="<?php echo $resourcesUrl; ?>images/video-left.png" alt="Video Left Placement" /><br />
				<input type="radio" <?php checked(in_array($meta['video-placement'], array('left', '')), true); ?> name="premise[video-placement]" id="premise-video-placement-left" value="left" />
				<?php _e('Left of Copy', 'premise' ); ?>
			</label>
		</li>
		<li>
			<label for="premise-video-placement-center">
				<img src="<?php echo $resourcesUrl; ?>images/video-center.png" alt="Video center Placement" /><br />
				<input type="radio" <?php checked(in_array($meta['video-placement'], array('center')), true); ?> name="premise[video-placement]" id="premise-video-placement-center" value="center" />
				<?php _e('Center No Copy', 'premise' ); ?>
			</label>
		</li>
		<li>
			<label for="premise-video-placement-right">
				<img src="<?php echo $resourcesUrl; ?>images/video-right.png" alt="Video right Placement" /><br />
				<input type="radio" <?php checked($meta['video-placement'], 'right'); ?> name="premise[video-placement]" id="premise-video-placement-right" value="right" />
				<?php _e('Right of Copy', 'premise' ); ?>
			</label>
		</li>
	</ul>

	<br class="clear" />
</div>

<div class="premise-option-box">
	<h4><label for="premise-video-image"><?php _e('Video Box Image', 'premise' ); ?></label></h4>
	<p><?php _e('Add the URL of an image to be placed in the video box as the link to the video lightbox.  These fields are required if you want the video to play in a lightbox and strongly recommended.', 'premise' ); ?></p>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="premise-video-image"><strong><?php _e('Image URL', 'premise' ); ?></strong></label></th>
				<td>
					<input type="text" class="regular-text code" value="<?php echo esc_attr($meta['video-image']); ?>" name="premise[video-image]" id="premise-video-image" /><br />
					<small><?php printf(__('If you don\'t have an image handy, upload one via the <a class="thickbox" href="%s">WordPress uploader</a>.', 'premise' ), esc_attr(add_query_arg(array('post_id' => 0, 'send_to_premise_field_id'=>'premise-video-image', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image'))))); ?></small>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="premise-video-image-title"><strong><?php _e('Image Title', 'premise' ); ?></strong></label></th>
				<td>
					<input type="text" class="regular-text" value="<?php echo esc_attr($meta['video-image-title']); ?>" name="premise[video-image-title]" id="premise-video-image-title" />
				</td>
			</tr>
		</tbody>
	</table>
</div>

<div class="premise-option-box">
	<h4><label for="premise-video-embed-code"><?php _e('Video Embed Code', 'premise' ); ?></label></h4>
	<p><?php _e('Add the embed code for your promotional video here.  Use any video provider you want, such as Youtube, Vimeo, or Ustream.', 'premise' ); ?></p>
	<textarea rows="4" class="large-text code" name="premise[video-embed-code]" id="premise-video-embed-code"><?php echo esc_html($meta['video-embed-code']); ?></textarea>
</div>