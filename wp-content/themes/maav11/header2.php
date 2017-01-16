<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

	<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>

	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats please -->

	<style type="text/css" media="screen">
		@import url( <?php bloginfo('stylesheet_url'); ?> );
	</style>

	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php wp_get_archives('type=monthly&format=link'); ?>
	<?php //comments_popup_script(); // off by default ?>
	<?php wp_head(); ?>
</head>

<body>
<div id="rap">

<div id="header">
<a class="logoLink" href="index.php" title="maasbesa - Home">&nbsp;</a>

<?php // check out
//http://goudendouche.com/plugins/dropdown-menu-plugin/
if (function_exists('gdd_dropdown_menu')) {
	?>
	<div id="dm" style="z-index: 50; margin-left: 133px; margin-top: 20px; float:left;">
	<?php 
	$ops='sort_column=menu_order&title_li=&exclude=2,3,4,5,6,7,8,9,94,43,74';
	gdd_dropdown_menu('',$ops); ?>
	</div>
	<?php
} // end if
?>

<a href="<?php bloginfo('url'); ?>/getting-help/"><img src="<?php bloginfo('template_directory'); ?>/images/btn_help.gif" alt="Get Help Now" name="btn_help" width="60" height="60" hspace="10" vspace="10" border="0" align="right" id="btn_help" /></a>

<?php

/// find ids of this page's parent
function page_ancestory_id( $id=0, $separator="," ){
	$itisme=get_post($id);
	$lineage=$itisme->ID;
	$parentID=$itisme->post_parent;
	while( $parentID != 0 ){
		$parent=get_post($parentID);
		$lineage=$parent->ID.$separator.$lineage;
		//$lineage=$parent->post_name.$separator.$lineage;
		$parentID=$parent->post_parent;
	}
	return $lineage;
}
?>


</div>
<div id="content" style="clear:both;">
<!-- end header -->
<?php
 // the_excerpt_reloaded(excerpt_length, 'allowedtags', 'filter_type', use_more_link, 'more_link_text', force_more_link, fakeit, fix_tags); 
function doexcerpt() {
 if(function_exists('wp_the_excerpt_reloaded')) { wp_the_excerpt_reloaded('excerpt_length=10&more_link_text=[more]'); } 
 //if(function_exists('wp_the_excerpt_reloaded')) { wp_the_excerpt_reloaded(40,'<img><b><strong>','none',true,'[more]'); } 
}
?>
