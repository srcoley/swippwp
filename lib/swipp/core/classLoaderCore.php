<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 12:33 PM
 * To change this template use File | Settings | File Templates.
 */
class classLoaderCore
{
    public static $ROOT_DIR;

    public static function init($root_dir)
    {
        self::$ROOT_DIR = $root_dir;
        spl_autoload_register(array(__CLASS__,'auto_load'));
    }

    public function __destruct()
    {
        spl_autoload_unregister(array(__CLASS__,'auto_load'));
    }

    static function auto_load($class_name)
    {
        $class_match = array();
        preg_match('([^\\\\]+$)',$class_name,$class_match);
        $class_name = (empty($class_match)) ? $class_name : $class_match[0];
        $matches = array();
        preg_match("(([A-Z])([a-z]+)$)",$class_name,$matches); //Match the last word of a camel case string
        if (empty($matches))
            $folder = $class_name;
        else if ($matches[0] == "List")
        {
            $tmp_class_name = str_replace("List","",$class_name);
            preg_match("(([A-Z])([a-z]+)$)",$tmp_class_name,$matches);
            $folder = strtolower($matches[0]);
        }
        else
            $folder = strtolower($matches[0]);
        if (is_file(self::$ROOT_DIR . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $class_name . ".php"))
            include (self::$ROOT_DIR . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $class_name . ".php");
        else if (is_file(self::$ROOT_DIR . DIRECTORY_SEPARATOR . "objects" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $class_name . ".php"))
            include (self::$ROOT_DIR . DIRECTORY_SEPARATOR . "objects" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $class_name . ".php");
    }

}
