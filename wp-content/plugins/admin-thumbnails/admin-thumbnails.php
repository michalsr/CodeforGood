<?php
/*
Plugin Name: Admin Thumbnails
Version: 0.1
Plugin URI: http://kolamilch.com/archive/2010/01/16/admin-thumbnails-plugin-for-wordpress/
Description: If your theme supports the thumbnail feature introduced in Wordpress 2.9, this plugin will display attached thumbnails on the admin area's manage posts and manage pages screen.
Author: Marc Becher
Author URI: http://kolamilch.com/
*/

/*
 * USAGE:
 *
 * Just activate the plugin. Your theme must support the thumbnail feature introduced in Wordpress 2.9.
 *
 */

// add thumbnail to posts and pages list in edit.php resp. edit-pages.php

	add_filter('manage_posts_columns', 'kolamilch_columns', 100, 1);
	function kolamilch_columns($posts_columns){
		$columns = array();
		foreach ($posts_columns as $column => $name){
			if ($column == 'title'){
				$columns['Thumbnail'] = __('Thumbnail');
				$columns[$column] = $name;
			} else $columns[$column] = $name;
		}
		return $columns;
	}
	
	add_action('manage_posts_custom_column', 'kolamilch_custom_column', 10, 2);
	function kolamilch_custom_column($column_name, $id){
		if($column_name == 'Thumbnail') {
			if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail())){
				the_post_thumbnail(array(80,53));
			}
		}
	}
	
	add_filter('manage_pages_columns', 'kolamilch_page_columns', 100, 1);
	function kolamilch_page_columns($posts_columns){
		$columns = array();
		foreach ($posts_columns as $column => $name){
			if ($column == 'title'){
				$columns['Thumbnail'] = __('Thumbnail');
				$columns[$column] = $name;
			} else $columns[$column] = $name;
		}
		return $columns;
	}
	
	add_action('manage_pages_custom_column', 'kolamilch_page_custom_column', 10, 2);
	function kolamilch_page_custom_column($column_name, $id){
		if( $column_name == 'Thumbnail' ){
			if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail())){
				the_post_thumbnail(array(80,53));
			}
		}
	}

?>
