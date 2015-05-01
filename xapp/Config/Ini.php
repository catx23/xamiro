<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Config');
xapp_import('xapp.Config.Exception');

/**
 * Config Ini class
 *
 * @package Config
 * @class Xapp_Config_Ini
 * @error 125
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Config_Ini extends Xapp_Config
{
    /**
     * load and parse php ini format string or file and return
     * parsed array
     *
     * @error 12501
     * @param string $ini expects a file pointer or php ini string
     * @return array
     * @throws Xapp_Config_Exception
     */
    public static function load($ini)
    {
        if((bool)preg_match('/\.ini$/i', $ini) && is_file($ini))
        {
            $ini = parse_ini_file($ini, true);
        }else{
            $ini = parse_ini_string($ini, true);
        }
        if($ini !== false)
        {
            return (array)$ini;
        }else{
            throw new Xapp_Config_Exception(_("unable to load ini config"), 1250101);
        }
    }
}