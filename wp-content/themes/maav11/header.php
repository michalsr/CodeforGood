<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>


	<style type="text/css" media="screen">
		@import url( <?php bloginfo('stylesheet_url'); ?> );
		@import url( <?php bloginfo('template_directory'); ?>/suckerfish_test.css);
	</style>

	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php wp_get_archives('type=monthly&format=link'); ?>
	<?php //comments_popup_script(); // off by default ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(bodypageclass()); ?>>
<div id="rap">

<div id="header">
<a href="/home/"><img src="<?php bloginfo('template_directory'); ?>/images/logo.png" alt="Melrose Alliance Against Violence" name="logo" width="168" height="173" border="0" align="left" id="logo" /></a>

<div id="topnav">
<div id="navtext">LEARN ABOUT:</div>
<ul id="suckerfishnav" class="sf-menu"  style="z-index: 50;">
<?php
make_suckerfishmenu('22,34,66');
?>
</ul>
</div>

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
<div id="content">
<!-- end header -->
<?php
 // the_excerpt_reloaded(excerpt_length, 'allowedtags', 'filter_type', use_more_link, 'more_link_text', force_more_link, fakeit, fix_tags); 
function doexcerpt() {
 if(function_exists('wp_the_excerpt_reloaded')) { wp_the_excerpt_reloaded('excerpt_length=10&more_link_text=[more]'); } 
 //if(function_exists('wp_the_excerpt_reloaded')) { wp_the_excerpt_reloaded(40,'<img><b><strong>','none',true,'[more]'); } 
}
?>

<?php function make_suckerfishmenu($idlist) {
	if ( !is_array($idlist) ) {$idlist = split(',', $idlist);}
	foreach ($idlist as $id) {
		$li = wp_list_pages("&include=$id&title_li=&echo=0");
		echo str_replace("</li>","",$li);
		echo "<ul>\n";
		$args = array(
			'child_of' => $id,
			'sort_column' => 'menu_order',
			'title_li' => ''
			);
		wp_list_pages($args);
		echo "\n\n</ul>\n</li>\n";
	}
}
?>