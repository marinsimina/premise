<?php 
$buton_id = isset( $_GET['premise-button-id'] ) ? $_GET['premise-button-id'] : '';
$button = $this->getConfiguredButton($buton_id); ?>
<div class="premise-thickbox-container">
	<h3 class="media-title"><?php _e('Create a Button', 'premise' ); ?></h3>
	
	<form method="post" id="premise-button-form" action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
		<?php if( !empty( $_GET['premise-button-id'] ) ) { ?>
			<input type="hidden" name="premise-button-id" value="<?php echo esc_attr( $buton_id ); ?>" />
		<?php } ?>
		
		<div id="premise-button-creation-container">
			<table class="form-table button-creation-form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="button-editing-title"><?php _e('Button Name', 'premise' ); ?></label></th>
						<td>
							<input class="large-text" type="text" name="button-editing[title]" id="button-editing-title" value="<?php echo esc_attr( $button['title'] ); ?>" /><br />
							<?php _e('This name is for identification purposes only.  You will choose the text for the button while configuring it with the landing page editor.', 'premise' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Background', 'premise' ); ?></th>
						<td>
							<ul>
								<li><?php $this->thickbox_button_color_picker( 'background-color-1', __( 'Start Color', 'premise' ), $button['background-color-1'] ); ?></li>
								<?php
								foreach( range( 2, 4 ) as $key ) {
									$id = 'background-color-' . $key;
									$button_enabled = isset( $button['background-color-'.$key.'-enabled'] ) ? $button['background-color-'.$key.'-enabled'] : false;
								?>
								<li>
									<?php
									$this->thickbox_button_color_picker( $id, __( 'Color Stop', 'premise' ), $button[$id] ); 
									$this->thickbox_button_select( $id . '-position', '', $button[$id . '-position'], 0, 100, '%' );
									?>
									<label>
										<input <?php checked( 'yes', $button_enabled ); ?> class="color-stop-enabler" rel="<?php echo $key; ?>" type="checkbox" id="button-editing-background-color-<?php echo $key; ?>-enabled" name="button-editing[background-color-<?php echo $key; ?>-enabled]" value="yes" />
										<?php _e('Enable', 'premise' ); ?>
									</label>
								</li>
								<?php } ?>
								<li><?php $this->thickbox_button_color_picker( 'background-color-5', __( 'End Color', 'premise' ), $button['background-color-5'] ); ?></li> 
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Background on Hover', 'premise' ); ?></th>
						<td>
							<ul>
								<li><?php $this->thickbox_button_color_picker( 'background-color-hover-1', __( 'Start Color', 'premise' ), $button['background-color-hover-1'] ); ?></li> 
								<?php 
								foreach( range( 2, 4 ) as $key ) {
									$id = 'background-color-hover-' . $key;
									$button_enabled = isset( $button['background-color-hover-'.$key.'-enabled'] ) ? $button['background-color-hover-'.$key.'-enabled'] : false;
								?>
								<li>
									<?php 
									$this->thickbox_button_color_picker( $id, __( 'Color Stop', 'premise' ), $button[$id] ); 
									$this->thickbox_button_select( $id . '-position', '', $button[$id . '-position'], 0, 100, '%' );
									?>
									<label>
										<input <?php checked( 'yes', $button_enabled ); ?> class="color-stop-enabler-hover color-stop-enabler" rel="hover-<?php echo $key; ?>" type="checkbox" id="button-editing-background-color-hover-<?php echo $key; ?>-enabled" name="button-editing[background-color-hover-<?php echo $key; ?>-enabled]" value="yes" />
										<?php _e('Enable', 'premise' ); ?>
									</label>
								</li>
								<?php } ?>
								<li><?php $this->thickbox_button_color_picker( 'background-color-hover-5', __( 'End Color', 'premise' ), $button['background-color-hover-5'] ); ?></li> 
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Border/Padding', 'premise' ); ?></th>
						<td>
							<ul>
								<li><?php $this->thickbox_button_color_picker( 'border-color', __( 'Color', 'premise' ), $button['border-color'] ); ?></li> 
								<li><?php $this->thickbox_button_select( 'border-width', __( 'Width', 'premise' ), $button['border-width'], 0, 10, 'px' ); ?></li> 
								<li><?php $this->thickbox_button_select( 'border-radius', __( 'Radius', 'premise' ), $button['border-radius'], 0, 100, 'px' ); ?></li> 
								<li><?php $this->thickbox_button_select( 'padding-tb', __( 'Top + Bottom', 'premise' ), $button['padding-tb'], 0, 50, 'px' ); ?></li>
								<li><?php $this->thickbox_button_select( 'padding-lr', __( 'Left + Right', 'premise' ), $button['padding-lr'], 0, 50, 'px' ); ?></li>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Drop Shadow', 'premise' ); ?></th>
						<td>
							<ul>
								<li><?php $this->thickbox_button_color_picker( 'drop-shadow-color', __( 'Color', 'premise' ), $button['drop-shadow-color'] ); ?></li> 
								<li><?php $this->thickbox_button_select( 'drop-shadow-opacity', __( 'Opacity', 'premise' ), $button['drop-shadow-opacity'], 0, 1, '', .1 ); ?></li> 
								<li><?php $this->thickbox_button_select( 'drop-shadow-x', __( 'X', 'premise' ), $button['drop-shadow-x'], -10, 10, 'px' ); ?></li>
								<li><?php $this->thickbox_button_select( 'drop-shadow-y', __( 'Y', 'premise' ), $button['drop-shadow-y'], -10, 10, 'px' ); ?></li> 
								<li><?php $this->thickbox_button_select( 'drop-shadow-size', __( 'Size', 'premise' ), $button['drop-shadow-size'], 0, 50, 'px' ); ?></li> 
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Inner Shadow', 'premise' ); ?></th>
						<td>
							<ul>
								<li><?php $this->thickbox_button_color_picker( 'inset-shadow-color', __( 'Color', 'premise' ), $button['inset-shadow-color'] ); ?></li> 
								<li><?php $this->thickbox_button_select( 'inset-shadow-opacity', __( 'Opacity', 'premise' ), $button['inset-shadow-opacity'], 0, 1, '', .1 ); ?></li> 
								<li><?php $this->thickbox_button_select( 'inset-shadow-x', __( 'X', 'premise' ), $button['inset-shadow-x'], -10, 10, 'px' ); ?></li>
								<li><?php $this->thickbox_button_select( 'inset-shadow-y', __( 'Y', 'premise' ), $button['inset-shadow-y'], -10, 10, 'px' ); ?></li> 
								<li><?php $this->thickbox_button_select( 'inset-shadow-size', __( 'Size', 'premise' ), $button['inset-shadow-size'], 0, 50, 'px' ); ?></li> 
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Text Font', 'premise' ); ?></th>
						<td>
							<ul>
								<li>
									<label class="descriptor" for="button-editing-font-family"><?php _e('Font', 'premise' ); ?></label>
									<select name="button-editing[font-family]" id="button-editing-font-family">
										<?php echo premise_create_options($button['font-family'], 'family'); ?>
									</select>
								</li>
								<li><?php $this->thickbox_button_color_picker( 'font-color', __( 'Color', 'premise' ), $button['font-color'] ); ?></li> 
								<li><?php $this->thickbox_button_select( 'font-size', __( 'Size', 'premise' ), $button['font-size'], 0, 50, 'px' ); ?></li>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Text Shadow 1', 'premise' ); ?></th>
						<td>
							<ul>
								<li><?php $this->thickbox_button_color_picker( 'text-shadow-1-color', __( 'Color', 'premise' ), $button['text-shadow-1-color'] ); ?></li> 
								<li><?php $this->thickbox_button_select( 'text-shadow-1-opacity', __( 'Opacity', 'premise' ), $button['text-shadow-1-opacity'], 0, 1, '', .1 ); ?></li> 
								<li><?php $this->thickbox_button_select( 'text-shadow-1-x', __( 'X', 'premise' ), $button['text-shadow-1-x'], -10, 10, 'px' ); ?></li>
								<li><?php $this->thickbox_button_select( 'text-shadow-1-y', __( 'Y', 'premise' ), $button['text-shadow-1-y'], -10, 10, 'px' ); ?></li> 
								<li><?php $this->thickbox_button_select( 'text-shadow-1-size', __( 'Size', 'premise' ), $button['text-shadow-1-size'], 0, 50, 'px' ); ?></li> 
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Text Shadow 2', 'premise' ); ?></th>
						<td>
							<ul>
								<li><?php $this->thickbox_button_color_picker( 'text-shadow-2-color', __( 'Color', 'premise' ), $button['text-shadow-2-color'] ); ?></li> 
								<li><?php $this->thickbox_button_select( 'text-shadow-2-opacity', __( 'Opacity', 'premise' ), $button['text-shadow-2-opacity'], 0, 1, '', .1 ); ?></li> 
								<li><?php $this->thickbox_button_select( 'text-shadow-2-x', __( 'X', 'premise' ), $button['text-shadow-2-x'], -10, 10, 'px' ); ?></li>
								<li><?php $this->thickbox_button_select( 'text-shadow-2-y', __( 'Y', 'premise' ), $button['text-shadow-2-y'], -10, 10, 'px' ); ?></li> 
								<li><?php $this->thickbox_button_select( 'text-shadow-2-size', __( 'Size', 'premise' ), $button['text-shadow-2-size'], 0, 50, 'px' ); ?></li> 
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	
		<pre class="code" id="button-code" style="display: none;">
			<?php echo $this->getButtonCode($button); ?>
		</pre>
		
		<h3><?php _e('Preview', 'premise' ); ?></h3>
		<div id="button-preview">
			<style type="text/css" id="example-button-style"></style>
			
			<br /><br />
			<a class="css3button" href="#" onclick="return false;"><?php _e('Example', 'premise' ); ?></a>
			<br /><br />
		</div>
		
		<p class="submit">
			<?php wp_nonce_field('premise-save-button', 'premise-save-button-nonce'); ?>
			<input type="hidden" name="action" value="premise_save_button" />
			<input type="submit" class="button button-primary" id="save-button" value="<?php _e('Save', 'premise' ); ?>" />
			<input type="button" class="button button-usage-cancel" value="<?php _e('Cancel', 'premise' ); ?>" />
		</p>
	</form>
	
	<?php $key = 'XXX'; ?>
	<div id="moz-background-color-stop-template" style="display:none;">
		<div class="background-color-<?php echo $key; ?>-enabled-container">,<span id="background-color-<?php echo $key; ?>-moz-flag"><span><span class="c background-color-<?php echo $key; ?>"><?php echo $button['background-color-'.$key] ?></span> <span class="p background-color-<?php echo $key; ?>-position"><?php echo $button['background-color-'.$key.'-position']; ?></span>%</span></div>
	</div>
	<div id="webkit-background-color-stop-template" style="display:none;">
		<div class="background-color-<?php echo $key; ?>-enabled-container">,<span class="background-color-<?php echo $key; ?>-container" id="background-color-<?php echo $key; ?>-webkit-flag"><span>color-stop(<span class="p">0.<span class="background-color-<?php echo $key; ?>-position"><?php echo $button['background-color-'.$key.'-position']; ?></span></span>, <span class="c background-color-<?php echo $key; ?>"><?php echo $button['background-color-'.$key]; ?></span>)</span></span></div>
	</div>
</div>

<script type="text/javascript">
	function premise_button_update_example_css() {
		jQuery('#example-button-style').text(jQuery('#button-code').text());
	}
	
	jQuery(document).ready(function($) {
	    // Add color picker to color input boxes.
	    $('input:text.premise-color-picker').each(function (i) {
	    	var $this = $(this);
	    	var val = $.trim($this.val());
	    	if('' != val) {
	    		$this.css('background-color', val);
	    	}
	    	
	        $(this).after('<div id="picker-' + i + '" style="z-index: 100; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>');
	        $('#picker-' + i).hide().farbtastic(function(color) { $this.css('background-color', color).val(color); });
	        var picker = $.farbtastic('#picker-'+i);
	        picker.setColor(val);
	    })
	    .focus(function() {
	        $(this).next().show();
	    })
	    .blur(function() {
	        $(this).next().hide();
	        $(this).css('background-color', $(this).val());
	        $.farbtastic('#'+$(this).next().attr('id')).setColor($(this).val());
	    }).keypress(function(event) {
	    	if(event.which == 13) { // They pressed enter
	    		event.preventDefault();
	    		$(this).next().hide();
	    		$(this).css('background-color', $(this).val());
		        $.farbtastic('#'+$(this).next().attr('id')).setColor($(this).val());
	    	}
	    });
	    
	    $('#premise-button-creation-container').find('input[type=text],select').bind('blur', function(event) {
	    	var $this = $(this);
	    	var $buttoncode = $('#button-code');
			var id = $this.attr('id').replace('button-editing-','');
	    	if('' == id) {
	    		id = $this.attr('id').replace('button-editing-','');
	    	}
	    	
	    	var val = $this.val();
	    	
	    	var $element = $('#button-code').find('.'+id);
	    	if($element.is('.rgb')) {
	    		val = hex2rgb(val);
	    	}
	    	
	    	$element.text(val);
	    	premise_button_update_example_css();
	    });
	    
	    $('#premise-button-creation-container').find('input[type=checkbox]').bind('change', function(event) {
	    	var $this = $(this);
	    	var $buttoncode = $('#button-code');
			var id = $this.attr('id').replace('button-editing-','');
	    	if('' == id) {
	    		id = $this.attr('id').replace('button-editing-','');
	    	}
	    	
	    	if($this.is('.color-stop-enabler')) {
	    		if($this.is(':checked')) {
    				
	    			$buttoncode.find('.moz-background-color-stops, .webkit-background-color-stops').empty();
	    			$('.color-stop-enabler:checked').each(function(i) {
		    			var hover = '';
	    				if($(this).is('.color-stop-enabler-hover')) {
	    					hover = '-hover';
	    				}
	    			
	    				var rel = $(this).attr('rel');
	    				var $mozclone = $($('#moz-background-color-stop-template').html().replace(/XXX/g,rel));
	    				var $webkitclone = $($('#webkit-background-color-stop-template').html().replace(/XXX/g,rel));

						$mozclone.find('.background-color-'+rel+'-position').text($('#button-editing-background-color-'+rel+'-position').val());
						$mozclone.find('.background-color-'+rel).text($('#button-editing-background-color-'+rel).val());
						
						$webkitclone.find('.background-color-'+rel+'-position').text($('#button-editing-background-color-'+rel+'-position').val());
						$webkitclone.find('.background-color-'+rel).text($('#button-editing-background-color-'+rel).val());
	    				
	    				
	    				$buttoncode.find('.moz-background-color'+hover+'-stops').append($mozclone);
	    				$buttoncode.find('.webkit-background-color'+hover+'-stops').append($webkitclone);
	    			}); 
	    		} else {
	    			$buttoncode.find('.'+id+'-container').remove();
	    		}
	    		
	    		
	    		premise_button_update_example_css();
	    	}
	    });
	    
	    premise_button_update_example_css();
	    
	    $('#premise-button-form').submit(function() {
	    	var name = $('#button-editing-title').val();
	    	if($.trim(name) == '') {
	    		$('#button-editing-title').val('My Button');
	    	}
	    });
	    $('#premise-button-form').ajaxForm(function() {
			var win = window.dialogArguments || opener || parent || top;
			win.location.reload(true);
	    });
	});
</script>
