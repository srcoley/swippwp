<?php
	/**
	Plugin Name: Swipp
	Plugin URI: 
	Description: Integrate the best social intelligence platform on the web to measure your visitors' sentiment on any topic.
	Version: 1.0
	Author: Stephen Coley, Douglas Karr
	Author URI: http://www.dknewmedia.com

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

	$GLOBALS['SWIPP_PLUGIN_PATH'] = plugins_url('', __FILE__);

	/**
	 * Start swipp plugin
	 *
	 * - Include settings file
	 * - Define constants
	 * - Add actions, filters, shortcodes
	 * - Register scripts
	 */
	function swipp_init() {
		define('SWIPP_APP_ID', base64_encode('wp'));
		define('SWIPP_APP_TOKEN', base64_encode('Jun62013'));

		// These contants can be changed to switch between Staging and Production
		define('SWIPP_API_URL', 'http://rest.swippeng.com');
		define('SWIPP_SEARCH_URL', 'http://rest.swippeng.com');
		define('SWIPP_INCLUDE_URL', 'http://swippplus.swippeng.com');

		if(is_admin()) {
			require_once('swipp-settings.php');
			add_action('media_buttons_context', 'add_swipp_button');
			add_action('admin_footer', 'add_inline_swipp_content');
			add_filter('manage_posts_columns', 'add_swipp_column');
			add_action('manage_posts_custom_column', 'swipp_columns', 10, 2);
			add_action('admin_menu', 'swipp_add_menu');
			add_action('admin_notices', 'swipp_warning');

			/* ajax listeners */
			add_action('wp_ajax_swipp_autosuggest', 'swipp_autosuggest_callback');
			add_action('wp_ajax_swipp_sign_up', 'swipp_sign_up_callback');
			add_action('wp_ajax_swipp_sign_in', 'swipp_sign_in_callback');
			add_action('wp_ajax_swipp_check_org', 'swipp_check_org_callback');
			add_action('wp_ajax_swipp_org_term', 'swipp_org_term_callback');
			add_action('wp_ajax_swipp_create_widget', 'swipp_create_widget_callback');

			add_action('admin_enqueue_scripts', 'swipp_widget_assets');
		} else {
			add_shortcode('swippjs', 'swipp_js');
			add_action('wp_enqueue_scripts', 'swipp_widget_assets');
		}
	}
	add_action('init', 'swipp_init');


	/*
	 * Displays a notice on wp-admin to authenticate the Swipp plugin
	 */
	function swipp_warning() {
		$options = get_option('swipp-settings'); 
		if($options['swipp_user_guid_hidden']=="") {
			echo "<div id='swipp-warning' class='updated fade'><p><strong>";
			echo __('Swipp is almost ready.');
			echo "</strong> ";
			echo sprintf(__('You must <a href="%1$s">authenticate with Swipp</a> for it to work.'), "admin.php?page=swipp-settings");
			echo "</p></div>";
		}
	}

	/**
	 * Registers Swipp assets for use within WordPress
	 */
	function swipp_widget_assets() {
		if(is_admin()) {
			wp_register_script('swipp-admin-js', $GLOBALS['SWIPP_PLUGIN_PATH'].'/js/swippAdmin.js', array('jquery', 'jquery-ui-autocomplete'), false, false);
			wp_register_style('jquery-ui-custom', $GLOBALS['SWIPP_PLUGIN_PATH'].'/css/smoothness/jquery-ui-1.10.3.custom.min.css');
			wp_enqueue_script('jquery');
			wp_enqueue_script('swipp-admin-js');
			wp_enqueue_style('jquery-ui-custom');
		}
		/*wp_register_script('swipp-jquery', 'http://code.jquery.com/jquery-1.8.3.js', array(), false, false);
		wp_register_script('swipp-widget-prep-js', $GLOBALS['SWIPP_PLUGIN_PATH'].'/js/swippWidgetPrep.js', array(), false, false);
		wp_register_script('swipp-widget-js', 'http://swippplus.swippeng.com/widget/js/swippWidget.js', array('swipp-widget-prep-js', 'jquery'), false, false);
		wp_register_script('swipp-widget-min-js', 'http://swippplus.swippeng.com/widget_minimal/js/swippWidget.js', array('swipp-widget-prep-js', 'jquery'), false, false);
		wp_register_script('swipp-custom-ui-core-js', 'http://swippplus.swippeng.com/widget/slider/js/jquery.ui.core.min.js', array('swipp-widget-prep-js', 'jquery'), false, false);
		wp_register_script('swipp-custom-ui-widget-js', 'http://swippplus.swippeng.com/widget/slider/js/jquery.ui.widget.min.js', array('swipp-custom-ui-core-js'), false, false);
		wp_register_script('swipp-custom-ui-mouse-js', 'http://swippplus.swippeng.com/widget/slider/js/jquery.ui.mouse.min.js', array('swipp-custom-ui-widget-js'), false, false);
		wp_register_script('swipp-custom-ui-slider-js', 'http://swippplus.swippeng.com/widget/slider/js/jquery.ui.slider.min.js', array('swipp-custom-ui-mouse-js'), false, false);
		wp_register_script('swipp-custom-ui-touch-js', 'http://swippplus.swippeng.com/widget/slider/js/jquery.ui.touch-punch.min.js', array('swipp-custom-ui-slider-js'), false, false);

		wp_register_style('swipp-widget-custom-css', $GLOBALS['SWIPP_PLUGIN_PATH'].'/css/swippWidget.css');
		wp_register_style('swipp-widget-css', 'http://swippplus.swippeng.com/widget/css/swippWidget.css');
		wp_register_style('swipp-widget-slider-css', 'http://swippplus.swippeng.com/widget_minimal/slider/css/slider.css');*/

		//wp_enqueue_script('swipp-widget-prep-js');
		if(!is_admin()) {
			/*wp_enqueue_style('swipp-widget-custom-css');
			wp_enqueue_style('swipp-widget-slider-css');
			wp_enqueue_style('swipp-widget-css');
			
			wp_enqueue_script('jquery');
			wp_enqueue_script('swipp-widget-prep-js');

			wp_enqueue_script('swipp-widget-js');

			wp_enqueue_script('swipp-jquery');
			wp_enqueue_script('swipp-custom-ui-core-js');
			wp_enqueue_script('swipp-custom-ui-widget-js');
			wp_enqueue_script('swipp-custom-ui-mouse-js');
			wp_enqueue_script('swipp-custom-ui-slider-js');
			wp_enqueue_script('swipp-custom-ui-touch-js');
			wp_enqueue_script('swipp-widget-min-js');*/
		}
		
	}


	/**
	 *
	 * The swippjs shortcode output
	 */
	function swipp_js( $atts ) {
		global $post;

		if(!is_page() && !is_single()) {
			return false;
		}

		extract( shortcode_atts( array(
			'type' => 1
		), $atts ) );

		if(!isset($atts['type']) || $atts['type'] == '') {
			return false;
		}

		if(!isset($atts['term_id']) || $atts['term_id'] == '') {
			return false;
		}

		if(!isset($atts['widget_key']) || $atts['widget_key'] == '') {
			return false;
		}

		$swipp_widget_key		= base64_encode($atts['widget_key']);
		$swipp_widget_type	= $atts['type'];
		$swipp_term_id			= $atts['term_id'];
		$swipp_app_token		= str_replace('-', '', SWIPP_APP_TOKEN);

		if($swipp_widget_type == '1') {
			$output = "<link rel='stylesheet' type='text/css' href='" . SWIPP_INCLUDE_URL . "/widget/css/swippWidget.css' />";
		} else if($swipp_widget_type == '3') {
			$output = "<link rel='stylesheet' href='" . SWIPP_INCLUDE_URL . "/widget_minimal/slider/css/slider.css' type='text/css'>";
			$output .= "<link rel='stylesheet' type='text/css' href='" . SWIPP_INCLUDE_URL . "/widget_minimal/css/swippWidget.css' />";
		}
		$output .= "<script>";
		$output .= "var Swipp= Swipp || {}; Swipp.baseUri = '" . SWIPP_INCLUDE_URL . "';";
		$output .= "</script>";
		$output .= "<script src='http://code.jquery.com/jquery-1.8.3.js'></script>";
		if($swipp_widget_type == '1') {
			$output .= "<script src='" . SWIPP_INCLUDE_URL . "/widget/js/swippWidget.js'></script>";
		} else if($swipp_widget_type == '3') {
			$output .= "<script src='" . SWIPP_INCLUDE_URL . "/widget/slider/js/jquery.ui.core.min.js'></script>";
			$output .= "<script src='" . SWIPP_INCLUDE_URL . "/widget/slider/js/jquery.ui.widget.min.js'></script>";
			$output .= "<script src='" . SWIPP_INCLUDE_URL . "/widget/slider/js/jquery.ui.mouse.min.js'></script>";
			$output .= "<script src='" . SWIPP_INCLUDE_URL . "/widget/slider/js/jquery.ui.slider.min.js'></script>";
			$output .= "<script src='" . SWIPP_INCLUDE_URL . "/widget/slider/js/jquery.ui.touch-punch.min.js'></script>";
			$output .= "<script src='" . SWIPP_INCLUDE_URL . "/widget_minimal/js/swippWidget.js'></script>";
		}

		$output .=	"<div termid='$swipp_term_id' ";
		if($swipp_widget_type == "3" || $swipp_widget_type == 3) {
			$output .=	"id='swipp-slider-$swipp_term_id' ";
		}
		$output .=	"widgetKey='$swipp_widget_key' ";
		$output .=	"apptoken='$swipp_app_token' ";
		$output .=	"name='swippButton' ";
		if($swipp_widget_type == "3" || $swipp_widget_type == 3) {
			$output .=	"class='swippSlider' ";
		} else {
			$output .=	"class='swippButton' ";
		}
		$output .=	"popupPosition='right' ";
		if($swipp_widget_type == "1" || $swipp_widget_type == 1) {
			$output .=	"scorePosition='right' ";
		}
		$output .=	"pictureUrl='http%3A%2F%2Fplus.swipp.com%2Fwidget%2Fimg%2Fdefault-avatar.png'>";
		$output .=	"</div>";

		//return "<script type='text/javascript'>alert('" . $post->ID . "');</script>";
		return $output;
	}

	/**
	 * Adds a Swipp column header to the All Posts table
	 */
	function add_swipp_column($columns) {
		return array_merge( $columns, array('swipp' => __('Swipp')) );
	}

	/**
	 * Adds a Swipp column value to the All Posts table
	 */
	function swipp_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'swipp' :
				//echo get_post_meta( $post_id , 'swipp' , true ); 
				echo '0';
				break;
		}
	}

	/**
	 * Puts an Add Swipp button to the content editor
	 */
	function add_swipp_button($context) {

		//path to my icon
		$img = $GLOBALS['SWIPP_PLUGIN_PATH'].'/images/swipp_16x16.png';
		//our popup's title
		$title = 'Add Swipp';
		//append the icon
		$context .= "<a id='swipp_add_swipp' title='{$title}' class='thickbox button' href='#TB_inline?width=400&height=555&inlineId=swipp_popup'>";
		$context .= "<img style=\"margin: -2px 0 0 0; padding: 0;\" src=\"$img\" /> Add Swipp";
		$context .= "</a>";

		return $context;
	}

	/**
	 * Content for the Add Swipp popup
	 */
	function add_inline_swipp_content() { ?>
		<?php
			$swipp_settings = get_option('swipp-settings');
		?>
		<div id="swipp_popup" style="display:none;">
			<h3>Step 1: Select a Widget Style</h3>
			<p style="width: 40%; text-align: left; display: inline-block; margin-left: 1em;">
				<label for="swipp_widget_style">Standard: </label>
				<input type="radio" id="swipp_widget_style_right" name="swipp-settings[swipp_widget_style]" class='swippStyle' value="1" checked='checked'/><br />
				<img src='<?php echo $GLOBALS['SWIPP_PLUGIN_PATH'] . '/images/swipp-widget-right.png'; ?>' />
			</p>
			<p style="width: 40%; text-align: left; display: inline-block; margin-left: 1em;">
				<label for="swipp_widget_style">Quick: </label>
				<input type="radio" id="swipp_widget_style_top" name="swipp-settings[swipp_widget_style]" class='swippStyle' value="3" /><br />
				<img src='<?php echo $GLOBALS['SWIPP_PLUGIN_PATH'] . '/images/swipp-widget-slider.png'; ?>' />
			</p>
			<h3>Step 2: Select a Topic</h3>
			<p style="margin-left: 1em;">
				<label for="swipp_select_term">Topic: </label>
				<input type="text" id="swipp_select_term" name="swipp-settings[swipp_select_term]" value="" />
				<span id="swipp_term_check" style="display: none; background: url(<?php echo $GLOBALS['SWIPP_PLUGIN_PATH']; ?>/images/swipp_term_check.png) no-repeat top left; width: 26px; height: 18px; text-indent: -2000em;"></span>
			</p>
			<h3 style="float: left; line-height: 35px;">Step 3:</h3>
			<p style="float: left; margin-left: 20px;">
				<input type="button" id="swipp_create_widget" class="button button-primary button-large" value="Insert Swipp" />
			</p>
			<p></p>
			<div id="swippInfoDiv">

			</div>
		</div>
	<?php }





	/********************
	 * Ajax callbacks
	 ********************/


	/**
	 * Swipp API: autofill
	 */
	function swipp_autosuggest_callback() {
		//echo "the term: " . $_POST['term'] . ':' . strlen($_POST['term']);
		if(isset($_POST['term']) && $_POST['term'] != '' && strlen($_POST['term']) >= 1) {
			$uri		= SWIPP_SEARCH_URL . '/search/autofill?query=' . urlencode($_POST['term']);
			$date		= gmdate(DATE_RFC822);
			$header	= array("Date: $date");

			echo json_encode(curlRequest($uri, $header, "GET", null));
			die();
		} else {
			echo json_encode(array('error_key' => 'INVALID_TERM'));
			die();
		}
	}

	/**
	 * Swipp API: usersignup
	 */
	function swipp_sign_up_callback() {
		$payload = array();

		foreach($_POST as $k=>$v) {
			if(isset($_POST[$k]) && $_POST[$k] != '') {
				$payload[$k] = $v;
			} else {
				die();
			}
		}
		if(array_key_exists('accountType', $payload) && array_key_exists('emailAddress', $payload) && array_key_exists('accountToken', $payload)) {
			$uri		= SWIPP_API_URL . "/user/usersignup?appId=" . SWIPP_APP_ID . "&appToken=" . SWIPP_APP_TOKEN;
			$date		= gmdate(DATE_RFC822);
			$header	= array("Date: $date", "Content-Type: application/json");

			if(preg_match('/^[\*]+?/', $payload['accountToken'])) {
				$swipp_settings = get_option('swipp-settings');
				$payload['accountToken'] = $swipp_settings['swipp_user_token'];
			}

			$body = json_encode(array(
				"accountType" => $payload['accountType'],
				"emailAddress" => $payload['emailAddress'],
				"accountToken" => base64_encode($payload['accountToken'])
			));

			$ret = curlRequest($uri, $header, "POST", $body);

			if($ret['status'] == 200) {
				$swipp_settings = get_option('swipp-settings');
				$swipp_settings['swipp_user_token'] = base64_encode($payload['accountToken']);
				update_option('swipp-settings', $swipp_settings);
			}

			echo json_encode($ret);
		} else {
			echo "Missing require parameters.";
		}
		die();
	}

	/**
	 * Swipp API: usersignin
	 */
	function swipp_sign_in_callback(){
		$payload = array();

		foreach($_POST as $k=>$v) {
			if(isset($_POST[$k]) && $_POST[$k] != '') {
				$payload[$k] = $v;
			} else {
				die();
			}
		}
		if(array_key_exists('accountType', $payload) && array_key_exists('emailAddress', $payload) && array_key_exists('accountToken', $payload)) {
			$uri			= SWIPP_API_URL . "/user/usersignin?appId=" . SWIPP_APP_ID . "&appToken=" . SWIPP_APP_TOKEN;
			$date			= gmdate(DATE_RFC822);
			$header		= array("Date: $date", "Content-Type: application/json");

			if(preg_match('/^[\*]+?/', $payload['accountToken'])) {
				$swipp_settings = get_option('swipp-settings');
				$payload['accountToken'] = $swipp_settings['swipp_user_token'];
			}

			$body	= json_encode(array(
				"accountType"	=> $payload['accountType'],
				"emailAddress"	=> $payload['emailAddress'],
				"accountToken" => base64_encode($payload['accountToken'])
			));


			$ret = curlRequest($uri, $header, "PUT", $body);
			if($ret['status'] == 200) {
				$swipp_settings = get_option('swipp-settings');
				$swipp_settings['swipp_account_token_hidden'] = $ret['response']->signInOutput->accessToken;
				$swipp_settings['swipp_user_guid_hidden'] = $ret['response']->signInOutput->userGuid;
				update_option('swipp-settings', $swipp_settings);
				echo json_encode($ret);
			} else {
				echo json_encode('Authentication failed');
			}
		} else {
			echo "Missing required parameters.";
		}
		die();
	}


	/**
	 * Swipp API: widget/orgaccount
	 */
	function swipp_check_org_callback(){

		$swipp_settings	= get_option('swipp-settings');
		$user_guid			= base64_encode($swipp_settings['swipp_user_guid_hidden']);
		$access_token		= base64_encode($swipp_settings['swipp_account_token_hidden']);
		
		$uri		= SWIPP_API_URL . "/widget/orgaccount?userGuid=" . $user_guid . "&accessToken=" . $access_token;
		$date		= gmdate(DATE_RFC822);
		$header	= array("Date: $date", "Content-Type: application/json");

		$ret = curlRequest($uri, $header, 'GET', null);

		if(count($ret['response']->orgAccountDetails) <= 0) {
			$body = json_encode(array("companyName" => get_bloginfo('name')));
			$ret = curlRequest($uri, $header, 'POST', $body);
		}
		
		$swipp_settings = get_option('swipp-settings');
		$swipp_settings['swipp_org_id_hidden'] = $ret['response']->orgAccountDetails.id;
		update_option('swipp-settings', $swipp_settings);
		echo json_encode($ret);
		die();
	}

	
	/**
	 * Swipp API: widget/orgaccount/{orgId}/orguser/orgterm
	 */
	function swipp_org_term_callback(){

		$payload = array();
		$post_id;

		foreach($_POST as $k=>$v) {
			if(isset($_POST[$k]) && $_POST[$k] != '' && $k != 'action' && $k != 'post_id') {
				$payload[$k] = $v;
			} else if($k == 'post_id' && is_numeric($v)) {
				$post_id = $v;
			}
		}

		$swipp_settings	= get_option('swipp-settings');
		$org_id				= $swipp_settings['swipp_org_id_hidden'];
		$user_guid			= base64_encode($swipp_settings['swipp_user_guid_hidden']);
		$access_token		= base64_encode($swipp_settings['swipp_account_token_hidden']);
		
		$uri		= SWIPP_API_URL . "/widget/orgaccount/$org_id/orguser/orgterm?userGuid=$user_guid&accessToken=$access_token";
		$date		= gmdate(DATE_RFC822);
		$header	= array("Date: $date", "Content-Type: application/json");

		$ret = curlRequest($uri, $header, 'POST', json_encode($payload));
		if($ret['status'] != 200) {
			echo json_encode(array('error_key' => 'REQUEST_FAILED', 'response' => $ret['response'], 'status' => 200));
			die();
		}

		echo json_encode($ret);
		die();
	}

	function swipp_create_widget_callback() {
		$payload = array();
		$post_id;
		$term_id;
		$widget_type;

		foreach($_POST as $k=>$v) {
			if(isset($_POST[$k]) && $_POST[$k] != '' && $k != 'action' && $k != 'post_id') {
				$payload[$k] = $v;
			} else if($k == 'post_id' && is_numeric($v)) {
				$post_id = $v;
			}
		}
		
		//echo json_encode($payload);

		$swipp_settings	= get_option('swipp-settings');
		$org_id				= $swipp_settings['swipp_org_id_hidden'];
		$user_guid			= base64_encode($swipp_settings['swipp_user_guid_hidden']);
		$access_token		= base64_encode($swipp_settings['swipp_account_token_hidden']);
		
		$uri		= SWIPP_API_URL . "/widget/orgaccount/$org_id/orguser/widget?userGuid=$user_guid&accessToken=$access_token";
		/*echo json_encode($uri);
		die();*/
		$date		= gmdate(DATE_RFC822);
		$header	= array("Date: $date", "Content-Type: application/json");

		$ret = curlRequest($uri, $header, 'POST', json_encode($payload));

		if($ret['status'] != 200) {
			echo json_encode(array('ret' => $ret, 'error_key' => 'REQUEST_FAILED 1'));
			die();
		}

		echo json_encode($ret);
		die();
	}


	/********************
	 * Miscellaneous
	 ********************/

	/**
	 *	cURL Wrapper reused and streamlined from
	 *	Swipp uriRequestCore.php
	 */
	function curlRequest($uri,$header,$method,$body) {

		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ($body !== null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			$ret = array(
				'response'	=> json_decode(curl_exec($ch)),
				'status'		=> curl_getinfo($ch, CURLINFO_HTTP_CODE)
			);
		} else {
			$ret = array(
				'response'	=> json_decode(curl_exec($ch)),
				'status'		=> curl_getinfo($ch, CURLINFO_HTTP_CODE)
			);
		}
		return $ret;
	}


?>
