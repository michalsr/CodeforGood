<?php
/*
Plugin Name: Gatherposts Shortcode
Plugin URI: http://www.bkjproductions.com/
Description: Adds a <code>[gatherposts]</code> shortcode which allows you to list posts that meet certain criteria. You can pass the shortcode any parameters that are accepted by query_posts(), for example: <code>[gatherposts category_name='news' orderby='date' order='desc' display_excerpt=1 posts_per_page=10]</code>.<br /> <a href="options-general.php?page=gatherposts">Additional parameters are available.</a>
Version: 1.0.8
Author: BKJ Productions
Author URI: http://www.bkjproductions.com
History: 1.0.8 fixed linktitle bug (default)
	1.0.7 Improved documentation, merged some other options from other forks
	1.0.6 Added target and class to things that are PDFs.
	1.0.5 Permalink will go to first PDF attachment if available
	1.0.4.1 test for post thumbnail function 1.0.4 bug on post type orderby 1.0.3 fixed use_meta_var_as_excerpt bug
*/

add_shortcode('gatherposts', 'gatherposts_func'); //define the new shortcode	
function gatherposts_func($attr) {
	if ($attr === '') {
		$attr = array();	
	}
	
	$defaults = array('orderby' => 'title',
					  'order' => 'ASC',
					  'posts_per_page' => -1,
					  'display_excerpt' => 0,
					  'display_complete' => 0,
					  'display_date' => 0,
					  'display_date_before' => 0,
					  'display_post_thumbnail' => 0,
					  'linktitle' => 1,
					  'title_wrap' => 'a',
					  'excerpt_wrap' => 'div',
					  'outer_id' => 'gatherposts',
					  'outer_class' => 'gatherposts',
					  'outer_wrap' => 'ul',
					  'item_wrap' => 'li',
					  'post_thumbnail_size' => 'large',
					  'show_more' => '[more]'
					  );
	
	$attr = array_merge($defaults, $attr);
	if ( isset($attr['category'])  )  {
		$attr['category_name']=$attr['category'];
		unset($attr['category']);
		}
	# if 'display_excerpt' is the string 'false', set it to a falsey value
	if (strtolower($attr['display_excerpt']) == 'false') {
		$attr['display_excerpt'] = 0;
	}

	# if 'display_complete' is the string 'false', set it to a falsey value
	if (strtolower($attr['display_complete']) == 'false') {
		$attr['display_complete'] = 0;
	}

	# if 'linktitle' is the string 'false', set it to a falsey value
	if (strtolower($attr['linktitle']) == 'false') {
		$attr['linktitle'] = 0;
	}
	
	if( @$attr['use_meta_var_as_excerpt']) {
		$attr['display_excerpt'] = true;
	}
	// get wraps:
	$outer_id = $attr['outer_id'];
	$outer_class = $attr['outer_class'];
	$outer_wrap = $attr['outer_wrap'];
	$item_wrap = $attr['item_wrap'];
	$title_wrap = $attr['title_wrap'];
	$excerpt_wrap = $attr['excerpt_wrap'];
	$linktitle = $attr['linktitle'];
	
	

	
	
		// unset some strings:
	/*	orderby=post_date
		order=desc
		posts_per_page=100
		----display_excerpt=1
		----display_date=0
		----display_date_before=0
		----display_post_thumbnail=1
		----post_thumbnail_size=large
		----show_more
		post_type=faqs
		category_name=hardware
	*/
	
	$display_excerpt = @$attr['display_excerpt'];
	$display_complete = @$attr['display_complete'];
	$display_date = @$attr['display_date'];
	$display_date_before = @$attr['display_date_before'];
	$display_post_thumbnail = @$attr['display_post_thumbnail'];
	$post_thumbnail_size = @$attr['post_thumbnail_size'];
	$show_more = @$attr['show_more'];

	unset( $attr['outer_id'] );
	unset( $attr['outer_class'] );
	unset( $attr['outer_wrap'] );
	unset( $attr['item_wrap'] );
	unset( $attr['title_wrap'] );
	unset( $attr['excerpt_wrap'] );
	unset( $attr['linktitle'] );
	
	
	unset( $attr['display_excerpt'] );
	unset( $attr['display_complete'] );
	unset( $attr['display_date'] );
	unset( $attr['display_date_before'] );
	unset( $attr['display_post_thumbnail'] );
	unset( $attr['post_thumbnail_size'] );
	unset( $attr['show_more'] );

	
	$query_string = trim(add_query_arg($attr, ''), '?');
	$gatherposts_query = new WP_Query($query_string);
	
	//echo "query_string: $query_string<br>";
	$specified_posts = $gatherposts_query->posts; # Should be able to say $specified_posts = get_posts($query_string) but for some reason that doens't work.
		
	ob_start();
	if ($specified_posts) {
		echo "<$outer_wrap class='$outer_class' id='$outer_id'>\n";
		
		
		foreach($specified_posts as $post) {
			$post_id = $post->ID;
			$post_name = $post->post_name;
			$permalink = get_permalink($post_id);
			$permalink_temp = $permalink;
			$title = get_the_title($post_id);
			$target = '';
			$class = '';
			
			$pdfs = get_children("post_type=attachment&post_mime_type=application/pdf&post_parent=$post_id");

			if ($pdfs) {
				$first_pdf = array_shift($pdfs);
				$permalink = $first_pdf->guid;
				$pdf_name = explode('/', $permalink);
				$pdf_name = $pdf_name[count($pdf_name)-1];
				$target = " target='_blank'";
				$class = " class='pdf lipdf'";
			}
					
			if ($display_date == 1) { 
				$display_date = 'n/d/y';
			}
			
			$thedate = strtotime($post->post_date);
			//$thedate = date( attr['display_date'], $thedate );
			$thedate = date( $display_date , $thedate);

			$post_text = trim_content_for_post_id($post_id);
			
			if (isset($attr['use_meta_var_as_excerpt'])) {
				$excerpt_text = get_post_meta($post_id, $attr['use_meta_var_as_excerpt'], true);
			} else {
				if ($post->post_excerpt) {
					$excerpt_text = apply_filters('get_the_excerpt', $post->post_excerpt);
				} else {
					$excerpt_text = trim_content_for_post_id($post_id);
				}
			}
			// so it it safe to assume that there is a PDF?
			if ($pdfs) {
				$target = " target='_blank'";
				$class = " class='pdf lipdf'";
			}
			
			// see if the PDF is found within the post_text?
			if ( strpos($post_text, $pdf_name) > -1) {
				$pdfs = false;	
				// back to square 1 with the permalink
				$permalink = $permalink_temp;
				$target = '';
				$class = '';
			}


			
			// TODO: add thumbnail size post_thumbnail_size
			//default: no thumbnail
			$thumbnail='';
			if (function_exists('get_the_post_thumbnail')) {
				if ($display_post_thumbnail ) {
					$size='large';
					if (strpos($post_thumbnail_size, ',')>-1)  {
						$size=split(',',$post_thumbnail_size);
					}
					
					//if ( $attr['post_thumbnail_size'] )  { $size = $attr['post_thumbnail_size'];}
					$default_attr = array(
						'alt'	=> trim(strip_tags( $title ))
						);
				
					$thumbnail=get_the_post_thumbnail( $post_id, $size, $default_attr ) . "\n";
				}
			}
			if ($display_excerpt && $excerpt_text) {
				$excerpt = " &mdash; $excerpt_text";
			} else {
				$excerpt = '';	
			}
			if ($display_complete) {
				$excerpt = trim_content_for_post_id($post_id, 1);
				$show_more = false;
			}
			if ($show_more) {
				$excerpt .= " <a href='$permalink'>" . $show_more . "</a>";
			}
			
			// TODO: Add logic for "display date before" here.
			if ( $display_date ) {
				$excerpt .= "\n <span class='date'>$thedate</span>\n";
			}
			// itemwrap, do we need it?
			$item_wrap_start = "";
			$item_wrap_stop = "";
			if ($item_wrap) {
				$item_wrap_start = "<$item_wrap>";
				$item_wrap_stop = "</$item_wrap>";
			}
			// linktitle, do we need it?
			$linktitle_start = "";
			$linktitle_stop = "";
			if ($linktitle) {
				$linktitle_start = "<a href='$permalink' $class$target>";
				$linktitle_stop = "</a>";
			}
			
			echo "$item_wrap_start<$title_wrap>$linktitle_start$thumbnail$title$linktitle_stop</$title_wrap>\n<$excerpt_wrap>$excerpt</$excerpt_wrap>$item_wrap_stop\n";
		}
		
		echo "</$outer_wrap>\n\n";
	}
	
	return ob_get_clean();
}

