<?php

/*
 *
 * alex
 *
 * 1. https://www.youtube.com/watch?v=yKAxOHnuk-I
 *
 */


//define('NEEDS_GD',1);
//define('NEEDS_GD_BUNDLE',1);
//define('NEEDS_MEMCACHE',1);
//define('NEEDS_MOD_REWRITE',1);
//define('NEEDS_PDO',1);
//define('NEEDS_TOKENIZER',1);
define('NEEDS_XAPP_LOAD_TESTS',1);
define('NEEDS_XAPP_LOAD_LOGGING_TEST',1);
//$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);



/**
 * Check PHP configuration.
 */
foreach (array('function_exists', 'version_compare', 'extension_loaded', 'ini_get') as $function) {
	if (!function_exists($function)) {
		die("Error: function '$function' is required by XApp Framework and this Requirements Checker.");
	}
}

/**
 * Check assets folder, template file must be readable
 */
define('TEMPLATE_FILE', __DIR__ . '/assets/checker.phtml');
if (!is_readable(TEMPLATE_FILE)) {
	die('Error: template file is not readable. Check assets folder (part of distribution), it should be present, readable and contain readable template file.');
}


$xapp_load_tests=array();


/**
 * Check XApp Framework requirements.
 */
$tests[] = array(
	'title' => 'Web server',
	'message' => $_SERVER['SERVER_SOFTWARE'],
);

$tests[] = array(
	'title' => 'PHP version',
	'required' => TRUE,
	'passed' => version_compare(PHP_VERSION, '5.3.1', '>='),
	'message' => PHP_VERSION,
	'description' => 'Your PHP version is too old. XApp Framework requires at least PHP 5.3.1 or higher.',
);

$tests[] = array(
	'title' => 'Memory limit',
	'message' => ini_get('memory_limit'),
);

$tests['hf'] = array(
	'title' => '.htaccess file protection',
	'required' => FALSE,
	'description' => 'File protection by <code>.htaccess</code> is not present. You must be careful to put files into document_root folder.',
	'script' => '<script src="assets/denied/checker.js"></script> <script>displayResult("hf", typeof fileProtectionChecker == "undefined")</script>',
);

if(defined('NEEDS_MOD_REWRITE')){
	$tests['hr'] = array(
		'title' => '.htaccess mod_rewrite',
		'required' => FALSE,
		'description' => 'Mod_rewrite is probably not present. You will not be able to use Cool URL.',
		'script' => '<script src="assets/rewrite/checker"></script> <script>displayResult("hr", typeof modRewriteChecker == "boolean")</script>',
	);
}


$tests[] = array(
	'title' => 'Function ini_set()',
	'required' => FALSE,
	'passed' => function_exists('ini_set'),
	'description' => 'Function <code>ini_set()</code> is disabled. Some parts of XApp Framework may not work properly.',
);

$tests[] = array(
	'title' => 'Function error_reporting()',
	'required' => TRUE,
	'passed' => function_exists('error_reporting'),
	'description' => 'Function <code>error_reporting()</code> is disabled. XApp Framework requires this to be enabled.',
);

$tests[] = array(
	'title' => 'Function flock()',
	'required' => TRUE,
	'passed' => flock(fopen(__FILE__, 'r'), LOCK_SH),
	'description' => 'Function <code>flock()</code> is not supported on this filesystem. XApp Framework requires this to process atomic file operations.',
);

$tests[] = array(
	'title' => 'Register_globals',
	'required' => TRUE,
	'passed' => !xapp_test_iniFlag('register_globals'),
	'message' => 'Disabled',
	'errorMessage' => 'Enabled',
	'description' => 'Configuration directive <code>register_globals</code> is enabled. XApp Framework requires this to be disabled.',
);

$tests[] = array(
	'title' => 'Variables_order',
	'required' => TRUE,
	'passed' => strpos(ini_get('variables_order'), 'G') !== FALSE && strpos(ini_get('variables_order'), 'P') !== FALSE && strpos(ini_get('variables_order'), 'C') !== FALSE,
	'description' => 'Configuration directive <code>variables_order</code> is missing. XApp Framework requires this to be set.',
);

$tests[] = array(
	'title' => 'Session auto-start',
	'required' => FALSE,
	'passed' => session_id() === '' && !defined('SID'),
	'description' => 'Session auto-start is enabled. XApp Framework recommends not to use this directive for security reasons.',
);

$tests[] = array(
	'title' => 'PCRE with UTF-8 support',
	'required' => TRUE,
	'passed' => @preg_match('/pcre/u', 'pcre'),
	'description' => 'PCRE extension must support UTF-8.',
);

$tests[] = array(
	'title' => 'ICONV extension',
	'required' => TRUE,
	'passed' => extension_loaded('iconv') && (ICONV_IMPL !== 'unknown') && @iconv('UTF-16', 'UTF-8//IGNORE', iconv('UTF-8', 'UTF-16//IGNORE', 'test')) === 'test',
	'message' => 'Enabled and works properly',
	'errorMessage' => 'Disabled or does not work properly',
	'description' => 'ICONV extension is required and must work properly.',
);

