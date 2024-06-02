<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title><?php wp_title(''); ?></title>
		<?php wp_head(); ?>
	<?php do_action('premise_immediately_after_head'); ?>
	</head>
	<body <?php body_class('full-width-content'); ?>>
		<div id="wrap">
			<?php if(premise_should_have_header_image() && premise_get_header_image()) { ?>
			<div id="header">
				<div class="wrap">
					<?php premise_do_header(); ?>
				</div>
			</div>
			<?php } ?>
			<div id="inner">