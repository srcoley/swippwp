<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 2:33 PM
 * To change this template use File | Settings | File Templates.
 */
class baseResource
{
    /**
     * @var uriRequestCore
     */
    protected $uriReq;

    public function __construct($uriRequest)
    {
        $this->uriReq = $uriRequest;
    }

    public function send($uri,$params=null,$method=uriRequestCore::GET,$body=null,$auth=uriRequestCore::AUTH_USER_AND_APPID,$content_type="application/json",$return_raw=false)
    {
        return $this->uriReq->request($uri,$params,$method,$body,$auth,$content_type,$return_raw);
    }


}