if(defined('NEEDS_TOKENIZER')){
	$tests[] = array(
		'title' => 'PHP tokenizer',
		'required' => TRUE,
		'passed' => extension_loaded('tokenizer'),
		'description' => 'PHP tokenizer is required.',
	);
}

if(defined('NEEDS_PDO')){
	$tests[] = array(
		'title' => 'PDO extension',
		'required' => FALSE,
		'passed' => $pdo = extension_loaded('pdo') && PDO::getAvailableDrivers(),
		'message' => $pdo ? 'Available drivers: ' . implode(' ', PDO::getAvailableDrivers()) : NULL,
		'description' => 'PDO extension or PDO drivers are absent. You will not be able to use <code>XApp\Database</code>.',
	);
}

$tests[] = array(
	'title' => 'Multibyte String extension',
	'required' => FALSE,
	'passed' => extension_loaded('mbstring'),
	'description' => 'Multibyte String extension is absent. Some internationalization components may not work properly.',
);

$tests[] = array(
	'title' => 'Multibyte String function overloading',
	'required' => TRUE,
	'passed' => !extension_loaded('mbstring') || !(mb_get_info('func_overload') & 2),
	'message' => 'Disabled',
	'errorMessage' => 'Enabled',
	'description' => 'Multibyte String function overloading is enabled. XApp Framework requires this to be disabled. If it is enabled, some string function may not work properly.',
);
if(defined('NEEDS_MEMCACHE')){
	$tests[] = array(
		'title' => 'Memcache extension',
		'required' => FALSE,
		'passed' => extension_loaded('memcache'),
		'description' => 'Memcache extension is absent. You will not be able to use <code>XApp\Caching\Storages\MemcachedStorage</code>.',
	);
}

if(defined('NEEDS_GD')){
	$tests[] = array(
		'title' => 'GD extension',
		'required' => FALSE,
		'passed' => extension_loaded('gd'),
		'description' => 'GD extension is absent. You will not be able to use <code>XApp\Image</code>.',
	);
}
if(defined('NEEDS_GD_BUNDLE')){
	$tests[] = array(
		'title' => 'Bundled GD extension',
		'required' => FALSE,
		'passed' => extension_loaded('gd') && GD_BUNDLED,
		'description' => 'Bundled GD extension is absent. You will not be able to use some functions such as <code>XApp\Image::filter()</code> or <code>XApp\Image::rotate()</code>.',
	);
}

$tests[] = array(
	'title' => 'Fileinfo extension or mime_content_type()',
	'required' => TRUE,
	'passed' => extension_loaded('fileinfo') || function_exists('mime_content_type'),
	'description' => 'Fileinfo extension or function <code>mime_content_type()</code> are absent. You will not be able to determine mime type of uploaded files.',
);

