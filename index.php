<?php
/**
 * @version 2.0.alpha
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 *
 * Server   : - RPC-JSON 2.0 + Dojo SMD (Service Method Definition)
 *            - All RPC calls go through here as well
 *            - see @link :http://localhost/xamiro/index.php?view=rpc for the full service map. plugins are exposed through this entry point too
 *
 * Client   : - Is a large Dojo & XJS application.
 *            - Client resources are described in client/xfile/xbox/resources-release.json
 *                        - Client theme is bootstrap-3 compatible and based.
 *
 * Security : - All RPC calls are signed upon its payload + md5(userName)=key + md5(sessionToken)=token
 *            - See component options to narrow it further for live stages.
 *            - See Xapp_Rpc_Gateway options, signing callbacks are possible as well
 *                        - Salt key is in this file or in a profile.
 *
 * Footprint: - 1MB JS gzipped, a few hundred kb for the rest. The server can vary from 15MB to a few hundred megabyte
 *
 * @Remarks    : - This file does UX rendering and handles/routes RPC calls
 *
 *
 *
 *
 *
 * Example urls (no longer supported!)
 * <a target="_blank" href="../index.php?layout=single">Single panel</a>
 * <a target="_blank" href="../index.php?layout=dual">Dual panel</a>
 * <a target="_blank" href="../index.php?layout=preview">Preview layout (split view with media preview)</a>
 * <a target="_blank" href="../index.php?layout=preview&theme=dot-luv">Preview layout in dark theme (split view with media preview)</a>
 * <a target="_blank" href="../index.php?layout=preview&open=Pictures">Auto open picture folder in preview mode (split view with media preview)</a>
 * <a target="_blank" href="../index.php?layout=single&minimal=true">Minimal (for mobile devices)</a>
 */

/**
 *
 * What happens here:
 *
 * 1. Setup constants and framework directories
 * 2. Setup a default configuration
 * 3. Load conf/default.php to override default configuration (first pass)
 * 4. Load conf/custom.php if exists! to override default configuration (second pass)
 * 4.1. Alternatively, use &profile= to switch configuration!
 * 5. Render RPC or client
 *
 */

/**
 * Profile - Directory structure
 *
 * profiles/default
 * profiles/default
 *
 */


/////////////////////////////////////////////////////////////////
//
// 1. Core directories & defines, don't touch !
//
/////////////////////////////////////////////////////////////////

$ROOT_DIRECTORY_ABSOLUTE = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR);
$XAPP_SITE_DIRECTORY = $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR;
$XAPP_BASE_DIRECTORY = $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR . 'xapp' . DIRECTORY_SEPARATOR;

define('XAPP_CLIENT_DIRECTORY', realpath($ROOT_DIRECTORY_ABSOLUTE . '/client/src') . DIRECTORY_SEPARATOR);
define('XAPP_BASEDIR', $XAPP_BASE_DIRECTORY);    //the most important constant
define('XAPP_LOG_DIRECTORY', realpath($ROOT_DIRECTORY_ABSOLUTE . '/log') . DIRECTORY_SEPARATOR);

require_once(XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php');
require_once(XAPP_BASEDIR . '/Service/Utils.php');
require_once(XAPP_BASEDIR . '/Utils/Debugging.php');
if (!defined('XAPP')) {
    include_once(XAPP_BASEDIR . '/Core/core.php');
    require_once(XAPP_BASEDIR . '/Xapp/Xapp.php');
    require_once(XAPP_BASEDIR . '/Xapp/Autoloader.php');
    require_once(XAPP_BASEDIR . '/Xapp/Cli.php');
    require_once(XAPP_BASEDIR . '/Xapp/Console.php');
    require_once(XAPP_BASEDIR . '/Xapp/Debug.php');
    require_once(XAPP_BASEDIR . '/Xapp/Error.php');
    require_once(XAPP_BASEDIR . '/Xapp/Event.php');
    require_once(XAPP_BASEDIR . '/Xapp/Option.php');
    require_once(XAPP_BASEDIR . '/Xapp/Reflection.php');
}

/////////////////////////////////////////////////////////////////
//
// 1.1 Default directories and variables
//
/////////////////////////////////////////////////////////////////

/**
 * CONF_DIRECTORY points to the configuration directory which contains our profile
 */
$CONF_DIRECTORY = $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR;

/**
 * CONF_DIRECTORY points to the configuration directory which contains our profile
 */
$PROFILE_DIRECTORY = $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR;

/**
 * CONF_DIRECTORY points to the configuration directory which contains our profile
 */
$DATA_DIRECTORY = $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;


/**
 * XF_PATH, the folder to browse; must be absolute and must have a trailing slash. This path can be outside of the web-server's httpdoc directory:
 */
$XF_PATH = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR);


