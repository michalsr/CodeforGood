<?php
/*
Plugin Name: Search All
Plugin URI: http://kinrowan.net/blog/wordpress/search-all
Description: Adds capability to search pages, attachments, draft posts, and comments to the WP default search, with an admin console to contol options.
Heavy props to <a href="http://randomfrequency.net">David B. Nagle</a> for <a href="http://randomfrequency.net/wordpress/search-pages/">Search Pages</a> and <a href="http://dancameron.org/">Dan Cameron</a> for <a href="http://dancameron.org/wordpress/wordpress-plugins/search-everything-wordpress-plugin/">Search Everything</a> for prior art.
Version: 0.2
Author: Cori Schlegel
Author URI: http://kinrowan.net
Changes:
	0.1	initial version
	0.2	fixed a bug in comments searching
*/

/*  Cori Schlegel  (email : cori@kinrowan.net)

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

//USING namespace prefix 'SA' for search all

//logging
$logging = 0;

function SA_log($msg) {
	global $logging;
	if ($logging) {
		$fp = fopen("logfile.log","a+");
		$date = date("Y-m-d H:i:s ");
		$source = "search-all plugin: ";
		fwrite($fp, "\n\n".$date."\n".$source."\n".$msg);
		fclose($fp);
	}
	return true;
	}



//add filters based upon option settings
if ("true" == get_option('SA_use_page_search')) {
	add_filter('posts_where', 'SA_search_pages');
	SA_log("searching pages");
	}

if ("true" == get_option('SA_use_comment_search')) {
	add_filter('posts_where', 'SA_search_comments');
	add_filter('posts_join', 'SA_comments_join');
	SA_log("searching comments");
	}

if ("true" == get_option('SA_use_draft_search')) {
	add_filter('posts_where', 'SA_search_draft_posts');
	SA_log("searching drafts");
	}

if ("true" == get_option('SA_use_attachment_search')) {
	add_filter('posts_where', 'SA_search_attachments');
	SA_log("searching attachments");
	}

//search pages
function SA_search_pages($where) {
	global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		//$where = str_replace(' AND (post_status = "publish"', ' AND (post_status = "publish" or post_status = "static"', $where);
		//bkj added:
		
		//$where = str_replace("(post_type = 'post' AND (post_status = 'publish'))","( (post_type = 'page' OR post_type='post') AND (post_status = 'publish'))",$where);
		$where = str_replace("post_type = 'post'"," (post_type = 'page' OR post_type='post')",$where);

	}

	//echo "searching pages... $where<BR>";
	SA_log("pages where: ".$where);
	return $where;
}

//search drafts
function SA_search_draft_posts($where) {
	global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$where = str_replace(' AND (post_status = "publish"', ' AND (post_status = "publish" or post_status = "draft"', $where);
	}

	SA_log("drafts where: ".$where);
	return $where;
}

//search attachments
function SA_search_attachments($where) {
	global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$where = str_replace(' AND (post_status = "publish"', ' AND (post_status = "publish" or post_status = "attachment"', $where);
		$where = str_replace('AND post_status != "attachment"','',$where);
	}

	SA_log("attachments where: ".$where);
	return $where;
}

//search comments
function SA_search_comments($where) {
global $wp_query;
	if (!empty($wp_query->query_vars['s'])) {
		$where .= " OR (comment_content LIKE '%" . $wp_query->query_vars['s'] . "%') ";
	}

	SA_log("comments where: ".$where);

	return $where;
}

//join for searching comments
function SA_comments_join($join) {
	global $wp_query, $wpdb;

	if (!empty($wp_query->query_vars['s'])) {

		if ('true' == get_option('SA_approved_comments_only')) {
			$comment_approved = " AND comment_approved =  '1'";
  		} else {
			$comment_approved = '';
    	}

		$join .= "LEFT JOIN $wpdb->comments ON ( comment_post_ID = ID " . $comment_approved . ") ";
	}
	SA_log("comments join: ".$join);
	return $join;
}


//build admin interface
function SA_option_page() {

global $wpdb, $table_prefix;

	if ( isset($_POST['SA_update_options']) ) {

		$errs = array();

		if ( !empty($_POST['search_pages']) ) {
			update_option('SA_use_page_search', "true");
		} else {
			update_option('SA_use_page_search', "false");
		}

		if ( !empty($_POST['search_comments']) ) {
			update_option('SA_use_comment_search', "true");
		} else {
			update_option('SA_use_comment_search', "false");
		}

		if ( !empty($_POST['appvd_comments']) ) {
			update_option('SA_approved_comments_only', "true");
		} else {
			update_option('SA_approved_comments_only', "false");
		}

		if ( !empty($_POST['search_drafts']) ) {
			update_option('SA_use_draft_search', "true");
		} else {
			update_option('SA_use_draft_search', "false");
		}

		if ( !empty($_POST['search_attachments']) ) {
			update_option('SA_use_attachment_search', "true");
		} else {
			update_option('SA_use_attachment_search', "false");
		}

		if ( empty($errs) ) {
			echo '<div id="message" class="updated fade"><p>Options updated!</p></div>';
		} else {
			echo '<div id="message" class="error fade"><ul>';
			foreach ( $errs as $name => $msg ) {
				echo '<li>'.wptexturize($msg).'</li>';
			}
			echo '</ul></div>';
	 }
	} // End if update

	//set up option checkbox values
	if ('true' == get_option('SA_use_page_search')) {
		$page_search = 'checked="true"';
	} else {
		$page_search = '';
	}

	if ('true' == get_option('SA_use_comment_search')) {
		$comment_search = 'checked="true"';
	} else {
		$comment_search = '';
	}

	if ('true' == get_option('SA_approved_comments_only')) {
		$appvd_comment = 'checked="true"';
	} else {
		$appvd_comment = '';
	}

	if ('true' == get_option('SA_use_draft_search')) {
		$draft_search = 'checked="true"';
	} else {
		$draft_search = '';
	}

	if ('true' == get_option('SA_use_attachment_search')) {
		$attachment_search = 'checked="true"';
	} else {
		$attachment_search = '';
	}

	?>

	<div style="width:75%;" class="wrap" id="SA_options_panel">
	<h2>Search All Options</h2>

	<div id="searchform"><?php include (TEMPLATEPATH . '/searchform.php'); ?></div>

	<p>Select the options you'd like to use for seaching.<br />
	Any items selected here will be searched in every search query on the site, in addition to the built-in post search<br />
	Use the search box above to test your results (this will not work with some themes).</p>

	<form method="post">

	<table id="search_options" cell-spacing="2" cell-padding="2">
		<tr>
			<td class="col1"><input type="checkbox" name="search_pages" value="<?php echo get_option('SA_use_page_search'); ?>" <?php echo $page_search; ?> /></td>
			<td class="col2">Search pages</td>
		</tr>
		<tr>
			<td class="col1"><input type="checkbox" name="search_comments" value="<?php echo get_option('SA_use_comment_search'); ?>" <?php echo $comment_search; ?> /></td>
			<td class="col2">Search comments</td>
		</tr>
		<tr class="child_option">
			<td>&nbsp;</td>
			<td>
				<table>
					<tr>
						<td class="col1"><input type="checkbox" name="appvd_comments" value="<?php echo get_option('SA_approved_comments_only'); ?>" <?php echo $appvd_comment; ?> /></td>
						<td class="col2">Approved comments only?</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="col1"><input type="checkbox" name="search_drafts" value="<?php echo get_option('SA_use_draft_search'); ?>" <?php echo $draft_search; ?> /></td>
			<td class="col2">Search drafts</td>
		</tr>
		<tr>
			<td class="col1"><input type="checkbox" name="search_attachments" value="<?php echo get_option('SA_use_attachment_search'); ?>" <?php echo $attachment_search; ?> /></td>
			<td class="col2">Search attachments</td>
		</tr>
	</table>

	<p class="submit">
	<input type="submit" name="SA_update_options" value="Update &raquo;"/>
	</p>
	</form>

	</div>

	<?php
}	//end SA_option_page

function SA_add_options_panel() {
	add_options_page('Search All Options', 'Search All Options', 1, 'sa_options_page', 'SA_option_page');
}
add_action('admin_menu', 'SA_add_options_panel');

//styling options page
function SA_options_style() {
	?>
	<style>

	table#search_options {
		table-layout: auto;
 	}


 	#search_options td.col1, #search_options th.col1 {
		width: 30px;
		text-align: left;
  	}

 	#search_options td.col2, #search_options th.col2 {
		width: 220px;
		margin-left: -15px;
		text-align: left;
  	}

  	#search_options tr.child_option {
		margin-left: 15px;
		margin-top; -3px;
   }

   #SA_options_panel p.submit {
		text-align: left;
   }

	div#searchform div {
		margin-left: auto;
		margin-right: auto;
		margin-top: 5px;
		margin-bottom: 5px;
 	}

 	</style>

<?php
}


add_action('admin_head', 'SA_options_style');

?>
