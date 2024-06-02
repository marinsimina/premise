<?php wp_nonce_field('save-premise-settings', 'save-premise-settings-nonce'); ?>

<input type="hidden" name="premise[saving]" id="premise-saving" value="1" />
<input type="hidden" name="premise-graphics-url" id="premise-graphics-url" value="<?php echo esc_attr(esc_url(premise_get_media_upload_src('premise-resources-graphics', array('send_to_premise_field_id' => '0')))); ?>" />
<input type="hidden" name="premise-optin-url" id="premise-optin-url" value="<?php echo esc_attr(esc_url(premise_get_media_upload_src('premise-resources-optin', array('send_to_premise_field_id' => '0')))); ?>" />
<input type="hidden" name="premise-buttons-url" id="premise-buttons-url" value="<?php echo esc_attr(esc_url(premise_get_media_upload_src('premise-button-usage', array('send_to_premise_field_id' => '0')))); ?>" />

<span id="premise-landing-page-type-name" style="display:none;"><small> (<?php echo esc_html( $type ); ?>)</small></span>

<div class="premise-option-box">
	<h4><label for="premise-subhead"><?php _e('Subheading', 'premise' ); ?></label></h4>
	<p><?php _e('Provide a great subheading to really pull your readers in.', 'premise' ); ?></p>
	<input tabindex="2" type="text" class="large-text subheading widget-inside" name="premise[subhead]" id="premise-subhead" value="<?php echo esc_attr($meta['subhead']); ?>" />

	<div id="subheadingwrap" class="editwidget">
		<label id="subheading-prompt-text" class="hide-if-no-js" for="premise-subhead" style=""><?php _e('Enter sub-head here', 'premise' ); ?></label>
		
	</div>
</div>

