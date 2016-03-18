<?php
/**
 * Class Xapp_VFS_Options
 */
class Xapp_VFS_Options{

}

/**
 * Default Gateway options
 */
$XAPP_VFS_OPTIONS = array(

);

/*******************************************************************************
 *
 *	Example : Load security config from Ini - File:
**/

/*
xapp_import('xapp.Config.Ini');
$iniFilePath = realpath($ROOT_DIRECTORY_ABSOLUTE .'/profiles/default/security.ini');
$_XAPP_GATEWAY_OPTIONS_INI_CONF = new Xapp_Config_Ini($iniFilePath);//will throw an error if it doesnt exists
$_XAPP_GATEWAY_OPTIONS_INI_CONF->load($iniFilePath);

//example  read a parameter in the ini file:
//$omit = $_XAPP_GATEWAY_OPTIONS_INI_CONF->get(Xapp_Rpc_Gateway_Options::OMIT_ERROR);

//example  in case the parameter is in a ini Section:
//$omit = $_XAPP_GATEWAY_OPTIONS_INI_CONF->get('Default'. Xapp_Rpc_Gateway_Options::OMIT_ERROR);

// set $XAPP_GATEWAY_OPTIONS to the Config-Instance:
$XAPP_GATEWAY_OPTIONS = $_XAPP_GATEWAY_OPTIONS_INI_CONF;

*/
