<?php
/*
Template Name: Content Scroller
Template Description: A simple content scroller.
*/

add_action( 'wp_head', 'premise_content_scroller_styles', 20 );
function premise_content_scroller_styles() {
	$landing_page_style = premise_get_landing_page_style();
	$width = ( premise_get_fresh_design_option( 'wrap_width', $landing_page_style ) - 2 * premise_get_fresh_design_option( 'wrap_padding', $landing_page_style ) - 90 );
?>
<style type="text/css">
	#content .coda-slider, #content .coda-slider .panel {
		width: <?php echo absint($width); ?>px;
	}
	#content .coda-slider .container-border {
		width: <?php echo (absint($width) - 2); ?>px;
	}
</style>
<?php
}

get_header();

$landing_page_style = premise_get_landing_page_style();
$width = ( premise_get_fresh_design_option( 'wrap_width', $landing_page_style ) - 2 * premise_get_fresh_design_option( 'wrap_padding', $landing_page_style ) - 90 );
?>

	<div id="content" class="hfeed">
		<?php the_post(); ?>
		<div class="hentry">
			<?php include('inc/headline.php'); ?>
			<?php
			$tabs = premise_get_content_tabs();
			if(!empty($tabs)) {
				?>
				<div class="coda-slider-wrapper">
					<?php if(premise_should_show_content_scroller_tabs()) { ?>
					<div id="coda-nav-1" class="coda-nav">
						<ul>
							<?php
							global $current_slider_tab, $current_slider_total_tabs;
							$current_slider_total_tabs = count($tabs);
							?>
							<?php foreach($tabs as $key => $tab) { ?>
							<li id="coda-nav-tab-<?php echo esc_attr($key+1); ?>" class="tab<?php echo esc_attr($key+1); ?>">
								<a title="<?php esc_attr_e($tab['tooltip'], 'premise' ); ?>" href="#<?php echo esc_attr($key+1); ?>">
									<?php echo apply_filters('the_title', $tab['title']); ?>
								</a>
							</li>
							<?php } ?>
						</ul>
					</div>
					<?php } ?>

					<?php if(premise_should_show_content_scroller_arrows()) { ?>
					<div id="coda-nav-left-1" class="coda-nav-left"><a href="#">&laquo;</a></div>
					<?php } else { ?>
					<div class="coda-nav-left-blank"></div>
					<?php } ?>

					<div class="coda-slider preload" id="coda-slider-1">
						<?php foreach($tabs as $key => $tab) { $current_slider_tab = $key+1; ?>
						<div class="panel">
							<div class="container-border">
								<div class="panel-wrapper">
									<h2 class="title"><?php echo apply_filters('the_title', $tab['title']); ?></h2>
									<?php echo apply_filters('the_content', $tab['text']); ?>
								</div>
							</div>
						</div>
						<?php } ?>
					</div><!-- .coda-slider -->

					<?php if(premise_should_show_content_scroller_arrows()) { ?>
					<div id="coda-nav-right-1" class="coda-nav-right"><a href="#">&raquo;</a></div>
					<?php } else { ?>
					<div class="coda-nav-right-blank"></div>
					<?php } ?>
				</div><!-- .coda-slider-wrapper -->
				<?php
			}
			?>

			<script type="text/javascript">
				var $ = jQuery;
				jQuery(document).ready(function($) {
					jQuery('#coda-slider-1').codaSlider(
						{
							dynamicArrows: false,
							dynamicTabs: false
						}
					);
					
					jQuery('a.xtrig').click(function(event) {
						var codaSliderOffset = $('#coda-slider-1').offset();
						$(window).scrollTo( codaSliderOffset.top, 1000 );
					});
				});
			</script>
		</div>
		<?php ?>
	</div><!-- end #content -->
	<?php
get_footer();