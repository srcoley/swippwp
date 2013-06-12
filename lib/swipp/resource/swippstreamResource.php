<?php
namespace Swipp\api;
/**
 * User: mmeyers
 * Date: 3/18/12
 * Time: 8:18 PM
 */
class swippstreamResource extends baseResource
{

    public function getEveryoneStream ($termid,$tag=null,$lat=null,$long=null,$pageNum=null,$pageSize=null)
    {
        return $this->getStream($termid,"all",null,$tag,$lat,$long,$pageNum,$pageSize);
    }

    public function getSelfStream ($termid,$tag=null,$lat=null,$long=null,$pageNum=null,$pageSize=null)
    {
        return $this->getStream($termid,"me",null,$tag,$lat,$long,$pageNum,$pageSize);
    }

    public function getFriendStream($termid,$friendType,$tag=null,$lat=null,$long=null,$pageNum=null,$pageSize=null)
    {
        return $this->getStream($termid,"friends",$friendType,$tag,$lat,$long,$pageNum,$pageSize);
    }

    public function getUserStream($guid=null,$pageNum=null,$pageSize=null)
    {
        $guid = ($guid == null) ? $this->uriReq->getCurrentGuid() : $guid;
        return $this->getGlobalStream("me",null,null,null,null,$pageNum,$pageSize,$guid);
    }

    public function getStream($termid,$filter,$friendType=null,$tag=null,$lat=null,$long=null,$pageNum=null,$pageSize=null)
    {
        $url = "swipp/swippstream/" . $termid;
        $param = array("filter"=>$filter,"friendType"=>$friendType,"tag"=>$tag,"latitude"=>$lat,"longitude"=>$long,"pageNumber"=>$pageNum,"pageSize"=>$pageSize);
        $response = $this->send($url,$param);
        return $response;
    }

    public function getGlobalStream($filter="all",$friendType=null,$tag=null,$lat=null,$long=null,$pageNum=null,$pageSize=null,$forguid=null)
    {
        $url = "swipp/swippstream";
        $param = array("filter"=>$filter,"friendType"=>$friendType,"tag"=>$tag,"latitude"=>$lat,"longitude"=>$long,"pageNumber"=>$pageNum,"pageSize"=>$pageSize,"forguid"=>$forguid);
        $response = $this->send($url,$param);
        return $response;
    }




}
