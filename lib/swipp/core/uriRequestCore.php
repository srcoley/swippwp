<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 12:33 PM
 * To change this template use File | Settings | File Templates.
 */
class uriRequestCore
{

    const GET = "GET";
    const POST = "POST";
    const DELETE = "DELETE";
    const PUT = "PUT";

    const AUTH_APP = "auth_app";
    const AUTH_USER = "auth_user";
    const AUTH_USER_AND_APPID = "auth_user_and_appid";
    const AUTH_NONE = "auth_none";

    private $host;

    public $app_id = null;
    private $app_token = null;
    /**
     * @var authCore
     */
    private $auth = null;

    private $use_ssl;
    private $last_error;

    private $curlHandle = null;


    public function __construct($host,$app_id,$app_token,$auth_class,$ssl=false)
    {
        $this->host = $host;
        $this->app_id = $app_id;
        $this->app_token = $app_token;
        $this->use_ssl = $ssl;
        $this->auth = $auth_class;
    }

    public function request ($uri,$parameters=null,$method="GET",$body=null,$auth=self::AUTH_APP,$content_type="application/json",$return_raw=false)
    {
        $uri .= $this->auth->getAuthParam($auth);
        if (($parameters != null) && (is_array($parameters)))
        {
            foreach ($parameters as $key => $value)
            {
                if ($value !== null)
                    $uri .= "&" . $key . "=" . $value;
            }
        }
        $uri = str_replace(" ", "%20", $uri);
        $fullUri = $this->getHttp() . $this->host . $uri;
        $header = $this->getHeaders($content_type);
        $json = $this->curlRequest($fullUri, $header,$method,$body);

        if ($json === false)
        {
            if ($this->last_error === 0)
                return $this->handleCurlError();
            else
                return $this->handleRestError();
        }
        if ($return_raw == false)
            return json_decode($json, true);
        else
            return $json;//its not really json

    }

    public function getCurrentGuid()
    {
        return $this->auth->getUserGuid();
    }

    private function getHeaders($content_type)
    {
        $date = gmdate(DATE_RFC822); //GMT Date
        $header[] = "Date: $date";
        if ($content_type !== null)
            $header[] = "Content-Type: ".$content_type;
        return $header;
    }


    private function curlRequest ($uri,$header,$method,$body)
    {
        loggerCore::log("Sending api request to uri: $uri", __FILE__, __LINE__, loggerCore::DEBUG);
        loggerCore::log("Api request body: $body", __FILE__, __LINE__, loggerCore::DEBUG);
        loggerCore::log("Api request headers: ". implode(";", $header), __FILE__, __LINE__, loggerCore::DEBUG);

        $this->curlHandle = curl_init($uri);
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($this->curlHandle,CURLOPT_USERAGENT, "swipp-php-wrapper/0.0.1");
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, $method);
        if ($body !== null)
            curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $body);
        $ret = curl_exec($this->curlHandle);
        loggerCore::log("Api response: ". $ret, __FILE__, __LINE__, loggerCore::DEBUG);
        if ($ret === false)
        {
            loggerCore::log("Curl error occurred:".  curl_error($this->curlHandle), __FILE__, __LINE__, loggerCore::ERROR);
            $this->last_error = 0;
            return false;
        }
        else
        {
            $error = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
            if ($error >= 300)
            {
                loggerCore::log("Server returned error code: $error", __FILE__, __LINE__, loggerCore::WARN);
                loggerCore::log("Server error text: $ret",__FILE__,__LINE__,loggerCore::WARN);
                $this->last_error = $error;
                return false;
            }
        }
        return $ret;

    }

    private function getHttp()
    {
        if ($this->use_ssl)
            return "https://";
        return "http://";
    }

    private function handleRestError()
    {
        //TODO: Rest errors should have the option to throw and error or call a callback function provided through the api
    }

    private function handleCurlError()
    {
        //TODO: Curl errors should throw an error. Curl errors are un-recoverable and will probably ruin the session but there should be a non Exception way to handle them as well.
    }
}
