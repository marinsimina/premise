<?php
/*
Template Name: Long Copy
Template Description: A simple long copy landing page.
*/

get_header();
	?>
	<div id="content" class="hfeed">
		<?php the_post(); ?>
		<div class="hentry">
			<?php include('inc/headline.php'); ?>
			<div class="entry-content"><?php the_content(); ?></div>
		</div>
	</div><!-- end #content -->
	<?php
get_footer();