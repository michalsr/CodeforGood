<?php
/*

Plugin Name: Duplicate Theme
Plugin URI: http://news.mullerdigital.com/wordpress-plugin-duplicate-theme/
Description: Choose an existing theme, duplicate it or create a child while giving it a new name. Modified by BKJ to indicate active theme, generate lowercase folder name, offer activation link.
Version: 1000.1.6bkj
Author: Muller Digital Inc.
Author URI: http://news.mullerdigital.com
License: GPL2





Copyright 2013    (email : info@mullerdigital.com)

    This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See th
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 
$plugin = plugin_basename(__FILE__); 


add_action( 'admin_menu', 'register_duplicate_theme_page' );

add_action( 'network_admin_menu', 'register_duplicate_theme_page' );

function register_duplicate_theme_page(){
    add_theme_page( 'Duplicate Theme', 'Duplicate Theme', 'administrator', 'duplicatetheme', 'duplicator_theme_admin' ); 
}


if (isset($_POST['newThemeName'])) {
	add_action('admin_head', 'themeDuplicationAction');
}


function duplicator_theme_admin() {  
	 $my_theme = wp_get_theme();
	 $my_theme = $my_theme->get( 'Name' );
?>

<div class="wrap"> 
  <div id="icon-edit-pages" class="icon32" style="float:left"></div>
<h2>Duplicate Theme</h2>

<form id="duplicateFile" method="post" action="" style="padding:20px 20px">
	
	<label for="currentTheme"><strong>Theme to Duplicate origin:</strong></label><br/>

	<select name="currentTheme" id="currentTheme">
<?php
	#Loop through theme items
	foreach ( wp_get_themes( array( 'errors' => null ) ) as $a_stylesheet => $a_theme ) {
		if ( $a_theme->errors() && 'theme_no_stylesheet' == $a_theme->errors()->get_error_code() )
			continue;
		$active = '';
		$selected = '';
		if ($a_theme->get('Name') == $my_theme) {
			$active = '*';
			$selected = ' selected';
			}		

		$parent_theme = $a_theme->parent() ? ' data-parent="'.$a_theme->template.'"' : '';
	
		echo "\n\t" . '<option value="' . esc_attr( $a_stylesheet ) . '"' . $parent_theme . $selected . '>' . $a_theme->display('Name') . $active .'</option>';
	}
?>
	</select>
	
	<span id="selCurrThemeMess" style="padding-left:20px;"></span>
	
	<br/>
	
	<div id="dup_theme_child_wrap" style="display:none;">
		<br />
		<input type="checkbox" name="dup_theme_child" id="dupThemeChild" disabled="disabled" /> <strong>Create Child Theme</strong> to selected theme <span id="dupThemeChildPointer" style="position:relative;"><a href="http://codex.wordpress.org/Child_Themes" target="_blank">What is this?</a></span>
		<br />
	</div>
	
	<div id="templateNameWrapper">
		<br/>
		<label for="newThemeName"><strong>New Theme name:</strong></label><br/>
		<input type="text" name="newThemeName" id="newThemeName" style="font-size:16px;padding:5px;width:250px" ><br />
		<label for="newDirectoryName">Directory Name:</label>&nbsp;<span id="dirName"></span>
		<input type="hidden" name="newDirectoryName" id="newDirectoryName" value="" />
	</div>
	
	<br/>
	<?php if(is_multisite()): ?>
	<input type="checkbox" name="dup_theme_net" value="1" /> <strong>Network Active</strong> duplicated theme <span id="dupThemeNetPointer" style="position:relative;"><a href="#">What is this?</a></span>
	<?php endif; ?>
		
	<br/><br/>
	
	<input type="submit" name="duplicateFileSubmit" value="Duplicate Theme" class="button-primary" style="position:relative;top:-2px"/>

</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function() {
		var selCurrThemeData, selCurrThemeOpt;		
		
		function childThemeInput(){
			selCurrThemeOpt = jQuery('#currentTheme option:selected');
			selCurrThemeData = selCurrThemeOpt.data("parent");
			if(selCurrThemeData){
				jQuery('#selCurrThemeMess').html("This theme is a child theme of <strong>"+selCurrThemeData+"</strong> parent theme. Your duplicated theme will also become a child theme of <strong>"+selCurrThemeData+"</strong> parent theme.");
				jQuery('#dup_theme_child_wrap input').val('').prop('checked', false).prop('disabled', true).parent().hide();
			}else{
				jQuery('#selCurrThemeMess').html("");
				jQuery('#dup_theme_child_wrap').show().children('input').prop('disabled', false).val(selCurrThemeOpt.val());
			}
		}
		childThemeInput(); // execute on load
			
		jQuery('#currentTheme').change(function(){
			childThemeInput();
		});
	
		jQuery('#newThemeName').keyup(function (e) {
			var str = jQuery(this).val().replace(/\W/g, '');
			str = str.toLowerCase();
			jQuery('#dirName').html(str);
			jQuery('#newDirectoryName').val(str);
		});		
		
	<?php if(is_multisite()): ?>
		jQuery('#dupThemeNetPointer').append(jQuery('<div style="display:none;width:300px;position:absolute;top:-10px;right:-335px;padding:0px 10px;color:#333;background-color:#f1f1f1;border:1px solid #ececec;-webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;"><h3 style="color:#21759b;">Network Activate</h3><p>After a theme is duplicated in a Wordpress Network, you still have to activate it for it to be available in your Wordpress site Appearance -> Themes area. Avoid this extra step by clicking this checkbox.</p></div>'));
		jQuery('#dupThemeNetPointer a').hover(function(){jQuery(this).next('div').fadeToggle(400);}).click(function(){return false;});
	<?php endif; ?>
	});
</script>

<?php } 

	
function recurse_copy($src,$dst) { 
    $dir = opendir($src); 
    @mkdir($dst); 
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                recurse_copy($src . '/' . $file,$dst . '/' . $file); 
            } 
            else { 
                copy($src . '/' . $file,$dst . '/' . $file); 
            } 
        } 
    } 
    closedir($dir);
    return true; 
}


function themeDuplicationAction() {

	$written = true;
	
	$_extra_response = '';
	
	$_doc_root = get_theme_root();
	
	$_new_theme_name = $_POST['newThemeName'];
	$_theme_to_dup = $_POST['currentTheme'];
	
	$_theme_style = $_doc_root.'/'.$_theme_to_dup;
	$_new_theme_loc = $_doc_root.'/'.$_POST['newDirectoryName'];
	$_new_theme_style_css = $_new_theme_loc.'/style.css';
	
	#Copy theme
	if(!recurse_copy($_theme_style,$_new_theme_loc)) $written = false;
	
	
	
	if(file_exists( $_new_theme_style_css )){
		$_theme_style_load = file_get_contents( $_new_theme_style_css );
		preg_match('/Theme Name:(.*)/i',$_theme_style_load,$_theme_name);
		if(trim($_theme_name[1])!=''){		
			$_theme_style_load = str_replace($_theme_name[1],' '.$_new_theme_name,$_theme_style_load);
			
			# Check to see if we make the duplicated theme a child theme.			
			if( @$_POST['dup_theme_child'] && @$_POST['dup_theme_child']!=""){
				# Check if original file had a Template, if it did, do not make a child theme
				$_template_check = preg_match('/Template:(.*)/i',$_theme_style_load,$_template_name);							
				if(!$_template_check){ # No template tag, so we have to add it
					$_theme_style_load = preg_replace("/Theme Name:(.*)/i","Theme Name:$1\nTemplate: ".$_theme_to_dup,$_theme_style_load);
				}else{				
					if(trim($_template_name[1])!=''){# Template tag was there, just empty, so let's populate it.
						$_theme_style_load = str_replace($_template_name[1],$_POST['dup_theme_child'],$_theme_style_load);
					}else{
						$_extra_response .= '<br />The original theme that was copied already was a child theme, so the duplicated theme has to keep the same parent theme.';						
					}
				}				
				
				# Set empty style.css file with just headers are reference to import parent css
				preg_match("/\/\*.*?\*\//is", $_theme_style_load, $_style_headers);
				$_theme_style_load = $_style_headers[0]."\r\n\r\n@import url(\"../".$_theme_to_dup."/style.css\");";
								
				# Overwrite functions.php file of duplicated theme for child themes
				$_empty_functions_file = <<<PHP
<?php
# Empty functions.php file for your childtheme
# The parents functions.php contents will be loaded.
# Add any additional or overwriting functions here.

PHP;
				if(!file_put_contents($_new_theme_loc."/functions.php",$_empty_functions_file)){
					$_extra_response .= "<br /><strong>Unable to create functions.php for child theme. Please manually delete contents of duplicated child themes functions.php contents in";
					if(is_multisite())
						$_extra_response .= " Network Admin -> Themes -> Editor";
					else
						$_extra_response .= " Appearance -> Editor";
					$_extra_response .= "</strong>";
				}
			}	
			
			if(file_put_contents($_new_theme_style_css,$_theme_style_load)){
				# Theme copied successfully 
				if(@$_POST['dup_theme_net']){
					$allowed_themes = get_site_option( 'allowedthemes' );
					$allowed_themes[$_POST['newDirectoryName']]=1;
					if(update_site_option( 'allowedthemes' , $allowed_themes )) $_extra_response .= '<br />Theme was network activated.';
				}									
			}else{
				$written = false;
			}
		}
	}
	
	// success/error message
	if ( $written != false ) { ?>
		<div class="updated fade"><p><strong><?=$_theme_to_dup?></strong> Duplicated to <strong><?=$_new_theme_name?></strong><br /> Do you want to <a href="themes.php" title="Activate now?">activate</a> it now?
<?=$_extra_response?></p></div>
	<?php } else { ?>
		<div class="error"><p><strong>ERROR: Unable to duplicate theme</strong></p></div>
	<?php } 
	
}