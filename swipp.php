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
	
	
	function swipp_init() {
		if(is_admin()) {
			require_once('swipp-settings.php');
			define('SWIPP_APP_ID', base64_encode('wp'));
			define('SWIPP_APP_TOKEN', base64_encode('Jun62013'));
			add_action('media_buttons_context', 'add_swipp_button');
			add_action('admin_footer', 'add_inline_swipp_content');
			add_filter('manage_posts_columns', 'add_swipp_column');
			add_action('manage_posts_custom_column', 'swipp_columns', 10, 2);

			wp_register_script('swipp-admin-js', plugins_url( '/swippAdmin.js', __FILE__ ), array('jquery'), false, true);
		}
	}
	add_action('init', 'swipp_init');

	function swipp_js( $atts ) {
		extract( shortcode_atts( array(
			'option_a' => 'true',
			'option_b' => 'false'
		), $atts ) );
		return "<script type='text/javascript'>alert('Swipp');</script>";
	}
	add_shortcode( 'swippjs', 'swipp_js' );

	function add_swipp_column($columns) {
		return array_merge( $columns, array('swipp' => __('Swipp')) );
	}

	function swipp_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'swipp' :
				//echo get_post_meta( $post_id , 'swipp' , true ); 
				echo '0';
				break;
		}
	}

	function add_swipp_button($context) {

		//path to my icon
		$img = $GLOBALS['SWIPP_PLUGIN_PATH'].'/images/swipp_16x16.png';
		//our popup's title
		$title = 'Add Swipp';
		//append the icon
		$context .= "<a title='{$title}' class='thickbox button' href='#TB_inline?width=400&inlineId=swipp_popup'>";
		$context .= "<img style=\"margin: -2px 0 0 0; padding: 0;\" src=\"$img\" /> Add Swipp";
		$context .= "</a>";

		return $context;
	}

	function add_inline_swipp_content() { ?>
		<div id="swipp_popup" style="display:none;">
			<h2>Add Swipp Options</h2>
		</div>
	<?php }


	/* Ajax Callbacks */

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
			$uri = "http://rest.swippeng.com/user/usersignup?appId=" . SWIPP_APP_ID . "&appToken=" . SWIPP_APP_TOKEN;
			$date = gmdate(DATE_RFC822);
			$header = array();
			$header[] = "Date: $date";
			$header[] = "Content-Type: application/json";
			$body = json_encode(array(
				"accountType" => $payload['accountType'],
				"emailAddress" => $payload['emailAddress'],
				"accountToken" => $payload['accountToken']
			));

			echo curlRequest($uri, $header, "POST", $body);
		} else {
			echo "Missing require parameters.";
		}
		die();
	}
	add_action('wp_ajax_swipp_sign_up', 'swipp_sign_up_callback');

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
			$uri = "http://rest.swippeng.com/user/usersignin?appId=" . SWIPP_APP_ID . "&appToken=" . SWIPP_APP_TOKEN;
			$date = gmdate(DATE_RFC822);
			$header = array();
			$header[] = "Date: $date";
			$header[] = "Content-Type: application/json";
			$body = json_encode(array(
				"accountType" => $payload['accountType'],
				"emailAddress" => $payload['emailAddress'],
				"accountToken" => $payload['accountToken']
			));

			echo curlRequest($uri, $header, "PUT", $body);
		} else {
			echo "Missing required parameters.";
		}
		die();
	}
	add_action('wp_ajax_swipp_sign_in', 'swipp_sign_in_callback');



function curlRequest($uri,$header,$method,$body) {

	$ch = curl_init($uri);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_USERAGENT, "swipp-wp-plugin/0.0.1");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	if ($body !== null) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		$ret = json_encode(array(
			'response'	=> json_decode(curl_exec($ch)),
			'status'		=> curl_getinfo($ch, CURLINFO_HTTP_CODE)
		));
	} else {
		return array('status' => 0);
	}
	return $ret;
}


?>