if (!function_exists('trim_content_for_post_id')) {
	
	function trim_content_for_post_id($post_id, $complete=false) {
		$post = get_post($post_id);
	
		if ( post_password_required($post_id) ) {
			return __('There is no excerpt because this is a protected post.');
		}
	
		#$raw_excerpt = $text;
		
		$text = $post->post_content;
	
		$text = strip_shortcodes( $text );
		$text = apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]&gt;', $text);
		if ($complete) {return $text;}
		$text = strip_tags($text,'<a>');
		$text = removeemptytags($text);
		$excerpt_length = apply_filters('excerpt_length', 55);
		$excerpt_more = apply_filters('excerpt_more', ' ' . '...');
		$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
		if ( count($words) > $excerpt_length ) {
			array_pop($words);
			$text = implode(' ', $words);
			$text = $text . $excerpt_more;
		} else {
			$text = implode(' ', $words);
		}
		
		return $text;
	}
}

 // from http://www.webmasterworld.com/forum88/7286.htm
//$html = "<a></a><b>non-empty</b>";
function removeemptytags($html_replace)
{
	$pattern = "/(<[^\/]>|<[^\/][^>]*[^\/]>)\s*<\/[^>]*>/";
	//$pattern = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";
	return preg_replace($pattern, '', $html_replace);
}

 

