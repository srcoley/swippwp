<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 12:34 PM
 * To change this template use File | Settings | File Templates.
 */
class userResource extends baseResource
{
    static $ACCOUNT_TYPE_DIRECT = 1;
    static $ACCOUNT_TYPE_FACEBOOK = 2;
    static $ACCOUNT_TYPE_TWITTER = 3;

    public function getUserInfo($guid=null)
    {
        if ($guid == null)
            $guid = $this->uriReq->getCurrentGuid();
        $response = $this->send("swipp/".$guid."/userinfo/getinfo");
        return new user($response['userInfo']);
    }

    /**
     * Api call to change user info, more complex but also future proof for api changes
     * @param user $user A user object with a valid guid and the values to change modified, values left as null will not be modified on platform
     */
    public function setUserInfo(user $user)
    {
        return $this->setUserInfoMain($user->userGuid,$user->getChangedValues());
    }

    /**
     * A simpler way to change user info if you do not want to use a user object & setUserInfo.
     * Note: if 1 share or notification value is null, none will be set, you must pass the existing value if you want it "unchanged"
     * @param null $fb_share Facebook share, 1=on 0=off null=Do not change
     * @param null $tw_share Twitter share, 1=on 0=off null=Do not change
     * @param null $email_notify Email notifications, 1=on 0=off null=Do not change
     * @param null $app_notify app notifications, 1=on 0=off null=Do not change
     */
    public function changeUserFlags($guid=null,$fb_share=null,$tw_share=null,$email_notify=null,$app_notify=null)
    {
        $changes = array();
        if (($fb_share !== null) && ($tw_share !== null))
            $changes['shareFlag'] = (($fb_share * 2) + ($tw_share * 4));
        if (($email_notify !== null) && ($app_notify !== null))
            $changes['notificationFlag'] = (($app_notify * 2)+($email_notify * 4));
        return $this->setUserInfoMain($guid,$changes);
    }

    /**
     * not to be used by library consumer
     * @param string $guid leave null for current user
     * @param array $changes
     */
    public function setUserInfoMain($guid,array $changes)
    {
        if ($guid == null)
            $guid = $this->uriReq->getCurrentGuid();
        $changes = json_encode($changes);
        $response = $this->send("swipp/$guid/userinfo/changeinfo/",null,uriRequestCore::PUT,$changes);
        return $response;
    }

    public function createUser($email,$password,$type="DIRECT")
    {
        $body['emailAddress'] = $email;
        $body['accountToken'] = $password;
        $body['accountType'] = $type;
        $body = json_encode($body);
        $response = $this->send("swipp/usersignup/",null,uriRequestCore::POST,$body,uriRequestCore::AUTH_APP);
        return $response;
    }

    /**
     * @param null $guid If null it will return subcribed terms for the currently logged in user
     */
    public function getSubscribedTerms($guid=null,$page=1,$pageSize=10)
    {
        $params = array('pageNumber'=>$page,'pageSize'=>$pageSize);
        if ($guid == null)
            $guid = $this->uriReq->getCurrentGuid();
        $response = $this->send("swipp/$guid/subscriptions",$params);
        return $response;
    }

    public function getSubscribedTermsSummary($guid=null)
    {
        if(null == $guid)
            $guid = $this->uriReq->getCurrentGuid();
        $params = array('idOnly'=>'true','pageSize'=>100);
        $response = $this->send("swipp/$guid/subscriptions", $params);
        return $response;
    }

    public function getFollowers($guid=null,$page=1,$pageSize=10)
    {
        $params = array('pageNumber'=>$page,'pageSize'=>$pageSize);
        if ($guid == null)
            $guid = $this->uriReq->getCurrentGuid();
        $response = $this->send("swipp/$guid/followers",$params);
        return $response;
    }

    public function getFollowing($guid=null,$page=1,$pageSize=10)
    {
        $params = array('pageNumber'=>$page,'pageSize'=>$pageSize);
        if ($guid == null)
            $guid = $this->uriReq->getCurrentGuid();
        $response = $this->send("swipp/$guid/following",$params);
        return $response;
    }

    public function followUser($guid)
    {
        $response = $this->send("swipp/$guid/follow",null,uriRequestCore::POST);
        return $response;
    }

    public function unfollowUser($guid)
    {
        $response = $this->send("swipp/$guid/unfollow",null,uriRequestCore::DELETE);
        return $response;
    }

    public function getActiveFriends($friendType=1,$pageSize=5,$pageNumber=1)
    {
        $params = array('friendType'=>$friendType,'pageSize'=>$pageSize,'pageNumber'=>$pageNumber);
        $response = $this->send("swipp/whoisonswipp",$params);
        return $response['accountUsers'];
    }

    public function getFriends($guid,$friendType=1,$pageSize=20,$pageNumber=1)
    {
        if ($guid == null)
            $guid = $this->uriReq->getCurrentGuid();
        $params = array('friendType'=>$friendType,'pageSize'=>$pageSize,'pageNumber'=>$pageNumber,'forguid'=>$guid);
        $response = $this->send("swipp/whoisonswipp",$params);
        return $response['accountUsers'];
    }

    public function findByName($firstName=null,$lastName=null,$pageSize=5,$pageNumber=1)
    {
        $params = array('firstName'=>$firstName, 'lastName'=>$lastName,
            'pageSize'=>$pageSize,'pageNumber'=>$pageNumber);
        $response = $this->send("swipp/finduser",$params);
        return $response;
    }

    public function primeSwippList($guid,$pageSize=10,$pageNumber=1,$sortColumn="createtime",$sortOrder="desc")
    {
        if ($guid == null)
            $guid = $this->uriReq->getCurrentGuid();
        $params = array('sortColumn'=>$sortColumn, 'sortOrder'=>$sortOrder,
            'pageSize'=>$pageSize,'pageNumber'=>$pageNumber);
        $response = $this->send("swipp/$guid/primeswipps",$params);
        return $response;
    }

    public function userSummary($guid)
    {
        if ($guid == null)
            $guid = $this->uriReq->getCurrentGuid();
        $response = $this->send("swipp/$guid/usersummary");
        return $response;
    }

    public function getAccount()
    {
        $guid = $this->uriReq->getCurrentGuid();
        $response = $this->send("swipp/$guid/usersummary/getaccount");
        return $response;
    }

    public function addAccount($token,$type)
    {
        $guid = $this->uriReq->getCurrentGuid();
        $body['accountToken'] = $token;
        $body['accountType'] = $type;
        $body = json_encode($body);
        $response = $this->send("swipp/$guid/usersummary/addaccount",null,uriRequestCore::POST,$body);
        return $response;
    }

    public function deleteAccount($id,$type)
    {
        $guid = $this->uriReq->getCurrentGuid();
        $param['accountType'] = $type;
        $param['accountId'] = $id;
        $response = $this->send("swipp/$guid/usersummary/addaccount",$param,uriRequestCore::DELETE);
        return $response;
    }

    public function optOut($optOut=null)
    {
        if ($optOut != null)
        {
            $param['out'] = $optOut;
            $response = $this->send("swipp/opt",$param,uriRequestCore::PUT);
        }
        else
            $response = $this->send("swipp/opt");
        return $response;
    }

}
