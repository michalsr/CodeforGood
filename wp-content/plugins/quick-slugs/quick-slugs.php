<?php	
/*
Plugin Name: Quick Slugs
Plugin URI:  http://hieudt.info/wp-plugins/quick-slugs/
Description: Enables you to edit many slugs quickly.
Author: HieuDT
Version: 1.0.0
Author URI: http://hieudt.info/
*/

/**
* The function below is used to remove all Vietnamese accents in titles
* Thanks to QAD - http://onetruebrace.com/2007/11/19/nicer-permalinks-for-vietnamese/
*/
define('qad_remove_accents', false); // set to TRUE if you want to remove all Vietnames accents

if (qad_remove_accents && !function_exists('qad_remove_accents')) {
	function qad_remove_accents($string) {
	  $trans = array(
	    'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a',
	    'ă'=>'a','ằ'=>'a','ắ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a',
	    'â'=>'a','ầ'=>'a','ấ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
	    'À'=>'a','Á'=>'a','Ả'=>'a','Ã'=>'a','Ạ'=>'a',
	    'Ă'=>'a','Ằ'=>'a','Ắ'=>'a','Ẳ'=>'a','Ẵ'=>'a','Ặ'=>'a',
	    'Â'=>'a','Ầ'=>'a','Ấ'=>'a','Ẩ'=>'a','Ẫ'=>'a','Ậ'=>'a',    
	    'đ'=>'d','Đ'=>'d',
	    'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e',
	    'ê'=>'e','ề'=>'e','ế'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
	    'È'=>'e','É'=>'e','Ẻ'=>'e','Ẽ'=>'e','Ẹ'=>'e',
	    'Ê'=>'e','Ề'=>'e','Ế'=>'e','Ể'=>'e','Ễ'=>'e','Ệ'=>'e',
	    'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
	    'Ì'=>'i','Í'=>'i','Ỉ'=>'i','Ĩ'=>'i','Ị'=>'i',
	    'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o',
	    'ô'=>'o','ồ'=>'o','ố'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o',
	    'ơ'=>'o','ờ'=>'o','ớ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
	    'Ò'=>'o','Ó'=>'o','Ỏ'=>'o','Õ'=>'o','Ọ'=>'o',
	    'Ô'=>'o','Ồ'=>'o','Ố'=>'o','Ổ'=>'o','Ỗ'=>'o','Ộ'=>'o',
	    'Ơ'=>'o','Ờ'=>'o','Ớ'=>'o','Ở'=>'o','Ỡ'=>'o','Ợ'=>'o',
	    'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
	    'ư'=>'u','ừ'=>'u','ứ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
	    'Ù'=>'u','Ú'=>'u','Ủ'=>'u','Ũ'=>'u','Ụ'=>'u',
	    'Ư'=>'u','Ừ'=>'u','Ứ'=>'u','Ử'=>'u','Ữ'=>'u','Ự'=>'u',
	    'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
	    'Y'=>'y','Ỳ'=>'y','Ý'=>'y','Ỷ'=>'y','Ỹ'=>'y','Ỵ'=>'y'
	  );
		return strtr($string, $trans);
	}
	add_filter('sanitize_title', 'qad_remove_accents', 1);
}

add_action('init', 'quickslugs_textdomain');
function quickslugs_textdomain() {
	load_plugin_textdomain('quick_slugs', false, 'quick-slugs/lang');
}

class QuickSlugsAdmin {

	var $message = '';
	var $status = '';

	function QuickSlugsAdmin() {
		add_action('admin_menu', array(&$this, 'adminMenu'));
		add_action('admin_notices', array(&$this, 'displayMessage'));		
		add_action('admin_init', array(&$this, 'checkQuickSlugs'));
	}

	function adminMenu() {	
		add_submenu_page( 'edit.php', __('Quick Slugs', 'quick_slugs'), __('Quick Slugs', 'quick_slugs'), 9, 'slugs', array(&$this, 'pageQuickSlugs'));
	}

