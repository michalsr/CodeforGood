<style>
.zm {display: none;}
</style>

<!-- begin sidebar -->


<?php 
//IT is assumed that prototype and effects are loaded; if not, use these 2 lines
?>
<script src="/scripts/prototype.js" type="text/javascript"></script>
<script src="/scripts/ajax/effects.js" type="text/javascript"></script>

<script src="/scripts/scriptaculous.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
<!--
var lastguy=''; // last item displayed
function showmenu(x) {
	if ( lastguy>'') {
		if ( lastguy !=x) {
			if (document.getElementById(lastguy).style.display=='') {
			// For some reason, you can't use the same Effect twice.
			// So, we're using the BlindUp effect to hide the lastguy
			//Effect.SlideUp(lastguy, {duration: .3});
			// If you want the lastguy to just "snap" back into nothing,
			// use the display='none' method:
			//document[lastguy].style.display='none';
			Effect.BlindUp(lastguy, {duration: .5});
			lastguy = x;
			}
		}
	}
	lastguy=x;
	var y = document.getElementById(x).style.display;
	var btn_name = "btn_"+ x;
	if (y=='none') 
	{
		Effect.SlideDown(x, {duration: .3});
		//document.getElementById(x).style.display = 'block';		
	}
	else
	{
		Effect.SlideUp(x, {duration: .2});
	}
}

// -->
</script>


 <div id="menu">

<ul>
 <li id="search">
   <?php
   // display a search box
   // first, decide if we want to just display the word "search..." here...
   global $s;
   $x='Search...';
   if ($s>'') {$x=$s;}
   ?>
   
   <form id="searchform" method="get" action="<?php bloginfo('home'); ?>/">
	<div>
		<input type="text" name="s" id="s" size="15" value="<?php echo $x?>" onfocus="this.value=''" /><input type="image" id="searchsubmit" value="Search" src="<?php bloginfo('template_directory'); ?>/images/btn_search.png" alt="Search" align="bottom" width="20" height="20" border="0" />
	</div>
	</form>
 </li>

	<?php 
	$fmenu2 = 'exclude=22,34,57,66,43';
	$fmenu = '';
	if (function_exists('fold_page_menus')) {
		$fmenu=fold_page_menus();
		$fmenu =str_replace('&',',43&',$fmenu);
	}
	// list pages we want to include (this hierarchy):
	//$fmenu2.='&include=' .  page_ancestory_id($post->ID);
	

		//fold_page_menus().'title_li=<h2>Pages'
	
