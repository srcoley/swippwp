<?php

	define('SWIPP_SHORTNAME', 'swipp'); // used to prefix the individual setting field id see wptuts_options_page_fields()  
	define('SWIPP_PAGE_BASENAME', 'swipp-settings'); // the settings page slug
  
	add_action( 'admin_menu', 'swipp_add_menu' );

	/**
	 * Add top level menu item
	 */
	function swipp_add_menu(){  
		 // Display Settings Page link under the "Appearance" Admin Menu  
		 // add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function);  
		 //$swipp_settings_page = add_theme_page(__('Swipp Options'), __('Swipp Options','swipp_textdomain'), 'manage_options', SWIPP_PAGE_BASENAME, 'swipp_settings_page_fn');            
		 $swipp_settings_page = add_menu_page(__('Swipp'), __('Swipp','swipp_textdomain'), 'manage_options', SWIPP_PAGE_BASENAME, 'swipp_settings_page_fn', $GLOBALS['SWIPP_PLUGIN_PATH'].'/images/swipp_16x16.png');          
		 add_action('admin_enqueue_scripts', 'swipp_admin_scripts');
	}

	function swipp_admin_scripts() {
		wp_enqueue_script('swipp-admin-js');
	}

	/** 
	 * Helper function for defining variables for the current page 
	 * 
	 * @return array 
	 */
	function swipp_get_settings() {  
			
		$output = array();  
			
		// put together the output array   
		//$output = swipp_get_or_add_option('swipp_user_email', $output);
		//$output = swipp_get_or_add_option('swipp_user_token', $output);
		//$output = swipp_get_or_add_option('swipp_account_email', $output);

		$output['swipp_page_title']		= __( 'Swipp Settings','swipp_textdomain'); // the settings page title  
		$output['swipp_page_sections']	= ''; // the setting section  
		$output['swipp_page_fields']		= ''; // the setting fields  
		$output['swipp_contextual_help']	= ''; // the contextual help  
			
		return $output;  
	}

	function swipp_get_or_add_option($option, $output) {
		if(false == get_option($option)) {    
			add_option($option);
			$output[$option] = '';
		} else {
			$output[$option] = get_option($option);
		}
		return $output;
	}

	function swipp_define_settings() {

		if(false == get_option('swipp-settings')) {    
			add_option('swipp-settings');
		}
		
		add_settings_section(  
			 'swipp_settings_section',         // ID used to identify this section and with which to register options  
			 'Authentication',                  // Title to be displayed on the administration page  
			 'swipp_settings_desc_callback', // Callback used to render the description of the section  
			 'swipp-settings'     // Page on which to add this section of options  
		);

		add_settings_field(   
			'swipp_user_email',                      // ID used to identify the field throughout the theme 
			'Email',                           // The label to the left of the option interface element 
			'swipp_user_email_callback',   // The name of the function responsible for rendering the option interface 
			'swipp-settings',    // The page on which this option will be displayed 
			'swipp_settings_section',         // The name of the section to which this field belongs 
			array('') 
		); 

		add_settings_field(   
			'swipp_user_token',                      // ID used to identify the field throughout the theme 
			'User Token',                           // The label to the left of the option interface element 
			'swipp_user_token_callback',   // The name of the function responsible for rendering the option interface 
			'swipp-settings',    // The page on which this option will be displayed 
			'swipp_settings_section',         // The name of the section to which this field belongs 
			array('') 
		);

		add_settings_field(   
			'swipp_account_token_hidden',                      // ID used to identify the field throughout the theme 
			'Account Token',                           // The label to the left of the option interface element 
			'swipp_account_token_hidden_callback',   // The name of the function responsible for rendering the option interface 
			'swipp-settings',    // The page on which this option will be displayed 
			'swipp_settings_section',         // The name of the section to which this field belongs 
			array('') 
		);

		add_settings_field(   
			'swipp_user_guid_hidden',                      // ID used to identify the field throughout the theme 
			'',                           // The label to the left of the option interface element 
			'swipp_user_guid_hidden_callback',   // The name of the function responsible for rendering the option interface 
			'swipp-settings',    // The page on which this option will be displayed 
			'swipp_settings_section',         // The name of the section to which this field belongs 
			array('') 
		);

		add_settings_field(   
			'swipp_org_id_hidden',                      // ID used to identify the field throughout the theme 
			'',                           // The label to the left of the option interface element 
			'swipp_org_id_hidden_callback',   // The name of the function responsible for rendering the option interface 
			'swipp-settings',    // The page on which this option will be displayed 
			'swipp_settings_section',         // The name of the section to which this field belongs 
			array('') 
		);

		register_setting('swipp-settings', 'swipp-settings');
	}

	add_action('admin_init', 'swipp_define_settings');

	function swipp_user_email_callback($args) { 
		$options = get_option('swipp-settings'); 
		$html = '<input type="text" id="swipp_user_email" name="swipp-settings[swipp_user_email]" value="'.$options['swipp_user_email'].'" />';  
		$html .= '<label for="swipp_user_email"> '  . $args[0] . '</label>';  
		echo $html; 
	}
	
	function swipp_user_token_callback($args) { 
		$options = get_option('swipp-settings'); 
		$html = '<input type="password" id="swipp_user_token" name="swipp-settings[swipp_user_token]" value="'.$options['swipp_user_token'].'" />';  
		$html .= '<label for="swipp_user_token"> '  . $args[0] . '</label>';  
		echo $html; 
	}

	function swipp_account_token_hidden_callback($args) { 
		$options = get_option('swipp-settings'); 
		if($options['swipp_account_token_hidden'] == $options['swipp_account_token_hidden']/*''*/) {
			$html = '<input type="hidden" id="swipp_account_token_hidden" name="swipp-settings[swipp_account_token_hidden]" value="'.$options['swipp_account_token_hidden'].'" />';  
			$html .= '<input type="text" style="width: 350px;" id="swipp_account_token" class="hidden" readonly value="'.$options['swipp_account_token_hidden'].'" />';  
			$html .= '<input type="button" id="swipp_sign_up" class="button" value="Sign Up To Generate" />';
		} else {
			$html = '<input type="text" style="width: 350px;" id="swipp_account_token" readonly value="'.$options['swipp_account_token_hidden'].'" />';  
			$html .= '<label for="swipp_account_token"> '  . $args[0] . '</label>';
			$html .= '<input type="hidden" id="swipp_account_token_hidden" name="swipp-settings[swipp_account_token_hidden]" value="'.$options['swipp_account_token_hidden'].'" />';  
		} 
		echo $html; 
	}

	function swipp_user_guid_hidden_callback($args) { 
		$html = '<input type="hidden" id="swipp_user_guid_hidden" name="swipp-settings[swipp_user_guid_hidden]" value="'.$options['swipp_user_guid_hidden'].'" />';  
		$html .= '<label for="swipp_user_guid_hidden"> '  . $args[0] . '</label>';
		echo $html; 
	}

	function swipp_org_id_hidden_callback($args) { 
		$html = '<input type="hidden" id="swipp_org_id_hidden" name="swipp-settings[swipp_org_id_hidden]" value="'.$options['swipp_org_id_hidden'].'" />';  
		$html .= '<label for="swipp_org_id_hidden"> '  . $args[0] . '</label>';
		echo $html; 
	}

	function swipp_settings_desc_callback() {
		return;
	}

	/* Define the custom box */

	add_action('add_meta_boxes', 'swipp_add_custom_box');
	add_action('save_post', 'swipp_save_postdata');

	/* Adds a box to the main column on the Post and Page edit screens */
	function swipp_add_custom_box() {
		 add_meta_box('swipp_sectionid', 'Swipp Details', 'swipp_inner_custom_box', 'post', 'side', 'low');
	}

	/* Prints the box content */
	function swipp_inner_custom_box( $post ) {

	  // Use nonce for verification
	  wp_nonce_field( plugin_basename( __FILE__ ), 'swipp_noncename' );

	  // The actual fields for data entry
	  // Use get_post_meta to retrieve an existing value from the database and use the value for the form
		if($value = get_post_meta( $post->ID, 'widget_detail', true )) {
			$widget_details = json_decode($value, true);
			echo "<pre>";
			print_r($widget_details['response']['widgetTermDetail']);
			echo "</pre>";
		}
	  /*echo '<label for="swipp_new_term">';
			 _e("Swipp Topic", 'swipp_textdomain' );
	  echo '</label> ';
	  echo '<input type="text" id="swipp_new_term" name="swipp_new_term" value="'.esc_attr($value).'" size="25" />';*/
	  
	}

	/* When the post is saved, saves our custom data */
	function swipp_save_postdata( $post_id ) {

	  // First we need to check if the current user is authorised to do this action. 
	  if ( 'page' == $_POST['post_type'] ) {
		 if ( ! current_user_can( 'edit_page', $post_id ) )
			  return;
	  } else {
		 if ( ! current_user_can( 'edit_post', $post_id ) )
			  return;
	  }

	  // Secondly we need to check if the user intended to change this value.
	  if ( ! isset( $_POST['swipp_noncename'] ) || ! wp_verify_nonce( $_POST['swipp_noncename'], plugin_basename( __FILE__ ) ) )
			return;

	  // Thirdly we can save the value to the database

	  //if saving in a custom table, get post_ID
	  $post_ID = $_POST['post_ID'];
	  //sanitize user input
	  $mydata = sanitize_text_field( $_POST['swipp_new_term'] );

	  // Do something with $mydata 
	  // either using 
	  /*add_post_meta($post_ID, 'swipp_term', $mydata, true) or
		  update_post_meta($post_ID, 'swipp_term', $mydata);*/
	  // or a custom table (see Further Reading section below)
	}




	/**
	 * Admin Settings Page HTML 
	 *  
	 * @return echoes output 
	 */  
	function swipp_settings_page_fn() {  
	// get the settings sections array  
		 $settings_output = swipp_get_settings();  
	?>  
		<div class="wrap">
			<div class="icon32" id="icon-options-general"></div>  
			<h2><?php echo $settings_output['swipp_page_title']; ?></h2>

			<?php settings_errors(); ?>
			 
			<!--<form action="admin.php?page=<?php echo SWIPP_PAGE_BASENAME; ?>" method="post">-->
			<form action="options.php" method="post">
				<?php settings_fields('swipp-settings'); ?> 
            <?php do_settings_sections('swipp-settings'); ?>          
            <?php submit_button(); ?> 
				<?php
					$swipp_settings = get_option('swipp-settings');
					echo "<pre>";
					print_r($swipp_settings);
					echo "</pre>";
				?>
			</form>  
		</div><!-- wrap -->  
	<?php }

?>