<?php if ( $premise_base->use_premise_theme() ) { ?>
<div class="premise-option-box">
	<h4><?php _e('Landing Page Style', 'premise' ); ?></h4>
	<p><?php _e('You can choose which of the preconfigured styles you wish to use for this landing page.', 'premise' ); ?></p>
	<select name="premise[style]" id="premise-style">
		<?php foreach( $Premise->getDesignSettings() as $key => $style ) { ?>
		<option <?php selected( $meta['style'], $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $style['premise_style_title'] ); ?></option>
		<?php } ?>
	</select>
</div>
<?php } else { ?>
<input type="hidden" name="premise[style]" value="<?php echo esc_attr( $meta['style'] ); ?>" />
<?php } ?>
<div class="premise-option-box">
	<h4><label for="premise-favicon"><?php _e('Favicon', 'premise' ); ?></label></h4>
		<p><?php printf(__('Enter a URL that points at the favicon you wish to use for this landing page.  You can upload one via the <a class="thickbox" href="%s">WordPress uploader</a>.', 'premise' ), esc_attr(add_query_arg(array('post_id' => 0, 'send_to_premise_field_id'=>'premise-favicon', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image'))))); ?></p>
		<p><?php printf(__('<strong>Note:</strong> If you leave this field blank but have entered a default favicon on the <a href="%s" target="_blank">main settings</a> page, that icon will be used for this landing page.', 'premise' ), admin_url('admin.php?page=premise-main')); ?></p>
	
	<input type="text" class="large-text" name="premise[favicon]" id="premise-favicon" value="<?php echo esc_attr($meta['favicon']); ?>" /><br />
</div>

<div class="premise-option-box">
	<h4><?php _e('Header Display', 'premise' ); ?></h4>
	<p><?php _e('You can choose whether or not to show the header (both main and subheadlines) for this landing page.  Remove it by checking the box.', 'premise' ); ?></p>
	<ul>
		<li>
			<label>
				<input <?php checked( 1, $meta['header'] ); ?> type="checkbox" name="premise[header]" id="premise-header" value="1" />
				<?php _e( 'Remove the main headlines area from this landing page', 'premise' ); ?>
			</label>
		</li>
		<li>
			<label>
				<input <?php checked( 1, $meta['header-image-hide'] ); ?> type="checkbox" name="premise[header-image-hide]" id="premise-header-image-hide" value="1" />
				<?php _e( 'Remove the header image from this landing page', 'premise' ); ?>
			</label>
		</li>
	</ul>

	<div class="premise-dependent-container premise-header-image-hide-dependent-container">
		<h4><label for="premise-header-image"><?php _e('Header Image', 'premise' ); ?></label></h4>
		<p><?php printf(__('Enter the URL of the image you wish to use in the header of your landing page.  If you don\'t have an image handy, upload one via the <a class="thickbox" href="%s">WordPress uploader</a>.'), esc_attr(add_query_arg(array('post_id' => 0, 'send_to_premise_field_id'=>'premise-header-image', 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image'))))); ?></p>
		<p><?php printf(__('<strong>Note:</strong> If you leave this field blank but have entered a default image on the <a href="%s" target="_blank">main settings</a> page, that image will be used for this landing page.'), admin_url('admin.php?page=premise-main')); ?></p>
		<input type="text" class="large-text" name="premise[header-image]" id="premise-header-image" value="<?php echo esc_attr( $meta['header-image'] ); ?>" /><br /><br />

		<h4><label for="premise-header-image-alt"><?php _e('Header Image Alternate Text', 'premise' ); ?></label></h4>
		<p><?php _e( '(Enter the alternate text you would like for the header image.', 'premise' ); ?></p>
		<p><?php printf(__('<strong>Note:</strong> If you leave this field blank but have entered a default Alternate Text on the <a href="%s" target="_blank">main settings</a> page, that Alternate Text will be used for this landing page.'), admin_url('admin.php?page=premise-main')); ?></p>
		<input type="text" class="large-text" name="premise[header-image-alt]" id="premise-header-image-alt" value="<?php echo esc_attr( $meta['header-image-alt'] ); ?>" /><br />

		<h4><label for="premise-header-image-url"><?php _e('Header Image URL', 'premise' ); ?></label></h4>
		<p><?php _e( 'Enter a URL that points to the website you wish the header image to link to.', 'premise' ); ?></p>
		<p><?php printf(__('<strong>Note:</strong> If you leave this field blank but have entered a default URL on the <a href="%s" target="_blank">main settings</a> page, that URL will be used for this landing page.'), admin_url('admin.php?page=premise-main')); ?></p>
		<input type="text" class="large-text" name="premise[header-image-url]" id="premise-header-image-url" value="<?php echo esc_attr( $meta['header-image-url'] ); ?>" /><br />
	</div>
	<ul>
		<li>
			<label>
				<input <?php checked( 1, $meta['header-image-new'] ); ?> type="checkbox" name="premise[header-image-new]" id="premise-header-image-new" value="1" />
				<?php _e( 'Open the Header Image Link in a new window', 'premise' ); ?>
			</label>
		</li>
	</ul>

</div>



<div class="premise-option-box">
	<h4><?php _e('Footer Display', 'premise' ); ?></h4>
	<p><?php _e('You can choose whether or not to show the footer (with text) for this landing page.  The footer is displayed by default, but you can remove it by checking the box.', 'premise' ); ?></p>
	<ul>
		<li>
			<label for="premise-footer">
				<input <?php checked(1, $meta['footer']); ?> type="checkbox" name="premise[footer]" id="premise-footer" value="1" />
				<?php _e('Remove the footer from this landing page', 'premise' ); ?>
			</label>
		</li>
	</ul>

	<div class="premise-dependent-container premise-footer-dependent-container">
		<h4><label for="premise-footer-copy"><?php _e('Footer Copy', 'premise' ); ?></label></h4>
		<p><?php _e('Enter a tagline that will appear in the footer of your landing page.', 'premise' ); ?></p>
		<input type="text" class="large-text" name="premise[footer-copy]" id="premise-footer-copy" value="<?php echo esc_attr($meta['footer-copy']); ?>" />
	</div>

</div>

<div class="premise-option-box">
	<h4><?php _e('Scripts', 'premise' ); ?></h4>
	<p><?php _e('Premise allows you to add content to either the header or footer.  Insert some code into the textareas below to make it appear on this particular landing page only.  These fields are meant for adding JavaScript, tracking codes and CSS, not content.'); ?></p>
	
	<div>
		<h4><label for="premise-header-scripts"><?php _e('Header Scripts', 'premise' ); ?></label></h4>
		<textarea rows="6" class="large-text code" name="premise[header-scripts]" id="premise-header-scripts"><?php echo esc_html($meta['header-scripts']); ?></textarea>
	</div>
	
	<div>
		<h4><label for="premise-footer-scripts"><?php _e('Footer Scripts', 'premise' ); ?></label></h4>
		<textarea rows="6" class="large-text code" name="premise[footer-scripts]" id="premise-footer-scripts"><?php echo esc_html($meta['footer-scripts']); ?></textarea>
	</div>
</div>

