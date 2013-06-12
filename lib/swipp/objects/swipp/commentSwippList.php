<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/17/12
 * Time: 12:13 PM
 * To change this template use File | Settings | File Templates.
 */
class commentSwippList extends listBase
{
    public function __construct($json_array)
    {
        $this->iter_pos = 0;
        foreach ($json_array as $key => $value)
        {
            $this->_internal_array[] = new commentSwipp($value);
        }
    }


}
