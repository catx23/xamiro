<?php
/**
 * @author     Guenter Baumgart
 * @author     David Grudl
 * @copyright 2004 David Grudl (http://davidgrudl.com)
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @license : http://opensource.org/licenses/BSD-3-Clause
 * @package XApp\Utils
 *
 * @original header : This file is part of the Nette Framework (http://nette.org)
 */
/**
 * Limited scope for PHP code evaluation and script including.
 *
 * @author     David Grudl
 */
class XApp_LimitedScope
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}


	/**
	 * Evaluates code in limited scope.
	 * @param  string  PHP code
	 * @param  array   local variables
	 * @return mixed   the return value of the evaluated code
	 */
	public static function evaluate(/*$code, array $vars = NULL*/)
	{
		if (func_num_args() > 1) {
			foreach (func_get_arg(1) as $__k => $__v) $$__k = $__v;
			unset($__k, $__v);
		}
		$res = eval('?>' . func_get_arg(0));
		if ($res === FALSE && ($error = error_get_last()) && $error['type'] === E_PARSE) {
			throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
		}
		return $res;
	}


	/**
	 * Includes script in a limited scope.
	 * @param  string  file to include
	 * @param  array   local variables or TRUE meaning include once
	 * @return mixed   the return value of the included file
	 */
	public static function load(/*$file, array $vars = NULL*/)
	{
		if (func_num_args() > 1) {
			if (func_get_arg(1) === TRUE) {
				return include_once func_get_arg(0);
			}
			foreach (func_get_arg(1) as $__k => $__v) $$__k = $__v;
			unset($__k, $__v);
		}
		return include func_get_arg(0);
	}

}
