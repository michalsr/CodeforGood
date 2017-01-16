<?php
// Hide WordPress Admin Bar
add_filter( 'show_admin_bar', '__return_false' );

add_editor_style('editor-style.css');

add_action( 'admin_print_scripts-profile.php', 'hide_admin_bar_prefs' );
function hide_admin_bar_prefs() {  ?>
	<style type="text/css">
        .show-admin-bar { display: none; }
    </style>
    <?php
}


add_theme_support( 'post-thumbnails' );

function bodypageclass() {
	global $post;
	$post_slug = $post->post_name;
	$parent = get_page($post->post_parent);
	$parent_post_slug = $parent->post_name;

// grandparent:
	$grandparent = get_page($parent->post_parent);
	$grandparent_post_slug = $grandparent->post_name;

// greatgrandparent:
	$greatgrandparent = get_page($grandparent->post_parent);
	$greatgrandparent_post_slug = $greatgrandparent->post_name;



	if ($parent_post_slug==$post_slug) {$parent_post_slug='';}
	$parent_id = $parent->ID;
	$post_type = 'outer_' . get_post_type($post->ID);
	// get some category information:
	$doc_post_category = '';
	foreach(  get_the_category($post->ID) as $catemp) { 
		$doc_post_category .= ' category_' . $catemp -> category_nicename;
		}
	return "$greatgrandparent_post_slug $grandparent_post_slug $post_slug $post_type $parent_post_slug $doc_post_category";

}
?>