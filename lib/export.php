<?php
/**
 * Prepares the contents of the export file.
 *
 * @author Gary Jones
 * @since 0.9.6
 * @return array $output multi-dimensional array holding CSS data
 * @uses premise_get_mapping()
 * @version 1.0
 */
 function premise_prepare_export($key) {
    $mapping = premise_get_mapping();

    foreach($mapping as $selector => $declaration) {
        if (!is_array($declaration)) {
            $output[$selector] = premise_get_design_option($declaration, $key);
        } else {
            foreach ($declaration as $property => $value) {
                if (!is_array($value)) {
                    $output[$selector][$property] = premise_get_design_option($value, $key);
                } else {
                    foreach($value as $index => $composite_value) {
                        $val = $composite_value[0];
                        $type = $composite_value[1];
                        if ('fixed_string' == $type) {
                            $output[$selector][$property][$index]['value'] = $val;
                        } else {
                            $output[$selector][$property][$index]['value'] = premise_get_design_option($val, $key);
                        }
                        $output[$selector][$property][$index]['type'] = $type;
                    }
                }
            }
        }
    }
    // Add in contents of custom stylesheet
    $custom = premise_get_custom_stylesheet_path();
    $output['custom_css'] = is_file( $custom ) ? file_get_contents( $custom ) : '';

    return apply_filters('premise_prepare_export', $output);
 }

/**
 * Returns the generated export file as a download.
 *
 * @author Gary Jones
 * @since 0.9.6
 */
function premise_create_export() {
	if(!isset($_GET['premise-design-key'])) {
		wp_die('Invalid export.');
	}
    $output = premise_prepare_export($_GET['premise-design-key']);
    $output = serialize($output);

    check_admin_referer('premise-export');
    header( 'Content-Description: File Transfer' );
    header('Cache-Control: public, must-revalidate');
    header('Pragma: hack');
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . premise_get_export_filename_prefix() . date("Ymd-His") . '.dat"');
//    header('Content-Length: ' . strlen($output));
    echo $output;
    exit();
}

/**
 * Sets the export file to download when requested
 *
 * @author Gary Jones
 * @since 0.9.6
 */
add_action('admin_init', 'premise_process_export');
function premise_process_export() {
    if (isset($_GET['premise'])) {
        if ('export' == $_GET['premise']) {
            premise_create_export();
        }
    }
}

function premise_get_export_filename_prefix() {
    return apply_filters('premise_get_export_filename_prefix', 'premise-settings-');
}