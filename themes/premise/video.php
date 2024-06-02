<?php
/*
Template Name: Video
Template Description: Embed a video that makes it easier to sell your product or subscription to potential customers.
*/

get_header();

$landing_page_style = premise_get_landing_page_style();
$entryVideoWidth = intval( premise_get_fresh_design_option( 'wrap_width', $landing_page_style ) ) - intval( premise_get_fresh_design_option( 'video_holder_padding', $landing_page_style ) * 2 + 2 * premise_get_fresh_design_option( 'video_holder_border', $landing_page_style ) );
?>
	<div id="content" class="hfeed">
		<?php the_post(); ?>
		<div class="hentry">
			<?php
			premise_do_headline();
			premise_do_before_video_content();
			?>
			<div class="entry-content"><?php echo apply_filters('the_content', premise_get_video_below_copy()); ?></div>
		</div>
	</div><!-- end #content -->
	<?php
get_footer();