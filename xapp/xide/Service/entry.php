<?php

$XAPP_BASE_DIRECTORY =  realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;

define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY); //important !

/***
 * A log directory, must be writable !!
 */
$XAPP_LOG_DIRECTORY =  realpath(XAPP_BASEDIR . '..' . DIRECTORY_SEPARATOR . 'log');
/***
 * A resolved variable value for the mounted virtual workspace
 */
$XAPP_WORKSPACE_DIRECTORY = realpath(XAPP_BASEDIR . '..' .DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .'/user/A/ws/workspace/';
/***
 * The path to the virtual file system configuration, holding all mounted resources
 */
$XAPP_VFS_CONFIG_PATH = realpath(XAPP_BASEDIR . '..' .DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .'maqetta' . DIRECTORY_SEPARATOR .'vfs.json';

/***
 * Dojo's JSON-RPC classes need an url to the entry here :
 */
$XAPP_SMD_TARGET = '../xapp/xide/Service/entry.php?view=smdCall';

/***
 * Framework minimal includes, ignore!
 */


require_once(XAPP_BASEDIR . '/Bootstrap.php');

XApp_Bootstrap::loadMin();
XApp_Bootstrap::loadRPC();
xapp_setup_language_standalone();

xapp_import('xapp.Service');
xapp_import('xapp.File.Utils');
xapp_import('xapp.Directory.Utils');
xapp_import('xapp.Directory.Service');
xapp_import('xapp.xide.Directory.Service');
xapp_import('xapp.Path.Utils');
xapp_import('xapp.VFS.Interface.Access');
xapp_import('xapp.VFS.Base');
xapp_import('xapp.VFS.Local');
xapp_import('xapp.Resource.Renderer');
xapp_import('xapp.Xapp.Hook');
xapp_import('xapp.Option.*');
xapp_import("xapp.xide.Models.User");
xapp_import('xapp.xide.Controller.UserManager');
xapp_import('xapp.xide.Controller.UserService');
xapp_import('xapp.xide.Workbench.Service');
xapp_import('xapp.Store.Json.Json');


/***
 * Build bootstrap config for the RPC service
 */
$user = new XApp_User();
$user->setName('A');

$opt = array(

    XApp_Bootstrap::BASEDIR                 =>  XAPP_BASEDIR,
    XApp_Bootstrap::FLAGS                   =>  array(

        XAPP_BOOTSTRAP_SETUP_XAPP,              //takes care about output encoding and compressing
        XAPP_BOOTSTRAP_SETUP_RPC,               //setup a RPC server
        //XAPP_BOOTSTRAP_SETUP_LOGGER,            //setup a logger
        XAPP_BOOTSTRAP_SETUP_GATEWAY,           //setup a gateway,
        XAPP_BOOTSTRAP_SETUP_SERVICES           //setup services
    ),
    XApp_Bootstrap::RPC_TARGET              =>  $XAPP_SMD_TARGET,
    XApp_Bootstrap::SIGNED_SERVICE_TYPES    =>  array(

    ),
    XApp_Bootstrap::GATEWAY_CONF            =>  array(
        Xapp_Rpc_Gateway::OMIT_ERROR        => XApp_Service_Entry_Utils::isDebug()
    ),
    XApp_Bootstrap::LOGGING_FLAGS           =>  array(
    ),
    XApp_Bootstrap::LOGGING_CONF            =>  array(
        Xapp_Log::PATH                      => $XAPP_LOG_DIRECTORY,
        Xapp_Log::EXTENSION                 => 'log',
        Xapp_Log::NAME                      => 'xide'
    ),
    XApp_Bootstrap::XAPP_CONF               => array(
        XAPP_CONF_DEBUG_MODE                => null,
        XAPP_CONF_AUTOLOAD                  => false,
        XAPP_CONF_DEV_MODE                  => XApp_Service_Entry_Utils::isDebug(),
        XAPP_CONF_HANDLE_BUFFER             => true,
        XAPP_CONF_HANDLE_SHUTDOWN           => false,
        XAPP_CONF_HTTP_GZIP                 => true,
        XAPP_CONF_CONSOLE                   => false,
        XAPP_CONF_HANDLE_ERROR              => true,
        XAPP_CONF_HANDLE_EXCEPTION          => true
    ),
    XApp_Bootstrap::SERIVCE_CONF             => array(

        XApp_Service::instance('XIDE_Directory_Service',array(

            XApp_Directory_Service::REPOSITORY_ROOT     => $XAPP_WORKSPACE_DIRECTORY,
            XApp_Directory_Service::FILE_SYSTEM         => 'XApp_VFS_Local',
            XApp_Directory_Service::VFS_CONFIG_PATH     => $XAPP_VFS_CONFIG_PATH,
            XApp_Directory_Service::FILE_SYSTEM_CONF    => array(

                XApp_VFS_Base::ABSOLUTE_VARIABLES=>array('WS_ABS_PATH' => $XAPP_WORKSPACE_DIRECTORY),
                XApp_VFS_Base::RELATIVE_VARIABLES=>array()
            )
        )),
        XApp_Service::factory('xapp.xide.Workbench.Service',array(
            XApp_XIDE_Workbench_Service::WORKBENCH_USER         =>$user,
            XApp_XIDE_Workbench_Service::WORKBENCH_DIRECTORY    =>$XAPP_WORKSPACE_DIRECTORY,
            XApp_XIDE_Workbench_Service::SITE_CONFIG_DIRECTORY  =>realpath(XAPP_BASEDIR .'/xide/Workbench/siteconfig/')
        )),
        XApp_Service::instance('XApp_XIDE_Controller_UserService',array(

            XApp_Service::MANAGED_CLASS_OPTIONS => array(

                XApp_UserManager::STORE_CONF => array(
                    XApp_Store_JSON::CONF_FILE => $XAPP_BASE_DIRECTORY. "xide" . DIRECTORY_SEPARATOR . "Data" . DIRECTORY_SEPARATOR . "Users.json"
                )
            )
        ))
    )
);

$xappBootrapper = new XApp_Bootstrap($opt);

$xappBootrapper->init();
//$userService = XApp_XIDE_Controller_UserService::instance();
//$xappBootrapper->run();



