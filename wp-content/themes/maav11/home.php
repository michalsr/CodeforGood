<?php
/*
Template Name: Home Page
*/
?>
<?php
get_header();
?>
<script src="http://www.maav.org/scripts/AC_RunActiveContent.js" type="text/javascript"></script>
<br />
<script type="text/javascript">
AC_FL_RunContent( 'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0','width','540','height','300','src','http://www.maav.org/homesplash/homesplash','loop','false','quality','high','pluginspage','http://www.macromedia.com/go/getflashplayer','wmode','transparent','movie','http://www.maav.org/homesplash/homesplash' ); //end AC code
</script><noscript><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="540" height="300">
  <param name="movie" value="http://www.maav.org/homesplash/homesplash.swf" />
  <param name="quality" value="high" /><param name="LOOP" value="false" />
  <param name="wmode" value="transparent" />
  <embed src="http://www.maav.org/homesplash/homesplash.swf" width="540" height="300" loop="false" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" wmode="transparent"></embed>
</object>
</noscript>
<br />


<?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
<?php  //was div class= //post_style(); ?>
<div class="post" id="post-<?php the_ID(); ?>">
	<div class="entry">
	<?php the_excerpt(); ?>
	</div><!-- end entry -->

</div><!-- end post -->
<?php endwhile; ?>

<?php include (TEMPLATEPATH . '/browse.php'); ?>

<?php else : ?>

<div class="post">
	<h2><?php _e('Not Found'); ?></h2>
	<div class="entry">
<p class="notfound"><?php _e('Sorry, but you are looking for something that isn&#39;t here.'); ?></p>
	</div>
</div>
<?php endif; ?>

<?php
homeposts();
home_callout();
?>
<span class="devnote">home.php</span>

<?php get_footer(); ?>

<?php
function homeposts($postcount=2) {
	global $post;
	$temppost=$post;
	$k=0;
	?>
	<div  id="homepage" class="post">
	  <?php 
		$k = 0;
		query_posts('showposts=' . $postcount . '&category_name=home'); ?>
	  <?php while (have_posts()) : the_post(); 
	  	$k++;
		echo "<div id='homespot_$k'>\n";
		?>
		<h2><?php the_title(); ?></h2>
		<?php the_content(); ?>
		<?php
        echo "</div>\n";
		// if we've done two articles, then go back to a normal page width
		if ($k==2) {echo "</div><div id='homepage_lower' class='post'>"; }
		endwhile;?>

	</div>
	<?php 
	$post=$temppost;
} // end homeposts


function home_callout($postcount=1) {
    global $post;
    $temppost=$post;
    $k=0;
    query_posts('showposts=' . $postcount . '&category_name=home-callout'); 
    if  (have_posts() ) {	
        the_post(); 
        echo "<div id='home_callout'>\n";
        ?>
        <h2><?php the_title(); ?></h2>
		<?php the_content(); ?>
        <?php
        echo "</div>\n";
        }
        // put the post back gently and no one gets hurt
	    $post=$temppost;
    } // end home_callout 
	?>