	function displayMessage() {
		if ( $this->message != '') {
			$message = $this->message;
			$status = $this->status;
			$this->message = $this->status = ''; // Reset
		}

		if ( $message ) {
		?>
			<div id="message" class="<?php echo ($status != '') ? $status :'updated'; ?> fade">
				<p><strong><?php echo $message; ?></strong></p>
			</div>
		<?php
		}
	}	

	function edit_data_query( $q = false ) {
		if ( false === $q ) {
			$q = $_GET;
		}
		
		// Date
		$q['m']   = (int) $q['m'];
		
		// Category
		$q['cat'] = (int) $q['cat'];
		
		// Quantity
		$q['posts_per_page'] = (int) $q['posts_per_page'];
		if ( $q['posts_per_page'] == 0 ) {
			$q['posts_per_page'] = 15;
		}		
		
		// Content type
		if ( $q['post_type'] == 'page' ) {
			$q['post_type'] = 'page';
		} else {
			$q['post_type'] = 'post';
		}
		
		// Post status
		$post_stati = array(	//	array( adj, noun )
			'publish' => array(__('Published'), __('Published posts'), __ngettext_noop('Published (%s)', 'Published (%s)')),
			'future' => array(__('Scheduled'), __('Scheduled posts'), __ngettext_noop('Scheduled (%s)', 'Scheduled (%s)')),
			'pending' => array(__('Pending Review'), __('Pending posts'), __ngettext_noop('Pending Review (%s)', 'Pending Review (%s)')),
			'draft' => array(__('Draft'), _c('Drafts|manage posts header'), __ngettext_noop('Draft (%s)', 'Drafts (%s)')),
			'private' => array(__('Private'), __('Private posts'), __ngettext_noop('Private (%s)', 'Private (%s)')),
		);
	
		$post_stati = apply_filters('post_stati', $post_stati);	
		$avail_post_stati = get_available_post_statuses('post');
	
		$post_status_q = '';
		if ( isset($q['post_status']) && in_array( $q['post_status'], array_keys($post_stati) ) ) {
			$post_status_q = '&post_status=' . $q['post_status'];
			$post_status_q .= '&perm=readable';
		}
	
		if ( 'pending' === $q['post_status'] ) {
			$order = 'ASC';
			$orderby = 'modified';
		} elseif ( 'draft' === $q['post_status'] ) {
			$order = 'DESC';
			$orderby = 'modified';
		} else {
			$order = 'DESC';
			$orderby = 'date';
		}
	
		wp("post_type={$q['post_type']}&what_to_show=posts$post_status_q&posts_per_page={$q['posts_per_page']}&order=$order&orderby=$orderby");
	
		return array($post_stati, $avail_post_stati);
	}

	function getSlugToEdit( $post_id, $sanitize_title = false ) {
		$post_id = (int) $post_id;
		if ( !$post_id )
			return false;
			
		$post = &get_post($post_id);
		if ( !$post )
			return false;
		
		if ($sanitize_title) {
			$out = sanitize_title($post->post_title);
		} else {
			$out = attribute_escape( $post->post_name );
		}
		return $out;
	}

