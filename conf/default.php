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
//$XF_CONFIG["PANEL_OPTIONS"]["ALLOW_MAIN_MENU"] = false;//important, this feature is not ready yet

/**
 * Override file repository root
 */
//$XF_PATH = '/home/mc007/Desktop/';




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

//
//  Define extra variables for client rendering. This array will override existing variables (see xapp/commander/App near '$XAPP_RELATIVE_VARIABLES')
//  The resource variables go into the the client side resource manager 'xide.manager.ResourceManager'
//
/*
$XF_RESOURCE_VARIABLES                  = array(
	//
	//  This is the user name automatically filled into the login form(client/xfile/xbox/login.html) , you may set this to ''
	//  Notice: this isn't setting the user name in the user database (xapp/commander/Users.php)
	//
	//
	'FILLED_USER_NAME'          => 'admin',
	//
	//  This is the password automatically filled into the login form(client/xfile/xbox/login.html), you may set this to ''
	//  Notice: this isn't setting the user password in the user database (xapp/commander/Users.php)
	//
	'FILLED_PASSWORD'           => 'admin',
	//
	// Pass the enabled components
	//
	'COMPONENTS'                => json_encode($XAPP_COMPONENTS),

	//
	//Adjust global font size
	//
	'GLOBAL_FONT_SIZE'          =>'0.78em',
	//
	//  Adjust action button icon size
	//
	'ACTION_BUTTON_SIZE'        =>'1.3em'

);
*/
/**
 * Compose XFile configuration
 */
/*
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
		"ALLOW_SOURCE_SELECTOR" =>  true,
		"ALLOW_COLUMN_RESIZE"   =>  true,
		"ALLOW_COLUMN_REORDER"  =>  true,
		"ALLOW_COLUMN_HIDE"     =>  true,
		"ALLOW_ACTION_TOOLBAR"  =>  true,
		"ALLOW_MAIN_MENU"     =>  true
	),

	//
	// Allowed actions in UI and the server. Please check xapp/commander/App.php in the auth-delegate::authorize!
	//
	"ALLOWED_ACTIONS" => array(
		0,  //none
		1,  //edit
		1,  //copy
		1,  //move
		1,  //info
		1,  //download  : remote download, needed by Aviary-Image-Editor or dropping links into file panels
		1,  //compress
		1,  //delete
		1,  //rename
		1,  //dnd
		1,  //copy &paste
		1,  //open
		1,  //reload
		1,  //preview
		1,  //insert image
		1,  //new file
		1,  //new dir
		1,  //upload
		1,  //read
		1,  //write
		1,  //plugins
		1,  //custom
		1,  //find
		1,  //perma link
		1,  //add mount
		1,  //remove mount
		1,  //edit mount
		1,   //perspective
		1,  //CLIPBOARD_COPY
		1,  //CLIPBOARD_CUT
		1,  //CLIPBOARD_PASTE
		1  //EXTRACT
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
*/