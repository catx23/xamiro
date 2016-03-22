<?php

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Config interface
 *
 * @package Conf
 * @author Frank Mueller <set@cooki.me>
 */
interface Xapp_Config_Interface
{
    /**
     * loads config values from string, file pointer or elsewhere and
     * returns an array/object of config values
     *
     * @param mixed $config expects config string or config file pointer
     * @return mixed|array
     */
    static function load($config);
}