<div class="premise-thickbox-container premise-education-thickbox-container">
<?php
global $Premise, $premise_base;
if( empty( $_GET['section'] ) ) {
	$advice = $Premise->getAdvice( $Premise->convertLandingPageToId( $premise_base->get_page_type( $_GET['post_id'] ) ) );
} else {
	$advice = $Premise->getSingleAdvice( $_GET['section'] );
}
if( is_wp_error( $advice ) ) {
	?><div class="error fade"><p><?php echo $advice->get_error_message(); ?></p></div><?php
} else {
	$advice['advice'] = do_shortcode($advice['advice']);
	if( !empty( $_GET['section'] ) )
		echo "<h2>{$advice['name']}</h2>";

	echo $advice['advice'];
	if( !empty( $_GET['section'] ) ) {
		?>
		<p>
		<?php printf(__('<a href="%s">&laquo; Back to Copywriting Assistance for %s Pages</a>', 'premise' ), add_query_arg(array('section' => false)), $Premise->getLandingPageTypeName($_GET['post_id'])); ?>
		</p>
		<?php
	}
}
?>
</div>