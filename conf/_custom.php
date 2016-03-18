<?php
/**
 * @version 1.7
 * @link https://github.com/mc007
 * @author mc007 mc007@pearls-media.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
require (dirname(__FILE__) . DIRECTORY_SEPARATOR . '_base.php');

/**
 * Example: hide info view (right panel)
 */

/**
 * Override file repository root
 */
//$XF_PATH = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR);




/**
 * XF_VISIBLE_FILE_EXTENSIONS is a comma separated list of visible file extensions,ie: css,html,png
 * If you want to show 'hidden' folders or files, you need to add '.*'
 *
 */
//$XF_VISIBLE_FILE_EXTENSIONS = '*';

/**
 * XF_VISIBLE_FILE_EXTENSIONS is a comma separated list of hidden file extensions,ie: php,sh
 */
//$XF_HIDDEN_FILE_EXTENSIONS = '.svn,.git,.idea';

/****************************************************************************/

// prohibited plugins, comma separated : 'XShell,XImageEdit,XZoho,XHTMLEditor,XSandbox,XSVN,XLESS'
//$XF_PROHIBITED_PLUGINS = _getKey('disabledPlugins','');

//jQuery theme, append the url by &theme=dot-luv ! You can choose from :
// black-tie, blitzer, cupertino, dark-hive, dot-luv,eggplant,excite-bike,flick,hot-sneaks,humanity,le-frog,mint-choc,overcast,pepper-grinder,redmond,smoothness,south-street,start,sunny,swanky-purse,trontastic,ui-darkness,ui-lightness,vader
// see http://jqueryui.com/themeroller/ for more!
//$XF_THEME = _getKey('theme','blitzer');

//
// $XAPP_COMPONENTS = array(
//	'XBLOX' => true,
//	'XIDE_VE' => _getKey('xide',false)
//);

/**
 * Define extra variables for client rendering. This array will override existing variables (see xapp/commander/App near '$XAPP_RELATIVE_VARIABLES')
 * The resource variables go into the the client side resource manager 'xide.manager.ResourceManager'
 */
/*
$XF_RESOURCE_VARIABLES                  = array(

	'FILLED_USER_NAME'          => 'admin',

	'FILLED_PASSWORD'           => 'admin',

	'COMPONENTS'                => json_encode($XAPP_COMPONENTS),

	'GLOBAL_FONT_SIZE'          => XApp_Service_Entry_Utils::isMobile() ? '1.1em' : '0.88em',

	'ACTION_BUTTON_SIZE'        => XApp_Service_Entry_Utils::isMobile() ? '1.5em' : '1.3em'

	'PACKAGE_CONFIG'            => 'run-release-debug'

);
*/
/**
 * Compose XFile configuration
 */

$XF_PROHIBITED_PLUGINS = _getKey('disabledPlugins','XSVN,XLESS,XHTMLEditor,XZoho');

$XF_CONFIG = array(

	"DEFAULT_STORE_OPTIONS" => array(
		"fields" => 1663,
		"includeList" => $XF_VISIBLE_FILE_EXTENSIONS,
		"excludeList" => $XF_HIDDEN_FILE_EXTENSIONS
	),

	"LAYOUT_PRESET" => XF_LAYOUT_PRESET_SINGLE,

	"PANEL_OPTIONS" => array(
		"ALLOW_NEW_TABS"        =>  true,
		"ALLOW_MULTI_TAB"       =>  false,
		"ALLOW_INFO_VIEW"       =>  true,
		"ALLOW_LOG_VIEW"        =>  true,
		"ALLOW_BREADCRUMBS"     =>  true,
		"ALLOW_CONTEXT_MENU"    =>  true,
		"ALLOW_LAYOUT_SELECTOR" =>  true,
		"ALLOW_SOURCE_SELECTOR" =>  false,
		"ALLOW_COLUMN_RESIZE"   =>  true,
		"ALLOW_COLUMN_REORDER"  =>  true,
		"ALLOW_COLUMN_HIDE"     =>  true,
		"ALLOW_MAIN_MENU"       =>  true,
		"ALLOW_ACTION_TOOLBAR"  =>  true
	),

	//
	// Allowed actions in UI and the server. Please check xapp/commander/App.php in the auth-delegate::authorize!
	//
	"ALLOWED_ACTIONS" => array(

/*0*/	0,  //none
/*1*/	1,  //edit : not used!
/*2*/	1,  //copy
/*3*/   1,  //move
/*4*/   1,  //info
/*5*/   1,  //download: images and file content
/*6*/   1,  //compress
/*7*/   1,  //delete
/*8*/   1,  //rename
/*9*/   1,  //dnd
/*10*/  1,  //copy &paste
/*11*/  1,  //open
/*12*/  1,  //reload
/*13*/  1,  //preview
/*14*/  1,  //reserved
/*15*/  1,  //insert image
/*16*/  1,  //new file
/*17*/  1,  //new dir
/*18*/  1,  //upload
/*19*/  1,  //read //not used
/*20*/  1,  //write
/*21*/  0,  //plugins
/*22*/  1,  //custom
/*23*/  1,  //find
/*24*/  1,  //perma link: not used
/*25*/  1,  //add mount
/*26*/  1,  //remove mount
/*27*/  1,  //edit mount
/*28*/  1,   //perspective
/*29*/  1,  //CLIPBOARD_COPY
/*30*/  1,  //CLIPBOARD_CUT
/*31*/  1,  //CLIPBOARD_PASTE
/*32*/  1,  //EXTRACT
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
