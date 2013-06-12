<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 1:32 PM
 * To change this template use File | Settings | File Templates.
 */

define("ACCOUNT_TYPE_DIRECT", 1);
define("ACCOUNT_TYPE_FACEBOOK", 2);
define("ACCOUNT_TYPE_TWITTER", 3);
define("ACCOUNT_TYPE_FOURSQUARE", 6);

class swippConfig
{

    //public $api_endpoint = "rest.swipp.com/";
	 public $api_endpoint = "rest.swippeng.com/";
    //public $api_endpoint = "staging.swipp.com/";

    public function __construct($app_id,$app_token,$api_endpoint=null)
    {
        $this->app_id = $app_id;
        $this->app_token = $app_token;
        if ($api_endpoint != null)
            $this->api_endpoint = $api_endpoint;
    }


}