	function checkQuickSlugs() {
		
		// Get GET data
		$type = stripslashes($_GET['post_type']);
		
		if ( isset($_POST['update_all_slugs']) || isset($_POST['update_selected_slugs']) ) {
			
			// origination and intention
			if ( ! ( wp_verify_nonce($_POST['secure_slugs'], 'mass_slugs') ) ) {
				$this->message = sprintf(__('Security problem. Please try again. If this problem persists, contact %1$s','quick_slugs'),'<a href="http://hieudt.info/contact/" target="_blank">'.__('plugin author','quick_slugs').'</a>.');
				$this->status = 'error';
				return false;
			}

			if ( isset($_POST['update_selected_slugs']) && isset($_POST['post']) && isset($_POST['slug']) ) {
				$counter = 0;
				foreach ( (array) $_POST['post'] as $object_id ) {
					$slugs = (array) $_POST['slug'];
					
					// Trim data
					$slug = trim(stripslashes($slugs[$object_id]));
					
					$post_data['post_name'] = $slug;
					$post_data['ID'] 		= $object_id;
					
					wp_update_post( $post_data );
					
					$counter++;
					
					// Clean cache
					if ( 'page' == $type ) {
						clean_page_cache($object_id);
					} else {
						clean_post_cache($object_id);
					}
				}
				
				if ( $type == 'page' ) {
					$this->message = sprintf(__('%s page(s) updated!', 'quick_slugs'), (int) $counter);
				} else {
					$this->message = sprintf(__('%s post(s) updated!', 'quick_slugs'), (int) $counter);
				}
				return true;
				
			} elseif (isset($_POST['update_all_slugs']) && isset($_POST['slug'])) {
				$counter = 0;
				foreach ( (array) $_POST['slug'] as $object_id => $slug ) {
					// Trim data
					$slug = trim(stripslashes($slug));
					
					$post_data['post_name'] = $slug;
					$post_data['ID'] 		= $object_id;
					
					wp_update_post( $post_data );
					
					$counter++;
					
					// Clean cache
					if ( 'page' == $type ) {
						clean_page_cache($object_id);
					} else {
						clean_post_cache($object_id);
					}
				}
				
				if ( $type == 'page' ) {
					$this->message = sprintf(__('%s page(s) updated!', 'quick_slugs'), (int) $counter);
				} else {
					$this->message = sprintf(__('%s post(s) updated!', 'quick_slugs'), (int) $counter);
				}
				return true;			
			}
		}
		return false;
	}	
	
