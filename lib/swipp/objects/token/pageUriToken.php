<?php
namespace Swipp\api;

/**
 *
 */
class pageUriToken
{
    public $url;
    public $beginValue;
    public $direction;
    public $fullParamString;
    public $fullParamArray;

    /**
     * @param null $uri
     * @return pageUriToken
     */
    public function __construct($uri=null)
    {
        $this->fullParamArray = null;//This needs to be null if a uri is not passed
        if ($uri != null)
        {
            $parsed_uri = parse_url($uri);
            $this->url = $parsed_uri['scheme'] . '://' . $parsed_uri['host'] . $parsed_uri['path'];
            $this->fullParamString = $parsed_uri['query'];
            parse_str($this->fullParamString,$this->fullParamArray);
            if (isset($this->fullParamArray['direction']))
                $this->direction = $this->fullParamArray['direction'];
            if (isset($this->fullParamArray['beginValue']))
                $this->direction = $this->fullParamArray['beginValue'];
        }
        return $this;//for chaining
    }


}
