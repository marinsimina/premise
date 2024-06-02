<?php

/**
 * If a file has been uploaded to import options, it parses the file, formats into a nice array
 * via the $mapping, then updates the setting in the DB.
 *
 * @author Gary Jones
 * @since 0.9.6
 * @uses premise_get_mapping
 */
function premise_process_import() {
	if ( isset($_POST['premise'])) {
		if ('import' == $_POST['premise']) {
			check_admin_referer('premise-import', '_wpnonce-premise-import');
			if (strpos($_FILES['file']['name'], premise_get_export_filename_prefix()) === false) {
				wp_redirect(admin_url('admin.php?page=premise-design&premise=wrongfile'));
			} elseif ($_FILES['file']['error'] > 0) {
				wp_redirect(admin_url('admin.php?page=premise-design&premise=file'));
			} else {
				$raw_options = file_get_contents($_FILES['file']['tmp_name']);
				$raw_options = trim($raw_options);
				$options = maybe_unserialize($raw_options);

				$mapping = premise_get_mapping();

				foreach ($options as $selector => $declaration) {
					if ( !isset( $mapping[$selector] ) )
						continue; 
					if (!is_array($declaration)) {
						// custom_css or minify_css
						if ('custom_css' == $selector) {
							premise_create_custom_stylesheet($declaration);
						} else {
							$opt = $selector;
							$newvalue = $declaration;
							$newarray[$opt] = $newvalue;
						}
					} else {
						foreach ($declaration as $property => $value) {
							if (!is_array($value)) {
								// color, font-style, text-decoration etc
								$opt = $mapping[$selector][$property];
								$newvalue = $value;
								$newarray[$opt] = $newvalue;
							} else {
								// multi-value properties: margin, padding, etc
								foreach($value as $index => $composite_value) {
									$type = $mapping[$selector][$property][$index][1];
									if ('fixed_string' != $type) {
										$opt = $mapping[$selector][$property][$index][0];
										$newvalue = $composite_value['value'];
										$newarray[$opt] = $newvalue;
									}
								}
							}
						}
					}
				}

				$title = empty($_POST['premise_style_title']) ? __('Imported Style', 'premise' ) : $_POST['premise_style_title'];
				$newarray['premise_style_title'] = __('Imported Styles', 'premise' );

				global $Premise;
				$key = $Premise->saveDesignSettings($newarray, $_POST['premise-design-key']);
				wp_redirect(admin_url('admin.php?page=premise-style-settings&updated=true&premise-design-key='.$key));
			}
		}
	}
}
add_action('admin_init', 'premise_process_import');

