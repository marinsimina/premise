<?php
/**
 * The $scroller variable is an array containing the keys:
 * - title
 * - text
 */
$tooltip = $icon = '';
extract($scroller);
?>
<div class="postbox premise-content-scrollers-postbox" id="premise-content-scrollers-<?php echo $key; ?>">
    <div title="Click to toggle" class="handlediv">
        <br>
    </div>
    <h3 class="hndle"><span><span class="tab-name"><?php echo esc_html($title); ?></span> <span class="tab-description">(Content Scroller Content Tab)</span></span></h3>
    <div class="inside">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="premise-content-scrollers-<?php echo $key; ?>-title"><?php _e('Title', 'premise' ); ?></label>
                    </th>
                    <td>
                        <input class="regular-text premise-content-scrollers-title" type="text" name="premise[content-scrollers][<?php echo $key; ?>][title]" id="premise-content-scrollers-<?php echo $key; ?>-title" value="<?php echo esc_attr($title); ?>" />
                        <a href="#" class="premise-content-scrollers-delete-tab"><?php _e('Delete This Tab', 'premise' ); ?></a>
                    </td>
                </tr>
                <tr>
                	<th scope="row">
                		<label for="premise-content-scrollers-<?php echo $key; ?>-tooltip"><?php _e('Tooltip', 'premise' ); ?></label>
                	</th>
                	<td>
                		<input class="regular-text premise-content-scrollers-tooltip" type="text" name="premise[content-scrollers][<?php echo $key; ?>][tooltip]" id="premise-content-scrollers-<?php echo $key; ?>-tooltip" value="<?php echo esc_attr($tooltip); ?>" />
                	</td>
                </tr>
                <tr>
                	<th scope="row">
                		<label for="premise-content-scrollers-<?php echo $key; ?>-icon"><?php _e('Icon URL', 'premise' ); ?></label>
                	</th>
                	<td>
                		<input class="regular-text premise-content-scrollers-icon" type="text" name="premise[content-scrollers][<?php echo $key; ?>][icon]" id="premise-content-scrollers-<?php echo $key; ?>-icon" value="<?php echo esc_attr($icon); ?>" />
                		<?php printf(__('You can upload one via the <a class="thickbox" href="%s">WordPress uploader</a>.', 'premise' ), esc_attr(add_query_arg(array('post_id' => 0, 'send_to_premise_field_id'=>"premise-content-scrollers-{$key}-icon", 'TB_iframe' => 1, 'width' => 640, 'height' => 459), add_query_arg('TB_iframe', null, get_upload_iframe_src('image'))))); ?>
                	</td>
                </tr>
            </tbody>
        </table>

        <?php premise_the_editor($text, 'premise[content-scrollers]['.$key.'][text]', '', true, $key+2 /*, true */); ?>
    </div>
</div>
