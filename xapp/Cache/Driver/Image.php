<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('XAPP') || require_once(dirname(__FILE__) . '/../Core/core.php');


/**
 * Cache driver file class
 *
 * @package Cache
 * @subpackage Cache_Driver
 * @class Xapp_Cache_Driver_Image
 * @error 155
 * @author Frank Mueller
 * @author Luis Ramos
 */
class Xapp_Cache_Driver_Image extends Xapp_Cache_Driver
{
	/**
	 * Image driver option cache directory must be a valid path and passed with instance
	 *
	 * @const PATH
	 */
	const PATH = 'XAPP_CACHE_DRIVER_IMAGE_PATH';
	const SUBPATH = 'XAPP_CACHE_DRIVER_IMAGE_SUBPATH';

	/**
	 * file driver option default cache file extension must a valid file extension value
	 *
	 * @const CACHE_EXTENSION
	 */
	const CACHE_EXTENSION = 'XAPP_CACHE_DRIVER_FILE_CACHE_EXTENSION';

	/**
	 *  Image cache descriptor file
	 */
	const FILE_HEADER_KEY_LIFETIME = 'lifetime';
	const FILE_HEADER_KEY_FILENAME = 'filename';

	protected static $headerFormat = array(
		self::FILE_HEADER_KEY_LIFETIME,
		self::FILE_HEADER_KEY_FILENAME
	);


	/**
	 * contains the singleton instance for this class
	 *
	 * @var null|Xapp_Cache_Driver_Image
	 */
	protected static $_instance = null;


	/**
	 * options dictionary for this class containing all data type values
	 *
	 * @var array
	 */
	public static $optionsDict = array
	(
		self::PATH => XAPP_TYPE_DIR,
		self::SUBPATH => XAPP_TYPE_STRING,
		self::CACHE_EXTENSION => XAPP_TYPE_STRING

	);

	/**
	 * options mandatory map for this class contains all mandatory values
	 *
	 * @var array
	 */
	public static $optionsRule = array
	(
		self::PATH => 1,
		self::SUBPATH => 1,
		self::CACHE_EXTENSION => 1
	);

	/**
	 * options default value array containing all class option default values
	 *
	 * @var array
	 */
	public $options = array
	(
		self::DEFAULT_EXPIRATION => 60,
		self::CACHE_EXTENSION => 'cache',
		self::SUBPATH => 'images'

	);


	/**
	 * static singleton method to create static instance of driver with optional third parameter
	 * xapp options array or object
	 *
	 * @error 15501
	 * @param null|mixed $options expects optional xapp option array or object
	 * @return Xapp_Cache_Driver_File
	 */
	public static function instance($options = null)
	{
		if (self::$_instance === null) {
			self::$_instance = new self($options);
		}
		return self::$_instance;
	}


