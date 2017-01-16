<?php 
/***************************************************************************

Plugin Name:  Dashboard: Recent Posts Extended
Plugin URI:   http://rick.jinlabs.com/code/dashboard-recent-posts-extended
Description:  Displays recent posts on your WordPress 2.7+ dashboard. Modified by BKJproductions.com to show recently modified Posts as well.
Version:      2.0.1bkj
Author:       Ricardo Gonz&aacute;lez Castro
Author URI:   http://rick.jinlabs.com/

*/

/***************************************************************************

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
The license is also available at http://www.gnu.org/copyleft/gpl.html

**************************************************************************/


// Load up the localization file if we're using WordPress in a different language
// Place it in this plugin's folder and name it "dashboard-recent-posts-extended-[value in wp-config].mo"
load_plugin_textdomain( 'dashboard-recent-posts-extended', '/wp-content/plugins/dashboard-recent-posts-extended' );


	function DashboardRecentPostsExtended() {

	// Add the widget to the dashboard
  
	global $wpdb;
	  
	$widget_options = DashboardRecentPostsExtended_Options();
	// set up an array of post types... could be Page, Revision, Media?
	$post_type=array('post');
	$post_type[]='media';
	if ($widget_options['pages']) {$post_type[]='page';}
	if ($widget_options['revisions']) {$post_type[]='revision';}
	$post_type_list=join("','",$post_type);

  $request = "SELECT $wpdb->posts.*, display_name as name FROM $wpdb->posts LEFT JOIN $wpdb->users ON $wpdb->posts.post_author=$wpdb->users.ID WHERE post_status IN ('publish','static') AND post_type IN ('$post_type_list') ";
	$request .= "ORDER BY post_modified DESC LIMIT ".$widget_options['items'];
	$posts = $wpdb->get_results($request);	
		//print_r($posts);
		
		if ( $posts ) {
			echo "				<ul id='dashboard-recent-posts-extended-list'>\n";
			
			$maxtime=$widget_options['maxtime'];
			$default_hilightcolor=$widget_options['timecolor'];

			foreach ( $posts as $post ) {
				$duration=round( ( strtotime($post->post_modified) - strtotime($post->post_date) ) /(60*60),2) ;
				$duration_note='No information about this';
				$hilightcolor='';
				if ($duration<$maxtime) {$duration_note='New! Created ' . human_time_diff(time(),strtotime($post->post_date) ) . ' ago'  ;}
				
				if ($duration>$maxtime) {
					$hilightcolor=$default_hilightcolor;
					$duration_note= human_time_diff(strtotime($post->post_modified),strtotime($post->post_date) )  . ' since last edit ';
					}


				$post_meta = sprintf('%s', '<a href="post.php?action=edit&amp;post=' . $post->ID . '" title="' . $duration_note. '">' . get_the_title($post->ID) . '</a> ' );

				if($widget_options['showauthor']) {				
				  $post_meta.= sprintf( __('by %s', 'dashboard-recent-posts-extended'),'<strong>'. $post->name .'</strong> ' );
				  }
				  				  
				
				  
								
				if($widget_options['showtime']) {				
				  $time = get_post_time('G', true);
		
				  if ( ( abs(time() - $time) ) < 86400 )
					$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
				  else
					$h_time = mysql2date(__('Y/m/d'), $post->post_date);


          $post_meta.= sprintf( __('&#8212; %s', 'dashboard-recent-posts-extended'),'<abbr title="' . get_post_time(__('Y/m/d H:i:s')) . '" style="color: ' . $hilightcolor . ';">' . $h_time . '</abbr>' );
          }
          
?>
					<li class='post-meta'>
						<?php echo $post_meta; ?>
					</li>
<?php
			}

			echo "				</ul>\n";
		} else {
				echo '				<p>' . __( "Sorry! You don't have any posts in your database!", 'dashboard-recent-posts-extended' ) . "</p>\n";
		}
		

}


function DashboardRecentPostsExtended_Init() {
	wp_add_dashboard_widget( 'DashboardRecentPostsExtended', __( 'Dashboard: Recent Posts Extended' ), 'DashboardRecentPostsExtended', 'DashboardRecentPostsExtended_Setup');
}

function DashboardRecentPostsExtended_Options() {
	$defaults = array( 'items' => 5, 'showtime' => 1, 'showauthor' => 1, 'maxtime' => 8, 'timecolor' => '#ff0000', 'pages'=>1, 'revisions'=>1 );
	if ( ( !$options = get_option( 'DashboardRecentPostsExtended' ) ) || !is_array($options) )
		$options = array();
	return array_merge( $defaults, $options );
}



function DashboardRecentPostsExtended_Setup() {

	$options = DashboardRecentPostsExtended_Options();


	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD']) && isset( $_POST['widget_id'] ) && 'DashboardRecentPostsExtended' == $_POST['widget_id'] ) {
		foreach ( array( 'items', 'showtime', 'showauthor','maxtime', 'timecolor', 'pages', 'revisions' ) as $key )
				$options[$key] = $_POST[$key];
		update_option( 'DashboardRecentPostsExtended', $options );
	}
		
?>
	<p>
		<label for="items"><?php _e('How many recent posts would you like to display?', 'dashboard-recent-posts-extended' ); ?>
			<select id="items" name="items">
				<?php
					for ( $i = 5; $i <= 20; $i = $i + 1 )
						echo "<option value='$i'" . ( $options['items'] == $i ? " selected='selected'" : '' ) . ">$i</option>";
				?>
			</select>
		</label>
	</p>

   <p>
		<label for="showauthor">
			<input id="showauthor" name="showauthor" type="checkbox" value="1"<?php if ( 1 == $options['showauthor'] ) echo ' checked="checked"'; ?> />
			<?php _e('Show post author?', 'dashboard-recent-posts-extended' ); ?>
		</label>
	</p>
	
   <p>
		<label for="showtime">
			<input id="showtime" name="showtime" type="checkbox" value="1"<?php if ( 1 == $options['showtime'] ) echo ' checked="checked"'; ?> />
			<?php _e('Show post date?', 'dashboard-recent-posts-extended' ); ?>
		</label>

	</p>
   <p>
		<label for="pages">
			<input id="pages" name="pages" type="checkbox" value="1"<?php if ( 1 == $options['pages'] ) echo ' checked="checked"'; ?> />
			<?php _e('Include Pages?', 'dashboard-recent-posts-extended' ); ?>
		</label>

	</p>

   <p>
		<label for="revisions">
			<input id="revisions" name="revisions" type="checkbox" value="1"<?php if ( 1 == $options['revisions'] ) echo ' checked="checked"'; ?> />
			<?php _e('Include Revisions?', 'dashboard-recent-posts-extended' ); ?>
		</label>

	</p>


	<p>	<label for="maxtime">
			<?php _e('Maxmimum hours before something is considered "old"', 'dashboard-recent-posts-extended' ); ?>
			<input id="maxtime" name="maxtime" type="text" size="5" value="<?php echo $options['maxtime'] ?>" />
		</label>

	</p>
	
	<p>	<label for="timecolor">
			<?php _e('Color for "old" posts which have been recently modified', 'dashboard-recent-posts-extended' ); ?>
			<input id="timecolor" name="timecolor" type="text" size="5" value="<?php echo $options['timecolor'] ?>" />
		</label>

	</p>
	
<?php
	}


/**
 * use hook, to integrate new widget
 */
add_action('wp_dashboard_setup', 'DashboardRecentPostsExtended_Init');
?>