<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

/**
 * Debug class
 *
 * @package Xapp
 * @class Xapp_Debug
 * @error 102
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Debug
{
    /**
     * dumps debug messages to screen or returns debug messages
     *
     * @static
     * @error 10201
     * @param mixed $msg expects array of debug messages or single debug message as string
     * @param null|string $sapi expects optional sapi name if not set will be set automatic
     * @param bool $echo expects a boolean value whether to return debug string or output
     * @return null|string
     */
    public static function dump($msg, $sapi = null, $echo = true)
    {
        $log = "";

        if($sapi === null)
        {
            $sapi = strtolower(php_sapi_name());
        }else{
            $sapi = strtolower(trim($sapi));
        }
        foreach((array)$msg as $k => $v)
        {
            $log .= trim(implode(", " , (array)$v)) . PHP_EOL;
        }
        if($sapi !== 'cli')
        {
            $log = "<pre>$log</pre>";
        }
        if((bool)$echo)
        {
            echo $log;
        }else{
            return $log;
        }
    }
}