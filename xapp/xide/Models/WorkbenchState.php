<?php
/**
 * @version 0.1.0
 *
 * @author Guenter Baumgart
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Models
 */
/**
 * Class XApp_XIDE_Workbench
 */
class XApp_XIDE_WorkbenchState extends XApp_Entity {

    public $activeEditor;
    public $editors =array();
    public $nhfo=array();
    public $project;
}

?>