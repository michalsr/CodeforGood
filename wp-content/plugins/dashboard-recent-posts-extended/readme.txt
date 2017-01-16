=== Dashboard: Recent Posts Extended ===
Donate link: http://rick.jinlabs.com/donate/
Tags: dashboard, widgets, dashboard widget, recent posts
Requires at least: 2.7
Tested up to: 2.7
Stable tag: trunk

Widget for the WordPress 2.7+ dashboard to display the latest posts.

== Description ==

WordPress 2.5 introduces a widgetized dashboard featuring the latest posts and WordPress news.

This plugin creates a new widget for that dashboard that lists out the latest posts.

**See Also:** [Dashboard Widget Manager](http://wordpress.org/extend/plugins/dashboard-widget-manager/)

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.
Or just use the 'Automatic  upgrade' feature

###Installing The Plugin###

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload it to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - dashboard-recent-posts-extended
            | dashboard-recent-posts-extended.php
            | readme.txt`

Then just visit your admin area and activate the plugin.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Using The Plugin###

The new widget will show up automatically on your dashboard. If you use [Dashboard Widget Manager plugin](http://wordpress.org/extend/plugins/dashboard-widget-manager/) and have specified a custom widget order, you will need to visit it's management page and add this new widget to your dashboard.

== Screenshots ==

1. Dashboard: Recent Posts Extended default view
2. Dashboard: Recent Posts Extended options

== Frequently Asked Questions ==

= Does this plugin support other languages? =

Yes, it does. See the [WordPress Codex](http://codex.wordpress.org/Translating_WordPress) for details on how to make a translation file. Then just place the translation file, named `dashboard-recent-posts-extended-[value in wp-config].mo`, into the plugin's folder.

== ChangeLog ==

**2.1 - 2009/01/30**

* 2.7 support

**Version 1.0**

* Initial release.