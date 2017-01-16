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
	 <h3 class="storytitle"><?php the_title(); ?></h3>
 	<div class="storycontent">
		<?php the_content(__('(more...)')); ?>
	</div>
	<div class="feedback">
		<?php wp_link_pages(); ?>
		<?php comments_popup_link(__(''), __('Comments (1)'), __('Comments (%)')); ?>
	</div>

</div>
<?php comments_template(); // Get wp-comments.php template ?>

<?php endwhile; else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
		
		
<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>

<span class="devnote">single.php</span>
<?php get_footer(); ?>