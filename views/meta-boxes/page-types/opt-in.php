<p><?php _e('Copy that is displayed next to the opt in form.', 'premise' ); ?></p>
<?php premise_the_editor($meta['optin-copy'], 'premise[optin-copy]', '', true, 4); ?>

<div class="premise-option-box">
	<h4><?php _e('Placement', 'premise' ); ?></h4>
	<p><?php _e('Where do you want your opt in form in relation to your copy?', 'premise' ); ?></p>
	<ul id="premise-video-placement-choices">
		<li>
			<label for="premise-optin-placement-left">
				<img src="<?php echo $resourcesUrl; ?>images/optin-left.png" alt="Opt In Left Placement" /><br />
				<input type="radio" <?php checked(in_array($meta['optin-placement'], array('left', '')), true); ?> name="premise[optin-placement]" id="premise-optin-placement-left" value="left" />
				<?php _e('Left of Copy', 'premise' ); ?>
			</label>
		</li>
		<li>
			<label for="premise-optin-placement-right">
				<img src="<?php echo $resourcesUrl; ?>images/optin-right.png" alt="Opt In right Placement" /><br />
				<input type="radio" <?php checked($meta['optin-placement'], 'right'); ?> name="premise[optin-placement]" id="premise-optin-placement-right" value="right" />
				<?php _e('Right of Copy', 'premise' ); ?>
			</label>
		</li>
	</ul>

	<br class="clear" />
</div>

<div class="premise-option-box">
	<h4><label for="premise-optin-form-code"><?php _e('Opt In Form Code', 'premise' ); ?></label></h4>
	<p><?php printf(__('You need to provide the code your mailing list provider supplied you with for this campaign.  Common mailing list providers are Constant Contact, Aweber and MailChimp.  <a class="thickbox" href="%s">Use the Premise opt in provider integration</a>.', 'premise' ), esc_url(premise_get_media_upload_src('premise-resources-optin', array('send_to_premise_field_id' => 'premise-optin-form-code')))); ?></p>
	<textarea rows="6" class="large-text code" name="premise[optin-form-code]" id="premise-optin-form-code"><?php echo esc_html($meta['optin-form-code']); ?></textarea>
</div>

<?php do_action( 'premise_optin_metabox_after_placement', $meta ); ?>
