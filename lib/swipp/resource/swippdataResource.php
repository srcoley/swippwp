<?php
namespace Swipp\api;
use DateTime;
/**
 * User: mmeyers
 * Date: 3/18/12
 * Time: 7:43 PM
 */

class swippdataResource extends baseResource
{

    protected $swippdata_base = "swipp/swippdata/";

    public function getSummary($termid, $filter=null, $friendType=null, $interval = 'weekly')
    {

        $url = $this->swippdata_base . "$termid/dashboardsummary";
        $param = array("interval" =>$interval, "filter"=>$filter,"friendType"=>$friendType);
        $response = $this->send($url,$param);

        if(is_null($response))
        {
           return false;
        }

        // manicure overTimeData to a zingCharts Friendly response.
        if(!isset($response['dashboardSummary']))
            $response['dashboardSummary'] = array();
        if(!isset($response['dashboardSummary']['averageSummary']))
            $response['dashboardSummary']['averageSummary'] = array();
        if(!isset($response['dashboardSummary']['genderSummary']))
            $response['dashboardSummary']['genderSummary'] = array();
        if(!isset($response['dashboardSummary']['locationSummary']))
            $response['dashboardSummary']['locationSummary'] = array();
        if(!isset($response['dashboardSummary']['overTimeData']))
            $response['dashboardSummary']['overTimeData'] = array();

        array_walk_recursive($response['dashboardSummary']['genderSummary'],'self::roundIfFloat');
        array_walk_recursive($response['dashboardSummary']['averageSummary'],'self::roundIfFloat');
        if (isset($response['dashboardSummary']['averageSummary']['trending']))
        {
            if ($response['dashboardSummary']['averageSummary']['trending'] == true)
                $response['dashboardSummary']['averageSummary']['trending'] = json_decode('"'.'\u2B06'.'"');
            else
                $response['dashboardSummary']['averageSummary']['trending'] = json_decode('"'.'\u2B07'.'"');
        }

        if(isset($response['dashboardSummary']['averageSummary']['dailyScore']))
            $response['dashboardSummary']['averageSummary']['dailyFace'] =  'swippface small rating-' . round($response['dashboardSummary']['averageSummary']['dailyScore']);
        else
            $response['dashboardSummary']['averageSummary']['dailyFace'] = '';
        $response['dashboardSummary']['averageSummary']['averageFace'] =  'swippface small rating-' . round($response['dashboardSummary']['averageSummary']['swippScore']);

        if (!isset($response['dashboardSummary']['averageSummary']['dailyScore']))
        {
            $response['dashboardSummary']['averageSummary']['dailyScore'] = '—';
            $response['dashboardSummary']['averageSummary']['trending'] = '—';
        }

        $this->sanitizeGenderArray($response['dashboardSummary']['genderSummary']['totalSwipps'], '0');
        $this->sanitizeGenderArray($response['dashboardSummary']['genderSummary']['swippScore']);





        $tmpFscore = $response['dashboardSummary']['genderSummary']['swippScore']['f'];
        $tmpMscore = $response['dashboardSummary']['genderSummary']['swippScore']['m'];
        $response['dashboardSummary']['genderSummary']['swippFace']['f'] = 'swippface small rating-' . round($tmpFscore);
        $response['dashboardSummary']['genderSummary']['swippFace']['m'] = 'swippface small rating-' . round($tmpMscore);

        if($tmpFscore > 0){
            $response['dashboardSummary']['genderSummary']['swippScore']['f'] = "+" . $tmpFscore;
        }
        if($tmpMscore > 0)
        {
            $response['dashboardSummary']['genderSummary']['swippScore']['m'] = "+" . $tmpMscore;
        }
        if($response['dashboardSummary']['averageSummary']['swippScore'] > 0)
        {
            $response['dashboardSummary']['averageSummary']['swippScore'] = "+" . $response['dashboardSummary']['averageSummary']['swippScore'];
        }
        if($response['dashboardSummary']['averageSummary']['dailyScore'] > 0)
        {
            $response['dashboardSummary']['averageSummary']['dailyScore'] = "+" . $response['dashboardSummary']['averageSummary']['dailyScore'];

        }
        return $response;
    }

