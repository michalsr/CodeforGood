<?php
/*
Template Name: News
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

<?php the_date('','<h2>','</h2>'); ?>

<div class="post" id="post-<?php the_ID(); ?>">
	 <h3 class="storytitle"><?php the_title(); ?></h3>
	<div class="storycontent">
		<?php the_content(__('(more...)')); ?>
	</div>
</div>

<?php comments_template(); // Get wp-comments.php template ?>

<?php endwhile; else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
		
		
<?php
$postslist = get_posts('category=6&numberposts=100&order=ASC&orderby=post_title');
foreach ($postslist as $post) : 
	setup_postdata($post);
	   
	$ex=$post->post_excerpt;
	if ($ex>'') {$ex='<br>' . $ex;}
		echo '<p><a href="' . $post->guid . '">' . $post->post_title . '</a>' . $ex . '</p>';

	//the_excerpt(); 
	echo "<!--\n";
	print_r($post);
	echo "\n-->";
	
endforeach;
/*
	[ID] => 78
	[post_author] => 1
	[post_date] => 2007-11-07 23:12:56
	[post_date_gmt] => 2007-11-07 23:12:56
	[post_content] => long string here
	[post_title] => Baseball & Bystanders
	[post_category] => 0
	[post_excerpt] =>
	[post_status] => publish
	[comment_status] => closed
	[ping_status] => open
	[post_password] =>
	[post_name] => baseball-bystanders
	[to_ping] =>
	[pinged] =>
	[post_modified] => 2007-11-08 15:50:54
	[post_modified_gmt] => 2007-11-08 15:50:54
	[post_content_filtered] =>
	[post_parent] => 0
	[guid] => http://74.50.18.203/home/?p=77
	[menu_order] => 0
	[post_type] => post
	[post_mime_type] =>
	[comment_count] => 0
	[restricted] => 0
	[object_id] => 77
	[term_taxonomy_id] => 6
	[term_id] => 6
	[taxonomy] => category
	[description] =>
	[parent] => 0
	[count] => 3 


*/

?>





		
<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>
<span class="devnote">news.php</span>

<?php get_footer(); ?>
