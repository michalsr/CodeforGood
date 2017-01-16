<?php
/*
Template Name: Calendar
*/
?><?php
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
	 <h3 class="storytitle"><?php the_title(); ?></h3>
 	<div class="storycontent">
		<?php the_content(__('(more...)')); ?>
	</div>
<br />
	<iframe src="https://www.google.com/calendar/embed?showTitle=0&amp;showTz=0&amp;height=600&amp;wkst=1&amp;bgcolor=%23ffffff&amp;src=9iqmg8k7m1tbhp72fb91kh3tg8%40group.calendar.google.com&amp;color=%23182C57&amp;ctz=America%2FNew_York" style=" border-width:0 " width="98%" height="480" frameborder="0" scrolling="no"></iframe>

</div>
<?php comments_template(); // Get wp-comments.php template ?>

<?php endwhile; else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
		
		
<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>

<span class="devnote">single.php</span>
<?php get_footer(); ?>