    public function getGenderGraph($termid,$filter=null,$friendType=null)
    {
        $param = array('filter'=>$filter,'friendType'=>$friendType);
        $url = $this->swippdata_base . $termid . "/" . "gendergraph/getsummary";
        $response = $this->send($url,$param);
        return $response;
    }

    public function getGenderGraphDetail($termid,$filter=null,$friendType=null)
    {
        $param = array('filter'=>$filter,'friendType'=>$friendType);
        $url = $this->swippdata_base . $termid . "/" . "gendergraph/getdetail";
        $response = $this->send($url,$param);

        $emptyArray = array (
            "swippScore" => array(),
            "totalSwipps" => array()
        );

        if(!isset($response['genderDetail']))
            $response['genderDetail'] = array();
        if(!isset($response['genderDetail']['total']))
            $response['genderDetail']['total'] = $emptyArray;
        if(!isset($response['genderDetail']['age_13_17']))
            $response['genderDetail']['age_13_17'] = $emptyArray;
        if(!isset($response['genderDetail']['age_18_24']))
            $response['genderDetail']['age_18_24'] = $emptyArray;
        if(!isset($response['genderDetail']['age_25_34']))
            $response['genderDetail']['age_25_34'] = $emptyArray;
        if(!isset($response['genderDetail']['age_35_44']))
            $response['genderDetail']['age_35_44'] = $emptyArray;
        if(!isset($response['genderDetail']['age_45_54']))
            $response['genderDetail']['age_45_54'] = $emptyArray;
        if(!isset($response['genderDetail']['age_55_64']))
            $response['genderDetail']['age_55_64'] = $emptyArray;
        if(!isset($response['genderDetail']['age_65_plus']))
            $response['genderDetail']['age_65_plus'] = $emptyArray;
        if(!isset($response['genderDetail']['other']))
            $response['genderDetail']['other'] = $emptyArray;

        /*
         *
         *
         * {"genderDetail": {
    "total": {
        "swippScore": {},
        "totalSwipps": {
            "f": 0,
            "allSwipps": 0,
            "fSwipps": 0,
            "mSwipps": 0,
            "m": 2,
            "all": 2
        }
    },
            "age_13_17": {
        "swippScore": {},
        "totalSwipps": {
            "f": 0,
            "allSwipps": 0,
            "fSwipps": 0,
            "mSwipps": 0,
            "m": 0,
            "all": 0
        }
    },
        "age_18_24": {
        "swippScore": {},
        "totalSwipps": {
            "f": 0,
            "allSwipps": 0,
            "fSwipps": 0,
            "mSwipps": 0,
            "m": 0,
            "all": 0
        }
    },
    "age_25_34": {
        "swippScore": {"m": 4},
        "totalSwipps": {
            "f": 0,
            "allSwipps": 1,
            "fSwipps": 0,
            "mSwipps": 1,
            "m": 1,
            "all": 1
        }
    },
    "age_35_44": {
        "swippScore": {"m": 4},
        "totalSwipps": {
            "f": 0,
            "allSwipps": 1,
            "fSwipps": 0,
            "mSwipps": 1,
            "m": 1,
            "all": 1
        }
    },
    "age_45_54": {
        "swippScore": {},
        "totalSwipps": {
            "f": 0,
            "allSwipps": 0,
            "fSwipps": 0,
            "mSwipps": 0,
            "m": 0,
            "all": 0
        }
    },
    "age_55_64": {
        "swippScore": {},
        "totalSwipps": {
            "f": 0,
            "allSwipps": 0,
            "fSwipps": 0,
            "mSwipps": 0,
            "m": 0,
            "all": 0
        }
    },
    "age_65_plus": {
        "swippScore": {},
        "totalSwipps": {
            "f": 0,
            "allSwipps": 0,
            "fSwipps": 0,
            "mSwipps": 0,
            "m": 0,
            "all": 0
        }
    }
}}
         *
         */

        foreach($response['genderDetail'] as $k => $v)
        {
            $this->sanitizeGenderArray($response['genderDetail'][$k]['totalSwipps'], '0');
            $this->sanitizeGenderArray($response['genderDetail'][$k]['swippScore']);
        }
        array_walk_recursive($response['genderDetail'],'self::roundIfFloat');
        return $response;
    }

