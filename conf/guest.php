<?php
/**
 * @version 1.7
 * @link https://github.com/mc007
 * @author mc007 mc007@pearls-media.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
require (dirname(__FILE__) . DIRECTORY_SEPARATOR . '_base.php');

/**
 * The "Minimum" profile is quick drop in for guest. Users can just browse
 */

// prohibited plugins, comma separated : 'XShell,XImageEdit,XZoho,XHTMLEditor,XSandbox,XSVN,XLESS,XMarkdown,XJSON'
$XF_PROHIBITED_PLUGINS = 'XShell,XZoho,XHTMLEditor,XSandbox,XSVN,XLESS';

//shift folder
//$XF_PATH.='/incoming';

/**
 * Compose XFile configuration
 */
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
		"ALLOW_BREADCRUMBS"     =>  false,
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
/*1*/	1,  //edit
/*2*/	0,  //copy
/*3*/   0,  //move
/*4*/   1,  //info
/*5*/   1,  //download
/*6*/   1,  //compress
/*7*/   0,  //delete
/*8*/   0,  //rename
/*9*/   0,  //dnd
/*10*/  0,  //copy &paste
/*11*/  1,  //open
/*12*/  1,  //reload
/*13*/  1,  //preview
/*14*/  0,  //reserved
/*15*/  0,  //insert image
/*16*/  0,  //new file
/*17*/  0,  //new dir
/*18*/  1,  //upload
/*19*/  1,  //read
/*20*/  0,  //write
/*21*/  1,  //plugins
/*22*/  0,  //custom
/*23*/  0,  //find
/*24*/  0,  //perma link
/*25*/  0,  //add mount
/*26*/  0,  //remove mount
/*27*/  0,  //edit mount
/*28*/  0,  //perspective
/*29*/  0,  //CLIPBOARD_COPY
/*30*/  0,  //CLIPBOARD_CUT
/*31*/  0,  //CLIPBOARD_PASTE
/*32*/  0,  //EXTRACT

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

/**
 * Disable authentication
 */
$XAPP_BOOTSTRAP_OVERRIDE = array(
	XApp_Commander_Bootstrap::FLAGS=>array(-XAPP_BOOTSTRAP_NEEDS_AUTHENTICATION)
);