//echo 'fmenu: ' . $fmenu  . '<BR>';

	//use the exclude= parameter to list the categories you DON't want, separated by commas
	$x=wp_list_pages('depth=2&sort_column=menu_order&title_li=&' . $fmenu . '&echo=0'); //
	//&exclude=22,34,57,66,74,43'); 
	$additional="&child_of=".@$post->post_parent;
	$additional='';
	$y=wp_list_pages('depth=2&sort_column=menu_order&title_li=&' . $fmenu2 . '&echo=0' . $additional); //
	//echo $y;
	echo "\n\n\n\n\n\n\n\n\n\n\n\n\n";
	echo "\n\n\n\n\n\n\n\n\n\n\n\n\n";
	echo "\n\n\n\n\n\n\n\n\n\n\n\n\n";
	echo "\n\n\n\n\n\n\n\n\n\n\n\n\n";
	echo "\n\n\n\n\n\n\n\n\n\n\n\n\n";
	echo "\n\n\n\n\n\n\n\n\n\n\n\n\n";
	$yarr=split("\n",$y);
	$k=0;
	for ($i=0; $i<count($yarr); $i++) {
		$yarr[$i]=trim($yarr[$i]);
		if ($yarr[$i]=='</ul>') {
			$yarr[$i]='</ul></div></div>';
			}
		$thisline='';
	// was 	if ($yarr[$i]=='<ul>') {
	if ($yarr[$i]=="<ul class='children'>") {
			$k++;
			$temp=$yarr[$i-1];
			$thisline=$yarr[$i];
			$of=strpos($temp,'page-item-');
			$of2=strpos($temp,'"',$of);
			$name=substr($temp,$of,$of2-$of);
			//$yarr[$i]="<div id='dm_$k' class='m'><ul>";
			// we wrap things in a double-div, one with a name and one without
			// the second unnamed div is used when doing the scriptaculous visual effect.
			$yarr[$i]="<div id='$name' class='m'><div><ul>";
			// try $yarr[$i]="<div id='$name'><div class='m'><ul>";
			//$javascript='javascript:showmenu("' . $name . '");';
			//$yarr[$i-1]=str_replace('href="','href=\'' . $javascript . '\' url="',$temp);
			$javascript="onclick='showmenu(\"$name\");'";
			$yarr[$i-1]=str_replace('href="','href="#" ' . $javascript . ' url="',$temp);
			//echo "test $javascript<br>\n";
			// see if this one should be hidden or not:
			if ( strpos($temp,'current_page')<1) {
				$yarr[$i]=str_replace("class='m'",'style="display: none;"',$yarr[$i]);
				}
				
		}
		
		// but backtrack to see if maybe it should be the previous page, if this is a page that has children.
		if ( strpos($thisline,'current_page_parent')>-1) {
			$yarr[$i-1]='TEST' . str_replace('style="display: none;"','style="display: block;"',$yarr[$i-1]);
		}
	}
	$y=join("\n",$yarr);
	//echo wp_list_pages('depth=2&sort_column=menu_order&title_li=&' . $fmenu . '&echo=0');
	echo $y;
	
	?>
	
	<br />



<?php 
if (function_exists('c2c_random_file') ) {
	$x= c2c_random_file('/home/wp-content/uploads/home/');
	if (file_exists($x)) {
		echo "<img src='$x' width='200' border='0' alt='Random image' vspace='10'>";
	}
} ?>
    <br />
 <?php include('include_youtube.php'); ?>
<div id="donations">
<!-- <form action="http://maav.org/home/ways-to-get-involved-with-maav/" method="post"> 
<input name="cmd" value="_xclick" type="hidden" /> <input name="business" value="info@maav.org" type="hidden" /> <input name="item_name" value="Donation" type="hidden" /> <input name="no_shipping" value="0" type="hidden" /> <input name="no_note" value="1" type="hidden" /> <input name="currency_code" value="USD" type="hidden" /> <input name="tax" value="0" type="hidden" /> <input name="lc" value="US" type="hidden" /> <input name="bn" value="PP-DonationsBF" type="hidden" /> <input name="submit" align="left" border="0"  id="donate" type="image" src="<?php bloginfo('template_directory'); ?>/images/btn_donate.png" alt="Make a difference: Make a donation" width="40" height="40" /><br />
<img src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" height="1" width="1" /></form>-->
<a href="/home/ways-to-get-involved-with-maav/"><img src="<?php bloginfo('template_directory'); ?>/images/btn_donate.png" id="donate" alt="Make a difference: Make a donation" width="40" height="40" border="0" /></a><br />
</div>
<br clear="all" /></p>

<hr />
<?php
if (is_home()) {

/*show only posts that have the same category as the name of this page:

 	$thetitle=the_title('','',false);
	$whichcategory= str_replace(' ','-',strtolower($thetitle));
	$thetitle=strtoupper($thetitle);
	*/
	$catname="Highlights";
	query_posts('category_name=' . $catname .'&showposts=10'); 
	
	if (have_posts()) {
		?>
		<div class="highlights">
		<h2>Highlights</h2>
		<ul>  	<?php while (have_posts()) : the_post(); ?>
			<li><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
			<br /><?php doexcerpt() ?>
			</li>
			<?php endwhile; ?>
		</ul>
		</div>	
	<?php
	}
}
	
?>

		
	

</ul>

</div>
<!-- end sidebar -->
