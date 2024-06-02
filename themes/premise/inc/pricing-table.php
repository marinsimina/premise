<?php
$columns = premise_get_pricing_columns();
$columnCount = count($columns);
$landing_page_style = premise_get_landing_page_style();

$ctaAlign = premise_get_fresh_design_option( 'pricing_tier_cta_align', $landing_page_style );
$wrapWidth = premise_get_fresh_design_option( 'wrap_width', $landing_page_style ) - ( 2 * premise_get_fresh_design_option( 'wrap_padding', $landing_page_style ) );
$margins = ( 10 * ( $columnCount - 1 ) ); // Account for margins
 // container extraneous
$extraneous = ( $columnCount * 2 * ( premise_get_fresh_design_option( 'pricing_tier_border', $landing_page_style ) + premise_get_fresh_design_option( 'pricing_tier_padding', $landing_page_style ) ) );
$available = $wrapWidth - $margins - $extraneous;

$max = 0;

foreach($columns as $column) { 
	if( count( $column['attributes'] ) > $max )
		$max = count( $column['attributes'] );
}

$marker = strtolower( premise_get_pricing_bullet_marker() );
if( !in_array( $marker, array( 'none', 'default' ) ) )
	$marker .= '-' . strtolower( premise_get_pricing_bullet_color() );
$bullet_class = sanitize_html_class( 'pricing-table-' . $marker );

?>
<div class="pricing-table-container">
	<div class="pricing-table <?php echo $bullet_class; ?>">
		<?php $count = 0; foreach($columns as $columnKey =>  $column) { $count++;  ?>
		<div class="pricing-table-column <?php if($columnCount == $count) { ?>last<?php } ?>" style="width: <?php printf('%d', $available / $columnCount); ?>px">
			<div class="pricing-table-column-header"><?php echo apply_filters('the_title', $column['title']); ?></div>
			<div class="pricing-table-column-features">
				<ul class="pricing-table-column-properties">
					<?php $atts = 0; foreach($column['attributes'] as $attribute) { $atts++; ?>
					<li><?php echo apply_filters('pricing_table_attribute', $attribute); ?></li>
					<?php } ?>
					<?php for($i = $atts; $i < $max; $i++) { ?>
					<li class="nothing">&nbsp;</li>
					<?php } ?>
				</ul>
				<div id="pricing-table-call-to-action-<?php the_ID(); ?>-column-<?php echo $columnKey; ?>" class="pricing-table-call-to-action">
					<?php if(!empty($column['callurl']) && !empty($column['calltext'])) { $target = ( isset( $column['newwindow'] ) && $column['newwindow'] == 'yes' ) ? 'target="_blank"' : ''; ?>
					<a id="pricing-table-call-to-action-<?php the_ID(); ?>-column-<?php echo $columnKey; ?>-link" <?php echo $target; ?> class="cta-align<?php echo $ctaAlign; ?>" href="<?php echo esc_url($column['callurl']); ?>"><?php echo esc_html( apply_filters( 'pricing_table_call_to_action', $column['calltext'] ) ); ?></a>
					<?php } ?>
				<br class="clear" />
				</div>
			</div>
		</div>
		<?php } ?>
		<br class="clear" />
	</div>
</div>