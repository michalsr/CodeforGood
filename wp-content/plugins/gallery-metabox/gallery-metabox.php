<?php
/*
Plugin Name: Gallery Metabox
Plugin URI: http://wordpress.org/extend/plugins/gallery-metabox/
Description: Displays all the post's attached images on the Edit screen. Uses pre-WordPress 3.5 style of media manager. This version is modified to fix problems with 3.5 style editor.
Author: Bill Erickson
Version: 1001.5a001
Author URI: http://www.billerickson.net
Version History: 1001.4a001 added width/height to size of thumbnails shown.
	Based on version 1.5.
	TODO: fix cropping/scaling/resize dialogso it fits properly.
	Updated by bkj.info to correct WP3.5+ problem in media gallery editor css, 
	added hover and thickbox to get more information about each thumbnail, 
	reveal thumbnail's order, link to standard media gallery.

*/

class BE_Gallery_Metabox
{

	/**
	 * This is our constructor
	 *
	 * @return BE_Gallery_Metabox
	 */
	public function __construct() {

		add_action( 'init',					array( $this, 'translations'	)	);
		add_action( 'add_meta_boxes',		  array( $this, 'admin_scripts'   ), 5 );
		add_action( 'add_meta_boxes',		  array( $this, 'metabox_add'	 )	);
		add_action( 'wp_ajax_refresh_metabox', array( $this, 'refresh_metabox' )	);
		add_action( 'wp_ajax_gallery_remove',  array( $this, 'gallery_remove'  )	);
	}

	/**
	 * Translations
	 * @since 1.0
	 *
	 * @author Bill Erickson
	 */

	public function translations() {
		load_plugin_textdomain( 'gallery-metabox', false, basename( dirname( __FILE__ ) ) . '/lib/languages' );
	}

	/**
	 * AJAX scripts to load on call
	 * @since 1.5
	 *
	 * @author Bill Erickson
	 */

	public function admin_scripts() {

		wp_register_script( 'gallery-metabox-ajax', plugins_url( '/lib/js/gallery-metabox-ajax.js', __FILE__ ) , array( 'jquery' ), null, true );
		wp_register_style( 'gallery-metabox-style', plugins_url( '/lib/css/gallery-metabox-style.css', __FILE__ ), array(), null, 'all' );

	}
	/**
	 * Add the Metabox
	 * @since 1.0
	 *
	 * @author Bill Erickson
	 */
	public function metabox_add() {
		$gallery_metabox_options = get_option( 'gallery_metabox_options' );
		if (count($gallery_metabox_options)<1) { $gallery_metabox_options = array('post','page'); }

		// Filterable metabox settings. 
		$post_types		= apply_filters( 'be_gallery_metabox_post_types', $gallery_metabox_options ); // 'post', 'page') );
	//	$post_types		= apply_filters( 'be_gallery_metabox_post_types', array( 'post', 'page') );
		$context		= apply_filters( 'be_gallery_metabox_context', 'normal' );
		$priority		= apply_filters( 'be_gallery_metabox_priority', 'high' );
		if (!is_array($post_types)) {
			$post_types = array('post','page', 'artist');
		}
		
		// Loop through all post types
		foreach( $post_types as $post_type ) {
			
			// Get post ID
			if( isset( $_GET['post'] ) ) $post_id = $_GET['post'];
			elseif( isset( $_POST['post_ID'] ) ) $post_id = $_POST['post_ID'];
			if( !isset( $post_id ) ) $post_id = false;
			
			// Granular filter so you can limit it to single page or page template
			if( apply_filters( 'be_gallery_metabox_limit', true, $post_id ) ) {
				// Add Metabox
				add_meta_box( 'be_gallery_metabox', __( 'Gallery Images', 'gallery-metabox' ), array( $this, 'gallery_metabox' ), $post_type, $context, $priority );
				// Add Necessary Scripts and Styles
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_script( 'gallery-metabox-ajax' );
				wp_enqueue_style( 'gallery-metabox-style' );
			}

		}
	}

	/**
	 * Build the Metabox
	 * @since 1.0
	 *
	 * @param object $post
	 *
	 * @author Bill Erickson
	 */

	public function gallery_metabox( $post ) {
		
		$original_post = $post;
		echo $this->gallery_metabox_html( $post->ID );
		$post = $original_post;
	}