/**
 * XF_DEFAULT_PROFILE is the filename to the default profile. It will be fully resolved by using CONF_DIRECTORY as prefix.
 * This profile will override the default profile. You can switch this profile also by appending the url with ('&profile=admin')
 *
 * There is also a guest profile : &profile=min
 */
$XF_DEFAULT_PROFILE = 'default';


/**
 * XF_DEFAULT_CUSTOM_PROFILE is the filename to the custom profile. It will be fully resolved by using CONF_DIRECTORY as prefix.
 * If this file exists, this profile is the last override. This pass enables you to run your own configuration with a developer distribution.
 * the problem is that git pull won't work anymore as soon you did change this file or the conf/default.php.
 */
$XF_CUSTOM_PROFILE = XApp_Service_Utils::_getKey('profile', 'custom');
/*
 *	Final resolution of profiles is:
 *    1. This very file
 *		2. conf/default.php
 *		3. if exists as url parmeter,eg: &profile=min
 * 		4. 	or conf/custom.php (if exists)
 *
*/

///////////////////////////////////////////////////////////////////
//
//  Some constants for building a valid XFile configuration
//
///////////////////////////////////////////////////////////////////

//no longer supported:
const XF_PANEL_MODE_TREE = 1;     //Tree
const XF_PANEL_MODE_LIST = 2;     //List
const XF_PANEL_MODE_THUMB = 3;     //Thumbnails
const XF_PANEL_MODE_PREVIEW = 4;     //Preview mode
const XF_PANEL_MODE_COVER = 5;     //Image Cover Flow
const XF_PANEL_MODE_SPLIT_VERTICAL = 6;     //Split Vertical
const XF_PANEL_MODE_SPLIT_HORIZONTAL = 7;     //Split Horizontal

const XF_LAYOUT_PRESET_DUAL = 1;     //Dual View ala Midnight commander or similar
const XF_LAYOUT_PRESET_SINGLE = 2;     //Single View only
const XF_LAYOUT_PRESET_BROWSER = 3;     //Classic Explorer like layout : left: tree, center : thumbs
const XF_LAYOUT_PRESET_PREVIEW = 4;     //Split view : top : preview window, bottom : thumbs
const XF_LAYOUT_PRESET_GALLERY = 5;     //Split view : top : image cover flow window, bottom : thumbs
const XF_LAYOUT_PRESET_EDITOR = 6;     //Split view : left : browser, center: editor


//update: type & media info added!
const XF_DIR_OPTION_SHOW_ISREADONLY = 1601;  //permission
const XF_DIR_OPTION_SHOW_ISDIR = 1602;  //required!
const XF_DIR_OPTION_SHOW_OWNER = 1604;  //posix
const XF_DIR_OPTION_SHOW_MIME = 1608;  //tries 3 things: apache, static tables and other libs
const XF_DIR_OPTION_SHOW_SIZE = 1616;  //as string and in sizeBytes as number
const XF_DIR_OPTION_SHOW_PERMISSIONS = 1632;  //octal
const XF_DIR_OPTION_SHOW_TIME = 1633;  //modified field
const XF_DIR_OPTION_SHOW_FOLDER_SIZE = 1634;  //only for Linux with popen and 'du' enabled/installed
const XF_DIR_OPTION_SHOW_TYPE = 1636;  // Folder, File, Image, Document and others
const XF_DIR_OPTION_SHOW_MEDIA_INFO = 1637;    //will use existing image libs (xapp/file/utils): imagick, video resulution and song length


/////////////////////////////////////////////////////////////////
//
// 2. Setup default configuration
//
/////////////////////////////////////////////////////////////////

