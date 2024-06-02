<?php
global $_premise_settings_pagehook, $screen_layout_columns;
if( $screen_layout_columns == 3 ) {
	$width = "width: 32.67%";
}
elseif( $screen_layout_columns == 2 ) {
	$width = "width: 49%;";
	$hide3 = " display: none;";
}
else {
	$width = "width: 99%;";
	$hide2 = $hide3 = " display: none;";
}
$design_key = isset( $_GET['premise-design-key'] ) ? $_GET['premise-design-key'] : null;
?>

<div id="design-settings" class="wrap premise-metaboxes">
	<?php if( isset( $_GET['reset'] ) && $_GET['reset'] == 'true' ) { ?>
	<div id="premise-design-settings-reset" class="updated fade"><p><strong><?php _e( 'Settings Reset.', 'premise' ); ?></strong></p></div>
	<?php } elseif( isset( $_GET['updated'] ) && $_GET['updated'] == 'true' ) { ?>
	<div id="premise-design-settings-update" class="updated fade"><p><strong><?php _e( 'Settings Saved.', 'premise' ); ?></strong></p></div>
	<?php } ?>
	<form id="design-settings-form" method="post" action="<?php echo admin_url( 'admin.php?page=premise-design' ); ?>">
		<?php
		wp_nonce_field('save-premise-design-settings', 'save-premise-design-settings-nonce');
		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
		settings_fields( $this->_option_DesignSettings ); // important!
		$style = $this->getConfiguredStyle( $design_key );
		?>
		<h3 id="top-buttons">
			<?php 
			if( $this->isValidStyleKey( $design_key ) ) {
				$title = $style['premise_style_title'];
			} else {
				 $title = __( 'Default', 'premise' ); 
			} 
			?>
			<span id="editing-style-name"><?php echo esc_html( $title ); ?></span>
			<input type="submit" class="button-primary" name="save-premise-design-settings" value="<?php _e( 'Save Settings', 'premise' ); ?>" />
			<input type="submit" class="button-highlighted button-reset" name="premise-design[reset]" value="<?php _e( 'Reset Settings', 'premise' ); ?>" />
		</h3>

		<div class="metabox-holder">
			<div class="postbox-container" style="<?php echo $width; ?>">
				<?php do_meta_boxes( $this->_cached_DesignPageSlug, 'column1', null ); ?>
			</div>
			<div class="postbox-container" style="<?php echo $width; ?>">
				<?php do_meta_boxes( $this->_cached_DesignPageSlug, 'column2', null ); ?>
			</div>

			<div class="clear"></div>
		</div>

		<div class="bottom-buttons">
			<input type="submit" class="button-primary" name="save-premise-design-settings" value="<?php _e('Save Settings', 'premise' ); ?>" />
			<input type="submit" class="button-highlighted button-reset" name="premise-design[reset]" value="<?php _e('Reset Settings', 'premise' ); ?>" />
		</div>

	</form>
</div>
