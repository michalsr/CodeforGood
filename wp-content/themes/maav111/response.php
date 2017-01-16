<hr style="border-style:dotted; height: 1px;" noshade="noshade" height="1" />
<p align="right" class="response"><?php
// response.php
// purpose: include for response mechanisms: email and print

//http://www.lesterchan.net/wordpress/readme/
	 edit_post_link('Edit this', '<span class="editlink">', '</span>');
	echo ' ' ;
	
	$current_page = get_permalink();
	
	if ($current_page != get_bloginfo('wpurl'))

	{
		if(function_exists('wp_email')) { email_link(); } 
		if(function_exists('wp_print')) { print_link(); }
		
		
	}
?>
</p>
