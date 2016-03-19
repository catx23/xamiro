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

xapp_import('xapp.Http.Url');
/**
 * Extended HTTP URL.
 *
 * <pre>
 * http://nette.org/admin/script.php/pathinfo/?name=param#fragment
 *                 \_______________/\________/
 *                        |              |
 *                   scriptPath       pathInfo
 * </pre>
 *
 * - scriptPath:  /admin/script.php (or simply /admin/ when script is directory index)
 * - pathInfo:    /pathinfo/ (additional path information)
 *
 * @author     David Grudl
 *
 * @property   string $scriptPath
 * @property-read string $pathInfo
 */
class XApp_Http_UrlScript extends XApp_Http_Url
{
	/** @var string */
	private $scriptPath = '/';


	/**
	 * Sets the script-path part of URI.
	 * @param  string
	 * @return self
	 */
	public function setScriptPath($value)
	{
		$this->scriptPath = (string) $value;
		return $this;
	}


	/**
	 * Returns the script-path part of URI.
	 * @return string
	 */
	public function getScriptPath()
	{
		return $this->scriptPath;
	}


	/**
	 * Returns the base-path.
	 * @return string
	 */
	public function getBasePath()
	{
		$pos = strrpos($this->scriptPath, '/');
		return $pos === FALSE ? '' : substr($this->getPath(), 0, $pos + 1);
	}


	/**
	 * Returns the additional path information.
	 * @return string
	 */
	public function getPathInfo()
	{
		return (string) substr($this->getPath(), strlen($this->scriptPath));
	}

}
