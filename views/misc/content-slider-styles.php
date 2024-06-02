<?php
$paddingVert = 0;
$bodyFontSize = premise_get_design_option('body_font_size', $styleKey);
$rules = '';
foreach($tabs as $key => $tab) {
	$tabNumber = $key + 1;
	$icon = isset( $tab['icon'] ) ? $tab['icon'] : null;
	
	if(!empty($icon) && false !== strpos($icon, $uploadUrl)) {
		$iconPath = str_replace($uploadUrl, $uploadDir, $icon);
		list($width, $height) = getimagesize($iconPath);
		
		if($width) {
			
			$paddingLeft = $width + 16;
			$thisPaddingVert = 10 + ($height / 2) - $bodyFontSize;
			$paddingVert = $paddingVert >= $thisPaddingVert ? $paddingVert : $thisPaddingVert;
			
			$rules .= "#coda-nav-tab-{$tabNumber} a { background-position: 8px center;  padding-left: {$paddingLeft}px; background-image: url({$icon}); background-repeat: no-repeat; }";
		}
	}
}
if ( ! $rules && ! $paddingVert )
	return;
?>
<style type="text/css">

	.coda-nav ul li a {
	<?php if($paddingVert > 0) { ?>
		padding-bottom: <?php echo $paddingVert; ?>px;
		padding-top: <?php echo $paddingVert; ?>px;
	<?php } ?> 
	}
	<?php echo $rules; ?>
</style>