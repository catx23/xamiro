<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Common paths
 */
$ROOT_DIRECTORY_ABSOLUTE = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR .'..');

$XAPP_BASE_DIRECTORY     =  $ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR;

/***
 * Common defines
 */
define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY);
define('XAPP_APPLICATION_CLASS','xide');
define('XAPP_CLIENT_APPLICATION_NAME','xide');
define('XAPP_CLIENT_DIRECTORY',realpath($ROOT_DIRECTORY_ABSOLUTE . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'client') . DIRECTORY_SEPARATOR);

/***
 * Dependencies
*/
require_once (XAPP_BASEDIR.'/xide/Bootstrap.php');


$bootStrap = XIDE_Bootstrap::createInstance(
    XAPP_APPLICATION_CLASS,
    XAPP_CLIENT_APPLICATION_NAME,
    XAPP_CLIENT_DIRECTORY);

$bootStrap->handleRequest();