$tests[] = array(
	'title' => 'HTTP_HOST or SERVER_NAME',
	'required' => TRUE,
	'passed' => isset($_SERVER['HTTP_HOST']) || isset($_SERVER['SERVER_NAME']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => 'Either <code>$_SERVER["HTTP_HOST"]</code> or <code>$_SERVER["SERVER_NAME"]</code> must be available for resolving host name.',
);

$tests[] = array(
	'title' => 'REQUEST_URI or ORIG_PATH_INFO',
	'required' => TRUE,
	'passed' => isset($_SERVER['REQUEST_URI']) || isset($_SERVER['ORIG_PATH_INFO']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => 'Either <code>$_SERVER["REQUEST_URI"]</code> or <code>$_SERVER["ORIG_PATH_INFO"]</code> must be available for resolving request URL.',
);

$tests[] = array(
	'title' => 'SCRIPT_NAME or DOCUMENT_ROOT & SCRIPT_FILENAME',
	'required' => TRUE,
	'passed' => isset($_SERVER['SCRIPT_NAME']) || isset($_SERVER['DOCUMENT_ROOT'], $_SERVER['SCRIPT_FILENAME']),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => '<code>$_SERVER["SCRIPT_NAME"]</code> or <code>$_SERVER["DOCUMENT_ROOT"]</code> with <code>$_SERVER["SCRIPT_FILENAME"]</code> must be available for resolving script file path.',
);

$tests[] = array(
	'title' => 'REMOTE_ADDR or php_uname("n")',
	'required' => TRUE,
	'passed' => isset($_SERVER['REMOTE_ADDR']) || function_exists('php_uname'),
	'message' => 'Present',
	'errorMessage' => 'Absent',
	'description' => '<code>$_SERVER["REMOTE_ADDR"]</code> or <code>php_uname("n")</code> must be available for detecting development / production mode.',
);


/***********************************************************************************************************************/
/*                                                                                                                     */
/*                             XApp-Dry-Run-Tests                                                                      */

function errorHandler($error_level, $error_message, $error_file, $error_line, $error_context)
{
	$error = "lvl: " . $error_level . " | msg:" . $error_message . " | file:" . $error_file . " | ln:" . $error_line;
	switch ($error_level) {
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_PARSE:
			log($error, "fatal");
			break;
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
			log($error, "error");
			break;
		case E_WARNING:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_USER_WARNING:
			log($error, "warn");
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			log($error, "info");
			break;
		case E_STRICT:
			log($error, "debug");
			break;
		default:
			log($error, "warn");
	}
}

function shutdownHandler() //will be called when php script ends.
{
	$lasterror = error_get_last();
	switch ($lasterror['type'])
	{
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_PARSE:
			$error = "[SHUTDOWN] lvl:" . $lasterror['type'] . " | msg:" . $lasterror['message'] . " | file:" . $lasterror['file'] . " | ln:" . $lasterror['line'];
			xapp_test_include_log($error, "fatal");
	}
}

function xapp_test_include_log($error, $errlvl)
{
	error_log('bad!');
}


if(xapp_is_defined('NEEDS_XAPP_LOAD_TESTS')){


	$XAPP_BASE_DIRECTORY = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
	if(!defined('XAPP_BASEDIR')){
		define('XAPP_BASEDIR',$XAPP_BASE_DIRECTORY);
	}


	function xapp_get_path_error_message($path){
		return XAPP_BASEDIR . '/' . $path . ' : doesnt exists';
	}

	function xapp_test_include($path){

		ob_start();

		$includePath = XAPP_BASEDIR . '/' . $path;
		if(!file_exists($includePath)){
			return false;//$includePath . ' doesn`t exists';
		}

		//- Try loadin
		try{
			require($includePath);
		}catch (Exception $e){
			return $e;
		}
		ob_end_clean();
		return true;
	}

	function xapp_test_loadMin(){
		//- Try loadin
		try{
			require_once(XAPP_BASEDIR . '/XApp_Service_Entry_Utils.php');
		}catch (Exception $e){
			return false;
		}
		return true;
	}

	function xapp_test_loggingFramework(){

		require_once(XAPP_BASEDIR. '/Log/Exception/Exception.php');
		require_once(XAPP_BASEDIR. '/Log/Interface/Interface.php');
		require_once(XAPP_BASEDIR. '/Log/Log.php');
		require_once(XAPP_BASEDIR. '/Log/Writer.php');
		require_once(XAPP_BASEDIR. '/Log/Writer/File.php');

	}


	/***
	 * Include tests
	 */
	$xapp_load_tests[] = array(
		'title' => 'XApp - Service Entry Tools Load',
		'required' => TRUE,
		'passed' => xapp_test_include('XApp_Service_Entry_Utils.php'),
		'message' => 'Ok',
		'description' => 'Loading of XApp_Service_Entry_Utils failed',
	);

	$xapp_load_tests[] = array(
		'title' => 'XApp - Bootstrap Base Load Test',
		'required' => TRUE,
		'passed' => xapp_test_include('Bootstrap.php'),
		'message' => 'Ok',
		'description' => 'Loading of Bootstrap failed',
		'errorMessage' => xapp_get_path_error_message('Bootstrap.php'),
	);


}

xapp_paint_test_results($tests,$xapp_load_tests);


/**
 * @param $varName
 * @return bool
 */
function xapp_is_defined($varName){

	if(defined($varName)){
		$res = constant($varName);
		return isset($res) && ($res== true || $res== 1);
	}
	return false;
}


/**
 * Paints checker.
 * @param  array
 * @return void
 */
function xapp_paint_test_results($requirements,$load_tests)
{
	$redirect = round(time(), -1);
	if (!isset($_GET) || (isset($_GET['r']) && $_GET['r'] == $redirect)) {
		$redirect = NULL;
	}

	$errors = $warnings = FALSE;

	foreach ($requirements as $id => $requirement)
	{
		$requirements[$id] = $requirement = (object) $requirement;
		if (isset($requirement->passed) && !$requirement->passed) {
			if ($requirement->required) {
				$errors = TRUE;
			} else {
				$warnings = TRUE;
			}
		}
	}


	foreach ($load_tests as $id => $requirement)
	{
		$xapp_load_tests[$id] = $requirement = (object) $requirement;
		if (isset($requirement->passed) && is_string($requirement->passed)) {
			//$requirement->description = $requirement->passed;
			if ($requirement->required) {
				$errors = TRUE;
			} else {
				$warnings = TRUE;
			}
		}else if (isset($requirement->passed) && !$requirement->passed) {
			if ($requirement->required) {
				$errors = TRUE;
			} else {
				$warnings = TRUE;
			}
		}
	}

	require TEMPLATE_FILE;
}

/**
 * Gets the boolean value of a configuration option.
 * @param  string  configuration option name
 * @return bool
 */
function xapp_test_iniFlag($var)
{
	$status = strtolower(ini_get($var));
	return $status === 'on' || $status === 'true' || $status === 'yes' || (int) $status;
}

