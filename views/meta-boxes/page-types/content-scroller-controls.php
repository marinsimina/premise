<div id="content-scroller-size-warning"><?php _e('<strong>Caution</strong>: Adding more tabs may cause the layout to break.', 'premise' ); ?></div>

<p>
	<strong><?php _e('Order:', 'premise' ); ?></strong>
	<?php _e('Drag boxes below to reorder the panels on your page.', 'premise' ); ?>
</p>


<ul id="premise-content-scrollers-order-container">
	<?php foreach($scrollers as $key => $scroller) { if(!is_array($scroller)) { continue; } ?>
	<li id="premise-content-scrollers-order-<?php echo esc_attr($key); ?>" class="premise-content-scrollers-order-item"><span><?php echo esc_html($scroller['title']); ?></span><input type="hidden" name="premise[content-scrollers-order][]" value="<?php echo $key; ?>" /></li>
	<?php } ?>
</ul>
<div class="alignright">
	<a href="#" class="premise-content-scrollers-add-another-tab button-secondary"><?php _e('Add Another Tab', 'premise' ); ?></a>
	<input type="hidden" id="premise-add-another-content-scroller-tab"  name="premise[add-another-content-scroller-tab]" value="" />
</div>
<br class="clear" />

<p id="premise-content-scroller-settings-divider"></p>

<p>
	<strong><?php _e('Options:', 'premise' ); ?></strong>
	<?php _e('Show or hide tabs and navigation arrows.', 'premise' ); ?>
</p>

<ul id="premise-content-scroller-settings">
	<li>
		<label for="premise-show-arrows-and-tabs">
			<input <?php checked($meta['show-arrows'], 'arrows-and-tabs'); checked($meta['show-arrows'], ''); ?> type="radio" id="premise-show-arrows-and-tabs" name="premise[show-arrows]" value="arrows-and-tabs" />
			<?php _e('Show Arrows and Tabs', 'premise' ); ?>
		</label>
	</li>
	<li>
		<label for="premise-show-tabs">
			<input <?php checked($meta['show-arrows'], 'tabs'); ?> type="radio" id="premise-show-tabs" name="premise[show-arrows]" value="tabs" />
			<?php _e('Just Show Tabs', 'premise' ); ?>
		</label>
	</li>
</ul>