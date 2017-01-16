<?php
/*
Template Name: Search Results
*/
?>
<?php
get_header();
?>
<div class="breadcrumb">
<?php
if (function_exists('breadcrumb_nav_xt_display'))
{
// Display the breadcrumb
if (!is_home()) {
	breadcrumb_nav_xt_display();
	}
}
?>
</div>

<div class="post" id="post-<?php the_ID(); ?>">
<h3>Search Results</h3>
<ul>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	 <li class="storytitle"><a href="<?php the_permalink() ?>" rel="bookmark"><strong><?php the_title(); ?></strong></a><br />
	<?php the_excerpt(); ?>
	<br clear="all" />

</li>



<?php endwhile; else: ?>
<p>Sorry, we couldn't find what you're looking for.</p>
<?php endif; ?>
</ul>
</div>


<?php

	// show this group?
function showgroup() {
	global $post;
	//hold on to post for a minute
	$temppost=$post;
	$mykey=get_post_meta($post->ID, 'group', $single=true);
	if ($mykey>"") {
		echo '<br clear="all" /><div class="post group">';
		$mykey=urlencode($mykey);
		$mykey=$post->post_parent;
		echo '<p>Other topics in <a href="'.get_permalink($post->post_parent).'">'.get_the_title($post->post_parent).'</a></p>';
		echo '<ul class="grandchildren">';
		wp_list_pages('depth=1&sort_column=menu_order&title_li=&child_of=' .$mykey );
		echo '</ul>';
		echo '</div>';
	}
	// show mygroup?
	$mykey=get_post_meta($post->ID, 'showgroup', $single=true);
	if ($mykey>"") {
		echo '<br clear="all" /><div class="post group">';
		$mykey=$post->ID;
		echo '<p>Other topics in ' . $post->post_title . '</p>';
		echo '<ul class="grandchildren">';
		wp_list_pages('depth=1&sort_column=menu_order&title_li=&child_of=' .$mykey );
		echo '</ul>';
		echo '</div>';
	}
	$post=$temppost;

}
	
?> 


<?php
//show links?
$key='showlinks';
$single=true;
$showlinks= get_post_meta($post->ID, $key, $single);
	
if ($showlinks>'') { 
	//bug in WP doesn't show category_name= values.
	// have to look up the category id:
	$cats = get_categories("type=link&hierarchical=0");
	foreach ($cats as $cat) {
		if ( strtolower($cat->cat_name ) == strtolower($showlinks) ) {
			wp_list_bookmarks('title_after=&title_before=&show_description=true&title_li=&category='.$cat->cat_ID);
			}
	}
	
}

function showarticle() {
	global $post;
	//hold on to post for a minute
	$temppost=$post;
	//show articles?
	$key='showarticles';
	$single=true;
	$showarticles= get_post_meta($post->ID, $key, $single);
		
	if ($showarticles>'') { 
	 
	/*show only posts that have the same category as the name of this page:
	
		$thetitle=the_title('','',false);
		$whichcategory= str_replace(' ','-',strtolower($thetitle));
		$thetitle=strtoupper($thetitle);
		*/
		
		query_posts('category_name=' . $showarticles .'&showposts=10'); 
		
		if (have_posts()) {
			?>
			<div class="post">
			<ul>  	<?php while (have_posts()) : the_post(); ?>
				<li><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permalink to <?php the_title(); ?>"><?php the_title(); ?></a>
				<?php 
				$excerpt=$post->post_excerpt;
				if ($excerpt>"") { echo "<span class='excerpt'>$excerpt</span>\n";}
				//doexcerpt() ?>
				</li>
				<?php endwhile; ?>
			</ul>
			</div>	
		<?php
		}
		
	} // end showaricles
	$post=$temppost;
}


showarticle();
showgroup();	
 
 
 function getparent_title($postid) {
 while( 0 != $bcn_theparentid ) {
				// Get the row of the parent's page;
				// 	*** Regarding performance this is not a perfect solution since this query is inside a loop ! ***
				//		However, the number of queries is reduced to the number of parents.
				$mylink = $wpdb->get_row("SELECT post_title, post_parent FROM $wpdb->posts WHERE ID = '$bcn_theparentid;'");
	
				// Title of parent into array incl. current permalink (via $bcn_theparentid, 
				// since we set this variable below we can use it here as current id!)
				$bcn_titlearray[$bcn_loopcount] = '<a href="' . get_permalink($bcn_theparentid) . '" title="' . $this->opt['urltitle_prefix'] . $mylink->post_title . $this->opt['urltitle_suffix'] . '">' . $mylink->post_title . '</a>';
	
				// New parent ID of parent
				$bcn_theparentid = $mylink->post_parent;
	
				$bcn_loopcount++;	
			}	// while
	
	}
	





/*
the post is like this:
 [post_title] => Links
    [post_category] => 0
    [post_excerpt] => 
    [post_status] => publish
    [comment_status] => closed
    [ping_status] => open
    [post_password] => 
    [post_name] => links
    [to_ping] => 
    [pinged] => 
    [post_modified] => 2007-11-11 09:24:52
    [post_modified_gmt] => 2007-11-11 14:24:52
    [post_content_filtered] => 
    [post_parent] => 66
    [guid] => http://74.50.18.203/home/bullying/links/
    [menu_order] => 5
    [post_type] => page
    [post_mime_type] => 
    [comment_count] => 0
    [restricted] => 0
	*/
	
?>
		
<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>

<span class="devnote">search.php</span>
<?php get_footer(); ?>