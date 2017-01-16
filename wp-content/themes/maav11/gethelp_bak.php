<?php
/*
Template Name: Get Help ALTERNATE Page
*/
?>
<?php
get_header();
?>
<div class="gethelp">
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

<?php 

$spacergif=   '/home/images/spacer.gif';

// we want to keep hold of the POST since we're dipping in and out of the data 
$post2=$post;
// case 1: 
// if we're looking at a subpage, show the parent info and some space
if ($post->post_parent > 0) { 
	echo '<table border="0" cellpadding="0" cellspacing="0"><tr valign="top"><td  class="smaller">';
	echo '<img src="' . $spacergif . '" width="310" height="220" alt="spacer" align="right">';
	showparent($post->post_parent);
	echo '</td>';
	echo '</table>';
	echo '<br clear="all" />';
	echo '<table border="0" cellpadding="0" cellspacing="0" align="right" style="padding-left: 15px;">';
	echo '<tr valign="top"><td width="300">';
	topiclist();
	echo '</td></tr></table>';
	
	// This is a subpage
} else {
	// This is not a subpage
	//	echo "Main page.<br>";
		echo '<img src="' . $spacergif . '" width="310" height="220" alt="spacer" align="right">';

}
?>





<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post" id="post-<?php the_ID(); ?>">
	 <h3 class="storytitle"><?php the_title(); ?></h3>
	<div class="storycontent">
	<?php the_content(__('(more...)')); ?>
	</div>
	</div>
<?php endwhile; else: ?>
<p>Sorry, we couldn't find what you're looking for.</p>
<?php endif; ?>


<?php
if ($post->post_parent > 0) { 
	// This is a subpage
	

	} else {
	topiclist('topiclist_parent');
	// This is not a subpage
	//	echo "Main page.<br>";
	}
	?>
	



<?php
function showparent($which) {
	get_a_post($which);   
	echo '<div class="post">';
	echo '<h3 class="storytitle">';
	the_title(); 
	echo '</h3>';
	echo '<div class="storycontent">';
	//the_content(); 
	the_excerpt();
	echo '</div>';
	echo '</div>';
}

function topiclist($which='topiclist') {
		?>
		<?php 
		echo "<div id='$which'>\n";
		echo '<h3 class="storytitle">Important Numbers</h3>';
		//show links?
		global $post, $post2;
		$post=$post2;
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
				<h2>Articles</h2>
				<ul><?php while (have_posts()) : the_post(); ?>
					<li><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permalink to <?php the_title(); ?>"><?php the_title(); ?></a>
					</li>
					<?php endwhile; ?>
				</ul>
				</div>	
			<?php
			}
			
		} // end showaricles
		
		
		?>
		
		<?php
		if($post->post_parent)
		$children = wp_list_pages("title_li=&child_of=".$post->post_parent."&echo=0"); else
		$children = wp_list_pages("title_li=&child_of=".$post->ID."&echo=0");
		if ($children) { ?>
			<ul>
			<?php echo $children; ?>
			</ul>
		<?php } ?>
		</div>
	<?php } ?>
		
<span class="devnote">gethelp.php</span>


</div>

<?php get_footer(); ?>
