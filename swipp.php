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
		if(is_admin()) {
			require_once('swipp-settings.php');
			define('SWIPP_APP_ID', base64_encode('wp'));
			define('SWIPP_APP_TOKEN', base64_encode('Jun62013'));
			add_action('media_buttons_context', 'add_swipp_button');
			add_action('admin_footer', 'add_inline_swipp_content');
			add_filter('manage_posts_columns', 'add_swipp_column');
			add_action('manage_posts_custom_column', 'swipp_columns', 10, 2);

			/* ajax listeners */
			add_action('wp_ajax_swipp_sign_up', 'swipp_sign_up_callback');
			add_action('wp_ajax_swipp_sign_in', 'swipp_sign_in_callback');
			add_action('wp_ajax_swipp_check_org', 'swipp_check_org_callback');
			add_action('wp_ajax_swipp_org_term', 'swipp_org_term_callback');
			add_action('wp_ajax_swipp_create_widget', 'swipp_create_widget_callback');

			wp_register_script('swipp-admin-js', $GLOBALS['SWIPP_PLUGIN_PATH'].'/swippAdmin.js', array('jquery'), false, true);
		} else {
			add_shortcode('swippjs', 'swipp_js');
		}
	}
	add_action('init', 'swipp_init');

	/**
	 * The swippjs shortcode output
	 */
	function swipp_js( $atts ) {
		extract( shortcode_atts( array(
			'option_a' => 'true',
			'option_b' => 'false'
		), $atts ) );
		return "<script type='text/javascript'>alert('Swipp');</script>";
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
	 * Swipp API: usersignup
	 */
	function swipp_sign_up_callback(){
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
		if($ret['status'] == 200) {
			add_post_meta($post_id, 'swipp_term', trim(' ' . $ret['response']->termId), false);
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
			echo json_encode(array('error_key' => 'REQUEST_FAILED 3'));
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
		$term_detail = $ret4['response']['widgetTermDetail']['termData']['swippTerm'];

		add_post_meta($post_id, 'widget_detail', json_encode($ret4), true);

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
		curl_setopt($ch, CURLOPT_USERAGENT, "swipp-wp-plugin/0.0.1");
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