add_action('admin_menu', 'gatherposts_plugin_menu');

function gatherposts_plugin_menu() {
	add_options_page('Gatherposts note', 'Gatherposts', 'manage_options', 'gatherposts', 'gatherposts_plugin_options');
}

function gatherposts_plugin_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '<div class="wrap">';
?>
<div class="icon32" id="icon-plugins"></div>
<h2>Gatherposts </h2>
<p>Adds a <code>[gatherposts]</code> shortcode which allows you to list posts that meet certain criteria. You can pass the shortcode any parameters that are accepted by query_posts().
<p>
<input style="width: 98%" value="[gatherposts category_name='news' orderby='date' order='desc' display_excerpt=1 posts_per_page=10]" />



		

<table class="widefat">
<thead><tr><th valign="top" colspan="2">Ordering, etc.</th></thead>
<tbody>
<tr><th valign="top" width="200"><strong>orderby='title'</strong></th>
<td>What field to order by: title | date | <em>custom field name</em></td></tr>
<tr><th valign="top" width="200"><strong>order='ASC'</strong></th>
<td>Order for sort: ASC | DESC</td></tr>
<tr>
  <th valign="top" width="200"><strong>category=''</strong></th>
  <td>If you don't want the current category, specify category name</td>
</tr>
<tr><th valign="top" width="200"><strong>posts_per_page=-1</strong></th>
<td>How many posts do you want to display on this page?</td></tr>
</tbody>
</table><br />

<table class="widefat">
<thead><tr><th valign="top" colspan="2">Content Display</th></thead>
<tbody>
<tr><th valign="top" width="200"><strong>display_excerpt=0</strong></th>
<td>If you want to display an excerpt or not</td></tr>
<tr><th valign="top" width="200"><strong>display_complete=0</strong></th>
<td>If you're doing an accordion, you would probably want to display the <em>complete</em> post instead of the excerpt</td></tr>
<tr><th valign="top" width="200"><strong>use_meta_var_as_excerpt</strong></th>
<td>Use the specified &quot;Custom Field&quot; (a.k.a. meta variable) instead of the excerpt, and forces <strong>display_excerpt</strong> to be true, even if you specify it as false. As an example, try use_meta_var_as_excerpt='listing_member_discount' when using our <a href="http://www.bkjproductions.com/wordpress-plugin-chamber-directory/" target="_blank">Chamber Directory plugin.</a></td>
</tr>
</tbody>
</table><br />

<table class="widefat">
<thead><tr><th valign="top" colspan="2">Title Display</th></thead>
<tbody>
<tr><th valign="top" width="200"><strong>display_date=0</strong></th>
<td>Whether or not to display the date, can be a date format such as <em>n/d/y h:i:sa</em></td></tr>
<tr><th valign="top" width="200"><strong>display_date_before=0</strong></th>
<td>If displaying the date, should it be shown before the link's title?</td></tr>
<tr><th valign="top" width="200"><strong>linktitle=1</strong></th><td>If you want to display the title as a clickable link (typically you would)</td></tr>
</tbody>
</table><br />

