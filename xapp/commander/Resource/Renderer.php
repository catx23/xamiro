<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Class XApp_Standalone_Resource_Renderer
 * @remarks : renders resources into strings or prints out
 */
xapp_import('xapp.xide.Resource.Renderer');
class XCOM_Resource_Renderer extends xide_Resource_Renderer
{
    /**
     * class constructor
     * call parent constructor for class initialization
     *
     * @error 14601
     * @param null|array|object $options expects optional options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        xapp_set_options($options, $this);
    }
}
