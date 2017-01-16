<?php
/**
 * @package ListPeople
 * @version 0.0
 */
/*
Plugin Name: ListPeople
Plugin URI: http://wwww.bkjproductions.com/lab/listpeople
Description: List post of type "People" broken out by type. Usage: [listpeople type='staff' width=72 height=90] or [listpeople size='full'] 
Author: BKJ Productions
Version: 0.0
Author URI: http://www.bkjproductions.com
*/


add_shortcode('listpeople', 'do_listpeople');


 
function do_listpeople($attr, $content = null) {
	global $wpdb;
	//, $table_prefix, $post;

	extract(shortcode_atts(array(
		'type' => '',
		'size'	=> '',
		'height'	=> '',
		'width'	=> '',
		'type' => '',
		'excerptonly' => ''
		), $attr));
	
	//if ( 1 > (int) $width || empty($caption) ) {return $val; }
	$thesize = 'thumbnail';
	if ( $size >'') {$thesize = $size; }
	if ( ((int) $width )&& ((int) $height) ) {$thesize = array((int)$width, (int)$height); }
	$thesize = array (70,192);
	//$loop = new WP_Query( array( 'post_type' => 'people', 'posts_per_page' => 1000 , 'taxonomy' => 'Type' , 'term' => 'staff' ) );
	$query = array();
	$query['post_type'] = 'people';
	$query['posts_per_page'] = -1 ;
	if ($type)  {$query['types'] = $type; }
	
	$loop = new WP_Query($query );
	//$loop = new WP_Query( array( 'post_type' => 'people', 'posts_per_page' => 1000 , 'types' => 'staff' ) );
	$alinkend = '';
	if ($excerptonly) { $alinkend = '</a>';}
	
	$output = "<ul id='peoplelist'>";
	while ( $loop->have_posts() ) : $loop->the_post();
		$postid = $post->ID;
		$thelink = get_permalink($postid);
		$postslug = $post->post_name;
		$thumbnail = get_the_post_thumbnail( $postid, $thesize );
		
		if ( (int)$width && (int) $height) {
			$thumb = get_post_thumbnail_id(); 
			$thumbnail = vt_resize( $thumb, '', $width, $height, true );
			$thumbnail = '<img src="' .   $thumbnail[url] .  '" width="' .$thumbnail[width] .'" height="' . $thumbnail[height]. '" />';
		}

		$post_content = get_the_content($postid  ) ; //$more_link_text, $stripteaser, $more_file )) 
		if ($excerptonly) {
			$post_content = get_the_excerpt();
			$alink = "<a href='$thelink'>";
		}
		
		$post_content = apply_filters('the_content', $post_content);
		$post_title = get_the_title($postid ) ; 
		$output .= "<li id='person_$postslug'>\n\t$alink$thumbnail$alinkend\n\t<div class='descriptionwrap'>\n\t<div class='name'>$alink$post_title$alinkend</div>\n\t<div class='description'>$post_content</div>\n\t</div>\n</li>\n";
	endwhile;
	$output .= "</ul>\n";
	wp_reset_query();
return $output;

}






add_filter( 'add_nifty_posts_columns', 'ilc_cpt_columns' );
add_action('add_nifty_posts_custom_column', 'ilc_cpt_custom_column', 10, 2);

function ilc_cpt_columns($defaults) {
    $defaults['type'] = 'Type';
    return $defaults;
}

function ilc_cpt_custom_column($column_name, $post_id) {
    $taxonomy = $column_name;
    $post_type = get_post_type($post_id);
    $terms = get_the_terms($post_id, $taxonomy);
 
    if ( !empty($terms) ) {
        foreach ( $terms as $term )
            $post_terms[] = "<a href='edit.php?post_type={$post_type}&{$taxonomy}={$term->slug}'> " . esc_html(sanitize_term_field('name', $term->name, $term->term_id, $taxonomy, 'edit')) . "</a>";
        echo join( ', ', $post_terms );
    }
    else echo '<i>No terms.</i>';
}




// courtesy http://www.wpquestions.com/question/show/id/1599
/*
 * Resize images dinamicaly using wp built in functions
 * Victor Teixeira
 *
 * php 5.2+
 *
 * Exemple use:
 * 
 * 
 * $thumb = get_post_thumbnail_id(); 
 * $image = vt_resize( $thumb, '', 140, 110, true );
 * 
 * <img src="  $image[url];  " width="$image[width]; " height=" $image[height]; " />
 *
 * @param int $attach_id
 * @param string $img_url
 * @param int $width
 * @param int $height
 * @param bool $crop
 * @return array
 */
function vt_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {

	// this is an attachment, so we have the ID
	if ( $attach_id ) {
	
		$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
		$file_path = get_attached_file( $attach_id );
	
	// this is not an attachment, let's use the image url
	} else if ( $img_url ) {
		
		$file_path = parse_url( $img_url );
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];
		
		//$file_path = ltrim( $file_path['path'], '/' );
		//$file_path = rtrim( ABSPATH, '/' ).$file_path['path'];
		
		$orig_size = getimagesize( $file_path );
		
		$image_src[0] = $img_url;
		$image_src[1] = $orig_size[0];
		$image_src[2] = $orig_size[1];
	}
	
	$file_info = pathinfo( $file_path );
	$extension = '.'. $file_info['extension'];

	// the image path without the extension
	$no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];

	$cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;

	// checking if the file size is larger than the target size
	// if it is smaller or the same size, stop right here and return
	if ( $image_src[1] > $width || $image_src[2] > $height ) {

		// the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
		if ( file_exists( $cropped_img_path ) ) {

			$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
			
			$vt_image = array (
				'url' => $cropped_img_url,
				'width' => $width,
				'height' => $height
			);
			
			return $vt_image;
		}

		// $crop = false
		if ( $crop == false ) {
		
			// calculate the size proportionaly
			$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
			$resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;			

			// checking if the file already exists
			if ( file_exists( $resized_img_path ) ) {
			
				$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

				$vt_image = array (
					'url' => $resized_img_url,
					'width' => $proportional_size[0],
					'height' => $proportional_size[1]
				);
				
				return $vt_image;
			}
		}

		// no cache files - let's finally resize it
		$new_img_path = image_resize( $file_path, $width, $height, $crop );
		$new_img_size = getimagesize( $new_img_path );
		$new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

		// resized output
		$vt_image = array (
			'url' => $new_img,
			'width' => $new_img_size[0],
			'height' => $new_img_size[1]
		);
		
		return $vt_image;
	}

	// default output - without resizing
	$vt_image = array (
		'url' => $image_src[0],
		'width' => $image_src[1],
		'height' => $image_src[2]
	);
	
	return $vt_image;
}


?>