	/**
	 * init class instance by validating cache directory for being readable and writable
	 *
	 * @error 15502
	 * @return void
	 * @throws Xapp_Cache_Driver_Exception
	 */
	protected function init()
	{
		try {
			$dir = new SplFileInfo(xapp_get_option(self::PATH, $this));
			if (!$dir->isReadable()) {
				throw new Xapp_Cache_Driver_Exception("cache directory is not readable", 1550201);
			}
			if (!$dir->isWritable()) {
				throw new Xapp_Cache_Driver_Exception("cache directory is not writable", 1550202);
			}
			xo_set(self::PATH, rtrim($dir->getRealPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $this);

			$complete_path = xo_get(self::PATH, $this) . DIRECTORY_SEPARATOR . xo_get(self::SUBPATH, $this);

			if (!is_dir($complete_path)) {
				@mkdir($complete_path);
			}
			$subpath = xo_get(self::SUBPATH, $this) . DS;
			xo_set(self::SUBPATH, $subpath, $this);


		} catch (Exception $e) {
			throw new Xapp_Cache_Driver_Exception(
				xapp_sprintf("cache directory file info error: %d - %s", $e->getCode(), $e->getMessage()), 1550203
			);
		}
	}


	/**
	 * get file path and image extension for cached image, if exists
	 *
	 * @error 15503
	 * @param string $key expects the cache key name as string
	 * @param null|mixed $default expects the default return value if cache key does not exist anymore
	 * @return null|mixed
	 * @throws Xapp_Cache_Driver_Exception
	 */
	public function get($key, $default = null)
	{
		if ($this->has($key)) {

			$filename = $this->cachePath() . $this->filename($key);

			if (($value = file_get_contents($filename)) !== false) {
				$header = self::getHeaderContent($this->cachePath() . $this->descriptorfilename($key));
				if (self::hasExpired($filename, $header[self::FILE_HEADER_KEY_LIFETIME])) {
					$this->forget($key);
					return $default;
				} else {
					return $this->cachePath() . $header[self::FILE_HEADER_KEY_FILENAME];
				}
			} else {
				throw new Xapp_Cache_Driver_Exception("unable to read content from cache file", 1550301);
			}
		} else {
			return $default;
		}
	}


	/**
	 * set value for cache key with optional lifetime value as third parameter. if not set default lifetime
	 * will be applied. returns value written
	 *
	 * @error 15504
	 * @param string $key expects the cache key name as string
	 * @param mixed $content expects the image binary data
	 * @param null|int $lifetime expects the optional lifetime value
	 * @return mixed
	 */
	public function set($key, $content, $lifetime = null)
	{
		if ($lifetime === null) {
			$lifetime = xapp_get_option(self::DEFAULT_EXPIRATION, $this);
		}
		$image_file = $this->filename($key);

		$header = self::headerContent(
			Array(
				self::FILE_HEADER_KEY_LIFETIME => $lifetime,
				self::FILE_HEADER_KEY_FILENAME => $image_file
			)
		);

		file_put_contents($this->cachePath() . $image_file, $content, LOCK_EX);
		file_put_contents($this->cachePath() . $this->descriptorfilename($key), $header, LOCK_EX);

		return $content;
	}


	/**
	 * check if cache key file still exists or has been remove already returning boolean value
	 *
	 * @error 15505
	 * @param string $key expects the cache key name as string
	 * @return bool
	 */
	public function has($key)
	{
		return (file_exists($this->cachePath() . $this->descriptorfilename($key)));
	}


	/**
	 * remove cache key file returning boolean value from file unlink function
	 *
	 * @error 15506
	 * @param string $key expects the cache key name as string
	 * @return bool
	 */
	public function forget($key)
	{
		if ($this->has($key)) {
			return (
				unlink($this->cachePath() . $this->filename($key))
				&&
				unlink($this->cachePath() . $this->descriptorfilename($key))
			);
		} else {
			return false;
		}
	}


	/**
	 * purge/remove only already expired cache entries if first parameter is set to true. if set
	 * to false will remove all cache key files independent of expiration value!
	 *
	 * @error 15507
	 * @param bool $expired expects boolean value for delete mode like explain above
	 * @return void
	 */
	public function purge($expired = true)
	{
		if (($files = glob($this->cachePath() . '*.' . xo_get(self::CACHE_EXTENSION))) !== false) {
			foreach ($files as $f) {
				if (is_file($f)) {
					$header = self::getHeaderContent($f);
					$delete = true;
					if ((bool)$expired) {
						$delete = self::hasExpired($f, $header[self::FILE_HEADER_KEY_LIFETIME]);
					}

					if ($delete) {
						unlink($f);
						unlink($this->cachePath() . $header[self::FILE_HEADER_KEY_FILENAME]);
					}
				}
			}
		}
		@clearstatcache();
	}


	protected function hasExpired($cacheFile, $expirationTime)
	{
		$age = time() - filectime($cacheFile);
		return ($age >= (int)$expirationTime);

	}

	/**
	 * make and return valid sha1 hashed file name from cache key
	 *
	 * @error 15508
	 * @param string $key expects the cache key name as string
	 * @return string
	 */
	protected function filename($key)
	{
		$image_extension = substr($key, strrpos($key, ".") + 1);
		return sha1(trim((string)$key)) . '.' . $image_extension;
	}

	protected function cachePath()
	{
		return xo_get(self::PATH, $this) . xo_get(self::SUBPATH);
	}

	protected function descriptorfilename($key)
	{
		return sha1(trim((string)$key)) . '.' . trim(xapp_get_option(self::CACHE_EXTENSION, $this), '.');
	}

	protected function headerContent($header_data = Array())
	{
		$header_string = implode(",", $header_data);
		return $header_string;
	}

	protected function getHeaderContent($file)
	{
		$header_string = file_get_contents($file);
		$header_array = explode(",", trim($header_string));
		$returned_array = array();
		foreach (self::$headerFormat as $n => $h_key) {
			$returned_array[$h_key] = $header_array[$n];
		}

		return $returned_array;
	}
}