$XAPP_SALT_KEY = 'k?Ur$0aE#9j1+7ui';     //Salt key to sign and verify client calls

// allowed upload extensions. this is also used when renaming files
$XF_ALLOWED_UPLOAD_EXTENSIONS = 'sh,php,js,css,less,bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,mp3,xblox,cfhtml,tar,zip,md,json';

/***************************************************************************
 *
 * File & directory masks. This must be compatible with PHP glob, see http://www.cowburn.info/2010/04/30/glob-patterns/ for more
 * patterns.
 *
 */

/**
 * XF_VISIBLE_FILE_EXTENSIONS is a comma separated list of visible file extensions,ie: css,html,png
 * If you want to show 'hidden' folders or files, you need to add '.*'
 *
 */
$XF_VISIBLE_FILE_EXTENSIONS = '*';

/**
 * XF_VISIBLE_FILE_EXTENSIONS is a comma separated list of hidden file extensions,ie: php,sh
 */
$XF_HIDDEN_FILE_EXTENSIONS = '.svn,.git,.idea';

/**
 * Flags to use when iterating a directory.
 */
$XF_NODE_FLAGS =
    XF_DIR_OPTION_SHOW_ISDIR |
    XF_DIR_OPTION_SHOW_SIZE |
    XF_DIR_OPTION_SHOW_FOLDER_SIZE |
    XF_DIR_OPTION_SHOW_PERMISSIONS |
    XF_DIR_OPTION_SHOW_MIME |
    XF_DIR_OPTION_SHOW_OWNER |
    XF_DIR_OPTION_SHOW_TIME |
    XF_DIR_OPTION_SHOW_TYPE |
    XF_DIR_OPTION_SHOW_MEDIA_INFO;

/****************************************************************************/

/**
 *
 * @deprecated : this pluins are no longer supported, currently a rewrite in progress!
 *
 * $XF_PROHIBITED_PLUGINS: prohibited plugins, comma separated : 'XShell,XImageEdit,XZoho,XHTMLEditor,XSandbox,XSVN,XLESS'
 */
$XF_PROHIBITED_PLUGINS = XApp_Service_Utils::_getKey('disabledPlugins', 'XSVN,XLESS,XHTMLEditor,XZoho');

/**
 * $XF_THEME: white or transparent.
 *
 * @Remarks
 *    - jQuery thmes are no longer supported
 *  - all is basing on and using bootstrap-3 classes, as sass.
 *        which has primarly a transparent theme in mind.
 *  - you can set a background picture simpply on BODY
 */
$XF_THEME = XApp_Service_Utils::_getKey('theme', 'white');

/**
 * $XAPP_COMPONENTS describes the components to be loaded.
 * There is currently
 * 1. xfile: the file-manager ui it self, mandatory!
 *
 * Not included:
 *
 * 2. xblox: a visual programming language to extend the file manager with a built-in macro system
 * 3. xideve: a visual editor for HTML - Authoring (Dojo only for now). This needs currently &debug=true to run
 * 4. xnode: tools to manager Node.JS services in xide applications
 *
 */
$XAPP_COMPONENTS = array(
    'xfile' => true,
    'xblox' => XApp_Service_Utils::_getKey('xblox', false),
    'xideve' => XApp_Service_Utils::_getKey('xide', false) ? array('cmdOffset' => 'xapp/xide/') : false,
    'xnode' => XApp_Service_Utils::_getKey('xnode', false)
);

/**
 *    All client resources (JS,CSS,HTML Templates, and configs are in client/src/xfile/xbox).
 *    By default it will use client/src/xfile/xbox/resources-release.json and here it can be
 *    overriden to another but different and relative filename.
 */
$XAPP_RESOURCE_CONFIG = XApp_Service_Utils::_getKey("resourceConfig", '');
/**
 *
 * Since all is rendered via templates, here a list variables going in them.
 *
 * @see client/src/xfile/xbox/index.html and Header.javascript for the entire's page HTML. Anything else is
 * done in javascript!.
 *
 * @descrption Define extra variables for client rendering.
 * This array will override existing variables (see xapp/commander/App near '$XAPP_RELATIVE_VARIABLES')
 * The resource variables go into the the client side resource manager 'xide.manager.ResourceManager'
 */
