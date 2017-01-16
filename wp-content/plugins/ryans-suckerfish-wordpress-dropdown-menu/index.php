<?php
/*
Plugin Name: Ryans Suckerfish WordPress Dropdown Menu
Plugin URI: https://geek.hellyer.kiwi/suckerfish-wordpress-plugin/
Description: This plugin loads a suckerfish script for providing support for older browsers such as Internet Explorer 6. Must be used with an unordered list with a class of .sf-menu. <strong>Note:</strong> Users upgrading from v1.6.6 should change to the <a href="https://geek.hellyer.kiwi/pixopoint-menu/">PixoPoint Menu plugin</a> instead. Version 2.0 of this plugin removes much of the functionality found in previous versions.
Author: Ryan Hellyer
Version: 2.0.1
Author URI: https://geek.hellyer.kiwi/

Copyright 2008 - 2015 Ryan Hellyer

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

/**
 * Adding Suckerfish script to the page
 */
function suckerfishhead() {
	?>
	<!-- Suckerfish WordPress plugin by Ryan Hellyer ... https://geek.hellyer.kiwi/ -->
	<!--[if lte IE 7]><script type="text/javascript" src="<?php echo plugins_url( 'suckerfish_ie.js', __FILE__ ); ?>"></script><![endif]-->

<?php
}
add_action( 'wp_head', 'suckerfishhead' );
