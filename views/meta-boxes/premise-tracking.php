<?php $trackingSettings = $settings['tracking']; ?>

<div class="premise-option-box">
	<h4><?php _e('Duplicate Page', 'premise' ); ?></h4>
	<p><?php _e('Do you want to duplicate this page and all associated meta so that you can A/B test two versions?', 'premise' ); ?></p>
	<input class="button button-primary" type="submit" name="premise-duplicate-page" id="premise-duplicate-page" value="<?php _e('Duplicate!', 'premise' ); ?>" />
</div>

<h4><?php _e('Visual Website Optimizer', 'premise' ); ?></h4>
<p><?php printf(__('To use VWO on this page, make sure you have your Visual Website Optimizer Account ID entered on the <a target="_blank" href="%s">Main Settings</a> page, then log into your VWO account to set up and conduct your tests.', 'premise' ), admin_url('admin.php?page=premise-main')); ?>

<h4><?php _e('Google Content Experiments', 'premise' ); ?></h4>
<?php if ( empty( $trackingSettings['account-id'] ) ) { ?>

<p><?php printf(__('You must use a Google Analytics plugin or <a target="_blank" href="%s">setup</a> your Google Analytics account ID before enabling tracking on this landing page.', 'premise' ), admin_url('admin.php?page=premise-main#premise-tracking')); ?></p>

<?php } ?>

<p><?php _e('You can use Google Content Experiments to track your conversions and test variations of this landing page. You have to set up a few options before it is ready to go.', 'premise' ); ?></p>

<p><?php _e('<strong>Note:</strong> You must publish your page before Google can validate your page for testing. GCE will not be able to see your page when it is set as Draft or Private. Once published, you can validate and set up your experiments in your Google Analytics account.'); ?></p>

<div class="premise-option-box">
	<h4><?php _e('Enable Content Experiments', 'premise' ); ?></h4>
	<p><?php _e('Content Experiments support for this page is disabled by default.  To turn it on, simply check the box.', 'premise' ); ?></p>
	<ul>
		<li>
			<label>
				<input <?php checked( 1, $tracking['enable-gce'] ); ?> type="checkbox" name="premise-tracking[enable-gce]" id="premise-tracking-enable-gce" value="1" />
				<?php _e('Enable Content Experiments for this page?', 'premise' ); ?>
			</label>
		</li>
	</ul>
</div>

<div id="premise-tracking-enabled-options">
	<div class="premise-option-box">
		<h4><label for="premise-tracking-test-id"><?php _e('Google Content Experiment Test ID', 'premise' ); ?></label></h4>
		<p><?php _e('If this is the original page enter your experiment tracking ID or enter your test tracking code and we\'ll parse the ID for you.', 'premise' ); ?></p>
		<input class="large-text code" name="premise-tracking[test-id]" id="premise-tracking-test-id" value="<?php echo esc_attr($tracking['test-id']); ?>" />
		<p>
			<strong><?php _e('Recommended:', 'premise' ); ?></strong>
			<?php _e('If you\'re not sure how to find your test ID, just copy the entire test tracking code that Google gives you into the input above.  Premise will sort everything out.', 'premise' ); ?>
		</p>
	</div>

</div>
<?php 

wp_nonce_field('save-premise-tracking-settings', 'save-premise-tracking-settings-nonce');