<table class="widefat">
<thead><tr><th valign="top" colspan="2">Wrapping</th></thead>
<tbody>
<tr><th valign="top" width="200"><strong>title_wrap='a'</strong></th>
<td>How you'd like the title wrapped: If not clickable, you might want it wrapped by a &lt;b&gt; tag, for example</td></tr>
<tr><th valign="top" width="200"><strong>excerpt_wrap='div'</strong></th>
<td>Wrapper for each Excerpt, could be blank if you want to reduce code but could be useful if you need to do an accordion</td></tr>
<tr><th valign="top" width="200"><strong>outer_id='gatherposts'</strong></th>
<td>The ID for the outer wrapper</td></tr>
<tr><th valign="top" width="200"><strong>outer_class='gatherposts'</strong></th>
<td>The Class for the outer wrapper</td></tr>
<tr><th valign="top" width="200"><strong>outer_wrap='ul'</strong></th>
<td>The outer wrapper of the list, typically &lt;ul&gt;</td></tr>
<tr><th valign="top" width="200"><strong>item_wrap='li'</strong></th>
<td>How each item in the list is wrapped, typically &lt;li&gt;</td></tr>
<tr><th valign="top" width="200"><strong>show_more='[more]'</strong></th>
<td>Visible text for &quot;More&quot; link</td></tr>
</tbody>
</table><br />


<table class="widefat">
<thead><tr><th valign="top" colspan="2">Thumbnails</th></thead>
<tbody>
  <tr><th valign="top" width="200"><strong>display_post_thumbnail=0</strong></th>
<td>If you want to display the post thumbnail (see also the post thumbnail size parameter)</td></tr>
<tr><th valign="top" width="200"><strong>post_thumbnail_size='large'</strong></th>
<td>If you want to use a thumbnail, indicate the size: large | medium | small | <em>w,h</em> (i.e.: 32,32 for a 32-pixel square)</td></tr>
<tr><td colspan="2"><br />
This assumes that you have configured your theme or otherwise enabled Post Thumbnails</td>
</tbody>
</table><br />

<table class="widefat">
<thead><tr><th valign="top" colspan="2">Examples</th></tr></thead>
<tbody>
<td colspan="2">
Wrap things in an accordion div<br />
<textarea style="width: 98%" rows="3" cols="80">
[gatherposts category='hardware' post_type='faqs' orderby='post_date' order='DESC' show_more='(more)' display_complete=1 posts_per_page=100 outer_wrap='div' outer_class='accordion' outer_id='accordion' item_wrap='' linktitle=0 title_wrap='h3' excerpt_wrap='div']</textarea>
</td>
</tbody>
</table><br />

<table class="widefat">
<thead><tr><th valign="top" colspan="2">TODO</th>
</tr></thead>
<tbody><td colspan="2">
<p>Specify length of excerpt </p>

</td>
</tbody>
</table><br />
</form>
<p>Plugin by 
<a href="http://www.bkjproductions.com">BKJproductions.com</a>
</p>

<?php 	
	echo '</div>';
}




/* handy script for accordion:
<script language="javascript" type="text/javascript">
// courtesy http://www.webdesignerwall.com/tutorials/jquery-tutorials-for-designers/
// without the overhead of jquery UI
jQuery(document).ready(function($) {
	$(".accordion h3:first").addClass("active");
	$(".accordion div:not(:first)").hide();
	
	$(".accordion h3").click(function(){
		$(this).next("div").slideToggle("slow")
		.siblings("div:visible").slideUp("slow");
		$(this).toggleClass("active");
		$(this).siblings("h3").removeClass("active");
	
	});

});

</script>


// handy stylesheet, too:
.accordion {
	width: 100%;
	border-bottom: solid 1px #c4c4c4;
}
.accordion h3 {
	background: #e9e7e7 url(images/arrow-square.gif) no-repeat right -51px;
	padding: 7px 15px;
	margin: 0;
	font: bold 120%/100% Arial, Helvetica, sans-serif;
	border: solid 1px #c4c4c4;
	border-bottom: none;
	cursor: pointer;
	text-align:left;
}
.accordion h3:hover {
	background-color: #e3e2e2;
}
.accordion h3.active {
	background-position: right 5px;
}
.accordion div {
	background: #f7f7f7;
	margin: 0;
	padding: 10px 15px 20px;
	border-left: solid 1px #c4c4c4;
	border-right: solid 1px #c4c4c4;
	text-align: left;
}
*/
?>