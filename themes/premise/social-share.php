<?php
/*
Template Name: Long Copy
Template Description: A simple long copy landing page.
*/

get_header();
the_post();
?>
	<div id="content" class="hfeed">
		<div class="hentry">
			<?php include('inc/headline.php'); ?>
			<div class="entry-content">
			<?php
				if ( premise_has_social_share_shared_page() ) {
					echo apply_filters('the_content', premise_get_social_share_after_share_page() );
				} else {
					echo apply_filters('the_content', premise_get_social_share_teaser_page() );
					premise_the_after_social_share_tease();
				}
			?>
			</div>
		</div>
	</div><!-- end #content -->
<?php
get_footer();