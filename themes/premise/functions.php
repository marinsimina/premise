<?php
add_theme_support('automatic-feed-links');

add_action( 'premise_immediately_after_head', 'premise_output_stylesheet_dir_uri_js' );
/*
add stylesheet url for code slider 
*/
function premise_output_stylesheet_dir_uri_js() {
?>

<script type="text/javascript" >
//<!--
var premise_theme_images_url = '<?php echo get_stylesheet_directory_uri(); ?>/images';
//-->
</script>
<?php
}