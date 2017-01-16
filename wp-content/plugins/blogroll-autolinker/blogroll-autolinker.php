<?php

/*
Plugin Name: Blogroll Autolinker
Plugin URI: http://stevenberg.net/projects/blogroll-autolinker/
Description: Automatically turns names from your blogroll into links in your posts.
Version: 1.1
Author: Steven Berg
Author URI: http://stevenberg.net/

    Copyright 2006  Steven Berg  (email : steven.m.berg@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class BlogrollAutolinker {

	function activate() {
		add_option('blogroll-autolinker-begin', '[');
		add_option('blogroll-autolinker-end', ']');
	}

	function init() {
		add_action('admin_menu', array(__CLASS__, 'add_options_page'));
		add_filter('the_content', array(__CLASS__, 'linkify'));
	}

	function add_options_page() {
		add_options_page(
			'Blogroll Autolinker Options',
			'Blogroll Autolinker',
			'manage_options',
			'blogroll-autolinker-options',
			array(__CLASS__, 'options_page'));
	}
	
	function linkify($content) {
	    $names = array();
	    $links = array();
	    foreach (get_bookmarks() as $link) {
	        $names[] =
	            get_option('blogroll-autolinker-begin') .
	            $link->link_name .
	            get_option('blogroll-autolinker-end');
	           //bkj added
			   $targ= get_option('blogroll-autolinker-targ');
			   if ($targ>'') {$targ=' target="' . $targ .'"';}
			   //bkj added $targ to links[]
	        $links[] = "<a href='{$link->link_url}' $targ rel='external {$link->link_rel}' title='{$link->link_description}'>{$link->link_name}</a>";
	    }
	    return str_replace($names, $links, $content);
	}

	function options_page() {
		global $wpdb;
		if (!current_user_can('manage_options')) {
			die('You don&#8217;t have sufficient permission to access this file.');
		}
		if (isset($_POST['update'])) {
			check_admin_referer('blogroll-autolinker-update-options');
			update_option('blogroll-autolinker-begin', $wpdb->escape($_POST['begin']));
			update_option('blogroll-autolinker-end', $wpdb->escape($_POST['end']));
			//bkj added:
			update_option('blogroll-autolinker-targ', $wpdb->escape($_POST['targ']));
			echo '<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>';
		}
?>
<div class="wrap">
<h2>Blogroll Autolinker Options</h2>
<p>With your current options, you can insert a link like this: <strong><code><?php echo get_option('blogroll-autolinker-begin') ?>name<?php echo get_option('blogroll-autolinker-end') ?></code></strong>.</p>
<form method="post" action="">
<?php if (function_exists('wp_nonce_field')) wp_nonce_field('blogroll-autolinker-update-options'); ?>
<table class="optiontable">
<tr valign="top">
<th scope="row">Begin linked name:</th>
<td><input name="begin" id="begin" type="text" value="<?php form_option('blogroll-autolinker-begin') ?>" size="1" /></td>
</tr>
<tr valign="top">
<th scope="row">End linked name:</th>
<td><input name="end" id="end" type="text" value="<?php form_option('blogroll-autolinker-end') ?>" size="1" /></td>
</tr>
<?php // bkj added: ?>
<tr valign="top">
<th scope="row">Target:</th>
<td><input name="targ" id="targ" type="text" value="<?php form_option('blogroll-autolinker-targ') ?>" size="20" /></td>
</tr>
<?php // bkj added: ?>


</table>
<p class="submit"><input name="update" type="submit" value="Update Options &raquo;" /></p>
</form>
</div>
<?php
	}
}

add_action('init', array('BlogrollAutolinker', 'init'));
add_action('activate_'.basename(__FILE__), array('BlogrollAutolinker', 'activate'));

?>
