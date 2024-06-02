<?php
/*
Template Name: Opt In
Template Description: Gather emails for your mailing list using the targeted Opt In page type.
*/
get_header();

?>
	<div id="content" class="hfeed">
		<?php the_post(); ?>
		<div class="hentry">
			<?php
			include('inc/headline.php');
			premise_do_before_post();
			?>

			<div class="entry-content"><?php the_content(); ?></div>
		</div>
	</div><!-- end #content -->
	<?php
get_footer();