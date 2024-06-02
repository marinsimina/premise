<img id="premise-assistant-image" src="<?php echo esc_attr($image); ?>" />
<?php $toc = $this->getAdviceSections($this->convertLandingPageToId($this->getLandingPageAdviceType($post->ID))); ?>
<div id="premise-education-actions">
	<div id="premise-education-first" class="">
		<a class="thickbox" href="<?php echo esc_url(premise_get_media_upload_src('premise-resources-education')); ?>"><?php _e('Get Copywriting Assistance', 'premise' ); ?></a>
	</div>
	<div id="premise-education-toggle"><br /></div>
	<div id="premise-education-inside" style="width: 144px; display: none;" class="slideUp">
		<?php foreach((array)$toc as $key => $info) { ?>
		<div class="premise-education-action">
			<a class="thickbox" href="<?php echo esc_url(premise_get_media_upload_src('premise-resources-education', array('section'=>$info['id']))); ?>"><?php echo esc_html($info['name']); ?></a>
		</div>
		<?php } ?>
	</div>
</div>
<br class="clear" />
<style type="text/css">
.premise-editor-quicktags {
	background-image: url("<?php echo site_url('wp-admin/images/ed-bg.gif'); ?>");
}

.premise-editor-quicktags-toolbar input {
	background: url("<?php echo site_url('wp-admin/images/fade-butt.png'); ?>") repeat-x scroll 0 -2px #FFFFFF;
}
</style>