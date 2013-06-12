<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 12:34 PM
 * To change this template use File | Settings | File Templates.
 */
class authCore
{
    public $isLoggedIn;
    private $userGuid = "";
    private $userToken = "";

    private $appId;
    private $appToken;

    public function __construct($appId,$appToken)
    {
        $this->appId = $appId;
        $this->appToken = $appToken;
        $this->isLoggedIn = false;
    }

    public function setUserCredentials($guid,$token)
    {
        $this->isLoggedIn = true;
        $this->userGuid = $guid;
        $this->userToken = $token;
    }

    public function revokeUserCredentials()
    {
        $this->isLoggedIn = false;
        $this->userGuid = "";
        $this->userToken = "";
    }

    public function getUserGuid()
    {
        return $this->userGuid;
    }

    public function getAuthParam($type)
    {
        if ($type == uriRequestCore::AUTH_APP)
            return "?appId=" . $this->appId . "&appToken=" . $this->appToken;
        else if ($type == uriRequestCore::AUTH_USER)
            return "?userGuid=" . base64_encode($this->userGuid) . "&accessToken=" . base64_encode($this->userToken);
        else if ($type == uriRequestCore::AUTH_USER_AND_APPID)
            return "?userGuid=" . base64_encode($this->userGuid) . "&accessToken=" . base64_encode($this->userToken) . "&appId=" . $this->appId . "&appToken=" . $this->appToken;
        else
            return "?appId=" . $this->appId;
    }

}
