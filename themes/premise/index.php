<?php
get_header();
	?>
	<div id="content" class="hfeed">
		<?php if(have_posts()) { ?>
		<?php the_post(); ?>
		<div class="hentry">
			<?php include('inc/headline.php'); ?>
			<div class="entry-content"><?php the_content(); ?></div>
		</div>
		<?php } else { ?>
		<h1>Not Found, Error 404</h1>
		<p>
			The page you are looking for no longer exists. Perhaps you can return back to the site's <a href="<?php echo esc_url( site_url( '/' ) ); ?>">homepage</a></a> and see if you can find what you are looking for.
			Or, you can try finding it with the information below.
		</p>
		<?php } ?>
	</div><!-- end #content -->
	<?php
get_footer();