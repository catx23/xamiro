<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\xide\Directory
 */

xapp_import('xapp.Service.Service');
xapp_import('xapp.Directory.Service');

/***
 * Class XIDE_Service extends the standard service
 */
class XIDE_Service extends XApp_Service
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
        //standard constructor
        xapp_set_options($options, $this);
    }

}