    public function getOverTime($termid,$interval=null,$filter=null,$friendType=null)
    {

        $param = array('interval'=>$interval,'filter'=>$filter,'friendType'=>$friendType);
        $url = $this->swippdata_base . $termid . "/" . "overtime";
        $response = $this->send($url,$param);
        return $response;
    }

    public function getRealTime($termid,$filter=null,$friendType=null)
    {
        $param = array('filter'=>$filter,'friendType'=>$friendType);
        $url = $this->swippdata_base . $termid . "/" . "realtime/getsummary";
        $response = $this->send($url,$param);
        return $response;
    }

    public function getRealTimeDetail($termid,$filter=null,$friendType=null)
    {
        $param = array('filter'=>$filter,'friendType'=>$friendType);
        $url = $this->swippdata_base . $termid . "/" . "realtime/getdetail";
        $response = $this->send($url,$param);
        if (!is_array($response))
            return false;

        foreach($response['averageDetail']['distribution'] as $k=>$v)
        {
            $response['averageDetail']['percentage'][$k] = ($v/$response['averageDetail']['totalSwipps']*100);
        }

        array_walk_recursive($response['averageDetail'],'self::roundIfFloat');
        return $response;
    }

    public function getLocation($termid,$filter=null,$friendType=null)
    {
        $param = array('filter'=>$filter,'friendType'=>$friendType);
        $url = $this->swippdata_base . $termid . "/" . "locations/getsummary";
        $response = $this->send($url,$param);
        return $response;
    }

    public function getLocationDetail($termid,$filter=null,$friendType=null)
    {
        $param = array('filter'=>$filter,'friendType'=>$friendType);
        $url = $this->swippdata_base . $termid . "/" . "locations/getdetail";
        $response = $this->send($url,$param);
        return $response;
    }

    public function getLocationBoundary($termid, $context, $neLat, $neLong, $swLat, $swLong, $filter=null,$friendType=null)
    {
        $param = array(
            'filter'=>$filter,
            'friendType'=>$friendType,
            'context' =>$context,
            'neLat' => $neLat,
            'neLong' => $neLong,
            'swLat' => $swLat,
            'swLong' => $swLong
        );
        $url = $this->swippdata_base . $termid . "/" . "locations/boundary";
        $response = $this->send($url,$param);
        return $response;
    }

    public function getLocationContinents($termid)
    {
        $url = $this->swippdata_base . $termid . "/" . "locations/continentsummary";
        $response = $this->send($url);
        return $response;
    }

    private function calcScore($total,$avg)
    {
        if ($total == 0)
            return 0;
        return round(($avg / $total) - 5,1);
    }

    private function calcMetaScore($total_a,$avg_a,$total_b,$avg_b)
    {
        if (($total_a + $total_b) == 0)
            return 0;
        $avg_a = ($avg_a + 5) * $total_a;
        $avg_b = ($avg_b + 5) * $total_b;
        $total = $total_a + $total_b;
        return round((($avg_a + $avg_b) / $total) - 5,1);
    }

    private function cmpTimeData($a,$b)
    {
       if($a['from'] == $b['from'])
           return 0;
       return ( $a['from'] < $b['from']) ? -1 : 1;
    }

    private function sanitizeGenderArray(&$a, $nullChar = '-')
    {
        if(!isset($a['f']) || null === $a['f'])
            $a['f'] = $nullChar;
        if(!isset($a['o']) || null === $a['o'])
            $a['o'] = $nullChar;
        if(!isset($a['m']) || null === $a['m'])
            $a['m'] = $nullChar;
        if(!isset($a['all']) || null === $a['all'])
            $a['all'] = $nullChar;
    }

    private function toStringPad($val,$str,$prefix)
    {
        return ($val < 10) ? ($str . $prefix . $val) : $str . $val;
    }

    //helper methods

    static function roundIfFloat(&$val, $idx, $precision=1)
    {
        if(is_float($val))
            $val = round($val,$precision);
    }


}


