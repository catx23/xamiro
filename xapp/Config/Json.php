<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Config');
xapp_import('xapp.Config.Exception');

/**
 * Config Json class
 *
 * @package Config
 * @class Xapp_Config_Json
 * @error 126
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Config_Json extends Xapp_Config
{
    /**
     * load and parse json format string or file pointer and return cleaned
     * array
     *
     * @error 12601
     * @param string $json expects a encoded json string or json file pointer
     * @return array
     * @throws Xapp_Config_Exception
     */
    public static function load($json)
    {
        $_json = json_decode(self::clean($json), true);
        if($_json !== false && $_json !== null)
        {
            return $_json;
        }else{
            if(is_file($json))
            {
                $json = json_decode(self::clean(file_get_contents($json)), true);
                if($json !== false && $json !== null)
                {
                    throw new Xapp_Config_Exception(xapp_sprintf(_("unable to load and decode json from: %s"), $json), 1260102);
                }else{
                    return $json;
                }
            }else{
                throw new Xapp_Config_Exception(xapp_sprintf(_("passed json file: %s is neither json string not json file pointer"), $json), 1260101);
            }
        }
    }


    /**
     * cleans json string of invalid characters before decoding
     *
     * @error 12602
     * @param string $json expects json string
     * @return string
     */
    protected static function clean($json)
    {
        $json = str_replace(array("\n", "\r"), '', $json);
        if(!(bool)preg_match('!!u', $json))
        {
            $json = utf8_encode($json);
        }
        $json = preg_replace('/,\s*([\]}])/miu', '$1', $json);
        return $json;
    }
}