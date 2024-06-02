<p>
	<strong><?php _e('Order:', 'premise' ); ?></strong>
	<?php _e('Drag boxes below to reorder the panels on your page.', 'premise' ); ?>
</p>

<ul id="premise-pricing-order-container">
	<?php foreach($columns as $key => $column) { if(!is_array($column)) { continue; } ?>
	<li id="premise-pricing-order-<?php echo esc_attr($key); ?>" class="premise-pricing-order-item premise-content-scrollers-order-item"><span><?php echo esc_html($column['title']); ?></span><input type="hidden" name="premise[pricing-order][]" value="<?php echo $key; ?>" /></li>
	<?php } ?>
</ul>
<div class="alignright">
	<a href="#" class="premise-pricing-add-another-tab button-secondary"><?php _e('Add Another Column', 'premise' ); ?></a>
</div>
<br class="clear" />

<p id="premise-pricing-settings-divider"></p>

<h4><?php _e('Choose Bullet Type', 'premise' ); ?></h4>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row"><label for="premise-pricing-bullets"><?php _e('Marker', 'premise' ); ?></label></th>
			<td>
				<select name="premise[pricing-bullets]" id="premise-pricing-bullets">
					<?php foreach(array('None', 'Default', 'Checkmark', 'Star', 'Arrow') as $bullets) { ?>
					<option <?php selected($bullets, $meta['pricing-bullets']); ?> value="<?php echo esc_attr($bullets); ?>"><?php echo esc_html($bullets); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="premise-pricing-bullets-color"><?php _e('Color', 'premise' ); ?></label></th>
			<td>
				<select name="premise[pricing-bullets-color]" id="premise-pricing-bullets-color">
					<?php foreach(array('Yellow', 'Red', 'Green', 'Blue') as $color) { ?>
					<option <?php selected($color, $meta['pricing-bullets-color']); ?> value="<?php echo esc_attr($color); ?>"><?php echo esc_html($color); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
	</tbody>
</table>