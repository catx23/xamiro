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
 * Rendering helpers for HTTP.
 *
 * @author     David Grudl
 */
class XApp_Http_Helpers
{

	/**
	 * Is IP address in CIDR block?
	 * @return bool
	 */
	public static function ipMatch($ip, $mask)
	{
		list($mask, $size) = explode('/', $mask . '/');
		$ipv4 = strpos($ip, '.');
		$max = $ipv4 ? 32 : 128;
		if (($ipv4 xor strpos($mask, '.')) || $size < 0 || $size > $max) {
			return FALSE;
		} elseif ($ipv4) {
			$arr = array(ip2long($ip), ip2long($mask));
		} else {
			$arr = unpack('N*', inet_pton($ip) . inet_pton($mask));
			$size = $size === '' ? 0 : $max - $size;
		}
		$bits = implode('', array_map(function ($n) {
				return sprintf('%032b', $n);
		}, $arr));
		return substr($bits, 0, $max - $size) === substr($bits, $max, $max - $size);
	}

}
