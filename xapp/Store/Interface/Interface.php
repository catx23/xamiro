<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Writer interface
 *
 * @package Log
 * @author Writer
 */
interface Xapp_Store_Interface
{
    /***
     * @param query, a Json path query
     * @return string
     */
    public function get($section,$path,$query=null);
    /**
     * @param $store
     * @param string $path
     * @param $searchQuery
     * @param $operation
     * @param null $newValue
     * @return mixed
     */
    public function set($section,$path='.',$searchQuery,$newValue=null);

    /**
     * @return mixed
     */
    public function read();

    /***
     * @return mixed
     */
    public function write($data);

}