$XF_RESOURCE_VARIABLES = array(
    /**
     * This is the user name automatically filled into the login form(client/xfile/xbox/login.html) , you may set this to ''
     * Notice: this isn't setting the user name in the user database (xapp/commander/Users.php)
     *
     */
    'FILLED_USER_NAME' => 'admin',

    /**
     * this is the password automatically filled into the login form(client/xfile/xbox/login.html), you may set this to ''
     * Notice: this isn't setting the user password in the user database (xapp/commander/Users.php)
     */
    'FILLED_PASSWORD' => 'admin',

    /**
     * Pass the enabled components
     */
    'COMPONENTS' => json_encode($XAPP_COMPONENTS),

    /**
     *  Adjust global font size: this will just go in a CSS class called "claro"
     */
    'GLOBAL_FONT_SIZE' => XApp_Service_Entry_Utils::isMobile() ? '1.1em' : '0.88em',

    /**
     *  Adjust action button icon size.
     *
     * Notice: no longer supported.
     */
    'ACTION_BUTTON_SIZE' => XApp_Service_Entry_Utils::isMobile() ? '1.5em' : '1.3em',

    /**
     * Package config (Dojo-Package paths). In release mode(default) this will be run.js
     */
    'PACKAGE_CONFIG' => 'run-release-debug',

    /**
     * CDN Host
     */
    'CDN_URL' => 'http://www.x4dojo.org/xbox-app/1.9/'

);

/**
 * Compose XFile configuration (override in profile if you like)
 */
$XF_CONFIG = array(

    /**
     * Default store options masks the directory iterator.
     */
    "DEFAULT_STORE_OPTIONS" => array(
        "fields" => $XF_NODE_FLAGS,
        "includeList" => $XF_VISIBLE_FILE_EXTENSIONS,
        "excludeList" => $XF_HIDDEN_FILE_EXTENSIONS
    ),

    "LAYOUT_PRESET" => XF_LAYOUT_PRESET_SINGLE,

    /**
     * No longer supported
     **/
    "PANEL_OPTIONS" => array(
        "ALLOW_NEW_TABS" => true,
        "ALLOW_MULTI_TAB" => false, //misleading flag, it has always multitab. ignore this switch!
        "ALLOW_INFO_VIEW" => true,
        "ALLOW_LOG_VIEW" => true,
        "ALLOW_BREADCRUMBS" => true,
        "ALLOW_CONTEXT_MENU" => true,
        "ALLOW_LAYOUT_SELECTOR" => true,
        "ALLOW_SOURCE_SELECTOR" => true,
        "ALLOW_COLUMN_RESIZE" => true,
        "ALLOW_COLUMN_REORDER" => true,
        "ALLOW_COLUMN_HIDE" => true,
        "ALLOW_MAIN_MENU" => false,
        "ALLOW_ACTION_TOOLBAR" => true
    ),
    "PERMISSIONS" => array(),
    /**
     * No longer supported:
     * Allowed actions in UI and the server. Please check xapp/commander/App.php in the auth-delegate::authorize!
     * @deprecated, switching to "PERMISSIONS"
     */
    "ALLOWED_ACTIONS" => array(
        /*0*/
        0,  //none
        /*1*/
        1,  //edit : not used!
        /*2*/
        1,  //copy
        /*3*/
        1,  //move
        /*4*/
        1,  //info
        /*5*/
        1,  //download: images and file content
        /*6*/
        1,  //compress
        /*7*/
        1,  //delete
        /*8*/
        1,  //rename
        /*9*/
        1,  //dnd
        /*10*/
        1,  //copy &paste
        /*11*/
        1,  //open
        /*12*/
        1,  //reload
        /*13*/
        1,  //preview
        /*14*/
        1,  //reserved
        /*15*/
        1,  //insert image
        /*16*/
        1,  //new file
        /*17*/
        1,  //new dir
        /*18*/
        1,  //upload
        /*19*/
        1,  //read //not used
        /*20*/
        1,  //write
        /*21*/
        1,  //plugins
        /*22*/
        1,  //custom
        /*23*/
        1,  //find
        /*24*/
        1,  //perma link: not used
        /*25*/
        1,  //add mount
        /*26*/
        1,  //remove mount
        /*27*/
        1,  //edit mount
        /*28*/
        1,   //perspective
        /*29*/
        1,  //CLIPBOARD_COPY
        /*30*/
        1,  //CLIPBOARD_CUT
        /*31*/
        1,  //CLIPBOARD_PASTE
        /*32*/
        1,  //EXTRACT
    ),
    "FILE_PANEL_OPTIONS_LEFT" => array( //left panel
        "LAYOUT" => XF_PANEL_MODE_LIST, //when using tree, its target is then the main panel
        "AUTO_OPEN" => "true"
    ),
    "FILE_PANEL_OPTIONS_MAIN" => array( //main panel
        "LAYOUT" => XF_PANEL_MODE_LIST,
        "AUTO_OPEN" => "true"
    ),
    "FILE_PANEL_OPTIONS_RIGHT" => array( //info panel on the right
        "LAYOUT" => XF_PANEL_MODE_LIST,  //has no mean!
        "AUTO_OPEN" => "true"
    )
);
/////////////////////////////////////////////////////////////////
//
//  Main - Routine, load framework and fire app
//
/////////////////////////////////////////////////////////////////
/**
 * Include main files
 */
