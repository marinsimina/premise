<?php
/**
 * The $column variable is an array containing the keys:
 * - title
 * - attributes
 */ 
$newwindow = ''; // have to do this to prevent overlap from previous iterations (should change scoping, but too much work right now) 
$calltext = $callurl = '';
extract($column);
?>
<div class="postbox premise-pricing-postbox" id="premise-pricing-<?php echo $key; ?>">
    <div title="Click to toggle" class="handlediv"><br></div>
    <h3 class="hndle"><span><span class="tab-name"><?php echo esc_html($title); ?></span> <span class="tab-description">(Pricing Column)</span></span></h3>
    <div class="inside">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="premise-pricing-columns-<?php echo $key; ?>-title"><?php _e('Price Column Header', 'premise' ); ?></label>
                    </th>
                    <td>
                        <input class="regular-text premise-pricing-title" type="text" name="premise[pricing-columns][<?php echo $key; ?>][title]" id="premise-pricing-columns-<?php echo $key; ?>-title" value="<?php echo esc_attr($title); ?>" />
                        <a href="#" class="premise-pricing-delete-column"><?php _e('Delete This Column', 'premise' ); ?></a>
                    </td>
                </tr>
                <tr>
                	<td colspan="2">
	                	<ul class="premise-pricing-attributes-container">
			        		<?php foreach((array)$attributes as $akey => $attribute) { ?>
				        	<li class="premise-pricing-attribute-container">
				        		<span class="premise-pricing-attribute-handle"></span>
				        		<input type="text" class="regular-text premise-pricing-attribute-input" name="premise[pricing-columns][<?php echo $key; ?>][attributes][]" value="<?php echo esc_attr($attribute); ?>" />
				        		<a href="#" class="remove-attribute-from-pricing-column">x</a>
				        	</li>
			        		<?php } ?>
			        		<li class="premise-pricing-attribute-template premise-pricing-attribute-container" style="display: none;">
			        			<span class="premise-pricing-attribute-handle"></span>
				        		<input type="text" class="regular-text premise-pricing-attribute-input" name="premise[pricing-columns][<?php echo $key; ?>][attributes][]" value="" />
				        		<a href="#" class="remove-attribute-from-pricing-column">x</a>
			        		</li>
				        </ul>
                	</td>
                </tr>
            </tbody>
        </table>

        <div style="width: 550px;">
        	<div class="alignright">
        		<a href="#" class="premise-pricing-add-another-attribute button button-secondary"><?php _e('Add Another', 'premise' ); ?></a>
        		<br class="clear" />
        	</div>
        	<br class="clear" />
        </div>
        <br class="clear" />

        <h4><?php _e('Call to Action', 'premise' ); ?></h4>
        <table class="form-table">
        	<tbody>
        		<tr>
        			<th scope="row"><label for="premise-pricing-columns-<?php echo $key; ?>-calltext"><?php _e('Text', 'premise' ); ?></label></th>
        			<td>
        				<input class="regular-text premise-pricing-cta-text" type="text" name="premise[pricing-columns][<?php echo $key; ?>][calltext]" id="premise-pricing-columns-<?php echo $key; ?>-calltext" value="<?php echo esc_attr($calltext); ?>" />
        			</td>
        		</tr>
        		<tr>
        			<th scope="row"><label for="premise-pricing-columns-<?php echo $key; ?>-callurl"><?php _e('URL', 'premise' ); ?></label></th>
        			<td>
        				<input class="regular-text premise-pricing-cta-url" type="text" name="premise[pricing-columns][<?php echo $key; ?>][callurl]" id="premise-pricing-columns-<?php echo $key; ?>-callurl" value="<?php echo esc_attr($callurl); ?>" />
        			</td>
        		</tr>
        		<tr>
        			<th scope="row"><label for="premise-pricing-columns-<?php echo $key; ?>-newwindow"><?php _e('Force New Window', 'premise' ); ?></label></th>
        			<td>
        				<label>
        					<input type="checkbox" class="premise-pricing-cta-newwindow" <?php checked('yes', $newwindow); ?> name="premise[pricing-columns][<?php echo $key; ?>][newwindow]" id="premise-pricing-columns-newwindow" value="yes" />
        					<?php _e('Clicks on the call to action button should open in a new window.', 'premise' ); ?>
        				</label>
        			</td>
        		</tr>
        	</tbody>
        </table>
    </div>
</div>
