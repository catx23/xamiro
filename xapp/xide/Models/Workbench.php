<?php
/**
 * @version 0.1.0
 *
 * @author Luis Ramos
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Models
 */

/**
 * Class XApp_XIDE_Workbench wraps lots of Maqetta specific data but also the current user's workbench state
 */
class XApp_XIDE_Workbench extends XApp_Entity {

    /***
     * Maqetta specific data, static part
     */
    public $themeDefaultSet;    //comes from a JSON file
    public $dojoOptions;        //comes from a JSON file
    public $widgetPalette;      //comes from a JSON file
    /***
     * Dynamic part
     */
    public $userInfo;           //hard coded
    public $workbenchState;     //hard coded
    public $project;            //current project, hard coded

}

?>