	/** 
	 * Image array for gallery metabox
	 * @since 1.3
	 *
	 * @param int $post_id
	 * @return string html output 
	 *
	 * @author Bill Erickson
	 */
	public function gallery_images( $post_id ) {

		$args = array(
			'post_type'		 => 'attachment',
			'post_status'	   => 'inherit',
			'post_parent'	   => $post_id,
			'post_mime_type'	=> 'image',
			'posts_per_page'	=> -1,
			'order'			 => 'ASC',
			'orderby'		   => 'menu_order',
			);

		$args = apply_filters( 'be_gallery_metabox_args', $args );

		$images = get_posts( $args );

		return $images;

	}

	/** 
	 * Display setup for images, which include filters and AJAX return
	 * @since 1.3
	 *
	 * @param int $post_id
	 * @return string html output 
	 *
	 * @author Bill Erickson
	 */
	public function gallery_display( $loop ) {
		$gallery_metabox_options = get_option( 'gallery_metabox_options' );
		// bkjchange: make smaller size rather than 'thumbnail'  in $thumbnail variable
		$mywidth = $gallery_metabox_options['gallery_metabox_thumb_size_w'];
		$myheight = $gallery_metabox_options['gallery_metabox_thumb_size_h'];
		if (!$mywidth) {$mywidth = 72;}
		if (!$myheight) {$myheight = 100;}

		$thesize = array($mywidth,$myheight);
		$gallery = '<style>.be_gallery_metabox_item_wrap img { max-height: ' . $myheight .'px; max-width: ' . $mywidth . 'px; }</style>';

		$gallery .= '<div class="be-image-wrapper">';
		foreach( $loop as $image ):
			$id = $image->ID;

			// bkjchange: get sort order
			$menu_order = $image->menu_order;
			//$thumbnail	= wp_get_attachment_image_src( $image->ID, apply_filters( 'be_gallery_metabox_image_size', 'thumbnail') );
			$thumbnail	= wp_get_attachment_image_src( $image->ID, $thesize );
			$full	= wp_get_attachment_image_src( $image->ID, 'full' );
			$resized = $full[3] ? 'resized' : 'original';
			$sizes = gallery_metabox_get_image_size_links($image->ID);
			// bkjchange: get title and stuff
			$titletext = 
				'WxH: ' . $full[1] . 'x' . $full[2]  . '<br />'.
				'Title: ' . $image->post_title . '<br />' .
				'Caption: '  . $image->post_excerpt .  '<br />' .
				'Description: ' . $image->post_content ;
			$title_hover = "<div class='be_gallery_metabox_hover' id='be_gallery_metabox_hover_id_$id'>" . $titletext . "</div>";
			
			$title2 = ' title="' . str_replace('"','&quot;',$titletext) . '" ';
			
			$gallery .= "<span class='be_gallery_metabox_item_wrap'><span class='be_gallery_metabox_menu_order'>$menu_order</span>";
			// bkjchange: wrap link to full size image
			$gallery .= "<a href='{$full[0]}?' $title2 class='be_gallery_metabox_fullsize thickbox' rel='gallery-metabox' target='_blank'>";
			$gallery .= apply_filters( 'be_gallery_metabox_output', '<img src="' . $thumbnail[0] . '" alt="' . $image->post_title .
				'" data-id="' . $image->ID .
			 	'" rel="' . $image->ID . 
				'" title="' . $image->post_content . '" /> ', $thumbnail[0], $image );
			$gallery .= "</a>";
			// removal button
			$gallery .= apply_filters( 'be_gallery_metabox_remove', '<span class="be-image-remove" rel="' . $image->ID .'"><img src="' . plugins_url('/lib/img/cross-circle.png', __FILE__) . '" alt="Remove Image" title="Remove Image"></span>' ); 
			$gallery .= $title_hover;
			$gallery .= '</span>';
		endforeach;
		
		$gallery .= '</div>';

		return $gallery;

	}

