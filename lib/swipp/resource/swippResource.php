<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 12:35 PM
 * To change this template use File | Settings | File Templates.
 */
class swippResource extends baseResource
{

    public function getSwipp($id)
    {
        $response = $this->send("swipp/swippterm/".$id);
        return new swipp($response['swippTerm']);
    }

    public function getSwippTags($id)
    {
        $response = $this->send("swipp/swippterm/".$id."/gettags");
        return $response['tags'];
    }

    public function addSwippTags($id,Array $tags)
    {
        $body['csvTag'] = implode(',',$tags);
        $body = json_encode($body);
        $response = $this->send("swipp/swippterm/".$id."/addtags",null,uriRequestCore::POST,$body);
        return $response;
    }

    public function getTrending($terms=10)
    {
        $param['n'] = $terms;
        $response = $this->send("swipp/swippterm/trending/",$param);
        return $response['swippTerms'];
    }

    public function getRelated($termid)
    {
        $response = $this->send("swipp/swippterm/$termid/related");
        return $response['swippTerms'];
    }

    public function getUserDetail($termid,$friendType=1)
    {
        $param = array('friendType'=>$friendType);
        $response = $this->send("swipp/swippterm/$termid/userinfo",$param);
        return $response['termUserInfo'];
    }

    public function postSwipp($term,$value,$type=1,$comment='',$image=null,$thumb=null,Array $tags=null,$shareFlag=0,$attribution=null, $longitude=null, $latitude=null)
    {
        $body['term'] = $term;
        $body['swippValue'] = $value;
        $body['applicationId'] = $this->uriReq->app_id;
        $body['swippType'] = $type;
        $body['comment'] = $comment;
        $body['sliderId'] = 1;
        $body['shareFlag'] = $shareFlag;
        $body['attribution'] = $attribution;
        if(php_sapi_name() != 'cli')
            $param['clientIP'] = $_SERVER['REMOTE_ADDR'];
        else
            $param['clientIP'] = '';
        $body['imageUrl'] = $image;
        $body['thumbnailUrl'] = $thumb;
        $body['longitude'] = $longitude;
        $body['latitude'] = $latitude;
        if ($tags != null)
        {
            $body['tags'] = $tags;
            $body['csTags'] = implode(',',$tags);
        }
        $body = json_encode($body);
        $response = $this->send("swipp/swippit",$param,uriRequestCore::POST,$body);
        return $response['swippItResult'];
    }


    public function getCommentsForSwipp($id,$page=1,$pageSize=10)
    {
        $param = array('pageNumber'=>$page,'pageSize'=>$pageSize);
        $response = $this->send("swipp/commentswipp/".$id,$param);
        return new commentSwippList($response['swippDetails']);
    }

    public function postComment($id,$swippValue,$comment="",$type=1)
    {
        $body['swippValue'] = $swippValue;
        $body['swippType'] = $type;
        $body['comment'] = $comment;
        $body['applicationId'] = $this->uriReq->app_id;
        $body = json_encode($body);
        $response = $this->send("swipp/commentswipp/".$id,null,uriRequestCore::POST,$body);
        return new commentSwipp($response['commentOutput']);
    }

    public function deleteSwipp($swippID)
    {
        $response = $this->send("swipp/swippstream/swipp/$swippID/remove",null,uriRequestCore::DELETE);
        return $response;
    }

    public function flagSwipp($swippID,$reason=0,$notes='')
    {
        $body['reason'] = $reason;
        $body['notes'] = $notes;
        $body = json_encode($body);
        $response = $this->send("swipp/swippstream/swipp/$swippID/reportabuse",null,uriRequestCore::PUT,$body);
        return $response;
    }

    public function flagTerm($termID,$reason=0,$notes='')
    {
        $body['reason'] = $reason;
        $body['notes'] = $notes;
        $body = json_encode($body);
        $response = $this->send("swipp/swippterm/$termID/reportabuse",null,uriRequestCore::PUT,$body);
        return $response;
    }

    public function subscribe($termID,$frequency=2,$private=false)
    {
        $body['isPrivate'] = $private;
        $body['frequency'] = $frequency;
        $body = json_encode($body);
        $response = $this->send("swipp/swippterm/$termID/subscribe",null,uriRequestCore::POST,$body);
        return $response;
    }

    public function unsubscribe($termID)
    {
        $response = $this->send("swipp/swippterm/$termID/unsubscribe",null,uriRequestCore::DELETE);
        return $response;
    }

    public function issubscribed($termID)
    {
        $response = $this->send("swipp/swippterm/$termID/issubscribed");
        return $response;
    }

    public function subscribers($termID,$page=1,$pageSize=10)
    {
        $param = array('pageNumber'=>$page,'pageSize'=>$pageSize);
        $response = $this->send("swipp/swippterm/$termID/subscribers",$param);
        return $response;
    }

    public function findByName($term)
    {
        $param = array("term"=>$term);
        $response = $this->send("swipp/swippterm/findbyname",$param);
        if ($response == null)
            return false;
        return $response;
    }

    public function termHint($text,$amount=10)
    {
        $param = array('filter'=>$text,'pageSize'=>$amount);
        $response = $this->send("swipp/termhint",$param);
        return $response;
    }

}
