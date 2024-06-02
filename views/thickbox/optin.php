<?php $settings = $this->getSettings(); $optin = $settings['optin']; ?>
<div class="premise-thickbox-container">
	<h3 class="media-title"><?php _e('Add an Opt In Form', 'premise' ); ?></h3>
	<form method="post" id="insert-optin-form-form">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="premise-optin-provider"><?php _e('Provider', 'premise' ); ?></label></th>
					<td>
						<?php
						$allowed = (array)$optin['allowed'] + array('manual' => 1);
						$first = current($allowed);
						?>
						<select name="premise-optin-provider" id="premise-optin-provider">
							<?php foreach($allowed as $key => $value) { if($value != 1) { continue; } ?>
							<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($this->_optin_Keys[$key]); ?></option>
							<?php } ?>
						</select>
						<img  alt="" title="" class="ajax-feedback" id="ajax-feedback-get-lists" src="<?php echo esc_url( site_url( 'wp-admin/images/wpspin_light.gif' ) ); ?>" style="padding-bottom: 7px; visibility: hidden;">
					</td>
				</tr>
			</tbody>
		</table>

		<div id="aweber-info" class="premise-optin-provider-info">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="aweber-list"><?php _e('List', 'premise' ); ?></label></th>
						<td>
							<select name="aweber[list]" id="aweber-list"></select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aweber-list-forms"><?php _e('Form', 'premise' ); ?></label></th>
						<td>
							<select name="aweber[list-forms]" id="aweber-list-forms"></select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="aweber-align"><?php _e('Align', 'premise' ); ?></label></th>
						<td>
							<select name="aweber[align]" id="aweber-align">
								<option value="none"><?php _e('None', 'premise' ); ?></option>
								<option value="left"><?php _e('Left', 'premise' ); ?></option>
								<option value="right"><?php _e('Right', 'premise' ); ?></option>
								<option value="center"><?php _e('Center', 'premise' ); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div id="constant-contact-info" class="premise-optin-provider-info">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="constant-contact-list"><?php _e('List', 'premise' ); ?></label></th>
						<td>
							<select name="constant-contact[list]" id="constant-contact-list"></select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="constant-contact-align"><?php _e('Align', 'premise' ); ?></label></th>
						<td>
							<select name="constant-contact[align]" id="constant-contact-align">
								<option value="none"><?php _e('None', 'premise' ); ?></option>
								<option value="left"><?php _e('Left', 'premise' ); ?></option>
								<option value="right"><?php _e('Right', 'premise' ); ?></option>
								<option value="center"><?php _e('Center', 'premise' ); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div id="mailchimp-info" class="premise-optin-provider-info">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="mailchimp-list"><?php _e('List', 'premise' ); ?></label></th>
						<td>
							<select name="mailchimp[list]" id="mailchimp-list"></select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mailchimp-align"><?php _e('Align', 'premise' ); ?></label></th>
						<td>
							<select name="mailchimp[align]" id="mailchimp-align">
								<option value="none"><?php _e('None', 'premise' ); ?></option>
								<option value="left"><?php _e('Left', 'premise' ); ?></option>
								<option value="right"><?php _e('Right', 'premise' ); ?></option>
								<option value="center"><?php _e('Center', 'premise' ); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div id="manual-info" class="premise-optin-provider-info">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="manual-form-code"><?php _e('Opt In Form Code', 'premise' ); ?></label></th>
						<td>
							<textarea rows="10" class="code large-text" name="manual[form-code]" id="manual-form-code"></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="manual-align"><?php _e('Align', 'premise' ); ?></label></th>
						<td>
							<select name="manual[align]" id="manual-align">
								<option value="none"><?php _e('None', 'premise' ); ?></option>
								<option value="left"><?php _e('Left', 'premise' ); ?></option>
								<option value="right"><?php _e('Right', 'premise' ); ?></option>
								<option value="center"><?php _e('Center', 'premise' ); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="premise-optin-header"><?php _e('Title', 'premise' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="premise-optin[header]" id="premise-optin-header" value="<?php _e('Sign Up', 'premise' ); ?>" />
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<img  alt="" title="" class="ajax-feedback" id="ajax-feedback-process-submit" src="<?php echo esc_url(site_url('wp-admin/images/wpspin_light.gif')); ?>" style="padding-bottom: 7px; visibility: hidden;">
			<input type="submit" class="button button-primary" id="insert-optin-form" value="<?php _e('Insert', 'premise' ); ?>" />
		</p>

	</form>
</div>