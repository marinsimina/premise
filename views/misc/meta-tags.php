<?php
$title = trim( $seo['title'] );
if( empty( $title ) )
	$title = $post->post_title;

$description = trim( $seo['description'] );
if( empty( $description ) )
	$description = substr( strip_tags( $post->post_content ), 0, 160 );

$metas = array();
foreach( array( 'noarchive', 'noindex', 'nofollow' ) as $possibleMeta ) {
	if( $seo[$possibleMeta] ) 
		$metas[] = $possibleMeta;
}

$keywords = trim( $seo['keywords'] );
$canonical = trim( $seo['canonical'] );

if( !empty( $title ) ) {
?>
<meta name="title" content="<?php echo esc_attr( $title ); ?>" />
<?php
}
if( !empty( $description ) ) {
	?>
	<meta name="description" content="<?php echo esc_attr($description); ?>" />
	<?php
}
if( !empty( $keywords ) ) {
?>
<meta name="keywords" content="<?php echo esc_attr( $keywords ); ?>" />
<?php
}
if( !empty( $canonical ) ) {
?>
<link rel="canonical" href="<?php echo esc_attr( $canonical ); ?>" />
<?php
}
if( !empty( $metas ) ) {
?>
<meta name="robots" content="<?php echo implode(',',$metas); ?>" />
<?php
}
