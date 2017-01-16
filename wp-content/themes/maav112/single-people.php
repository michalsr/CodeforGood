<?php
get_header();
?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<div class="breadcrumb">

<!-- Breadcrumb,-->
<a title="Browse to: MAAV" href="http://maav.org/home">MAAV</a>&nbsp;&gt;&nbsp;<a href="/home/people/">People</a> &nbsp;&gt;&nbsp; <?php the_title(); ?>
</div>


<div class="post" id="post-<?php the_ID(); ?>">
	 <h3 class="storytitle"><?php the_title(); ?></h3>
 	<?php
    $thepix = get_the_post_thumbnail( $post->ID, 'full' , array('class' => 'people') );
	$thepix = str_replace('height=', 'zheight=', $thepix);
	echo $thepix;
	?>
    
    <div class="storycontent">
		<?php the_content(__('(more...)')); ?>
	</div>
	

</div>
<?php comments_template(); // Get wp-comments.php template ?>

<?php endwhile; else: ?>
<p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
<?php endif; ?>
		
		
<?php posts_nav_link(' &#8212; ', __('&laquo; Previous Page'), __('Next Page &raquo;')); ?>

<span class="devnote">single-people.php</span>
<?php get_footer(); ?>