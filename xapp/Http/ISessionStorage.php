<?php

/**
 * @author     Guenter Baumgart
 * @author     David Grudl
 * @copyright 2004 David Grudl (http://davidgrudl.com)
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @license : http://opensource.org/licenses/BSD-3-Clause
 * @package XApp-Commander
 *
 * @original header : This file is part of the Nette Framework (http://nette.org)
 */
/**
 * User session storage for PHP < 5.4. @see http://php.net/session_set_save_handler
 *
 * @author     David Grudl
 */
interface XApp_Http_ISessionStorage
{

	function open($savePath, $sessionName);

	function close();

	function read($id);

	function write($id, $data);

	function remove($id);

	function clean($maxlifetime);

}
