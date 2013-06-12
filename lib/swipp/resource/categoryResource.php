<?php
namespace Swipp\api;
/**
 * categoryResource.php
 * michaelmeyers
 * 6/21/12 4:46 PM
 */
class categoryResource extends baseResource
{

    public function getCategories()
    {
        $response = $this->send("swipp/category/getcategories");
        return $response;
    }

    public function getSubCategories($cid)
    {

        $response = $this->send("swipp/category/$cid/getsubcategories");
        return $response;
    }

    public function getTerms($cid,$pageUri=null)
    {
        $tmp = new pageUriToken($pageUri);
        $param = $tmp->fullParamArray;
        $response = $this->send("swipp/category/$cid/getterms/v2",$param);
        return $response;
    }

    public function addSubCategories($cid,$name)
    {
        $body['name'] = $name;
        $body = json_encode($body);
        $response = $this->send("swipp/category/$cid/addsubcategory",null,uriRequestCore::POST,$body);
        return $response;
    }

    public function addTerm($cid,$terms)
    {
        $body['csvIds'] =  $terms;
        $body = json_encode($body);
        $response = $this->send("swipp/category/$cid/addterms",null,uriRequestCore::PUT,$body);
        return $response;
    }

    public function removeTerm($cid,$terms)
    {
        $body['csvIds'] =  $terms;
        $body = json_encode($body);
        $response = $this->send("swipp/category/$cid/removeterms",null,uriRequestCore::PUT,$body);
        return $response;
    }

    public function removeCategory($cid)
    {
        $response = $this->send("swipp/category/$cid/removecategory",null,uriRequestCore::DELETE);
        return $response;
    }


}