	function pageQuickSlugs() {	
		global $wpdb, $wp_locale, $wp_query;		
		list($post_stati, $avail_post_stati) = $this->edit_data_query();
		
		if ( !isset( $_GET['paged'] ) ) {
			$_GET['paged'] = 1;
		}
			
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e('Quick Slugs', 'quick_slugs'); if ( isset($_GET['s']) && $_GET['s'] ) printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;','quick_slugs') . '</span>', wp_specialchars( get_search_query() ) ); ?></h2>
		
			<form id="posts-filter" action="" method="get">
				<input type="hidden" name="page" value="slugs" />
							
				<ul class="subsubsub">
					<?php
					$status_links = array();
					$num_posts = wp_count_posts('post', 'readable');
					$class = (empty($_GET['post_status']) && empty($_GET['post_type'])) ? ' class="current"' : '';
					$status_links[] = "<li><a href=\"edit.php?page=slugs\"$class>".__('All Posts', 'quick_slugs')."</a>";
					foreach ( $post_stati as $status => $label ) {
						$class = '';
					
						if ( !in_array($status, $avail_post_stati) ) {
							continue;
						}
					
						if ( empty($num_posts->$status) )
							continue;
						if ( $status == $_GET['post_status'] )
							$class = ' class="current"';
					
						$status_links[] = "<li><a href=\"edit.php?page=slugs&amp;post_status=$status\"$class>" . sprintf(__ngettext($label[2][0], $label[2][1], $num_posts->$status), '<span class="count">' . number_format_i18n( $num_posts->$status ) . '</span>') . '</a>';
					}
					echo implode(' |</li>', $status_links) . ' |</li>';
					unset($status_links);
					
					$class = (!empty($_GET['post_type'])) ? ' class="current"' : '';
					?>
					<li><a href="edit.php?page=slugs&amp;post_type=page" <?php echo $class; ?>><?php _e('All Pages', 'quick_slugs'); ?></a>
				</ul>
				
				<?php if ( isset($_GET['post_status'] ) ) : ?>
					<input type="hidden" name="post_status" value="<?php echo attribute_escape($_GET['post_status']) ?>" />
				<?php endif; ?>
				
				<p class="search-box">
					<label class="hidden" for="post-search-input"><?php _e( 'Search', 'quick_slugs' ); ?>:</label>
					<input type="text" class="search-input" id="post-search-input" name="s" value="<?php the_search_query(); ?>" />
					<input type="submit" value="<?php _e( 'Search', 'quick_slugs' ); ?>" class="button" />
				</p>				
				
				<?php if ( have_posts() ) : ?>
				
				<div class="tablenav">		
					<?php
					$posts_per_page = (int) $_GET['posts_per_page'];
					if ( $posts_per_page == 0 ) {
						$posts_per_page = 15;
					}
					
					$page_links = paginate_links( array(
						'base' => add_query_arg( 'paged', '%#%' ),
						'format' => '',
						'prev_text' => __('&laquo;'),
						'next_text' => __('&raquo;'),
						'total' => $wp_query->max_num_pages,
						'current' => $_GET['paged']
					));
					?>
					
					<div class="alignleft actions">
						<?php 						
						if ( !is_singular() ) {
						$arc_query = "SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts WHERE post_type = 'post' ORDER BY post_date DESC";
						
						$arc_result = $wpdb->get_results( $arc_query );
						
						$month_count = count($arc_result);
						
						if ( $month_count && !( 1 == $month_count && 0 == $arc_result[0]->mmonth ) ) { ?>
							<select name='m'>
							<option<?php selected( @$_GET['m'], 0 ); ?> value='0'><?php _e('Show all dates', 'quick_slugs'); ?></option>
							<?php
							foreach ($arc_result as $arc_row) {
								if ( $arc_row->yyear == 0 )
									continue;
								$arc_row->mmonth = zeroise( $arc_row->mmonth, 2 );
							
								if ( $arc_row->yyear . $arc_row->mmonth == $_GET['m'] )
									$default = ' selected="selected"';
								else
									$default = '';
							
								echo "<option$default value='$arc_row->yyear$arc_row->mmonth'>";
								echo $wp_locale->get_month($arc_row->mmonth) . " $arc_row->yyear";
								echo "</option>\n";
							}
							?>
							</select>
						<?php } ?>
						
						<?php wp_dropdown_categories('show_option_all='.__('View all categories', 'quick_slugs').'&hide_empty=0&hierarchical=1&show_count=1&orderby=name&selected='.$_GET['cat']); do_action('restrict_manage_posts'); ?>
									
						<select name="posts_per_page" id="posts_per_page">							
							<option <?php if ( !isset($_GET['posts_per_page']) ) echo 'selected="selected"'; ?> value=""><?php _e('Quantity', 'quick_slugs'); ?></option>
							<option <?php if ( $posts_per_page == 10 ) echo 'selected="selected"'; ?> value="10">10</option>
							<option <?php if ( $posts_per_page == 20 ) echo 'selected="selected"'; ?> value="20">20</option>
							<option <?php if ( $posts_per_page == 30 ) echo 'selected="selected"'; ?> value="30">30</option>
							<option <?php if ( $posts_per_page == 40 ) echo 'selected="selected"'; ?> value="40">40</option>
							<option <?php if ( $posts_per_page == 50 ) echo 'selected="selected"'; ?> value="50">50</option>
							<option <?php if ( $posts_per_page == 100 ) echo 'selected="selected"'; ?> value="100">100</option>
							<option <?php if ( $posts_per_page == 200 ) echo 'selected="selected"'; ?> value="200">200</option>
							<option <?php if ( $posts_per_page == 300 ) echo 'selected="selected"'; ?> value="300">300</option>
							<option <?php if ( $posts_per_page == 400 ) echo 'selected="selected"'; ?> value="400">400</option>
							<option <?php if ( $posts_per_page == 500 ) echo 'selected="selected"'; ?> value="500">500</option>
						</select>
						
						<input type="submit" id="post-query-submit" value="<?php _e('Filter', 'quick_slugs'); ?>" class="button-secondary" /> | 
						<input id="reget-slugs" class="button" type="button" value="<?php _e('Get slugs from titles', 'quick_slugs'); ?>" onclick="refresh_slugs();" />
						<?php } ?>
					</div>
					
					<?php if ( $page_links ) { ?>
					<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s','quick_slugs' ) . '</span>%s',
						number_format_i18n( ( $_GET['paged'] - 1 ) * $wp_query->query_vars['posts_per_page'] + 1 ),
						number_format_i18n( min( $_GET['paged'] * $wp_query->query_vars['posts_per_page'], $wp_query->found_posts ) ),
						number_format_i18n( $wp_query->found_posts ),
						$page_links
					); echo $page_links_text; ?></div>
					<?php } ?>
					
