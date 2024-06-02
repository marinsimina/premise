<?php

/*
 * Landing page template customization for Premise landing pages in Genesis
 */

// add premise hook for scripts
add_action( 'wp_head', 'premise_landing_page_wp_head', 999 );
function premise_landing_page_wp_head() {

	do_action( 'premise_immediately_after_head' );

}
// move Genesis wp_title handling to after Premise wp_title handling
remove_filter( 'wp_title', 'genesis_doctitle_wrap', 20 );
add_filter( 'wp_title', 'genesis_doctitle_wrap', 50 );

// header image
add_action( 'genesis_header', 'premise_landing_page_header' );
function premise_landing_page_header() {

	if ( ! premise_should_have_header_image() || ! premise_get_header_image() )
		return;

	genesis_markup( '<header class="premise-site-header">', '<div id="premise-header" class="premise-landing-header">' );
	genesis_structural_wrap( 'header' );

	premise_do_header();

	genesis_structural_wrap( 'header', 'close' );
	genesis_markup( '</header>', '</div><!--end #header-->' );

}

// add page to post classes
add_filter( 'post_class', 'premise_post_class' );
function premise_post_class( $classes ) {

	$classes[] = 'page';
	$classes[] = 'entry-content';
	return $classes;

}

// add premise post title
remove_action( 'genesis_post_title', 'genesis_do_post_title' );
add_action( 'genesis_post_title', 'premise_do_headline' );

//remove the post info/meta
add_filter( 'genesis_post_info', 'premise_post_info_meta_filter', 95 );
add_filter( 'genesis_post_meta', 'premise_post_info_meta_filter', 95 );
function premise_post_info_meta_filter() {

	return '';

}

// add extra Premise landing page content
add_action( 'genesis_before_post_content', 'premise_do_before_post' );
add_action( 'genesis_after_post_content', 'premise_do_after_post' );

// add premise footer hooks
add_action( 'wp_footer', 'premise_landing_page_before_footer', 0 );
function premise_landing_page_before_footer() {

	if ( premise_should_have_footer() ) {
?>
	<div id="footer">
		<div class="wrap">
			<div class="creds">
<?php
		if ( premise_get_footer_copy() )
			echo '<p>' . premise_get_footer_copy() . '</p>';

?>
			</div>
		</div><!-- end .wrap -->
	</div>
<?php
	}
	do_action( 'premise_before_footer' );

}

add_action( 'genesis_after', 'premise_landing_page_after_footer' );
function premise_landing_page_after_footer() {

	do_action( 'premise_after_footer' );

}
