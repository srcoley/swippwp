<?php
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 1:31 PM
 * To change this template use File | Settings | File Templates.
 */
namespace Swipp\api;
require "core/classLoaderCore.php";
require "swippConfig.php";



class swippApi
{
    private $swippConfig;
    private $uriRequest;
    private $auth;



    // Resource Accessors
    /**
     * @var assetResource
     */
    public $asset;

    /**
     * @var userResource;
     */
    public $user;

    /**
     * @var swippdataResource
     */
    public $swippdata;

    /**
     * @var swippstreamResource
     */
    public $swippstream;

    /**
     * @var categoryResource
     */
    public $category;

    /**
     * @var swippResource;
     */
    public $swipp;

    /**
     * @var notificationResource
     */
    public $notification;


    // End
    /**
     * @param $app_id
     * @param $app_token
     * @param null $api_endpoint
     * @param null $config_params
     */
    public function __construct($app_id,$app_token,$api_endpoint=null,$config_params=null)
    {
        classLoaderCore::init(dirname(__FILE__));
        $this->swippConfig = new swippConfig($app_id,$app_token,$api_endpoint);
        $this->auth = new authCore($app_id,$app_token);
        $this->uriRequest = new uriRequestCore($this->swippConfig->api_endpoint,$this->swippConfig->app_id,$this->swippConfig->app_token,$this->auth);
        $this->loadResources();
    }

    /**
     * @param $email
     * @param $password
     * @param int $accountType 1 for swipp user, 2 for FB, 3 for twitter
     * @return loginToken
     */
    public function login($email,$password,$accountType=1)
    {

        $body['emailAddress'] = $email;
        $body['accountToken'] = $password;
        $body['accountType'] = $accountType;
        $body = json_encode($body);
        $response = $this->user->send("user/usersignin",null,uriRequestCore::PUT,$body,uriRequestCore::AUTH_APP);
        $loginToken = new loginToken($response['signInOutput']);
        $this->auth->setUserCredentials($loginToken->userGuid,$loginToken->accessToken);
        return $loginToken;
    }

    /**
     * @return bool
     */
    public function logout()
    {
        $response = $this->user->send("user/usersignout",null,uriRequestCore::PUT);
        $this->auth->revokeUserCredentials();
        return true;
    }

    /**
     * @param $loginTokenString
     * @return bool|loginToken
     */
    public function restoreSession($loginTokenString)
    {
       $loginToken = new loginToken(null,$loginTokenString);
        if ($loginToken->_isEmpty)
            return false;
        $this->auth->setUserCredentials($loginToken->userGuid,$loginToken->accessToken);
        return $loginToken;
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->auth->getUserGuid();
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->auth->isLoggedIn;
    }

    /**
     *
     */
    private function loadResources()
    {
        //TODO: create a way to lazy load the resource while preserving the ease of use for the api
        $this->user = new userResource($this->uriRequest);
        $this->swipp = new swippResource($this->uriRequest);
        $this->swippdata = new swippdataResource($this->uriRequest);
        $this->swippstream = new swippstreamResource($this->uriRequest);
        $this->asset = new assetResource($this->uriRequest);
        $this->category = new categoryResource($this->uriRequest);
        $this->notification = new notificationResource($this->uriRequest);
    }
}

