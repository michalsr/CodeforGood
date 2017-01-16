<?php
/*
Template Name: Invitation with link to PayPal
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

  </div></td>
				
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"> 
<input name="business" value="info@maav.org" type="hidden" /> 
<input name="no_shipping" value="0" type="hidden" /> 
<input name="no_note" value="0" type="hidden" /> 
<input name="currency_code" value="USD" type="hidden" /> 
<input name="tax" value="0" type="hidden" /> 
<input name="lc" value="US" type="hidden" /> 
<input name="add" value="1" type="hidden" />
<input name="item_name" value="MAAV's Annual Spring Gala Tickets" type="hidden" /> 
<input name="item_number" value="GALATICK" type="hidden" /> 
<input name="amount" value="40" type="hidden" /> 
<input name="return" value="http://www.maav.org/home/thanks/" type="hidden" /> 
<input name="cmd" type="hidden" value="_cart" /> 
Quantity:
<input name="quantity" size="3" /> 

<input src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" name="submit" alt="Purchase tickets via Credit Card" align="right" border="0" height="47" type="image" width="122" />
</form>

<br clear="all" /></p>

	<?php the_content(__('(more...)')); ?>
	</div>

	

</div>

<?php endwhile; else: ?>
<p>Sorry, we couldn't find what you're looking for.</p>
<?php endif; ?>

<span class="devnote">donate.php</span>


<?php get_footer(); ?>
