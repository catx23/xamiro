<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Config');
xapp_import('xapp.Config.Exception');

/**
 * Config Php class
 *
 * @package Config
 * @class Xapp_Config_Json
 * @error 127
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Config_Php extends Xapp_Config
{
    /**
     * load config from php array of php file containing a single array like:
     *
     * return array
     * (
     *    "foo" => 1,
     *    "test" => "test",
     *    ...
     * );
     *
     * @error 12701
     * @param array|string $php expects php array or php file pointer
     * @return array
     * @throws Xapp_Config_Exception
     */
    public static function load($php)
    {
        if(!is_array($php) && (bool)preg_match('/\.phtml|\.php([0-9]{1,})?|\.inc$/i', $php))
        {
            if(is_file($php))
            {
                $php = require $php;
                if($php === 1)
                {
                    $php = array_slice(get_defined_vars(), 1);
                }
            }else{
                throw new Xapp_Config_Exception(xapp_sprintf(_("unable to load php config file: %s"), $php), 1270101);
            }
        }
        return (array)$php;
    }
}