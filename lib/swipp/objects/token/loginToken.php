<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 4:53 PM
 * To change this template use File | Settings | File Templates.
 */
class loginToken extends objectBase
{
    public function __construct($response_object=null,$loginTokenString=null)
    {
        if ($response_object !== null)
            parent::__construct($response_object);
        else if ($loginTokenString !== null)
        {
            if (strpos($loginTokenString,"##") > 0)
            {
                $tmp = explode("##",$loginTokenString);
                $this->userGuid = $tmp[0];
                $this->accessToken = $tmp[1];
                $this->locale($tmp[2]);
            }
            else
                $this->_isEmpty = true;
        }
        else
            $this->_isEmpty = true;
        if (($this->language == null) || ($this->country == null))
            $this->locale('en_US');
    }

    public $userGuid;
    public $accessToken;
    public $assetHandlerUrl;
    public $language;
    public $country;

    public function locale($setVal=null)
    {
        if ($setVal == null)
            return $this->language . '_' . $this->country;
        else
        {
            $val = explode('_',$setVal);
            $this->language = $val[0];
            $this->country = $val[1];
            return $this->locale();
        }
    }

    public function toString()
    {
        return implode("##",array($this->userGuid,$this->accessToken,$this->locale()));
    }

}
