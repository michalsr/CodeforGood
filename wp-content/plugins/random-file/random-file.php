<?php
/**
 * Plugin Name: Random File
 * Version:     1.8.2
 * Plugin URI:  http://coffee2code.com/wp-plugins/random-file/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com/
 * Text Domain: random-file
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Description: Retrieve the name, path, or link to a randomly chosen file or files in a specified directory.
 *
 * Compatible with WordPress 2.8 through 4.5+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: https://wordpress.org/plugins/random-file/
 *
 * TODO:
 * - Add widget
 * - Facilitate running a randomly chosen .php file
 * - Facilitate inlining randomly chosen file (any, or at least: .txt, .css, .js, .php, .html)
 * - Facilitate displaying randomly chosen image
 *
 * @package Random_File
 * @author  Scott Reilly
 * @version 1.8.2
 */

/*
	Copyright (c) 2004-2016 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! function_exists( 'c2c_random_file' ) ):

/**
 * Retrieves the name, path, or link to a randomly chosen file in a specified
 *  directory.
 *
 * Details on the acceptable values for $reftype:
 *
 *   'relative' => A location relative to the primary domain:
 *      /journal/random/randomfile.txt
 *      [This is the default setting as it is the most applicable.  Relative
 *      referencing is necessary if the random file is to be used as an
 *      argument to include() or virtual().  It's also a valid way to reference
 *      a file for A HREF= and IMG SRC= linking.]
 *
 *   'absolute' => An absolute location relative to the root of the server's
 *      file system:
 *      /usr/local/htdocs/yoursite/www/journal/random/randomfile.txt
 *
 *   'url' => The URL of the random file:
 *      http://www.yoursite.org/journal/random/randomfile.txt
 *      [If you desire the use of full URL, i.e. for A HREF= or IMG SRC= link.]
 *
 *   'filename' => The filename of the random file:
 *      randomefile.txt
 *
 *   'hyperlink' => The filename of the random file hyperlinked to that random file:
 *      <a href='http://yoursite.org/journal/random/randomfile.txt' title='randomfile.txt'>randomfile.txt</a>
 *
 * @param  string $dir        The directory (relative to the root of the site)
 *                            containing the files to be random chosen from.
 * @param  string $extensions Optional. A space-separated list of extensions,
 *                            one of which the chosen file must have (case
 *                            insensitive). Default ''.
 * @param  string $reftype    Optional. One of: absolute, filename, hyperlink,
 *                            relative, or url.  Default 'relative'.
 * @param  array  $exclusions Optional. Filenames to exclude from consideration
 *                            as a random file.
 * @return string The random file chosen (if possible).
 */
function c2c_random_file( $dir, $extensions = '', $reftype = 'relative', $exclusions = array() ) {
	$files   = array();
	$i       = -1;
	$pattern = '/.*';

	if ( ! empty( $extensions ) ) {
		$exts = array();
		foreach ( explode( ' ', $extensions ) as $ext ) {
			$exts[] = preg_quote( $ext, '/' );
		}
		$pattern .= '\.(' . implode( '|', $exts ) . ')';
	}
	$pattern .= '$/i';

	$dir     = trim( $dir, '/' );
	$abs_dir = ABSPATH . $dir;

	if ( ! file_exists( $abs_dir ) ) {
		return;
	}

	$handle = @opendir( $abs_dir );
	if ( false === $handle ) {
		return;
	}

	$exclusions = empty( $exclusions ) ? array() : array_map( 'basename', (array) $exclusions );

	while ( false != ( $file = readdir( $handle ) ) ) {
		if ( is_file( $abs_dir . '/' . $file ) && preg_match( $pattern, $file ) && ! in_array( $file, $exclusions ) ) {
			$files[] = $file;
			++$i;
		}
	}

	closedir( $handle );

	if ( empty( $files ) ) {
		return;
	}
	
	mt_srand( (double) microtime() * 1000000 );
	$rand = mt_rand( 0, $i );

	if ( ! empty( $dir ) ) {
		$dir .= '/';
	}

	$random_file = $files[ $rand ];

	if ( in_array( $reftype, array( 'hyperlink', 'url' ) ) ) {
		$url = trailingslashit( get_option( 'siteurl' ) ) . $dir . $random_file;
	}

	if ( 'url' == $reftype ) {
		return $url;
	}

	if ( 'absolute' == $reftype ) {
		return $abs_dir . DIRECTORY_SEPARATOR . $random_file;	/* could also do realpath($random_file); */
	}

	if ( 'filename' == $reftype ) {
		return $random_file;
	}

	if ( 'hyperlink' == $reftype ) {
		return '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $random_filename ) . '">' . $random_file . '</a>';
	}

	// Else this is handling 'relative' == $reftype

	// Need to obtain location relative to root of domain (in case site is based out of subdirectory)
	preg_match( "/^(https?:\/\/)?([^\/]+)\/?(.+)?$/", get_option( 'siteurl' ), $matches );
	$relpath = isset( $matches[3] ) ? '/' . $matches[3] : '';

	return $relpath . '/' . $dir . $random_file;
} //end c2c_random_file()

add_filter( 'c2c_random_file', 'c2c_random_file', 10, 4 );

endif;


if ( ! function_exists( 'c2c_random_files' ) ) :

/**
 * Retrieves the name, path, or link to a specified number of randomly chosen
 * files in a specified directory.
 *
 * Note: the number of files returned will be UP TO the specified number.
 * Obviously, if the directly has less than that number of files, then
 * only those files can be returned
 *
 * (see docs for c2c_random_file() for more details regarding values for
 * arguments)
 *
 * @param  int    $number     The number of random files to select from the
 *                            specified directory.
 * @param  string $dir        The directory (relative to the root of the site)
 *                            containing the files to be random chosen from.
 * @param  string $extensions Optional. A space-separated list of extensions,
 *                            one of which the chosen file must have (case
 *                            insensitive). Default ''.
 * @param  string $reftype    Optional. One of: absolute, filename, hyperlink,
 *                            relative, or url.  Default 'relative'.
 * @param  array  $exclusions Optional. Filenames to exclude from consideration
 *                            as a random file.
 * @return array  The random files chosen (if possible).
 */
function c2c_random_files( $number, $dir, $extensions = '', $reftype = 'relative', $exclusions = array() ) {
	$number     = absint( $number );
	$exclusions = (array) $exclusions;
	$files      = array();
	for ( $i = 0; $i < $number; $i++ ) {
		$f = c2c_random_file( $dir, $extensions, $reftype, $exclusions );
		if ( ! $f ) {
			break;
		}
		$files[]      = $f;
		$exclusions[] = $f;
	}
	return $files;
}

add_filter( 'c2c_random_files', 'c2c_random_files', 10, 5 );

endif;
