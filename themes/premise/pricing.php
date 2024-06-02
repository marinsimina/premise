<?php
/*
Template Name: Pricing Table
Template Description: The pricing table landing page allows you to break out your product or subscription cost in an easy to understand manner.
*/

get_header();
	?>
	<div id="content" class="hfeed">
		<?php the_post(); ?>
		<div class="hentry">
			<?php include('inc/headline.php'); ?>

			<div class="entry-content">
				<?php echo apply_filters( 'the_content', premise_get_pricing_content() ); ?>
			</div>
		</div>
	</div><!-- end #content -->
	
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var columnHeight = 0;
		$('.pricing-table-column-properties').each(function() {
			var height = $(this).height();
			if(height > columnHeight) {
				columnHeight = height;
			}
		});
		
		$('.pricing-table-column-properties').css('height', columnHeight+'px');
	});
	</script>
	<?php
get_footer();