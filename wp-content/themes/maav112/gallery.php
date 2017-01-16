<?php
/*
Template Name: Gallery
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

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>


<div class="post" id="post-<?php the_ID(); ?>">
	 <h3 class="storytitle"><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h3>
	<div class="storycontent">
	<?php the_content(__('(more...)')); ?>

<?php
showphotos();
wp_reset_query();
?>

	</div>
</div>


<?php endwhile; else: ?>
<p>Sorry, we couldn't find what you're looking for.</p>
<?php endif; ?>




<span class="devnote">gallery.php</span>
<?php get_footer(); ?>
<?php
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
	



function givepix($html) {
	$pattern = '/(["\'])?(([^\.]*\.)*?(jpe?|pn)g)\1/';
	preg_match_all($pattern, $html, $matches);
	$images = $matches[2]; // well holy mother of christ! right where i guessed!!!
	// trim out the last item if it's got too many quotes screwing it up
	$k=array();
		foreach ($images as $image)
		{
			$temp=split('"',$image);
			$k[]=$temp[count($temp)-1];
		}
	return $k;
}

// draw a list of images:
function thumbpix($arr,$w=32,$attrib='') {
	$x='';
	foreach ($arr as $r) {
		$x.= "<img src='$r' width='$w' border='0' $attrib /> ";
		}
	return $x;
}

// draw a single image, given an array of them, want 2nd element
function randthumbpix($arr,$w=220,$attrib='') {
	$howmany=count($arr);
	$which=rand(0,$$howmany);
	$r=$arr[$which];
	$x= "<img src='$r' width='$w' border='0' $attrib /> ";
	return $x;
}

function showphotos() {
	global $post;
	$showarticles = 'photos';
	query_posts('category_name=' . $showarticles .'&showposts=10'); 
	if (have_posts()) {
		?>
		<br clear="all" />
		<div class="post"><table border="0" cellpadding="5" class="thumbset">
		<?php 
		$k = 0;
		$howmanycolumns = 3;
		$thesize = array (220, 200);
		while (have_posts()) : the_post(); 
			if ($k==0) {
				echo "<tr>\n"; 
				}
			$k++;
			?>
			
			<td>
			<a href="<?php the_permalink(); ?>">
			<?php 
			$html=$post->post_content;
			$x=givepix($html);
            if ($x) {
				echo randthumbpix($x);
			}
			else {
				echo get_the_post_thumbnail( $post->ID, $thesize); //, $size, $attr  			
			}
			?></a>
			
			<br />
			<a href="<?php the_permalink(); ?>" class="thumblet"><?php the_title(); ?></a>
			</td>
			<?php 
			if ($k>=$howmanycolumns) {
				$k=0;
				echo "</tr>\n"; 
			}			
			endwhile; ?>
		</tr></table>
		</div>	
	<?php
		}
		
} // end showaricles


	// show this group?
function showgroup() {
	global $post;
	//hold on to post for a minute
	//$temppost=$post;
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
	//$post=$temppost;

}

?>