	/** 
	 * Gallery Metabox HTML 
	 * @since 1.3
	 *
	 * @param int $post_id
	 * @return string html output 
	 *
	 * @author Bill Erickson
	 */
	public function gallery_metabox_html( $post_id ) {
		
		$return = '';
		
		$intro	= '<p class="be-metabox-links">';
		$intro	.= '<a href="media-upload.php?post_id=' . $post_id .'&amp;type=image&amp;TB_iframe=1&amp;width=640&amp;height=715" id="add_image" class="be-button thickbox button-secondary" title="' . __( 'Add Image', 'gallery-metabox' ) . '">' . __( 'Upload Images', 'gallery-metabox' ) . '</a>';
		$intro	.= '<a href="#" id="be_gallery_metabox_standardgallery" class="be-button button-secondary" >' . __( 'Manage Gallery (standard)', 'gallery-metabox' ) . '</a>';
		$intro	.= '<a href="media-upload.php?post_id=' . $post_id .'&amp;type=image&amp;tab=gallery&amp;TB_iframe=1&amp;width=640&amp;height=715" id="manage_gallery" class="thickbox be-button button-secondary" title="' . __( 'Manage Oldstyle Gallery', 'gallery-metabox' ) . '">' . __( 'Manage Oldstlye Gallery', 'gallery-metabox' ) . '</a>';
		$intro	.= '<input id="update-gallery" class="be-button button-secondary" type="button" value="Update Gallery" name="update-gallery"></p>';
		
		$return .= apply_filters( 'be_gallery_metabox_intro', $intro );

		
		$loop = $this->gallery_images( $post_id );

		if( empty( $loop ) ) { $return .= '<p>No images.</p>'; }
		else {$return .= '<p>The order of images shown may be different when you use the [gallery] shortcode.</p>';}
		$gallery = $this->gallery_display( $loop );

		$return .= $gallery;

		return $return;
	}

	/**
	 * Gallery Metabox AJAX Update
	 * @since 1.5
	 *
	 * This function will refresh image gallery on AJAX call.
	 *
	 *
	 * @author Andrew Norcross
	 *
	 */
	
	public function refresh_metabox() {

		$parent	= $_POST['parent'];
		$loop = $this->gallery_images( $parent );
		$images	= $this->gallery_display( $loop );

		$ret = array();

		if( !empty( $parent ) ) {
			$ret['success'] = true;
			$ret['gallery'] = $images;
		} else {
			$ret['success'] = false;
		}

		echo json_encode( $ret );
		die();
	}

	/**
	 * Gallery image removal
	 * @since 1.5
	 *
	 * This function will remove the image from the gallery by setting
	 * the post_parent to 0
	 *
	 * @author Andrew Norcross
	 *
	 */
	
	public function gallery_remove() {

		// content from AJAX post
		$image = $_POST['image'];
		$parent	= $_POST['parent'];

		// no image ID came through, so bail
		if( empty( $image ) ) {
			$ret['success'] = false;
			echo json_encode( $ret );
			die();
		}

		// removal function
		$remove				 = array();
		$remove['ID']		   = $image;
		$remove['post_parent']	= 0;

		$update = wp_update_post( $remove );

		// AJAX return array
		$ret = array();

		if( $update !== 0 ) {

			// loop to refresh the gallery
			$loop = $this->gallery_images( $parent );
			$images	= $this->gallery_display( $loop );
			// return values
			$ret['success'] = true;
			$ret['gallery'] = $images;

		} else {
			// failure return. can probably make more verbose
			$ret['success'] = false;

		}

		echo json_encode( $ret );
		die();
	}

}


// Instantiate our class
$BE_Gallery_Metabox = new BE_Gallery_Metabox();
// bkjchange: add stylesheet to Gallery page
function be_gallery_metabox_admin_theme_style() {
	// add it only when we're on the gallery page
	if (@$_GET['tab']=='gallery') {
		wp_enqueue_style('be_gallery_metabox-admin-theme', plugins_url('lib/css/gallery-metabox-style.css', __FILE__));
	}
}
add_action('admin_enqueue_scripts', 'be_gallery_metabox_admin_theme_style');


include('gallery-metabox-settings.php');


// from http://justintadlock.com/archives/2011/01/28/linking-to-all-image-sizes-in-wordpress
function gallery_metabox_get_image_size_links($id) {

	/* If not viewing an image attachment page, return. */
	if ( !wp_attachment_is_image( $id ) )
		return;

	/* Set up an empty array for the links. */
	$links = array();

	/* Get the intermediate image sizes and add the full size to the array. */
	$sizes = get_intermediate_image_sizes();
	$sizes[] = 'full';

	/* Loop through each of the image sizes. */
	foreach ( $sizes as $size ) {

		/* Get the image source, width, height, and whether it's intermediate. */
		$image = wp_get_attachment_image_src( $id, $size );

		/* Add the link to the array if there's an image and if $is_intermediate (4th array value) is true or full size. */
		if ( !empty( $image ) && ( true == $image[3] || 'full' == $size ) )
			$links[] = "<a class='image-size-link' href='{$image[0]}'>{$image[1]}&times;{$image[2]}</a>";
	}

	/* Join the links in a string and return. */
	return join( ' <span class="sep">, </span> ', $links );
}


// Add settings link on plugin page
function be_gallery_metabox_plugin_settings_link($links) { 
	$settings_link = '<a href="options-general.php?page=gallery_metabox-setting-admin">Settings</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'be_gallery_metabox_plugin_settings_link' );
