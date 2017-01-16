<?php
/*
Plugin Name: People Content Type
Version: 0.1
Plugin URI: http://www.bkjproductions.com/
Description: Adds a "People" content type to WordPress.
Author: BKJ Productions
Author URI: http://bkjproductions.com/
Revision History:
*/

/*
 * USAGE:
 *
 * Just activate the plugin.
 *
 */


	add_filter('manage_posts_columns', 'people_content_type_columns', 100, 1);
	function people_content_type_columns($posts_columns){
		$columns = array();
		foreach ($posts_columns as $column => $name){
			if ($column == 'author'){
				$columns['Type'] = __('Type');
				$columns[$column] = $name;
			} else $columns[$column] = $name;
		}
		return $columns;
	}
	
	add_action('manage_posts_custom_column', 'people_content_type_custom_column', 10, 2);
	function people_content_type_custom_column($column_name, $id){
		if($column_name == 'Type') {
			$terms = get_the_term_list( $id, 'types' ,'', ', ');
			$terms = strip_tags( $terms );
			echo $terms;
		}
	}
	
	add_filter('manage_pages_columns', 'people_content_type_page_columns', 100, 1);
	function people_content_type_page_columns($posts_columns){
		$columns = array();
		foreach ($posts_columns as $column => $name){
			if ($column == 'author'){
				$columns['Type'] = __('Type');
				$columns[$column] = $name;
			} else $columns[$column] = $name;
		}
		return $columns;
	}
	
	add_action('manage_pages_custom_column', 'people_content_type_page_custom_column', 10, 2);
	function people_content_type_page_custom_column($column_name, $id){
		if( $column_name == 'Type' ){
			if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail())){
				the_post_thumbnail(array(80,53));
			}
		}
	}


?>
