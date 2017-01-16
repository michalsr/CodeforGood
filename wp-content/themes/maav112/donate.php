<?php
/*
Template Name: Donate
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
<p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"> <input name="cmd" value="_xclick" type="hidden" /> <input name="business" value="info@maav.org" type="hidden" /> <input name="item_name" value="Donation" type="hidden" /> <input name="no_shipping" value="0" type="hidden" /> <input name="no_note" value="1" type="hidden" /> <input name="currency_code" value="USD" type="hidden" /> <input name="tax" value="0" type="hidden" /> <input name="lc" value="US" type="hidden" /> <input name="bn" value="PP-DonationsBF" type="hidden" /> <input src="http://www.maav.org/home/wp-content/uploads/art/btn_youcanhelp.gif" name="submit" alt="You Can Help" align="left" border="0" height="62" type="image" width="62" />Make a difference---<br />
Make a donation <img src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" height="1" width="1" /></form>
<br clear="all" /></p>

	<?php the_content(__('(more...)')); ?>
	</div>

	

</div>

<?php endwhile; else: ?>
<p>Sorry, we couldn't find what you're looking for.</p>
<?php endif; ?>

<span class="devnote">donate.php</span>


<?php get_footer(); ?>
