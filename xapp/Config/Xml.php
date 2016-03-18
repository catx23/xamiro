<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');

xapp_import('xapp.Config');
xapp_import('xapp.Config.Exception');

/**
 * Config Xml class
 *
 * @package Config
 * @class Xapp_Config_Xml
 * @error 128
 * @author Frank Mueller <set@cooki.me>
 */
class Xapp_Config_Xml extends Xapp_Config
{
    /**
     * load xml config structure from xml string or xml file pointer converting the xml
     * to json and back to array to get xml to array result
     *
     * @error 12801
     * @param string $xml expects xml string or xml file pointer
     * @return array
     * @throws Xapp_Config_Exception
     */
    public static function load($xml)
    {
        if((bool)preg_match('/\.xml$/i', $xml))
        {
            if(is_file($xml) && ($xml = simplexml_load_file($xml, 'SimpleXMLElement', LIBXML_NOBLANKS | LIBXML_NOCDATA)) === false)
            {
                throw new Xapp_Config_Exception(xapp_sprintf(_("unable to load xml config from file: %s, error: %s"), $xml, libxml_get_last_error()), 1280101);
            }
        }else{
            if(($xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOBLANKS | LIBXML_NOCDATA)) === false)
            {
                throw new Xapp_Config_Exception(xapp_sprintf(_("unable to load xml config from string, error: %s"), libxml_get_last_error()), 1280102);
            }
        }
        if(($json = json_encode($xml)) !== false)
        {
            return json_decode($json, true);
        }else{
            throw new Xapp_Config_Exception(xapp_sprintf(_("unable to parse xml config: %s"), json_last_error()), 1280103);
        }
    }
}