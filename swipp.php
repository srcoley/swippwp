<?php
	/**
	Plugin Name: Swipp
	Plugin URI: 
	Description: Integrate the best social intelligence platform on the web to measure your visitors' sentiment on any topic.
	Version: 0.2b
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
			wp_register_script('swipp-admin-js', $GLOBALS['SWIPP_PLUGIN_PATH'].'/js/swippAdmin.js', array('jquery', 'jquery-ui-autocomplete'), false, true);
			wp_register_style('jquery-ui-custom', $GLOBALS['SWIPP_PLUGIN_PATH'].'/css/smoothness/jquery-ui-1.10.3.custom.min.css');
			wp_enqueue_script('swipp-admin-js');
			wp_enqueue_style('jquery-ui-custom');
		}
		wp_register_script('swipp-widget-prep-js', $GLOBALS['SWIPP_PLUGIN_PATH'].'/js/swippWidgetPrep.js', array('jquery'), false, true);
		wp_register_script('swipp-widget-js', 'http://plus.swipp.com/widget/js/swippWidget.js', array('jquery', 'swipp-widget-prep-js'), false, true);
		wp_register_style('swipp-widget-custom-css', $GLOBALS['SWIPP_PLUGIN_PATH'].'/css/swippWidget.css');
		wp_register_style('swipp-widget-css', 'http://plus.swipp.com/widget/css/swippWidget.css');

		wp_enqueue_script('swipp-widget-prep-js');
		wp_enqueue_script('swipp-widget-js');
		wp_enqueue_style('swipp-widget-custom-css');
		wp_enqueue_style('swipp-widget-css');
	}


	/**
	 *
	 * The swippjs shortcode output
	 */
	function swipp_js( $atts ) {
		global $post;
		$swipp_settings		= get_option('swipp-settings');
		$swipp_widget			= json_decode(get_post_meta($post->ID, 'swipp_widget', true), true);
		//$swipp_widget_key		= str_replace('-', '', $swipp_widget['response']['widgetTermDetail']['widgetKey']);
		$swipp_widget_key		= str_replace('-', '', $swipp_widget['response']['widgetTermDetail']['widgetKey']);
		$swipp_widget_info	= $swipp_widget['response']['widgetTermDetail']['termData']['swippTerm'];
		$swipp_app_token		= str_replace('-', '', SWIPP_APP_TOKEN);

		
		$output =	"<div termid='" . $swipp_widget_info['termId']. "' ";
		$output .=	"widgetKey='" . $swipp_widget_key . "' ";
		$output .=	"apptoken='" . $swipp_app_token . "' ";
		$output .=	"name='swippButton' class='swippButton' ";
		$output .=	"popupPosition='left' scorePosition='right' ";
		$output .=	"pictureUrl=''>";
		$output .=	"</div>";

		extract( shortcode_atts( array(
			'option_a' => 'true',
			'option_b' => 'false'
		), $atts ) );

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
		$context .= "<a id='swipp_add_swipp' title='{$title}' class='thickbox button' href='#TB_inline?width=400&inlineId=swipp_popup'>";
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
			<h2>Add Swipp Options</h2>
			<p>
				<label for="swipp_select_term">Term: </label>
				<input type="text" id="swipp_select_term" name="swipp-settings[swipp_select_term]" value="" />
				<input type="button" id="swipp_create_widget" class="button" value="Create widget with term" />
			</p>
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
			$uri = 'http://search.swipp.com/search/autofill?query=' . urlencode($_POST['term']);
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
			$uri		= "http://rest.swippeng.com/user/usersignup?appId=" . SWIPP_APP_ID . "&appToken=" . SWIPP_APP_TOKEN;
			$date		= gmdate(DATE_RFC822);
			$header	= array("Date: $date", "Content-Type: application/json");

			$body = json_encode(array(
				"accountType" => $payload['accountType'],
				"emailAddress" => $payload['emailAddress'],
				"accountToken" => $payload['accountToken']
			));

			echo json_encode(curlRequest($uri, $header, "POST", $body));
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
			$uri			= "http://rest.swippeng.com/user/usersignin?appId=" . SWIPP_APP_ID . "&appToken=" . SWIPP_APP_TOKEN;
			$date			= gmdate(DATE_RFC822);
			$header		= array("Date: $date", "Content-Type: application/json");

			$body	= json_encode(array(
				"accountType"	=> $payload['accountType'],
				"emailAddress"	=> $payload['emailAddress'],
				"accountToken" => $payload['accountToken']
			));

			$ret = curlRequest($uri, $header, "PUT", $body);
			$swipp_settings = get_option('swipp-settings');
			$swipp_settings['swipp_account_token_hidden'] = $ret['response']->signInOutput->accessToken;
			$swipp_settings['swipp_user_guid_hidden'] = $ret['response']->signInOutput->userGuid;
			update_option('swipp-settings', $swipp_settings);
			echo json_encode($ret);
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
		
		$uri		= "http://rest.swippeng.com/widget/orgaccount?userGuid=" . $user_guid . "&accessToken=" . $access_token;
		$date		= gmdate(DATE_RFC822);
		$header	= array("Date: $date", "Content-Type: application/json");

		$ret = curlRequest($uri, $header, 'GET', null);
		if($ret['response']->orgAccountDetails->length <= 0) {
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
		
		$uri		= "http://rest.swippeng.com/widget/orgaccount/$org_id/orguser/orgterm?userGuid=$user_guid&accessToken=$access_token";
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


	function swipp_create_widget_callback(){

		$payload = array();
		$post_id;
		$term_id;

		foreach($_POST as $k=>$v) {
			if(isset($_POST[$k]) && $_POST[$k] != '' && $k != 'action' && $k != 'post_id' && $k != 'term_id') {
				$payload[$k] = $v;
			} else if($k == 'post_id' && is_numeric($v)) {
				$post_id = $v;
			} else if($k == 'term_id' && $v != '') {
				$term_id = $v;
			}
		}

		$swipp_settings	= get_option('swipp-settings');
		$org_id				= $swipp_settings['swipp_org_id_hidden'];
		$user_guid			= base64_encode($swipp_settings['swipp_user_guid_hidden']);
		$access_token		= base64_encode($swipp_settings['swipp_account_token_hidden']);
		
		$uri		= "http://rest.swippeng.com/widget/orgaccount/$org_id/orguser/widgetkey?userGuid=$user_guid&accessToken=$access_token";
		$date		= gmdate(DATE_RFC822);
		$header	= array("Date: $date", "Content-Type: application/json");

		$ret = curlRequest($uri, $header, 'POST', null);

		if($ret['status'] != 200) {
			echo json_encode(array('error_key' => 'REQUEST_FAILED 1'));
			die();
		}

		if(!isset($ret['response']->widgetKey) || $ret['response']->widgetKey == '') {
			echo json_encode(array('error_key' => 'NO_WIDGET_KEY'));
			die();
		}

		$widget_key		= $ret['response']->widgetKey;
		$uri				= "http://rest.swippeng.com/widget/orgaccount/$org_id/orguser/widget?userGuid=$user_guid&accessToken=$access_token";

		$body				= json_encode(array(
								'widgetKey' => $widget_key,
								'type'		=> 1
							));

		$ret2 = curlRequest($uri, $header, 'POST', $body);

		if($ret2['status'] != 200) {
			echo json_encode(array('error_key' => 'REQUEST_FAILED 2'));
			die();
		}

		if(!isset($ret2['response']->widgetId) || $ret2['response']->widgetId == '') {
			echo json_encode(array('error_key' => 'NO_WIDGET_ID'));
			die();
		}

		$widget_id			= $ret2['response']->widgetId;
		$uri					= "http://rest.swippeng.com/widget/orgaccount/$org_id/orguser/widget/$widget_id/term?userGuid=$user_guid&accessToken=$access_token";
		$uri_with_term		= "http://rest.swippeng.com/widget/orgaccount/$org_id/orguser/widget/$widget_id/term?termId=$term_id&userGuid=$user_guid&accessToken=$access_token";

		$ret3 = curlRequest($uri_with_term, $header, 'PUT', null);

		if($ret3['status'] != 200) {
			echo json_encode(array('error_key' => 'REQUEST_FAILED 3', 'response' => $ret3['response']));
			die();
		}

		$ret4 = curlRequest($uri, $header, 'GET', null);

		if($ret4['status'] != 200) {
			echo json_encode(array('error_key' => 'REQUEST_FAILED 4'));
			die();
		}

		$ret4['response'] = (array) $ret4['response'];
		$ret4['response']['widgetTermDetail'] = (array) $ret4['response']['widgetTermDetail'];
		$ret4['response']['widgetTermDetail']['termData'] = (array) $ret4['response']['widgetTermDetail']['termData'];
		$ret4['response']['widgetTermDetail']['termData']['swippTerm'] = (array) $ret4['response']['widgetTermDetail']['termData']['swippTerm'];
		$ret4['response']['widgetTermDetail']['widgetKey'] = $widget_key;
		$term_detail = $ret4['response']['widgetTermDetail']['termData']['swippTerm'];

		update_post_meta($post_id, 'swipp_widget', json_encode($ret4));

		echo json_encode($ret4);
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
