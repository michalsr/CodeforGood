=== Random File ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: random, file, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 2.8
Tested up to: 4.5
Stable tag: 1.8.2

Retrieve the name, path, or link to a randomly chosen file or files in a specified directory.


== Description ==

This plugin provides template tags that allow you to retrieve the name, path (relative or absolute), url, or fully marked-up link to a randomly chosen file or files in a specified directory.

Arguments to the functions permit you to specify limit what file(s) can be randomly selected based on a given set of file extensions. You can also explicitly specify files that should not be randomly selected.

This functionality can be useful for displaying random images/logos or including text from random files onto your site (writing excerpts, multi-line quotes, etc). Other ideas: random ads, random CSS files, random theme template selection.

Notes:

* If you want to actually display the name of the random file, be sure to 'echo' the results:
`<?php echo c2c_random_file( '/random' ); ?>`

* Unless you limit the file search to only include a particular extension (via `$extensions` argument), all files in the specified `$dir` will be under consideration for random selection

* Can be run inside or outside of "the loop"

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/random-file/) | [Plugin Directory Page](https://wordpress.org/plugins/random-file/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Unzip `random-file.zip` inside the `/wp-content/plugins/` directory (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Make use of the `c2c_random_file()` or `c2c_random_files()` template function in your code or template (see examples below).


== Frequently Asked Questions ==

= Does this plugin do dynamic random rotation within a loaded page (i.e. randomly rotating images within a loaded page)? =

No. This plugin only selects a random file when the page is loaded. Once loaded, it does not currently add any dynamic functionality to automatically retrieve another random file on its own.


== Template Tags ==

The plugin provides two optional template tag for use in your theme templates.

= Functions =

* `<?php function c2c_random_file( $dir, $extensions = '', $reftype = 'relative', $exclusions = array() ) ?>`
This retrieves the name of a random file from a specified directory and returns information based on the file according to the `$reftype` value.

* `<?php function c2c_random_files( $number, $dir, $extensions = '', $reftype = 'relative', $exclusions = array() ) ?>`
This retrieves the name, path, or link to a specified number of randomly chosen files in a specified directory. All but the `$number` argument are passed along in calls to `c2c_random_file()`.

= Arguments =

* `$number` (only for `c2c_random_files()`)
The number of random files to select from the specified directory. If less files are present in the specified directory, then all files in the directory will be returned (but will be listed in random order).

* `$dir`
The directory to search for a random file. The directory must exist at the directory structure level of your WordPress installation or below. (i.e., if your site is installed on your server at `/usr/local/htdocs/yoursite/www/journal/`, then the directory of random files you specified will assume that as its base... so a value of `'randomfiles'` would be assumed to actually be: `/usr/local/htdocs/yoursite/www/journal/randomfiles/`)

* `$extensions`
Optional argument. A space-separated list of extensions (case insensitive), i.e. 'jpg gif png jpeg'.

* `$reftype`
Optional argument. Can be one of the following: 'relative' (which is the default), 'absolute', 'url', 'filename', 'hyperlink'. See Examples section for more details and examples.

* `$exclusions`
Optional argument. If specified, MUST be an array of filenames to exclude from consideration as a random file.

= Examples =

* The reference to the randomly selected file can be returned in one of five ways:
[Assume your WordPress installation is at http://www.yoursite.org/journal/ and you've invoked `c2c_random_file('random/', 'txt', $reftype)`]

	$reftype = 'relative'

	=> A location relative to the primary domain:

    `/journal/random/randomfile.txt`

	[This is the default setting as it is the most applicable. Relative referencing is necessary if the random file is to be used as an argument to include() or virtual(). It's also a valid way to reference a file for A HREF= and IMG SRC= linking.]

    `$reftype = 'absolute'`

	=> An absolute location relative to the root of the server's file system:

    `/usr/local/htdocs/yoursite/www/journal/random/randomfile.txt`

	$reftype = 'url'

	=> The URL of the random file:

    `http://www.yoursite.org/journal/random/randomfile.txt`

	[If you desire the use of full URL, such as for a A HREF= or IMG SRC= link.]

	$reftype = 'filename'

	=> The filename of the random file:

    `randomefile.txt`
	
	$reftype = 'hyperlink'

	=> The filename of the random file hyperlinked to that random file:

    `<a href='http://yoursite.org/journal/random/randomfile.txt' title='randomfile.txt'>randomfile.txt</a>`

* Include random logo or image on your site:

    `<img alt="logo" class="logo" src="<?php echo c2c_random_file( 'wp-content/images/logos/' ); ?>" />`

* Insert text from a random file (i.e. for random multi-line quotes) (Apache web-server only, probably):

`
<div class="todayquote">
   <?php virtual( c2c_random_file( 'quotes/', 'txt' ) ); ?>
</div>
`

* If you wanted to source a random .php file:

    `<?php include( c2c_random_file( '/randomphp', 'php' ) ); ?>`

* List 5 random files:

`
<ul>
	<?php
		$random_files = c2c_random_files( 5, 'wp-content/files' );
		foreach ( $random_files as $f ) {
			echo "<li>$f</li>";
		}
	?>
</ul>
`


== Filters ==

The plugin exposes two filters for hooking. Typically, customizations utilizing these hooks would be put into your active theme's functions.php file, or used by another plugin.

= c2c_random_file (filter) =

The 'c2c_random_file' hook allows you to use an alternative approach to safely invoke `c2c_random_file()` in such a way that if the plugin were deactivated or deleted, then your calls to the function won't cause errors in your site.

Arguments:

* same as for `c2c_random_file()`

Example:

Instead of:

`<?php $file = c2c_random_file( 'wp-content/randomfiles' ); ?>`

Do:

`<?php $file = apply_filters( 'c2c_random_file', 'wp-content/randomfiles' ); ?>`

= c2c_random_files (filter) =

The 'c2c_random_files' hook allows you to use an alternative approach to safely invoke `c2c_random_files()` in such a way that if the plugin were deactivated or deleted, then your calls to the function won't cause errors in your site.

Arguments:

* same as for `c2c_random_files()`

Example:

Instead of:

`<?php $files = c2c_random_files( 5, 'wp-content/randomfiles' ); ?>`

Do:

`<?php $files = apply_filters( 'c2c_random_files', 5, 'wp-content/randomfiles' ); ?>`



== Changelog ==

= 1.8.2 (2016-03-22) =
* Change: Add 'Text Domain' to plugin header.
* Change: Explicitly declare methods in unit tests as public.
* Change: Minor inline documentation reformatting.
* New: Add LICENSE file.
* New: Add empty index.php to prevent files from being listed if web server has enabled directory listings.
* Change: Note compatibility through WP 4.5+.
* Change: Update copyright date (2016).

= 1.8.1 (2015-02-16) =
* Minor plugin header reformatting
* Change documentation links to wp.org to be https
* Minor readme space formatting changes
* Note compatibility through WP 4.1+
* Update copyright date (2015)
* Add plugin icon

= 1.8 (2014-01-11) =
* Add unit tests
* Fix bug to actually permit multiple extensions to be specified
* Change casting $number from intval() to absint() in c2c_random_files()
* Minor code changes (spacing, bracing)
* Note compatibility through WP 3.8+
* Update copyright date (2014)
* Minor readme.txt tweaks
* Change donate link
* Add banner

= 1.7.1 =
* Add check to prevent execution of code if file is directly accessed
* Note compatibility through WP 3.5+
* Update copyright date (2013)

= 1.7 =
* Use DIRECTORY_SEPARATOR instead of hardcoding '/' when determining absolute path
* Properly escape the attributes for the link markup
* preg_quote() the extensions
* Cast $exclusions arg to array before use
* Re-license as GPLv2 or later (from X11)
* Add 'License' and 'License URI' header tags to readme.txt and plugin file
* Minor code reformatting (spacing)
* Remove ending PHP close tag
* Note compatibility through WP 3.4+
* Drop support for versions of WP older than 2.8

= 1.6.2 =
* Note compatibility through WP 3.3+
* Add link to plugin directory page to readme.txt
* Add Upgrade Notice section to readme.txt
* Update copyright date (2012)

= 1.6.1 =
* Note compatibility through WP 3.2+
* Minor code formatting changes (spacing)
* Minor readme.txt formatting changes
* Add plugin homepage and author links in description in readme.txt
* Update copyright date (2011)

= 1.6 =
* Add c2c_random_files() to retrieve array of random unique files
* Add hooks 'c2c_random_file' (filter) and 'c2c_random_files' (filter) to respond to the function of the same name so that users can use the apply_filters() notation for invoking template tags
* Use get_option() instead of deprecated get_settings()
* Wrap functions in if(!function_exists()) check
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Tweak description
* Note compatibility with WP 3.0+
* Minor tweaks to code formatting (spacing)
* Add Filters and Upgrade Notice sections to readme.txt
* Remove trailing whitespace

= 1.5.2 =
* Add PHPDoc documentation
* Note compatibility with WP 2.9+
* Update copyright date
* Update readme.txt

= 1.5.1 =
* Fixed: missing '/' in path construction for reftype 'absolute'

= 1.5 =
* Added new reftype of 'hyperlink' to return the filename of the random file hyperlinked to that file
* Added error checking to avoid error when referenced directory does not exist
* Added error checking for when there is an error opening a directory
* Explicit handling of reftype 'absolute' in the code was actually supposed to be 'serverabsolute'
* Minor code tweaks
* Tweaked installation instructions
* Added readme.txt
* Noted and tested compatibility with WP 2.3.3 through 2.8

= 1.0 =
* Renamed function from random_file() to c2c_random_file()
* Added new reftype of 'filename'
* Added optional array argument $exceptions for files not to be considered in random file selection
* Updated license and examples
* Verified that plugin works in WP 1.5 (and still works in WP 1.2)

= 0.9 =
* Initial release


== Upgrade Notice ==

= 1.8.2 =
Trivial update: noted compatibility through WP 4.5+ and updated copyright date (2016)

= 1.8.1 =
Trivial update: noted compatibility through WP 4.1+ and updated copyright date (2015)

= 1.8 =
Recommended minor update: fixed bug which prevented specified multiple file extensions from working; added unit tests; noted compatibility through WP 3.8+

= 1.7.1 =
Trivial update: noted compatibility through WP 3.5+

= 1.7 =
Recommended minor update: improved compatibility and data sanitization; noted compatibility through WP 3.4+; explicitly stated license

= 1.6.2 =
Trivial update: noted compatibility through WP 3.3+

= 1.6.1 =
Trivial update: noted compatibility through WP 3.2+

= 1.6 =
Feature update. Highlights: added c2c_random_files() to retrieve array of random unique files; added hooks to allow customizations; verified WP 3.0 compatibility.
