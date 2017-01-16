<?php
/*
Template Name: 404 Error
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


<p>Sorry, we couldn't find what you're looking for.</p>

<span class="devnote">404.php</span>


<?php get_footer(); ?>
