<?php
/*
Plugin Name: Drop-down menu
Plugin URI: http://goudendouche.com/dropdown-menu-plugin/
Description: A plugin to create css dropdown menu's, based on some work stolen by <a href="http://www.jeroenonstenk.nl">Joid</a>.
Author: Zoute snor
Version: 0.2
Author URI: http://goudendouche.com/

 Copyright 2007 - Zoute snor
 
 All rights reserved. You are free to use this software and redistribute it for free but may not include it in any commercial distribution without prior written permission.
 
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if (isset($_GET['action']) && $_GET['action'] == 'activate') { 
gdd_dropdown_install();
}

function gdd_dropdown_install() {
add_option('dropdown_list_pages', '1');
add_option('dropdown_menu_file', '0');
add_option('dropdown_use_register', '1');
}

function gdd_dropdown_adminpage() {
	add_management_page('Dropdown Menu Management', 'Dropdown Menu', 'edit_plugins', "dropdown_menu", 'gdd_dropdown_admin');
}

function gdd_dropdown_admin() {

		if ( !current_user_can('edit_plugins') )
			wp_die('<p>'.__('You do not have sufficient permissions to edit templates for this blog.').'</p>');
?>
<div class="wrap">
<h2>Admin for your dropdown menu's</h2>
<form name="gdd_dropdown_options" action="edit.php?page=dropdown_menu" method="get" id="dropdown_options">
	<input type="hidden" name="page" value="dropdown_menu" />
	<?php _e('Use page list to build menu',dropdown_menu);?>: <input type="checkbox" name="listpages" <?php if(get_option('dropdown_list_pages') == '1') echo 'checked="checked" '; ?>/> <br /><br />
	<?php _e('Use custom menu file to build menu',dropdown_menu);?>: <input type="checkbox" name="menufile" <?php if(get_option('dropdown_menu_file') == '1') echo 'checked="checked" '; ?>/><br /><br />
	<?php _e('Use wp_register() to link to admin or login page',dropdown_menu);?>: <input type="checkbox" name="useregister" <?php if(get_option('dropdown_use_register') == '1') echo 'checked="checked" '; ?>/><br /><br />
	<input type="submit" name="gdd_dropdown_options" value="<?php _e('Save',dropdown_menu);?>" class="button" style="font-size: 100%"  /><br /><br />
</form>
<p><a href="/wp-admin/templates.php?file=wp-content/plugins/drop-down-menu/custom_menu.php">Edit custom menu file.</a>
</p></div><?php
}

function gdd_dropdown_options() {
    global $wpdb;
    
    // Security
    if (!current_user_can('edit_plugins'))
        die('Not today');

	$listpages = ($_GET['listpages']) ? "1" : "0";
	$menufile = ($_GET['menufile']) ? "1" : "0";
	$useregister = ($_GET['useregister']) ? "1" : "0";

	update_option('dropdown_list_pages', $listpages);
	update_option('dropdown_menu_file', $menufile);
	update_option('dropdown_use_register', $useregister);
}

function gdd_load_dropdown_style($unused) { 
	$gdd_wp_url = get_bloginfo('wpurl') . "/";

	echo '
	<!-- Added By Drop-down Menu Plugin -->
	<link rel="stylesheet" href="'.$gdd_wp_url.'wp-content/plugins/drop-down-menu/dropdown_style.css" type="text/css" />
	<style type="text/css">
	body { behavior: url("'.$gdd_wp_url.'wp-content/plugins/drop-down-menu/csshover.htc"); }
	</style>
	';
}
	//bkj added: $options=
function gdd_dropdown_menu($home='Home',$options='sort_column=menu_order&title_li=&exclude=2,3,4,5,6,7,8,9') {

	$gdd_wp_url = get_bloginfo('wpurl') . "/";
	/* bkj was 
	echo '<table width=100%><tr><td>
	<div id="dropdownmenu">
	<ul><li class="page_item"><a href="'.$gdd_wp_url.'" title="'.$home.'">'.$home.'</a></li>';
	*/
	echo '<table width="100%"><tr><td>
	<div id="dropdownmenu"><ul>';
	
	$listpages = (get_option('dropdown_list_pages') == "1") ? TRUE : FALSE;
	if ($options>'') {
		//bkj added:
	
			$output = wp_list_pages($options);
	}
	else {
	if ($listpages)
		$output = wp_list_pages('sort_column=menu_order&echo=1&title_li=');
	}
	$output = str_replace(" class='children'",'',$output);
	echo $output;
	
	$menufile = (get_option('dropdown_menu_file') == "1") ? TRUE : FALSE;
	if ($menufile)
		require_once('wp-content/plugins/drop-down-menu/custom_menu.php');

	$useregister = (get_option('dropdown_use_register') == "1") ? TRUE : FALSE;
	if ($useregister)
		wp_register('<li class="admintab">','</li>'); 
	echo '</ul>
	</div>
	</td>
	</tr></table>';

}

if (function_exists('add_action')) {
	add_action('wp_head', 'gdd_load_dropdown_style'); 
	add_action('admin_menu', 'gdd_dropdown_adminpage');
	if (isset($_GET['gdd_dropdown_options']))
    	add_action('init', 'gdd_dropdown_options');
}