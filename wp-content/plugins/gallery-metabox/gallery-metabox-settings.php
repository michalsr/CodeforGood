<?php
class GalleryMetaboxSettingsPage
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin', 
			'Gallery Metabox', 
			'manage_options', 
			'gallery_metabox-setting-admin', 
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		// Set class property
		$this->options = get_option( 'gallery_metabox_options' );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Gallery Metabox Settings</h2>		   
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'gallery_metabox_option_group' );   
				do_settings_sections( 'gallery_metabox-setting-admin' );
				submit_button(); 
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init()
	{		
		register_setting(
			'gallery_metabox_option_group', // Option group
			'gallery_metabox_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'Gallery Metabox adds a way to see thumbnails of the page/post you are editing', // Title
			array( $this, 'print_section_info' ), // Callback
			'gallery_metabox-setting-admin' // Page
		);  

		
		add_settings_field(
			'post_types', 
			'Post Types', 
			array( $this, 'gallery_metabox_post_types_callback' ), 
			'gallery_metabox-setting-admin', 
			'setting_section_id'
		);
		add_settings_field(
			'thumb_size', 
			'Thumbnail Size', 
			array( $this, 'gallery_metabox_thumb_size_callback' ), 
			'gallery_metabox-setting-admin', 
			'setting_section_id'
		);
}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input )
	{
		$new_input = array();
	   foreach($input as $key=>$value) {
		   $new_input[$key] = sanitize_text_field($value);
	   }
		if( isset( $input['gallery_metabox_post_types'] ) )
			$new_input['gallery_metabox_post_types'] = sanitize_text_field( $input['gallery_metabox_post_types'] );

		return $new_input;
	}

	/** 
	 * Print the Section text
	 */
	public function print_section_info()
	{
		echo "Generally speaking, you will want to display the Gallery Metabox for all post types. <br /><br />
				If you choose <em>nothing</em> here, the default Post and Page editor will display the Gallery Metabox.";
	}

	
	

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function gallery_metabox_post_types_callback()
	{
	   /* printf(
			'<input type="text" id="gallery_metabox_post_types" name="gallery_metabox_options[gallery_metabox_post_types]" value="%s" />',
			isset( $this->options['gallery_metabox_post_types'] ) ? esc_attr( $this->options['gallery_metabox_post_types']) : ''
		);
	   */
	   $x = be_gallery_metabox_all_types();
	   foreach ($x as $name) {
		   echo "<input type='checkbox' value='$name' ";
		   echo "name='gallery_metabox_options[$name]' ";
		   echo "id='$name'";
		   if (@in_array($name, $this->options) ) {echo 'checked';}
		   echo " /> $name<br />";
	   }
	}

	public function gallery_metabox_thumb_size_callback()
	{
	  printf(
			'<label>Width: <input type="text" id="gallery_metabox_thumb_size_w" name="gallery_metabox_options[gallery_metabox_thumb_size_w]" size=3 maxlength=3 value="%s" /></label>',
			isset( $this->options['gallery_metabox_thumb_size_w'] ) ? esc_attr( $this->options['gallery_metabox_thumb_size_w']) : ''
		);
	  printf(
			'<label>Height: <input type="text" id="gallery_metabox_thumb_size_h" name="gallery_metabox_options[gallery_metabox_thumb_size_h]" size=3 maxlength=3 value="%s" /></label>',
			isset( $this->options['gallery_metabox_thumb_size_h'] ) ? esc_attr( $this->options['gallery_metabox_thumb_size_h']) : ''
		);
	 
	}
	
}

if( is_admin() ) {	$gallery_metabox_settings_page = new GalleryMetaboxSettingsPage(); }

//bkjadd: get a list of custom post types so they all have the gallery metabox:
function be_gallery_metabox_all_types() {
	global $be_gallery_metabox_all_types;
	$be_gallery_metabox_all_types = array('page','post');
	// if you want only custom post types use '_builtin'=> false in the arguments array
	// 2nd argument should be 'objects' not 'object'
	$cpts = get_post_types(  array('_builtin'=> false, 'public' => true)); 
	foreach ($cpts as $cpt) {
		if ($cpt=='attachment') {continue;}
		$be_gallery_metabox_all_types[] = $cpt;
	}
	return $be_gallery_metabox_all_types;
}

