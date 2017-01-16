<?php
// this code removes the WP emoji features
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// define this constant so we don't get sloggy database queries
define('TEMPLATE_URL',get_template_directory_uri() );


//see also http://wptheming.com/2011/08/admin-notices-in-wordpress/
// http://www.presscoders.com/2011/10/better-theme-activation-handling/
add_action('admin_init', 'newtheme_nag');

function newtheme_nag() {
	global $pagenow;
	if ( is_admin() && isset($_GET['activated']) && $pagenow == "themes.php" ) {
		newtheme_nag_message();
	}
}

function newtheme_nag_message() {
	$msg = '<div class="updated"><p>'; 
	$msg .=  'Please remember to update the <a href="nav-menus.php?action=locations">Menu Location settings</a> after changing the Theme.';
	$msg .=  "</p></div>";
	add_action( 'admin_notices', create_function( '', 'echo "' . addcslashes( $msg, '"' ) . '";' ) );
}

//courtesy http://www.chaosm.net/blog/2013/06/21/how-to-detect-mobile-phones-not-tablets-in-wordpress/
function isMobilePhone() {
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if ( preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent,0,4) ) ) {
	return true;}
	return false;
}

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