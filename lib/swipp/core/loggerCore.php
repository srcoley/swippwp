<?php
namespace Swipp\api;
/**
 * Created by JetBrains PhpStorm.
 * User: michaelmeyers
 * Date: 1/16/12
 * Time: 1:53 PM
 * To change this template use File | Settings | File Templates.
 */
class loggerCore
{
    static $error_string_heading = "swippPhpApi";


    const DEBUG   = 1;
    const NOTICE  = 2;
    const WARN    = 4;
    const ERROR   = 8;

    const ALL	  = 15;

    static $error_level = 8; //Default to only Errors
    /*
     * To set different error levels,
     *   loggerCore::$error_level = loggerCore::DEBUG | loggerCore::ERROR;
     *   or
     *   loggerCore::$error_level = loggerCore::DEBUG | loggerCore::NOTICE | loggerCore::ERROR;
     *   ect..
     */
    const LOG_OFF	    = 0; //No logging
    const LOG_PHP	    = 1; //Default php error log
    const LOG_FILE	    = 2; //Log to file
    const LOG_FUNC	    = 3; //Call an outside function providing the error string. This can be used to integrate with an outside logging system

    static $error_log_method = 1; // ex: loggerCore::$error_log_method = loggerCore::LOG_OFF;
    static $error_log_file = "default_error.log"; //Set this if using FILE method
    protected static $error_log_file_handler = null;
    static $error_log_function = "error_log"; //Set this if using FUNC method. Can call static functions by doing "classname::my_error_handler";
    static $session_id = null;

    static function log($message,$file,$line,$level=1)
    {
        if (self::$session_id === null)
            self::init_session_id();

        if (self::$error_log_method == self::LOG_OFF)
            return;
        else if (self::$error_log_method == self::LOG_PHP)
        {
            if ((self::$error_level & $level) > 0)
                error_log(self::$error_string_heading .":[$file][$line][$level]:$message");
        }
        else if (self::$error_log_method == self::LOG_FILE)
        {
            if ((self::$error_level & $level) > 0)
            {
                if (self::$error_log_file_handler == null)
                {
                    self::$error_log_file_handler = @fopen(self::$error_log_file, "a");
                    if (self::$error_log_file_handler === false)
                        self::error_method_fallback();
                }
                @fwrite(self::$error_log_file_handler,self::$error_string_heading .":[$file][$line][$level]:$message"."\n");
            }
        }
        else if (self::$error_log_method == self::LOG_FUNC)
        {
            if ((self::$error_level & $level) > 0)
            {
                if (is_callable(self::$error_log_function))
                    call_user_func (self::$error_log_function,self::$error_string_heading .":[$file][$line][$level]:$message");
                else
                    self::error_method_fallback();
            }
        }
    }

    static function init_session_id($session=null)
    {
        if ($session !== null)
            self::$session_id = $session;
        else
            self::$session_id = substr(time(),-4) . '-' . rand(1000,9999);
    }

    protected static function error_method_fallback()
    {
        $old_method = self::$error_log_method;
        self::$error_log_method = 1; //Fall back to PHP
        if ($old_method == 2)
            $old_method_info = self::$error_log_file;
        else if ($old_method == 3)
            $old_method_info = self::$error_log_function;
        $message = "Logger method failed, falling back to php error_log! Method:".$old_method." Info:".$old_method_info;
        self::log($message,__FILE__,__LINE__,self::ERROR);
    }

}


