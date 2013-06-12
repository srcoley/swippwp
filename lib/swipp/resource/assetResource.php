<?php
namespace Swipp\api;
/**
 * assetResource.php
 * michaelmeyers
 * 3/21/12 9:37 AM
 */
class assetResource extends baseResource
{

    protected $asset_base = "swipp-asset/";

    /**
     * @param $file
     * @return photoAsset
     */
    public function postPhoto($file)
    {
        if (file_exists($file))
        {
        $body = file_get_contents($file);
        $content = $this->getMimeType($file);
        $result = $this->send($this->asset_base . "photo",null,uriRequestCore::POST,$body,uriRequestCore::AUTH_USER_AND_APPID,$content);
        return new photoAsset($result);
        }
        else
            return false;
    }

    public function postPhotoRaw($raw_data,$mime)
    {
        $body = $raw_data;
        $content = $mime;
        $result = $this->send($this->asset_base . "photo",null,uriRequestCore::POST,$body,uriRequestCore::AUTH_USER_AND_APPID,$content);
        return new photoAsset($result);
    }

    public function getPhoto($location,$return_img_tag=false)
    {
        $param['location'] = $location;
        $content = $this->getMimeType($location);
        $result = $this->send($this->asset_base . "photo",$param,uriRequestCore::GET,null,uriRequestCore::AUTH_USER,"application/json",true);
        if ($return_img_tag)
            return '<img src="data:image/jpeg;base64,' . base64_encode($result) . '" />';
        else
            return array("content_type"=>$content,"img"=>base64_encode($result));
    }



    protected function getMimeType($filename)
    {
        $path_info = pathinfo($filename);
        $ext = $path_info['extension'];
        switch ($ext)
        {
            case "jpg":
            case "jpeg":
                return "image/jpeg";
            case "png":
                return "image/png";
            case "gif":
                return "image/gif";

        }
    }

}
