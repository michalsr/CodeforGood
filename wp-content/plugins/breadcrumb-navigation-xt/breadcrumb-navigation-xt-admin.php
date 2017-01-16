<?php
/*
	Breadcrumb-navigation-xt administration interface
	Version 1.10.0
	Copyright 2007 John Havlik
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	There is absolutly no need to modify anything in this file.
*/
$breadcrumb_nav_xt_admin_version = "1.10.1";

//Administration input complex, replaces the broken WordPress one
//Based off of the suggestions and code of Tom Klingenberg
function bcn_get($varname)
{
	$val = $_POST[$varname];
	$val = stripslashes($val);
	//Keep out spaces please ;)
	if(isset($_POST['bcn_preserve_space']))
	{
		update_option('breadcrumb_nav_preserve', '1');
		$val = str_replace(" ", "&nbsp;", $val);
	}
	else
	{
		$val = htmlspecialchars($val);
	}
	return $val;
}
//Security function
function breadcrumb_nav_xt_security()
{
	global $user_level, $breadcrumb_nav_xt_admin_req;
	get_currentuserinfo();
	if ($user_level <  $pharaoh_admin_req) { die('Bad User, No Cookie For You'); }
}
//Install script
function breadcrumb_nav_xt_install()
{
	global $breadcrumb_nav_xt_admin_req, $bcn_version;
	breadcrumb_nav_xt_security();
	if(get_option(breadcrumb_nav_version) != $bcn_version)
	{
		update_option('breadcrumb_nav_version' , $bcn_version);
		update_option('breadcrumb_nav_preserve', 0);
		update_option('breadcrumb_nav_static_frontpage', 'false');
		update_option('breadcrumb_nav_url_blog', '');
		update_option('breadcrumb_nav_home_display', 'true');
		update_option('breadcrumb_nav_home_link', 'true');
		update_option('breadcrumb_nav_title_home', 'Home');
		update_option('breadcrumb_nav_title_blog', 'Blog');
		update_option('breadcrumb_nav_separator', '&nbsp;>&nbsp;');
		update_option('breadcrumb_nav_search_prefix', 'Search results for &#39;');
		update_option('breadcrumb_nav_search_suffix', '&#39;');
		update_option('breadcrumb_nav_author_prefix', 'Posts by ');
		update_option('breadcrumb_nav_author_suffix', '');
		update_option('breadcrumb_nav_author_display', 'display_name');
		update_option('breadcrumb_nav_singleblogpost_prefix', 'Blog article: ');
		update_option('breadcrumb_nav_singleblogpost_suffix', '');
		update_option('breadcrumb_nav_page_prefix', '');
		update_option('breadcrumb_nav_page_suffix', '');
		update_option('breadcrumb_nav_urltitle_prefix', 'Browse to: ');
		update_option('breadcrumb_nav_urltitle_suffix', '');
		update_option('breadcrumb_nav_archive_category_prefix', 'Archive by category &#39;');
		update_option('breadcrumb_nav_archive_category_suffix', '&#39;');
		update_option('breadcrumb_nav_archive_date_prefix', 'Archive: ');
		update_option('breadcrumb_nav_archive_date_suffix', '');
		update_option('breadcrumb_nav_archive_date_format', 'EU');
		update_option('breadcrumb_nav_attachment_prefix', 'Attachment:&nbsp;');
		update_option('breadcrumb_nav_attachment_suffix', '');
		update_option('breadcrumb_nav_archive_tag_prefix', 'Archive by tag &#39;');
		update_option('breadcrumb_nav_archive_tag_suffix', '&#39;');
		update_option('breadcrumb_nav_title_404', '404');
		update_option('breadcrumb_nav_link_current_item', 'false');
		update_option('breadcrumb_nav_current_item_urltitle', 'Link of current page (click to refresh)');
		update_option('breadcrumb_nav_current_item_style_prefix', '');
		update_option('breadcrumb_nav_current_item_style_suffix', '');
		update_option('breadcrumb_nav_posttitle_maxlen', 0);
		update_option('breadcrumb_nav_singleblogpost_category_display', 'true');
		update_option('breadcrumb_nav_singleblogpost_category_maxdisp', 0);
		update_option('breadcrumb_nav_singleblogpost_category_prefix', '');
		update_option('breadcrumb_nav_singleblogpost_category_suffix', '');
	}
}
//Display a breadcrumb, only used if admin interface is used
function breadcrumb_nav_xt_display()
{
	//Playing things really safe here
	if(class_exists('breadcrumb_navigation_xt'))
	{
		//Make new breadcrumb object
		$breadcrumb_nav_xt = new breadcrumb_navigation_xt;
		//Set the settings
		$breadcrumb_nav_xt->opt['static_frontpage'] = get_option('breadcrumb_nav_static_frontpage');
		$breadcrumb_nav_xt->opt['url_blog'] = get_option('breadcrumb_nav_url_blog');
		$breadcrumb_nav_xt->opt['home_display'] = get_option('breadcrumb_nav_home_display');
		$breadcrumb_nav_xt->opt['home_link'] = get_option('breadcrumb_nav_home_link');
		$breadcrumb_nav_xt->opt['title_home'] = get_option('breadcrumb_nav_title_home');
		$breadcrumb_nav_xt->opt['title_blog'] = get_option('breadcrumb_nav_title_blog');
		$breadcrumb_nav_xt->opt['separator'] = get_option('breadcrumb_nav_separator');
		$breadcrumb_nav_xt->opt['search_prefix'] = get_option('breadcrumb_nav_search_prefix');
		$breadcrumb_nav_xt->opt['search_suffix'] = get_option('breadcrumb_nav_search_suffix');
		$breadcrumb_nav_xt->opt['author_prefix'] = get_option('breadcrumb_nav_author_prefix');
		$breadcrumb_nav_xt->opt['author_suffix'] = get_option('breadcrumb_nav_author_suffix');
		$breadcrumb_nav_xt->opt['author_display'] = get_option('breadcrumb_nav_author_display');
		$breadcrumb_nav_xt->opt['attachment_prefix'] = get_option('breadcrumb_nav_attachment_prefix');
		$breadcrumb_nav_xt->opt['attachment_suffix'] = get_option('breadcrumb_nav_attachment_suffix');
		$breadcrumb_nav_xt->opt['singleblogpost_prefix'] = get_option('breadcrumb_nav_singleblogpost_prefix');
		$breadcrumb_nav_xt->opt['singleblogpost_suffix'] = get_option('breadcrumb_nav_singleblogpost_suffix');
		$breadcrumb_nav_xt->opt['page_prefix'] = get_option('breadcrumb_nav_page_prefix');
		$breadcrumb_nav_xt->opt['page_suffix'] = get_option('breadcrumb_nav_page_suffix');
		$breadcrumb_nav_xt->opt['urltitle_prefix'] = get_option('breadcrumb_nav_urltitle_prefix');
		$breadcrumb_nav_xt->opt['urltitle_suffix'] = get_option('breadcrumb_nav_urltitle_suffix');
		$breadcrumb_nav_xt->opt['archive_category_prefix'] = get_option('breadcrumb_nav_archive_category_prefix');
		$breadcrumb_nav_xt->opt['archive_category_suffix'] = get_option('breadcrumb_nav_archive_category_suffix');
		$breadcrumb_nav_xt->opt['archive_date_prefix'] = get_option('breadcrumb_nav_archive_date_prefix');
		$breadcrumb_nav_xt->opt['archive_date_suffix'] = get_option('breadcrumb_nav_archive_date_suffix');
		$breadcrumb_nav_xt->opt['archive_date_format'] = get_option('breadcrumb_nav_archive_date_format');
		$breadcrumb_nav_xt->opt['archive_tag_prefix'] = get_option('breadcrumb_nav_archive_tag_prefix');
		$breadcrumb_nav_xt->opt['archive_tag_suffix'] = get_option('breadcrumb_nav_archive_tag_suffix');
		$breadcrumb_nav_xt->opt['title_404'] = get_option('breadcrumb_nav_title_404');
		$breadcrumb_nav_xt->opt['link_current_item'] = get_option('breadcrumb_nav_link_current_item');
		$breadcrumb_nav_xt->opt['current_item_urltitle'] = get_option('breadcrumb_nav_current_item_urltitle');
		$breadcrumb_nav_xt->opt['current_item_style_prefix'] = get_option('breadcrumb_nav_current_item_style_prefix');
		$breadcrumb_nav_xt->opt['current_item_style_suffix'] = get_option('breadcrumb_nav_current_item_style_suffix');
		$breadcrumb_nav_xt->opt['posttitle_maxlen'] = get_option('breadcrumb_nav_posttitle_maxlen');
		$breadcrumb_nav_xt->opt['singleblogpost_category_display'] = get_option('breadcrumb_nav_singleblogpost_category_display');
		$breadcrumb_nav_xt->opt['singleblogpost_category_maxdisp'] = get_option('breadcrumb_nav_singleblogpost_category_maxdisp');
		$breadcrumb_nav_xt->opt['singleblogpost_category_prefix'] = get_option('breadcrumb_nav_singleblogpost_category_prefix');
		$breadcrumb_nav_xt->opt['singleblogpost_category_suffix'] = get_option('breadcrumb_nav_singleblogpost_category_suffix');
		//Display the breadcrumb
		$breadcrumb_nav_xt->display();
	}
}
//Sets the settings
function breadcrumb_nav_xt_admin_options()
{
	global $wpdb, $breadcrumb_nav_xt_admin_req;
	breadcrumb_nav_xt_security();
	if(isset($_POST['bcn_preserve_space'])) 
	{
		$temp = 1;
	}
	else
	{
		$temp = 0;
	} 
	update_option('breadcrumb_nav_preserve', $temp);
	update_option('breadcrumb_nav_static_frontpage', bcn_get('static_frontpage'));
	update_option('breadcrumb_nav_url_blog', bcn_get('url_blog'));
	update_option('breadcrumb_nav_home_display', bcn_get('home_display'));
	update_option('breadcrumb_nav_home_link', bcn_get('home_link'));
	update_option('breadcrumb_nav_title_home', bcn_get('title_home'));
	update_option('breadcrumb_nav_title_blog', bcn_get('title_blog'));
	update_option('breadcrumb_nav_separator', bcn_get('separator'));
	update_option('breadcrumb_nav_search_prefix', bcn_get('search_prefix'));
	update_option('breadcrumb_nav_search_suffix', bcn_get('search_suffix'));
	update_option('breadcrumb_nav_author_prefix', bcn_get('author_prefix'));
	update_option('breadcrumb_nav_author_suffix', bcn_get('author_suffix'));
	update_option('breadcrumb_nav_author_display', bcn_get('author_display'));
	update_option('breadcrumb_nav_attachment_prefix', bcn_get('attachment_prefix'));
	update_option('breadcrumb_nav_attachment_suffix', bcn_get('attachment_suffix'));
	update_option('breadcrumb_nav_singleblogpost_prefix', bcn_get('singleblogpost_prefix'));
	update_option('breadcrumb_nav_singleblogpost_suffix', bcn_get('singleblogpost_suffix'));
	update_option('breadcrumb_nav_page_prefix', bcn_get('page_prefix'));
	update_option('breadcrumb_nav_page_suffix', bcn_get('page_suffix'));
	update_option('breadcrumb_nav_urltitle_prefix', bcn_get('urltitle_prefix'));
	update_option('breadcrumb_nav_urltitle_suffix',	bcn_get('urltitle_suffix'));
	update_option('breadcrumb_nav_archive_category_prefix', bcn_get('archive_category_prefix'));
	update_option('breadcrumb_nav_archive_category_suffix', bcn_get('archive_category_suffix'));
	update_option('breadcrumb_nav_archive_date_prefix', bcn_get('archive_date_prefix'));
	update_option('breadcrumb_nav_archive_date_suffix', bcn_get('archive_date_suffix'));
	update_option('breadcrumb_nav_archive_date_format', bcn_get('archive_date_format'));
	update_option('breadcrumb_nav_archive_tag_prefix', bcn_get('archive_tag_prefix'));
	update_option('breadcrumb_nav_archive_tag_suffix', bcn_get('archive_tag_suffix'));
	update_option('breadcrumb_nav_title_404', bcn_get('title_404'));
	update_option('breadcrumb_nav_link_current_item', bcn_get('link_current_item'));
	update_option('breadcrumb_nav_current_item_urltitle', bcn_get('current_item_urltitle'));
	update_option('breadcrumb_nav_current_item_style_prefix', bcn_get('current_item_style_prefix'));
	update_option('breadcrumb_nav_current_item_style_suffix', bcn_get('current_item_style_suffix'));
	update_option('breadcrumb_nav_posttitle_maxlen', bcn_get('posttitle_maxlen'));
	update_option('breadcrumb_nav_singleblogpost_category_display', bcn_get('singleblogpost_category_display'));
	update_option('breadcrumb_nav_singleblogpost_category_maxdisp', bcn_get('singleblogpost_category_maxdisp'));
	update_option('breadcrumb_nav_singleblogpost_category_prefix', bcn_get('singleblogpost_category_prefix'));
	update_option('breadcrumb_nav_singleblogpost_category_suffix', bcn_get('singleblogpost_category_suffix'));
}
//Creates link to admin interface
function breadcrumb_nav_xt_add_page()
{
	global $breadcrumb_nav_xt_admin_req;
    add_options_page('Breadcrumb Navigation XT Settings', 'Breadcrumb Nav XT', $breadcrumb_nav_xt_admin_req, 'breadcrumb-nav-xt', 'breadcrumb_nav_xt_admin');
}
//The actual interface
function breadcrumb_nav_xt_admin()
{
	global $breadcrumb_nav_xt_admin_req, $breadcrumb_nav_xt_admin_version, $bcn_version;
	breadcrumb_nav_xt_security();
	list($breadcrumb_major, $breadcrumb_minor, $breadcrumb_bugfix) = explode('.', $bcn_version);
	list($major, $minor, $bugfix) = explode('.', $breadcrumb_nav_xt_admin_version);
	if($breadcrumb_major != $major || $breadcrumb_minor != $minor)
	{ ?>
		<div id="message" class="updated fade">
			<p>Warning, your version of Breadcrumb Navigation XT does not match the version supported by 
			this administrative interface. As a result things may not work as intened.</p>
			<p>Your Breadcrumb Navigation XT version is <?php echo $bcn_version;?>.</p>
			<p>Your Breadcrumb Navigation XT Administration interface version is <?php echo $breadcrumb_nav_xt_admin_version;?>.</p>
		</div>
	<?php } ?>
	<div class="wrap"><h2>Breadcrumb Navigation XT Settings:</h2>
	<p>This administration interface allows the full customization of the breadcrumb output with no loss
	of functionality when compared to manual configuration. Each setting is the same as the corresponding
	class option, please refer to the 
	<a title="Go to the Breadcrumb Navigation XT documentation" href="http://mtekk.weblogs.us/code/breadcrumb-nav-xt/breadcrumb-nav-xt-doc/">documentation</a> 
	for more detailed explanation of each setting.</p>
	<form action="options-general.php?page=breadcrumb-nav-xt" method="post" id="breadcrumb_nav_xt_admin_options">
		<fieldset id="general">
			<legend>General Settings</legend>
			<p>
				<label for="bcn_preserve_space">
				<input type="checkbox" name="bcn_preserve_space" id="bcn_preserve_space" <?php if(get_option('breadcrumb_nav_preserve') == 1) echo 'checked="checked"'; ?> />
				Preserve spaces at the end and begining of the options, if not checked these spaces will not be saved.</label>
			</p>
			<p>
				<label for="title_blog">Blog Title:</label>
				<input type="text" name="title_blog" id="title_blog" value="<?php echo get_option('breadcrumb_nav_title_blog'); ?>" size="32" />
			</p>
			<p>
				<label for="separator">Breadcrumb Separator:</label>
				<input type="text" name="separator" id="separator" value="<?php echo get_option('breadcrumb_nav_separator'); ?>" size="32" />
			</p>
			<p>
				<label for="search_prefix">Search Prefix:</label>
				<input type="text" name="search_prefix" id="search_prefix" value="<?php echo get_option('breadcrumb_nav_search_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="search_prefix">Search Suffix:</label>
				<input type="text" name="search_suffix" id="search_suffix" value="<?php echo get_option('breadcrumb_nav_search_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="title_404">404 Title:</label>
				<input type="text" name="title_404" id="title_404" value="<?php echo get_option('breadcrumb_nav_title_404'); ?>" size="32" />
			</p>
		</fieldset>
		<fieldset id="static_front_page">
			<legend>Static Frontpage Settings</legend>
			<p>Static Frontpage: 
				<select name="static_frontpage">
					<?php $breadcrumb_nav_opta = array("true", "false");?>
					<option><?php echo get_option('breadcrumb_nav_static_frontpage'); ?></option>
					<?php foreach($breadcrumb_nav_opta as $option)
					{
						if($option != get_option('breadcrumb_nav_static_frontpage'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="url_blog">Relative Blog URL:</label>
				<input type="text" name="url_blog" id="url_blog" value="<?php echo get_option('breadcrumb_nav_url_blog'); ?>" size="32" />
			</p>
			<p>Display Home: 
				<select name="home_display">
					<?php $breadcrumb_nav_opta = array("true", "false");?>
					<option><?php echo get_option('breadcrumb_nav_home_display'); ?></option>
					<?php foreach($breadcrumb_nav_opta as $option)
					{
						if($option != get_option('breadcrumb_nav_home_display'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>Display Home Link: 
				<select name="home_link">
					<?php $breadcrumb_nav_opta = array("true", "false");?>
					<option><?php echo get_option('breadcrumb_nav_home_link'); ?></option>
					<?php foreach($breadcrumb_nav_opta as $option)
					{
						if($option != get_option('breadcrumb_nav_home_link'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="title_home">Home Title:</label>
				<input type="text" name="title_home" id="title_home" value="<?php echo get_option('breadcrumb_nav_title_home'); ?>" size="32" />
			</p>
		</fieldset>
		<fieldset id="author">
			<legend>Author Page Settings</legend>
			<p>
				<label for="author_prefix">Author Prefix:</label>
				<input type="text" name="author_prefix" id="author_prefix" value="<?php echo get_option('breadcrumb_nav_author_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="author_suffix">Author Suffix:</label>
				<input type="text" name="author_suffix" id="author_suffix" value="<?php echo get_option('breadcrumb_nav_author_suffix'); ?>" size="32" />
			</p>
			<p>Author Display Format: 
				<select name="author_display">
					<?php $breadcrumb_nav_opta = array("display_name", "nickname", "first_name", "last_name");?>
					<option><?php echo get_option('breadcrumb_nav_author_display'); ?></option>
					<?php foreach($breadcrumb_nav_opta as $option)
					{
						if($option != get_option('breadcrumb_nav_author_display'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
		</fieldset>
		<fieldset id="category">
			<legend>Archive Display Settings</legend>
			<p>
				<label for="urltitle_prefix">URL Title Prefix:</label>
				<input type="text" name="urltitle_prefix" id="urltitle_prefix" value="<?php echo get_option('breadcrumb_nav_urltitle_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="urltitle_suffix">URL Title Suffix:</label>
				<input type="text" name="urltitle_suffix" id="urltitle_suffix" value="<?php echo get_option('breadcrumb_nav_urltitle_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_category_prefix">Archive by Category Prefix:</label>
				<input type="text" name="archive_category_prefix" id="archive_category_prefix" value="<?php echo get_option('breadcrumb_nav_archive_category_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_category_suffix">Archive by Category Suffix:</label>
				<input type="text" name="archive_category_suffix" id="archive_category_suffix" value="<?php echo get_option('breadcrumb_nav_archive_category_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_date_prefix">Archive by Date Prefix:</label>
				<input type="text" name="archive_date_prefix" id="archive_date_prefix" value="<?php echo get_option('breadcrumb_nav_archive_date_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_date_suffix">Archive by Date Suffix</label>
				<input type="text" name="archive_date_suffix" id="archive_date_suffix" value="<?php echo get_option('breadcrumb_nav_archive_date_suffix'); ?>" size="32" />
			</p>
			<p>Archive by Date Format: 
				<select name="archive_date_format">
					<?php $breadcrumb_nav_opta = array("EU", "US", "ISO");?>
					<option><?php echo get_option('breadcrumb_nav_archive_date_format'); ?></option>
					<?php foreach($breadcrumb_nav_opta as $option)
					{
						if($option != get_option('breadcrumb_nav_archive_date_format'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="archive_tag_prefix">Archive by Tag Prefix:</label>
				<input type="text" name="archive_tag_prefix" id="archive_tag_prefix" value="<?php echo get_option('breadcrumb_nav_archive_tag_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="archive_tag_suffix">Archive by Tag Suffix:</label>
				<input type="text" name="archive_tag_suffix" id="archive_tag_suffix" value="<?php echo get_option('breadcrumb_nav_archive_tag_suffix'); ?>" size="32" />
			</p>
		</fieldset>
		<fieldset id="current">
			<legend>Current Item Settings</legend>
			<p>Link Current Item: 
				<select name="link_current_item">
					<?php $breadcrumb_nav_opta = array("true", "false");?>
					<option><?php echo get_option('breadcrumb_nav_link_current_item'); ?></option>
					<?php foreach($breadcrumb_nav_opta as $option)
					{
						if($option != get_option('breadcrumb_nav_link_current_item'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="current_item_urltitle">Current Item URL Title:</label>
				<input type="text" name="current_item_urltitle" id="current_item_urltitle" value="<?php echo get_option('breadcrumb_nav_current_item_urltitle'); ?>" size="32" />
			</p>
			<p>
				<label for="current_item_style_prefix">Current Item Style Prefix:</label>
				<input type="text" name="current_item_style_prefix" id="current_item_style_prefix" value="<?php echo get_option('breadcrumb_nav_current_item_style_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="current_item_style_suffix">Current Item Style Suffix:</label>
				<input type="text" name="current_item_style_suffix" id="current_item_style_suffix" value="<?php echo get_option('breadcrumb_nav_current_item_style_suffix'); ?>" size="32" />
			</p>
		</fieldset>
		<fieldset id="single">
			<legend>Single Post Settings</legend>
			<p>
				<label for="singleblogpost_prefix">Single Blogpost Prefix:</label>
				<input type="text" name="singleblogpost_prefix" id="singleblogpost_prefix" value="<?php echo get_option('breadcrumb_nav_singleblogpost_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="singleblogpost_suffix">Single Blogpost Suffix:</label>
				<input type="text" name="singleblogpost_suffix" id="singleblogpost_suffix" value="<?php echo get_option('breadcrumb_nav_singleblogpost_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="page_prefix">Page Prefix:</label>
				<input type="text" name="page_prefix" id="page_prefix" value="<?php echo get_option('breadcrumb_nav_page_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="page_suffix">Page Suffix:</label>
				<input type="text" name="page_suffix" id="page_suffix" value="<?php echo get_option('breadcrumb_nav_page_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="attachment_prefix">Post Attachment Prefix:</label>
				<input type="text" name="attachment_prefix" id="attachment_prefix" value="<?php echo get_option('breadcrumb_nav_attachment_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="attachment_suffix">Post Attachment Suffix:</label>
				<input type="text" name="attachment_suffix" id="attachment_suffix" value="<?php echo get_option('breadcrumb_nav_attachment_suffix'); ?>" size="32" />
			</p>
			<p>
				<label for="title_home">Post Title Maxlen:</label>
				<input type="text" name="posttitle_maxlen" id="posttitle_maxlen" value="<?php echo get_option('breadcrumb_nav_posttitle_maxlen'); ?>" size="10" />
			</p>
			<p>
				<label for="singleblogpost_category_display">Single Blog Post Category Display:</label>
				<select name="singleblogpost_category_display">
					<?php $breadcrumb_nav_opta = array("true", "false");?>
					<option><?php echo get_option('breadcrumb_nav_singleblogpost_category_display'); ?></option>
					<?php foreach($breadcrumb_nav_opta as $option)
					{
						if($option != get_option('breadcrumb_nav_singleblogpost_category_display'))
						{
							echo "<option>" . $option . "</option>";
						}
					}?>
				</select>
			</p>
			<p>
				<label for="singleblogpost_category_maxdisp">Single Blog Post Category Max Display:</label>
				<input type="text" name="singleblogpost_category_maxdisp" id="singleblogpost_category_maxdisp" value="<?php echo get_option('breadcrumb_nav_singleblogpost_category_maxdisp'); ?>" size="10" />
			</p>
			<p>
				<label for="singleblogpost_category_prefix">Single Blog Post Category Prefix:</label>
				<input type="text" name="singleblogpost_category_prefix" id="singleblogpost_category_prefix" value="<?php echo get_option('breadcrumb_nav_singleblogpost_category_prefix'); ?>" size="32" />
			</p>
			<p>
				<label for="singleblogpost_category_suffix">Single Blog Post Category Suffix:</label>
				<input type="text" name="singleblogpost_category_suffix" id="singleblogpost_category_suffix" value="<?php echo get_option('breadcrumb_nav_singleblogpost_category_suffix'); ?>" size="32" />
			</p>
		</fieldset>
		<input type="submit" name="breadcrumb_nav_xt_admin_options" value="Save &raquo;" />
	</form>
	</div>
	<?php
}
//Additional styles for admin interface
function breadcrumb_nav_xt_options_style()
{
?>
<style>
	fieldset {
	margin-bottom: 5px;
	padding: 10px;
	border: #ccc solid 1px;
	}
	.halfl {
	width: 46.25%;
	float: left;
	}
	.halfr {
	width: 46.25%;
	float: right;
	}
</style>
<?php
}
//WordPress hooks
if(function_exists('add_action')){
	//Installation Script hook
	add_action('activate_breadcrumb-navigation-xt/breadcrumb-navigation-xt.php','breadcrumb_nav_xt_install');
	//WordPress Admin interface hook
	add_action('admin_menu', 'breadcrumb_nav_xt_add_page');
	add_action('admin_head', 'breadcrumb_nav_xt_options_style');
	//Admin Options hook
	if(isset($_POST['breadcrumb_nav_xt_admin_options']))
	{
		add_action('init', 'breadcrumb_nav_xt_admin_options');
	}
}
?>