					<div class="clear"></div>
				</div>
			</form>
			
			<?php add_filter('the_title','wp_specialchars'); ?>
			<form name="post" id="post" method="post">
				<table class="widefat post fixed" cellspacing="0">
					<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
						<th scope="col" id="slug" class="manage-column column-slug"><?php _e('Slug', 'quick_slugs'); ?></th>
						<th scope="col" id="title" class="manage-column column-title"><?php _e('Title', 'quick_slugs'); ?></th>
						<th scope="col" id="categories" class="manage-column column-categories"><?php _e('Categories', 'quick_slugs'); ?></th>
						<th scope="col" id="date" class="manage-column column-date"><?php _e('Date', 'quick_slugs'); ?></th>
						
					</tr>
					</thead>
					<tfoot>
					<tr>
						<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
						<th scope="col" class="manage-column column-slug"><?php _e('Slug', 'quick_slugs'); ?></th>
						<th scope="col" class="manage-column column-title"><?php _e('Title', 'quick_slugs'); ?></th>
						<th scope="col" class="manage-column column-categories"><?php _e('Categories', 'quick_slugs'); ?></th>
						<th scope="col" class="manage-column column-date"><?php _e('Date', 'quick_slugs'); ?></th>
					</tr>
					</tfoot>					
					<tbody>
					<?php
					$class = 'alternate';
					while (have_posts()) { the_post(); global $post;
						$class = ( $class == 'alternate' ) ? '' : 'alternate';
						$edit_link = get_edit_post_link( $post->ID );
						$title = _draft_or_post_title();
						$js_array .= $post->ID . ' ';
						?>
						<tr valign="top" class="<?php echo $class; ?>">
							<th scope="row" class="check-column"><input type="checkbox" name="post[]" value="<?php the_ID(); ?>" /></th>
							<td class="slug column-slug">
								<input id="slug-input-<?php the_ID(); ?>" style="width:98%;" type="text" size="100" name="slug[<?php the_ID(); ?>]" value="<?php echo $this->getSlugToEdit( get_the_ID(), false ); ?>" />
								<input id="extra-slug-input-<?php the_ID(); ?>" type="hidden" name="extra-slug[<?php the_ID(); ?>]" value="<?php echo $this->getSlugToEdit( get_the_ID(), true ); ?>" />
							</td>
							<td class="title column-title">
								<input type="hidden" name="title[<?php the_ID(); ?>]" value="<?php the_title(); ?>" />
								<strong><?php if ( current_user_can( 'edit_post', $post->ID ) ) { ?><a class="row-title" href="<?php echo $edit_link; ?>" title="<?php echo attribute_escape(sprintf(__('Edit "%s"'), $title)); ?>"><?php echo $title ?></a><?php } else { echo $title; }; _post_states($post); ?></strong>
							</td>
							<td class="categories column-categories"><?php
								$categories = get_the_category();
								if ( !empty( $categories ) ) {
									$out = array();
									foreach ( $categories as $c )
										$out[] = "<a href='edit.php?category_name=$c->slug'> " . wp_specialchars(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display')) . "</a>";
										echo join( ', ', $out );
								} else {
									_e('Uncategorized');
								}
							?></td>
							<?php
								if ( '0000-00-00 00:00:00' == $post->post_date ) {
									$t_time = $h_time = __('Unpublished');
								} else {
									$t_time = get_the_time(__('Y/m/d g:i:s A'));
									$m_time = $post->post_date;
									$time = get_post_time('G', true, $post);

									$time_diff = time() - $time;

									if ( ( 'future' == $post->post_status) ) {
										if ( $time_diff <= 0 ) {
											$h_time = sprintf( __('%s from now'), human_time_diff( $time ) );
										} else {
											$h_time = $t_time;
											$missed = true;
										}
									} else {

										if ( $time_diff > 0 && $time_diff < 24*60*60 )
											$h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
										else
											$h_time = mysql2date(__('Y/m/d'), $m_time);
									}
								}

								echo '<td ' . $attributes . '>';
								if ( 'excerpt' == $mode )
									echo apply_filters('post_date_column_time', $t_time, $post, $column_name, $mode);
								else
									echo '<abbr title="' . $t_time . '">' . apply_filters('post_date_column_time', $h_time, $post, $column_name, $mode) . '</abbr>';
								echo '<br />';
								if ( 'publish' == $post->post_status ) {
									_e('Published');
								} elseif ( 'future' == $post->post_status ) {
									if ( isset($missed) )
										echo '<strong class="attention">' . __('Missed schedule') . '</strong>';
									else
										_e('Scheduled');
								} else {
									_e('Last Modified');
								}
								echo '</td>';							
							?>
						</tr>
						<?php					
					}
					?>
					</tbody>
				</table>
				
				<div class="tablenav">
				<?php
				if ( $page_links )
					echo "<div class='tablenav-pages'>$page_links_text</div>";
				?>
					<div class="alignleft actions">
						<input class="button" type="hidden" name="secure_slugs" value="<?php echo wp_create_nonce('mass_slugs'); ?>" />
						<input class="button" type="submit" name="update_all_slugs" value="<?php _e('Update All', 'quick_slugs'); ?>" /> | 
						<input class="button" type="submit" name="update_selected_slugs" value="<?php _e('Update Selected', 'quick_slugs'); ?>" />
						<br class="clear" />
					</div>
				</div>			
			</form>
			<script type="text/javascript"> /* <![CDATA[ */
			
				var backup = new Array();
				var ID_string = "<?php echo $js_array; ?>";
				var ID_array = ID_string.split(" ");
				var btn = document.getElementById('reget-slugs');
				
				function refresh_slugs() {
					if (btn.value == '<?php _e('Get slugs from titles', 'quick_slugs'); ?>') {
						for (var i = 0; i < ID_array.length; i++) {
							var old_slug = document.getElementById('slug-input-' + ID_array[i]);
							var new_slug = document.getElementById('extra-slug-input-' + ID_array[i]);
							if (old_slug && new_slug) {
								backup[i] = old_slug.value;
								old_slug.value = new_slug.value;
							}
						}
						btn.value = '<?php _e('Undo', 'quick_slugs'); ?>';
					} else {
						for (var i = 0; i < ID_array.length; i++) {
							var old_slug = document.getElementById('slug-input-' + ID_array[i]);
							var new_slug = backup[i];
							if (old_slug && new_slug) {
								old_slug.value = new_slug;
							}
						}
						btn.value = '<?php _e('Get slugs from titles', 'quick_slugs'); ?>';
					}
					
				}

			/* ]]> */
			</script>
				
			<?php else: ?>
				<div class="clear"></div>
				<p><?php _e('No posts found', 'quick_slugs'); ?>
				
			<?php endif; ?>
			<p><?php printf(__('Visit the %1$s for more details. If you find a bug or have a fantastic idea for this plugin, feel free to %2$s!','quick_slugs'),'<a href="http://hieudt.info/wp-plugins/quick-slugs/" target="_blank">'.__("plugin's homepage","quick_slugs").'</a>','<a href="http://hieudt.info/contact/" target="_blank">'.__('contact me','quick_slugs').'</a>', 'quick_slugs'); ?></p>
		</div>
    <?php
	}
}

function quick_slugs_init() {
	global $quick_slugs_admin;
	
	// Admin and XML-RPC
	if ( is_admin() || ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) ) {
		$quick_slugs_admin = new QuickSlugsAdmin();
	}
}
add_action('plugins_loaded', 'quick_slugs_init');

?>