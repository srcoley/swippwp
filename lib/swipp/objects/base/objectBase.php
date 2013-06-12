<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 2:33 PM
 * To change this template use File | Settings | File Templates.
 */
class objectBase
{
    private $_orphans = array();
    public $_lastModified;
    public $_isEmpty = false;
    public $_initialValues = null;
    public $_readOnly = array();

    protected function assignSimpleValues(array $json_array)
    {
        if (empty($json_array))
        {
            $this->_isEmpty = true;
            return;
        }
        $this->_initialValues = $json_array;
        foreach ($json_array as $key => $value)
        {
            if (!is_array($value))
                $this->$key = $value;
        }

    }

    public function __set($name,$value)
    {
        loggerCore::log("Value $name does not exist in ". __CLASS__ ." but was found in response", __FILE__, __LINE__, loggerCore::NOTICE);
        $this->_orphans[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->_orphans[$name]))
        {
            return $this->_orphans[$name];
        }
        loggerCore::log("Value $name not found in ". __CLASS__ ." ", __FILE__, __LINE__, loggerCore::NOTICE);
    }

    public function __construct($response_object)
    {
        if (!is_array($response_object))
        {
            $this->_isEmpty = true;
            return null;
        }
        $this->assignSimpleValues($response_object);
        return $this;
    }

    public function getChangedValues()
    {
        $tmp = array();
        foreach (get_object_vars($this) as $k => $v)
        {
            if (!in_array($k,$this->_readOnly))
            {
                if (strpos($k,'_') !== 0)
                {
                    if (($v !== null) && ((!isset($this->_initialValues[$k])) || ($this->_initialValues[$k] !== $v)))
                    {
                        $tmp[$k] = $v;
                        $this->_initial_values[$k] = $v; //Save it
                    }
                }
            }
        }
        return $tmp;
    }
}