require_once(XAPP_BASEDIR . 'commander/Bootstrap.php');
//require_once(XAPP_BASEDIR . 'commander/App2.php');


/**
 * Bootstrap override
 */
$XAPP_BOOTSTRAP_OVERRIDE = array();
$XAPP_PROFILE = array();

/////////////////////////////////////////////////////////////////
//
//  3. First pass, override config with CONF_DIRECTORY/default.php
//
/////////////////////////////////////////////////////////////////
$XF_DEFAULT_PROFILE = realpath($CONF_DIRECTORY . DIRECTORY_SEPARATOR . $XF_DEFAULT_PROFILE . '.php');
if (file_exists($XF_DEFAULT_PROFILE)) {
    require_once($XF_DEFAULT_PROFILE);
}

/////////////////////////////////////////////////////////////////
//
//  4. Second pass, override config with CONF_DIRECTORY/custom.php
//
/////////////////////////////////////////////////////////////////
$XF_CUSTOM_PROFILE = realpath($CONF_DIRECTORY . DIRECTORY_SEPARATOR . $XF_CUSTOM_PROFILE . '.php');
if (file_exists($XF_CUSTOM_PROFILE)) {
    require_once($XF_CUSTOM_PROFILE);
}


$XAPP_GATEWAY_OPTIONS = array();

require($PROFILE_DIRECTORY . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'security.php');
//require ($PROFILE_DIRECTORY . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'permissions.php');
//xapp_import('../profiles/default/permissions');


$XAPP_BOOTSTRAP_OVERRIDE['XAPP_BOOTSTRAP_GATEWAY_CONF'] = $XAPP_GATEWAY_OPTIONS;
$XAPP_BOOTSTRAP_OVERRIDE['SALT'] = $XAPP_SALT_KEY;
//$XAPP_BOOTSTRAP_OVERRIDE['XAPP_BOOTSTRAP_GATEWAY_CONF']
/////////////////////////////////////////////////////////////////
//
//  5. Call app
//
/////////////////////////////////////////////////////////////////
$bootstrap = XApp_Commander_Bootstrap::createInstance(

    'commander',                //$serverApplicationClassName       1
    'xbox',                     //$clientApplicationName            2
    XAPP_CLIENT_DIRECTORY,      //clientDirectory                   3
    'lib',                      //lib offset                        4
    XAPP_LOG_DIRECTORY,         //logging directory                 5
    $DATA_DIRECTORY,            //profiles's data directory         6
    $ROOT_DIRECTORY_ABSOLUTE,   //root directory                    7
    '/src',                     //$clientOffset                     8
    $PROFILE_DIRECTORY,         //profile directory                 9
    //VFS Variables                    10
    array(
        'path' => realpath($DATA_DIRECTORY . '/VFS.json'),
        'variables' => array(
            'root' => $XF_PATH
        )
    ),
    $XAPP_BOOTSTRAP_OVERRIDE
);
$bootstrap->handleRequest();