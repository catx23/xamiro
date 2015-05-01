<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp-Commander\Plugin\XSandbox
 */

/***
 * Example server plugin.
 * @remarks
    -This class is running in the CMS context already!
    -A function's result will be wrapped automatically into the specified transport envelope, eg: JSON-RPC-2.0 or JSONP
 */
class XSandbox extends Xapp_Commander_Plugin{

    /***
     * Dummy call
     * @return string
     * @link http://0.0.0.0/wordpress/wp-content/plugins/xcom/server/service/index_wordpress_admin.php?service=XSandbox.test&callback=bla
     */
    public function test(){
        return 'cool';
    }

}