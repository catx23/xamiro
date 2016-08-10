<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\File
 */

/***
 * Copy Collision Modus
 */
define('XAPP_XFILE_OVERWRITE_NONE', 1501);
define('XAPP_XFILE_OVERWRITE_ALL', 1502);
define('XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS', 1503);
define('XAPP_XFILE_OVERWRITE_IF_NEWER', 1504);

/***
 * Directory listing flags
 *
 * */
define('XAPP_XFILE_SHOW_ISREADONLY', 1601);
define('XAPP_XFILE_SHOW_ISDIR', 1602);
define('XAPP_XFILE_SHOW_OWNER', 1604);
define('XAPP_XFILE_SHOW_MIME', 1608);
define('XAPP_XFILE_SHOW_SIZE', 1616);
define('XAPP_XFILE_SHOW_PERMISSIONS', 1632);
define('XAPP_XFILE_SHOW_TIME', 1633);
define('XAPP_XFILE_SHOW_FOLDER_SIZE', 1634);
define('XAPP_XFILE_SHOW_HIDDEN', 1635);
define('XAPP_XFILE_SHOW_TYPE', 1636);
define('XAPP_XFILE_SHOW_MEDIA_INFO', 1637);


/**
 * Directory/File node field keys
 */
define('XAPP_NODE_FIELD_NAME', 'name');
define('XAPP_NODE_FIELD_PATH', 'path');
define('XAPP_NODE_FIELD_USER', 'user');
define('XAPP_NODE_FIELD_CHILDREN', 'children');
define('XAPP_NODE_FIELD_PERMISSIONS', 'permissions');
define('XAPP_NODE_FIELD_OWNER', 'owner');
define('XAPP_NODE_FIELD_READ_ONLY', 'readOnly');
define('XAPP_NODE_FIELD_SIZE', 'size');
define('XAPP_NODE_FIELD_SIZE_BYTES', 'sizeBytes');
define('XAPP_NODE_FIELD_MIME', 'mime');
define('XAPP_NODE_FIELD_TIME', 'modified');
define('XAPP_NODE_FIELD_IS_DIRECTORY', 'isDir');
define('XAPP_NODE_FIELD_IS_DIRTY', 'isDirty');
define('XAPP_NODE_FIELD_IS_NEW', 'isNew');
define('XAPP_NODE_FIELD_TYPE', 'fileType');
define('XAPP_NODE_FIELD_MEDIA_INFO', 'mediaInfo');




//xide specific
define('XAPP_NODE_FIELD_PARENT_ID', 'parentId');

define('XAPP_NODE_FIELD_BLOX', 'blox');

global $mmType;


class XApp_File_Utils
{

	const OPTION_RECURSIVE = 'recursive';
	const OPTION_TIMEOUT = 'timeout';
	const OPTION_DRYRUN = 'dryrun';
	const OPTION_CONFLICT_MODUS = 'overwriteModus';
	const OPTION_LOGGING_STRIP_BASE_PATH = 'stripBasePath';
	const OPTION_NEW_CHMOD = 'newFileMask';

	const OPTION_SIZE_LIMIT = "sizeLimit";
	const OPTION_CHUNK_SIZE = "chunkSize";
	const OPTION_TEMP_PATH = "tempPath";
	const OPTION_AS_ATTACHMENT = "asAttachment";
	const OPTION_TEST = "runTest";
	const OPTION_SEND = "send";
	const OPTION_DIR_LIST_FIELDS = "fields";
	const OPTION_DIR_LIST = "dirOptions";
	const OPTION_EMIT = "emit";

	const OPTION_INCLUDE_LIST = "includeList";
	const OPTION_EXCLUDE_LIST = "excludeList";

	const OPTION_RESIZE_TO = "width";
	const OPTION_PREVENT_CACHE = "preventCache";


	const GET_FILE_SIZE_LIMIT = '6M';   // File size limit for "get" method, in MB
	const GET_FILE_CHUNK_SIZE = '1M';     // Chunk size for "get" method, in MB

	/***
	 * @return array of scandir queries or reg-expressions
	 */
	public static function defaultExclusionPatterns()
	{
		return array(
				'.svn',
				'.git',
				'.idea'
		);
	}


	/***
	 *
	 * @return array|null
	 */
	public static function defaultInclusionPatterns()
	{
		return array(
				'*',
				'.*'    // if not, grep skips hidden files
		);
	}

	/***
	 * Track the very input directories as copyDirectoryEx is recursive
	 */
	protected static $_tmpSrcRootPath;
	protected static $_tmpDstRootPath;

	/**
	 *
	 *  Copies $srcDir into $dstDirectory
	 *
	 * It must be possible to use this function recursive!
	 * Must work with php5.2
	 *
	 * @param $srcDir : expects sanitized absolute directory
	 * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
	 * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
	 * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
	 * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
	 * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
	 * @param $success : track all copied items here
	 */
	public static function copyDirectoryEx(
			$srcDir,
			$dstDirectory,
			$options = Array(),
			$inclusionMask = Array(),
			$exclusionMask = Array(),
			&$error,
			&$success
	) {

		if (!self::$_tmpSrcRootPath) {
			self::$_tmpSrcRootPath = realpath($srcDir . DIRECTORY_SEPARATOR . '..');
		}
		if (!self::$_tmpDstRootPath) {
			self::$_tmpDstRootPath = realpath($dstDirectory . DIRECTORY_SEPARATOR . '..');
		}

		if (isset($options[self::OPTION_TIMEOUT])) {
			ini_set('max_execution_time', intval($options[self::OPTION_TIMEOUT]));
		}
		if (!isset($options[self::OPTION_CONFLICT_MODUS])) {
			$options[self::OPTION_CONFLICT_MODUS] = XAPP_XFILE_OVERWRITE_NONE;
		}
		if (!isset($options[self::OPTION_NEW_CHMOD])) {
			$options[self::OPTION_NEW_CHMOD] = '0777';
		}
		if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) {
			$options[self::OPTION_LOGGING_STRIP_BASE_PATH] = true;
		}

		if(is_object($exclusionMask)){
			$exclusionMask = array();
		}

		//$exclusionMask =[];
		$scanlist = XApp_Directory_Utils::getFilteredDirList( realpath($srcDir) .DIRECTORY_SEPARATOR , $inclusionMask, $exclusionMask);



		if ($scanlist === false) {
			$error[] = XAPP_TEXT_FORMATTED('CAN_NOT_READ_DIR', array($srcDir));
		} else {
			// Create dest dir if not exists
			if (!is_dir($dstDirectory)) {
				$mkres = @mkdir($dstDirectory);
				if (!$mkres) {
					$error[] = XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_DIRECTORY', array($dstDirectory));
					return;
				} else {
					if (!self::changeModeDirectory($dstDirectory, $options[self::OPTION_NEW_CHMOD])) {
						$error[] = XAPP_TEXT_FORMATTED(
								'COULD_NOT_CHANGE_DIRECTORY_MODE',
								array($options[self::OPTION_NEW_CHMOD], $dstDirectory)
						);
					}
				}
			}


			foreach ($scanlist as $direntry) {

				$targetFile = $dstDirectory . substr($direntry, strlen($srcDir));

				// Dir found
				if (is_dir($direntry)) {
					if ($options[self::OPTION_RECURSIVE]) {
						self::copyDirectoryEx(
								$direntry,
								$targetFile,
								$options,
								$inclusionMask,
								$exclusionMask,
								$error,
								$success
						);
					}
				} else {
					// File found
					if (self::singleFileCopy(
							$direntry,
							$targetFile,
							$options[self::OPTION_CONFLICT_MODUS],
							$success,
							$error
					)
					) {

						$success[] = XAPP_TEXT_FORMATTED('THE_FILE') . XAPP_TEXT_FORMATTED(
										'COPIED_OK',
										Array($srcDir, $dstDirectory)
								);

						if (!self::changeModeFile($targetFile, $options[self::OPTION_NEW_CHMOD])) {
							$error[] = XAPP_TEXT_FORMATTED(
									'COULD_NOT_CHANGE_FILE_MODE',
									array($options[self::OPTION_NEW_CHMOD], $dstDirectory)
							);
						}

					}
				}
			}
			$success[] = XAPP_TEXT_FORMATTED('DIRECTORY') . XAPP_TEXT_FORMATTED(
							'COPIED_OK',
							Array($srcDir, $dstDirectory)
					);
		}

		if ($options[self::OPTION_LOGGING_STRIP_BASE_PATH]) {
			self::StripBasePaths($error, $success);
		}
	}

	/**
	 *
	 *  Copies $srcDir into $dstDirectory
	 *
	 * It must be possible to use this function recursive!
	 * Must work with php5.2
	 *
	 * @param $srcDir : expects sanitized absolute directory
	 * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
	 * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
	 * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
	 * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
	 * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
	 * @param $success : track all copied items here
	 */

	public static function copyDirectory(
			$srcDir,
			$dstDirectory,
			$options = Array(),
			$inclusionMask = Array(),
			$exclusionMask = Array(),
			&$error,
			&$success
	) {

		// defaults
		if (!isset($options[self::OPTION_RECURSIVE])) {
			$options[self::OPTION_RECURSIVE] = true;
		}
		if (!isset($options[self::OPTION_TIMEOUT])) {
			$options[self::OPTION_TIMEOUT] = 60;
		}
		if (!isset($options[self::OPTION_CONFLICT_MODUS])) {
			$options[self::OPTION_CONFLICT_MODUS] = XAPP_XFILE_OVERWRITE_NONE;
		}
		if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) {
			$options[self::OPTION_LOGGING_STRIP_BASE_PATH] = true;
		}
		if (!isset($options[self::OPTION_NEW_CHMOD])) {
			$options[self::OPTION_NEW_CHMOD] = '0777';
		}


		self::copyDirectoryEx($srcDir, $dstDirectory, $options, $inclusionMask, $exclusionMask, $error, $success);

	}

	/**
	 * Moves $srcDir into $dstDirectory
	 *
	 * @param $srcDir : expects sanitized absolute directory
	 * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
	 * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
	 * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
	 * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
	 * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
	 * @param $success : track all moved items here
	 */
	public static function moveDirectoryEx(
			$srcDir,
			$dstDirectory,
			$options = Array(),
			$inclusionMask = Array(),
			$exclusionMask = Array(),
			&$error,
			&$success
	) {
		if (!self::$_tmpSrcRootPath) {
			self::$_tmpSrcRootPath = realpath($srcDir . DIRECTORY_SEPARATOR . '..');
		}
		if (!self::$_tmpDstRootPath) {
			self::$_tmpDstRootPath = realpath($dstDirectory . DIRECTORY_SEPARATOR . '..');
		}
		// defaults
		if (!isset($options[self::OPTION_RECURSIVE])) {
			$options[self::OPTION_RECURSIVE] = true;
		}
		if (!isset($options[self::OPTION_TIMEOUT])) {
			$options[self::OPTION_TIMEOUT] = 60;
		}
		if (!isset($options[self::OPTION_CONFLICT_MODUS])) {
			$options[self::OPTION_CONFLICT_MODUS] = XAPP_XFILE_OVERWRITE_NONE;
		}
		if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) {
			$options[self::OPTION_LOGGING_STRIP_BASE_PATH] = true;
		}
		if (!isset($options[self::OPTION_NEW_CHMOD])) {
			$options[self::OPTION_NEW_CHMOD] = '0777';
		}

		// First copy
		self::copyDirectoryEx($srcDir, $dstDirectory, $options, $inclusionMask, $exclusionMask, $error, $success);

		// If everything went ok, delete source
		if (Count($error) == 0) {
			self::deleteDirectoryEx($srcDir, $options, $inclusionMask, $exclusionMask, $error, $success);
		} else {    // If not, notify and delete destination folder (undo copy)
			$error[] = XAPP_TEXT_FORMATTED('CAN_NOT_MOVE', array($srcDir));
			self::deleteDirectoryEx($dstDirectory, $options, $inclusionMask, $exclusionMask, $error, $success);
		}

		if ($options[self::OPTION_LOGGING_STRIP_BASE_PATH]) {
			self::StripBasePaths($error, $success);
		}

	}

	/**
	 *  Removes directory and all its contents
	 *
	 * @param $path : expects sanitized absolute directory
	 * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, dryrun (true/false) - don't delete for real, default false]
	 * @param array $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
	 * @param array $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
	 * @param $error : a pointer to an array reference
	 * @param $success : track all copied items here
	 */
	public static function deleteDirectoryEx(
			$path,
			$options = Array(),
			$inclusionMask = Array(),
			$exclusionMask = Array(),
			&$error,
			&$success
	) {
		if (!self::$_tmpSrcRootPath) {
			self::$_tmpSrcRootPath = realpath($path . DIRECTORY_SEPARATOR . '..');
		}
		if (!self::$_tmpDstRootPath) {
			self::$_tmpDstRootPath = realpath($path . DIRECTORY_SEPARATOR . '..');
		}

		// defaults
		if (!isset($options[self::OPTION_RECURSIVE])) {
			$options[self::OPTION_RECURSIVE] = true;
		}
		if (!isset($options[self::OPTION_TIMEOUT])) {
			$options[self::OPTION_TIMEOUT] = 60;
		}
		if (!isset($options[self::OPTION_DRYRUN])) {
			$options[self::OPTION_DRYRUN] = false;
		}
		if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) {
			$options[self::OPTION_LOGGING_STRIP_BASE_PATH] = true;
		}
		ini_set('max_execution_time', intval($options[self::OPTION_TIMEOUT]));


		$scanlist = XApp_Directory_Utils::getFilteredDirList($path, $inclusionMask, $exclusionMask);
		if ($scanlist === false) {
			$error[] = XAPP_TEXT_FORMATTED('CAN_NOT_READ_DIR', array($path));
		} else {
			foreach ($scanlist as $direntry) {
				// Dir found
				if (is_dir($direntry)) {
					if ($options[self::OPTION_RECURSIVE]) {
						self::deleteDirectoryEx($direntry, $options, $inclusionMask, $exclusionMask, $error, $success);
					}
				} else {
					// File found
					if (!$options[self::OPTION_DRYRUN]) {
						if (!@unlink($direntry)) {
							$error[] = XAPP_TEXT_FORMATTED('THE_FILE') . " " . $direntry . " " . XAPP_TEXT_FORMATTED(
											'HAS_NOT_BEEN_DELETE'
									);
						} else {
							$success[] = XAPP_TEXT_FORMATTED('THE_FILE') . " " . $direntry . " " . XAPP_TEXT_FORMATTED(
											'HAS_BEEN_DELETED'
									);
						}
					} else {
						$success[] = XAPP_TEXT_FORMATTED('SIMULATED') . " " . XAPP_TEXT_FORMATTED(
										'THE_FILE'
								) . " " . $direntry . " " . XAPP_TEXT_FORMATTED('HAS_BEEN_DELETED');
					}
				}
			}

			// Remove dir
			if (!$options[self::OPTION_DRYRUN]) {
				if (!@rmdir($path)) {
					$error[] = XAPP_TEXT_FORMATTED('THE_FOLDER') . " " . $path . " " . XAPP_TEXT_FORMATTED(
									'HAS_NOT_BEEN_DELETE'
							);
				} else {
					$success[] = XAPP_TEXT_FORMATTED('THE_FOLDER') . " " . $path . " " . XAPP_TEXT_FORMATTED(
									'HAS_BEEN_DELETED'
							);
				}
			} else {
				$success[] = XAPP_TEXT_FORMATTED('SIMULATED') . " " . XAPP_TEXT_FORMATTED(
								'THE_FOLDER'
						) . " " . $path . " " . XAPP_TEXT_FORMATTED('HAS_BEEN_DELETED');
			}

		}
		if ($options[self::OPTION_LOGGING_STRIP_BASE_PATH]) {
			self::StripBasePaths($error, $success);
		}
	}


	/**
	 *  Removes a file
	 * @param $path : expects sanitized absolute directory
	 * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, dryrun (true/false) - don't delete for real, default false]
	 * @param $error : a pointer to an array reference
	 * @param $success : track all copied items here
	 */
	public static function deleteFile($path, $options = Array(), &$error, &$success)
	{
		if (!self::$_tmpSrcRootPath) {
			self::$_tmpSrcRootPath = realpath($path . DIRECTORY_SEPARATOR . '..');
		}

		//defaults
		if (!isset($options[self::OPTION_LOGGING_STRIP_BASE_PATH])) {
			$options[self::OPTION_LOGGING_STRIP_BASE_PATH] = true;
		}

		if (file_exists($path)) {
			if (!@unlink($path)) {
				$error[] = XAPP_TEXT_FORMATTED('THE_FILE') . " " . $path . " " . XAPP_TEXT_FORMATTED(
								'HAS_NOT_BEEN_DELETE'
						);
			} else {
				$success[] = XAPP_TEXT_FORMATTED('THE_FILE') . " " . $path . " " . XAPP_TEXT_FORMATTED(
								'HAS_BEEN_DELETED'
						);
			}
		} else {

		}
		if ($options[self::OPTION_LOGGING_STRIP_BASE_PATH]) {
			self::StripBasePaths($error, $success);
		}
	}

	/***
	 * @param $path
	 * @param $content
	 * @throws Xapp_Util_Exception_Storage
	 */
	public static function set($path, $content,$from=null)
	{
		xapp_import('xapp.Utils.Strings');

		$realPath = '' . $path;
		$return = null;
		$error = array();
		$autoCreate = false;

		if ($content) {
			if (!file_exists($realPath)) {

				//$this->mkfile(dirname($path),basename($realPath),'');
			}
			if (file_exists($realPath)) {

				if (!is_writeable($realPath)) {
					throw new Xapp_Util_Exception_Storage(
							vsprintf('File: %s is not writable', array(basename($realPath))), 1640102
					);
				} else {


					if( XApp_Utils_Strings::startsWith($content,'data:image/')){
						$ifp = fopen($realPath, "wb");
						$data = explode(',', $content);
						fwrite($ifp, base64_decode($data[1]));
						fclose($ifp);
						return true;
					}


					//write out
					$fp = fopen($realPath, "w");
					fputs($fp, $content);
					fclose($fp);
					clearstatcache(true, $realPath);
					$return = true;



					//tell plugins
					if($from) {
						xcom_event( XC_OPERATION_WRITE_STR,'',
								array(
										XAPP_EVENT_KEY_PATH     => $realPath,
										XAPP_EVENT_KEY_REL_PATH => $path,
										XAPP_EVENT_KEY_CONTENT  => &$content
								),$from );
					}

				}

			} else {
				throw new Xapp_Util_Exception_Storage(
						'unable to write storage to file  :  ' . $path . ' at : ' . $realPath, 1640104
				);
			}
		} else {
			throw new Xapp_Util_Exception_Storage('unable to save to storage, empty content', 1640401);
		}

		if ($return === false) {
			throw new Xapp_Util_Exception_Storage('unable to save to storage', 1640401);
		}
		return true;
	}


	/**
	 * @param $basePath : absolute path on disc
	 * @param $relativePath : can be file path within the baseDirectory or a directory. In case its a directory, it must zip it and send it back as attachment
	 * @param $options : options array
	 * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression. This is a blacklist.
	 */
	public static function get($basePath, $relativePath, $options = Array(), $exclusionMask = Array())
	{
		try {
			ini_set( 'memory_limit', '128M' );

			if ( ! isset( $options[ self::OPTION_SIZE_LIMIT ] ) ) {
				$options[ self::OPTION_SIZE_LIMIT ] = self::GET_FILE_SIZE_LIMIT;
			}
			if ( ! isset( $options[ self::OPTION_CHUNK_SIZE ] ) ) {
				$options[ self::OPTION_CHUNK_SIZE ] = self::GET_FILE_CHUNK_SIZE;
			}
			if ( ! isset( $options[ self::OPTION_TEMP_PATH ] ) ) {
				$options[ self::OPTION_TEMP_PATH ] = sys_get_temp_dir();
			}
			if ( ! isset( $options[ self::OPTION_AS_ATTACHMENT ] ) ) {
				$options[ self::OPTION_AS_ATTACHMENT ] = false;
			}
			if ( ! isset( $options[ self::OPTION_TEST ] ) ) {
				$options[ self::OPTION_TEST ] = false;
			}
			if ( ! isset( $options[ self::OPTION_SEND ] ) ) {
				$options[ self::OPTION_SEND ] = true;
			}
			if ( ! isset( $options[ self::OPTION_PREVENT_CACHE ] ) ) {
				$options[ self::OPTION_PREVENT_CACHE ] = false;
			}


			$target_file = XApp_Directory_Utils::normalizePath( $basePath . $relativePath );
			if ( is_dir( $target_file ) ) {  // ZIP dir contents
				$error   = Array();
				$success = Array();

				$tempFile = tempnam( $options[ self::OPTION_TEMP_PATH ], 'getZIP' );
				XApp_Directory_Utils::zipDir(
						$basePath . $relativePath,
						$tempFile,
						self::defaultInclusionPatterns(),
						$exclusionMask,
						$error,
						$success
				);
				$target_file = $tempFile;
				$mime        = "application/zip";
			} else {
				$mime = self::getMime( $target_file );
			}

			if ( $options[ self::OPTION_SEND ] === true ) {

				if ( ! $options[ self::OPTION_TEST ] ) {

					self::sendHeader(
							$mime,
							( $options[ self::OPTION_AS_ATTACHMENT ] ? $target_file : '' ),
							basename( $target_file )
					);
					if ( strpos( $mime, "text" ) !== false ) {
						if ( substr_count( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) ) {
							ob_start( "ob_gzhandler" );
						} else {
							ob_start();
						}
					}
				}

				/**
				 * take care about resumed downloads
				 */
				$file_size = filesize( $target_file );
				if ( isset( $_SERVER['HTTP_RANGE'] ) ) {

					$file = @fopen( $target_file, "rb" );
					list( $size_unit, $range_orig ) = explode( '=', $_SERVER['HTTP_RANGE'], 2 );
					if ( $size_unit == 'bytes' ) {
						//multiple ranges could be specified at the same time, but for simplicity only serve the first range
						//http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
						if ( strpos( $range_orig, ',' ) !== false ) {
							list( $range, $extra_ranges ) = explode( ',', $range_orig, 2 );
						} else {
							$range = $range_orig;
						}
					} else {
						$range = '';
						header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
						exit;
					}
					//figure out download piece from range (if set)
					list( $seek_start, $seek_end ) = explode( '-', $range, 2 );

					//set start and end based on range (if set), else set defaults
					//also check for invalid ranges.
					$seek_end   = ( empty( $seek_end ) ) ? ( $file_size - 1 ) : min( abs( intval( $seek_end ) ),
							( $file_size - 1 ) );
					$seek_start = ( empty( $seek_start ) || $seek_end < abs( intval( $seek_start ) ) ) ? 0 : max(
							abs( intval( $seek_start ) ),
							0
					);

					//Only send partial content header if downloading a piece of the file (IE workaround)
					if ( $seek_start > 0 || $seek_end < ( $file_size - 1 ) ) {
						header( 'HTTP/1.1 206 Partial Content' );
						header( 'Content-Range: bytes ' . $seek_start . '-' . $seek_end . '/' . $file_size );
						header( 'Content-Length: ' . ( $seek_end - $seek_start + 1 ) );
					} else {
						header( "Content-Length: $file_size" );
					}

					header( 'Accept-Ranges: bytes' );

					set_time_limit( 0 );
					fseek( $file, $seek_start );

					while ( ! feof( $file ) ) {
						print( @fread( $file, 1024 * 8 ) );
						ob_flush();
						flush();
						if ( connection_status() != 0 ) {
							@fclose( $file );
							exit;
						}
					}

					// file save was a success
					@fclose( $file );

				} else {

					/**
					 * download as attachment
					 */

					// send streamed or complete
					$limit = intval( $options[ self::OPTION_SIZE_LIMIT ] ) * 1024 * 1024; // Convert limit to bytes
					if ( filesize( $target_file ) > $limit ) {
						// chunk file
						$chunk_size = intval($options[ self::OPTION_CHUNK_SIZE ]) * 1024 * 1024; // Convert chunk size to bytes
						$handle     = fopen( $target_file, 'rb' );
						while ( ! feof( $handle ) ) {
							$buffer = fread( $handle, $chunk_size );
							echo $buffer;
							set_time_limit( 50 );
							ob_flush();
							flush();
						}
						fclose( $handle );

					} else {
						/**
						 * send direct as attachment
						 */
						self::sendHeader(
								$mime,
								( $options[ self::OPTION_AS_ATTACHMENT ] ? $target_file : '' ),
								basename( $target_file )
						);
						$content = file_get_contents( $target_file );
						echo $content;
					}

				}


			} else {

				/**
				 * deal with resize!
				 */
				if ( isset( $options[ self::OPTION_RESIZE_TO ] ) &&
						//check we have some image tools
						(
								( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) || //GD is fine , otherwise try imagick
								extension_loaded( 'imagick' )
						)
						&& strpos( $target_file, '.gif' ) == false  &&//skip gifs
						strpos( $mime, 'image' ) !== false //must be image
				) {

					xapp_import( 'xapp.Image.Utils' );
					xapp_import( 'xapp.Directory.Utils' );
					$cacheImage = false;
					$options = array(
							XApp_Image_Utils::OPTION_WIDTH         => $options[ self::OPTION_RESIZE_TO ],
							XApp_Image_Utils::OPTION_PREVENT_CACHE => self::OPTION_PREVENT_CACHE
					);

					//enable caching if possible
					$cacheDir = XApp_Directory_Utils::getCacheDirectory( true, 'xcom' );
					if ( $cacheDir != null && XApp_Directory_Utils::is_writable( $cacheDir ) ) {
						$cacheImage                                    = true;
						$options[ XApp_Image_Utils::OPTION_CACHE_DIR ] = $cacheDir;
						XApp_Image_Utils::$cacheDir                    = $cacheDir;
					}

					$job = array(
							XApp_Image_Utils::IMAGE_OPERATION   => XApp_Image_Utils::OPERATION_RESIZE,
							XApp_Image_Utils::OPERATION_OPTIONS => $options
					);

					$jobs   = array();
					$jobs[] = $job;


					$errors = array();
					XApp_Image_Utils::execute( $target_file,
							null,
							json_encode( $jobs ),
							$errors,
							false,
							$cacheImage,
							true );

					exit;
				}


				// send streamed or complete
				$limit = intval( $options[ self::OPTION_SIZE_LIMIT ] ) * 1024 * 1024; // Convert limit to bytes
				if ( filesize( $target_file ) > $limit ) {

					self::sendHeader( $mime, false, basename( $target_file ) );

					// chunk file
					$chunk_size = intval( $options[ self::OPTION_CHUNK_SIZE ] ) * 1024 * 1024; // Convert chunk size to bytes
					$handle     = fopen( $target_file, 'rb' );
					while ( ! feof( $handle ) ) {
						$buffer = fread( $handle, $chunk_size );
						echo $buffer;
						set_time_limit( 50 );
						ob_flush();
						flush();
					}
					fclose( $handle );
				} else {
					if ( ! file_exists( $target_file ) ) {
						xapp_clog( '' . $target_file . ' doesnt exists' );
					}
					self::sendHeader( $mime, false, basename( $target_file ) );
					/*header("Content-Transfer-Encoding: binary");*/
					//header("Transfer-Encoding: binary");
					header("Content-Length: " . filesize($target_file));
					return file_get_contents( $target_file );
				}
			}

		}catch (Exception $e){
			echo "error reading file:" . $e->getMessage();
		}
	}


	/**
	 * Returns the MIME content type of file.
	 * @param  string
	 * @return string
	 */
	public static function mimeFromString($data)
	{
		if (extension_loaded('fileinfo') && preg_match(
						'#^(\S+/[^\s;]+)#',
						finfo_buffer(finfo_open(FILEINFO_MIME), $data),
						$m
				)
		) {
			return $m[1];

		} elseif (strncmp($data, "\xff\xd8", 2) === 0) {
			return 'image/jpeg';

		} elseif (strncmp($data, "\x89PNG", 4) === 0) {
			return 'image/png';

		} elseif (strncmp($data, "GIF", 3) === 0) {
			return 'image/gif';

		} else {
			return 'application/octet-stream';
		}
	}

	/**
	 * Returns the mimetype for a file
	 *
	 * @param $filepath :   full path for the file to check
	 * @return bool|mixed|string    :   mime string if success, false if not
	 */
	public static function getMime($filepath)
	{
		if (empty($filepath) || !file_exists($filepath)) {
			return false;
		}
		$mime = "";
		if (function_exists("finfo_file")) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $filepath);
			finfo_close($finfo);

			//php bug
			if (strpos($filepath, '.css') !== false) {
				$mime = 'text/css';
			}

		} else {
			if (function_exists("mime_content_type")) {
				$mime = mime_content_type($filepath);
			} else {
				if (!stristr(ini_get("disable_functions"), "shell_exec")) {  // Unix systems
					$file = escapeshellarg($filepath);
					$mime = shell_exec("file -bi " . $file);
					$mime_arr = explode(";", $mime);
					$mime = $mime_arr[0];
				}
			}
		}

		if (empty($mime)) {
			xapp_import("/File/mime_types.php");
			$mimes = mime_types();
			$parts = explode(".", $filepath);
			$ext = end($parts);
			if (array_key_exists($ext, $mimes)) {
				$mime = $mimes[$ext];
			}
			if (isset($mime)) {
				return $mime;
			} else {
				return false;
			}
		}
		return $mime;
	}

	/**
	 * Guesses the mimetype for a file name
	 *
	 * @param $filepath :   file name
	 * @return bool|mixed|string    :   mime string if success, false if not
	 */
	public static function guessMime($filepath)
	{
		if (empty($filepath)) {
			return false;
		}
		$mime = "";
		xapp_import("/File/mime_types.php");
		$mimes = mime_types();
		$parts = explode(".", $filepath);
		$ext = end($parts);
		if (array_key_exists($ext, $mimes)) {
			$mime = $mimes[$ext];
		}

		if (isset($mime)) {
			return $mime;
		}
		return $mime;
	}

	/***
	 * @param string $mime
	 * @param string $attachment
	 * @param string $rename_attachment
	 */
	public static function sendHeader($mime = "", $attachment = "", $rename_attachment = "")
	{
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		if ($mime != "") {
			header("Content-Type: " . $mime);
		}


		if ($attachment != "") {

			$attachment = XApp_Directory_Utils::normalizePath($attachment);
			$attachment_name = ($rename_attachment != '' ? $rename_attachment : basename($attachment));
			$attachment_name = urlencode(XApp_SystemTextEncoding::toUTF8($attachment_name));
			$attachment_name = str_replace('+', '%20', $attachment_name);

			if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false ||
					preg_match('/ MSIE /',$_SERVER['HTTP_USER_AGENT'])  ||
					preg_match('/ WebKit /',$_SERVER['HTTP_USER_AGENT'])) {
				header("Content-Disposition: attachment; filename=" . $attachment_name);
			}else{
				header("Content-Disposition: attachment; filename*=UTF-8'" . $attachment_name . ";");
			}
			header("Content-Transfer-Encoding: binary");
			if(@is_readable($attachment) &&  file_exists($attachment)) {
				header("Content-Length: " . filesize($attachment));
			}
		}
	}


	/***
	 * @param $error
	 * @param $success
	 */
	private static function StripBasePaths(&$error, &$success)
	{
		$error = str_replace(Array(self::$_tmpSrcRootPath, self::$_tmpDstRootPath), '', $error);
		$success = str_replace(Array(self::$_tmpSrcRootPath, self::$_tmpDstRootPath), '', $success);
	}


	/***
	 * @param $source
	 * @param $destination
	 * @param $overwriteModus
	 * @param $success
	 * @param $error
	 * @return bool
	 */
	private static function singleFileCopy($source, $destination, $overwriteModus, &$success, &$error)
	{


		if ((is_file($destination)) && (!($overwriteModus == XAPP_XFILE_OVERWRITE_ALL))) {
			if ($overwriteModus == XAPP_XFILE_OVERWRITE_NONE) {
				$error[] = XAPP_TEXT_FORMATTED('FILE_EXISTS') . ": " . $destination;
				return false;
			} elseif ($overwriteModus == XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS) {
				if (@filesize($source) == @filesize($destination)) {
					$error[] = XAPP_TEXT_FORMATTED('FILES_NOT_COPIED_SAME_SIZE', Array($source, $destination));
					return false;
				}
			} elseif ($overwriteModus == XAPP_XFILE_OVERWRITE_IF_NEWER) {
				if (@filemtime($source) <= @filemtime($destination)) {
					$error[] = XAPP_TEXT_FORMATTED('FILES_NOT_COPIED_NOT_NEWER', Array($source, $destination));
					return false;
				}
			}
		}
		if (@copy($source, $destination) === false) {
			$error[] = XAPP_TEXT_FORMATTED('UNKNOW_ERROR_WHILST_COPY') . $destination;
			return false;
		}
		$success[] = XAPP_TEXT_FORMATTED('THE_FILE') . " " . $source . " " . XAPP_TEXT_FORMATTED('HAS_BEEN_COPIED');
		return true;
	}

	/**
	 * @param $file
	 * @return array|bool
	 */
	public static function get_file_ownership($file)
	{
		if (empty($file) || !file_exists($file)) {
			return false;
		}

		$stat = stat($file);
		if ($stat) {
			if (function_exists('posix_getgrgid') && function_exists('posix_getpwuid')) {
				$group = posix_getgrgid($stat[5]);
				$user = posix_getpwuid($stat[4]);
				return compact('user', 'group');
			}
		}
		return false;
	}


	public static function getFileTime($path)
	{
		if (file_exists($path)) {
			$atts = stat(realpath($path));
			return $atts[9];
		}
		return 'nodate';
	}

	public static function getMMType(){

		global $mmType;

		if(!$mmType){

			xapp_import('xapp/externs/vendor/autoload.php');


			$TypeClz  = 'mm\\Mime\\Type';
			$Type = new $TypeClz();

			$Type::config('glob', array(
					'adapter' => 'Freedesktop',
					'file' => realpath(XAPP_BASEDIR . '/externs/vendor/davidpersson/mm/data/glob.db')
			));

			if(extension_loaded('fileinfo')) {
				$Type::config('magic', array(
					'adapter' => 'Fileinfo'
				));
			}


			$MediaClz  = 'mm\\Media\\Info';
			$Media = new $MediaClz();

			if(extension_loaded( 'imagick')) {
				$Media::config(
						array(
								'image' => array('ImageBasic', 'Imagick')
						)
				);
			}else{
				$Media::config(
						array(
								'image' => array('ImageBasic')
						)
				);
			}

			$mmType = array(
					'type'=>$Type,
					'media' => $Media
			);

		}

		return $mmType;
	}

	/**
	 * Return generic file type
	 * @param $path
	 *
	 * @return string
	 */
	public static function getFileType($path)
	{

		if(is_dir($path)){
			return 'folder';
		}

		if (version_compare(PHP_VERSION, '5.4.0', '>')) {
			$mm = self::getMMType();
			if ($mm) {

				$type = $mm['type'];
				$FileType = $type::guessName($path); // returns 'image'
				if ($FileType) {
					return $FileType;
				}
			}
		}
		return 'unknown';
	}

	/**
	 * Return generic file type
	 * @param $path
	 *
	 * @return string
	 */
	public static function getMediaInfo($path)
	{

		if(is_dir($path)){
			return 'folder';
		}

		$mm = self::getMMType();

		if($mm){

			$media = $mm['media'];

			$info = $media::factory(array('source' => $path));

			if($info){
				$width      = $info->width();
				$height      = $info->height();

				$result = $width . ' x ' . $height;
				return $result;

			}


		}
		return 'unknown';
	}

	/***
	 * @param $path
	 * @return int|string
	 */
	public static function get_file_permissions($path)
	{
		$fPerms = @fileperms($path);
		if ($fPerms !== false) {
			$fPerms = substr(decoct($fPerms), 1);
		} else {
			$fPerms = '0000';
		}
		return $fPerms;
	}


	/**
	 * @param $path
	 * @param $chmodValue
	 * @return bool         : change with success
	 */
	public static function changeModeFile($path, $chmodValue)
	{
		if (isSet($chmodValue) && $chmodValue != "") {
			$chmodValue = octdec(ltrim($chmodValue, "0"));
			return @chmod($path, $chmodValue);
		} else {
			return true;
		}
	}

	/**
	 * @param $path
	 * @param $chmodValue
	 * @return bool         : change with success
	 */
	public static function changeModeDirectory($path, $chmodValue)
	{
		if (isSet($chmodValue) && $chmodValue != "") {
			$dirMode = octdec(ltrim($chmodValue, "0"));
			if ($dirMode & 0400) {
				$dirMode |= 0100;
			} // User is allowed to read, allow to list the directory
			if ($dirMode & 0040) {
				$dirMode |= 0010;
			} // Group is allowed to read, allow to list the directory
			if ($dirMode & 0004) {
				$dirMode |= 0001;
			} // Other are allowed to read, allow to list the directory
			return @chmod($path, $dirMode);
		} else {
			return true;
		}
	}

	/***
	 * @param $path
	 * @return string
	 */
	public static function createEmptyFile($path)
	{
		if ($path == "") {
			return XAPP_TEXT('INVALID_FILE_NAME');
		}

		if (file_exists($path)) {
			return XAPP_TEXT('FILE_EXISTS');
		}
		if (!is_writeable(dirname($path))) {

			return XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE', array(dirname($path)), 55100);
		}
		$fp = fopen($path, "w");
		if ($fp) {
			fclose($fp);
			return 'OK';
		} else {
			return XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_FILE', array(basename($path)), 55100);
		}
	}

	/***
	 * @param $path
	 * @return string
	 */
	public static function mkDir($path, $mask = 0755, $recursive = true)
	{
		if ($path == "") {
			return XAPP_TEXT('INVALID_FILE_NAME');
		}

		if (file_exists($path)) {
			return XAPP_TEXT_FORMATTED('DIRECTORY_EXISTS', array(dirname($path)), 55100);
		}

		if (!is_writeable(dirname($path)) && $recursive === false) {

			return XAPP_TEXT_FORMATTED('DIRECTORY_NOT_WRITEABLE', array(dirname($path)), 55100);
		}

		$res = @mkdir($path, $mask, $recursive);

		if (!is_dir($path) || $res == false) {
			return XAPP_TEXT_FORMATTED('COULD_NOT_CREATE_DIRECTORY', array(dirname($path)), 55100);
		}

		return true;
	}

	/**
	 * Format to human readable size
	 * @param $bytes
	 * @return string
	 */
	public static function formatSizeUnits($bytes)
	{
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		} elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		} elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		} elseif ($bytes > 1) {
			$bytes = $bytes . ' bytes';
		} elseif ($bytes == 1) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	public static function get_allowed_mime_types()
	{
		return array(
			// Image formats.
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif' => 'image/gif',
				'download' => 'application/rar',
				'png' => 'image/png',
				'bmp' => 'image/bmp',
				'tif|tiff' => 'image/tiff',
				'ico' => 'image/x-icon',
			// Video formats.
				'asf|asx' => 'video/x-ms-asf',
				'wmv' => 'video/x-ms-wmv',
				'wmx' => 'video/x-ms-wmx',
				'wm' => 'video/x-ms-wm',
				'avi' => 'video/avi',
				'divx' => 'video/divx',
				'flv' => 'video/x-flv',
				'mov|qt' => 'video/quicktime',
				'mpeg|mpg|mpe' => 'video/mpeg',
				'mp4|m4v' => 'video/mp4',
				'ogv' => 'video/ogg',
				'webm' => 'video/webm',
				'mkv' => 'video/x-matroska',
				'3gp|3gpp' => 'video/3gpp', // Can also be audio
				'3g2|3gp2' => 'video/3gpp2', // Can also be audio
			// Text formats.
				'txt|asc|c|cc|h|srt' => 'text/plain',
				'csv' => 'text/csv',
				'tsv' => 'text/tab-separated-values',
				'ics' => 'text/calendar',
				'rtx' => 'text/richtext',
				'css' => 'text/css',
				'htm|html' => 'text/html',
				'vtt' => 'text/vtt',
				'dfxp' => 'application/ttaf+xml',
			// Audio formats.
				'mp3|m4a|m4b' => 'audio/mpeg',
				'ra|ram' => 'audio/x-realaudio',
				'wav' => 'audio/wav',
				'ogg|oga' => 'audio/ogg',
				'mid|midi' => 'audio/midi',
				'wma' => 'audio/x-ms-wma',
				'wax' => 'audio/x-ms-wax',
				'mka' => 'audio/x-matroska',
			// Misc application formats.
				'rtf' => 'application/rtf',
				'js' => 'application/javascript',
				'pdf' => 'application/pdf',
				'swf' => 'application/x-shockwave-flash',
				'class' => 'application/java',
				'tar' => 'application/x-tar',
				'zip' => 'application/zip',
				'gz|gzip' => 'application/x-gzip',
				'rar' => 'application/rar',
				'7z' => 'application/x-7z-compressed',
				'exe' => 'application/x-msdownload',
			// MS Office formats.
				'doc' => 'application/msword',
				'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
				'wri' => 'application/vnd.ms-write',
				'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
				'mdb' => 'application/vnd.ms-access',
				'mpp' => 'application/vnd.ms-project',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
				'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
				'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
				'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
				'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
				'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
				'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
				'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
				'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
				'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
				'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
				'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
				'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
				'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
				'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
				'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
				'oxps' => 'application/oxps',
				'xps' => 'application/vnd.ms-xpsdocument',
			// OpenOffice formats.
				'odt' => 'application/vnd.oasis.opendocument.text',
				'odp' => 'application/vnd.oasis.opendocument.presentation',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
				'odg' => 'application/vnd.oasis.opendocument.graphics',
				'odc' => 'application/vnd.oasis.opendocument.chart',
				'odb' => 'application/vnd.oasis.opendocument.database',
				'odf' => 'application/vnd.oasis.opendocument.formula',
			// WordPerfect formats.
				'wp|wpd' => 'application/wordperfect',
			// iWork formats.
				'key' => 'application/vnd.apple.keynote',
				'numbers' => 'application/vnd.apple.numbers',
				'pages' => 'application/vnd.apple.pages',
		);
	}

	/**
	 * Sanitizes a filename, replacing whitespace with dashes.
	 *
	 * Removes special characters that are illegal in filenames on certain
	 * operating systems and special characters requiring special escaping
	 * to manipulate at the command line. Replaces spaces and consecutive
	 * dashes with a single dash. Trims period, dash and underscore from beginning
	 * and end of filename.
	 *
	 * @since 2.1.0
	 *
	 * @param string $filename The filename to be sanitized
	 * @return string The sanitized filename
	 */
	public static function sanitize_file_name($filename)
	{
		xapp_import('xapp.Utils.Strings');

		$filename_raw = $filename;
		$special_chars = array(
				"?",
				"[",
				"]",
				"/",
				"\\",
				"=",
				"<",
				">",
				":",
				";",
				",",
				"'",
				"\"",
				"&",
				"$",
				"#",
				"*",
				"(",
				")",
				"|",
				"~",
				"`",
				"!",
				"{",
				"}",
				chr(0)
		);
		/**
		 * Filter the list of characters to remove from a filename.
		 *
		 * @param array $special_chars Characters to remove.
		 * @param string $filename_raw Filename as it was passed into sanitize_file_name().
		 *
		 */
		$filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
		$filename = str_replace($special_chars, '', $filename);
		$filename = str_replace(array('%20', '+'), '-', $filename);
		$filename = preg_replace('/[\s-]+/', '-', $filename);
		$filename = trim($filename, '.-_');

		// Split the filename into a base and extension[s]
		$parts = explode('.', $filename);

		// Process multiple extensions
		$filename = array_shift($parts);
		$extension = array_pop($parts);
		$mimes = self::get_allowed_mime_types();
		$ignoreMime = true;

		/*
		 * Loop over any intermediate extensions. Postfix them with a trailing underscore
		 * if they are a 2 - 5 character long alpha string not in the extension whitelist.
		 */
		foreach ((array)$parts as $part) {
			$filename .= '.' . $part;

			if (preg_match("/^[a-zA-Z]{2,5}\d?$/", $part)) {
				$allowed = $ignoreMime;
				foreach ($mimes as $ext_preg => $mime_match) {
					$ext_preg = '!^(' . $ext_preg . ')$!i';
					if (preg_match($ext_preg, $part)) {
						$allowed = true;
						break;
					}
				}
				if (!$allowed) {
					$filename .= '_';
				}
			}
		}
		$filename .= '.' . $extension;
		return $filename;
	}

	/**
	 * Get a filename that is sanitized and unique for the given directory.
	 *
	 * If the filename is not unique, then a number will be added to the filename
	 * before the extension, and will continue adding numbers until the filename is
	 * unique.
	 *
	 * The callback is passed three parameters, the first one is the directory, the
	 * second is the filename, and the third is the extension.
	 *
	 * @param string $dir Directory.
	 * @param string $filename File name.
	 * @param callback $unique_filename_callback Callback. Default null.
	 * @return string New filename, if given wasn't unique.
	 */
	public static function unique_filename($dir, $filename, $unique_filename_callback = null)
	{
		// Sanitize the file name before we begin processing.
		$filename = self::sanitize_file_name($filename);

		// Separate the filename into a name and extension.
		$info = pathinfo($filename);
		$ext = !empty($info['extension']) ? '.' . $info['extension'] : '';
		$name = basename($filename, $ext);

		// Edge case: if file is named '.ext', treat as an empty name.
		if ($name === $ext) {
			$name = '';
		}

		/*
		 * Increment the file number until we have a unique file to save in $dir.
		 * Use callback if supplied.
		 */
		if ($unique_filename_callback && is_callable($unique_filename_callback)) {
			$filename = call_user_func($unique_filename_callback, $dir, $name, $ext);
		} else {
			$number = '';

			// Change '.ext' to lower case.
			if ($ext && strtolower($ext) != $ext) {
				$ext2 = strtolower($ext);
				$filename2 = preg_replace('|' . preg_quote($ext) . '$|', $ext2, $filename);

				// Check for both lower and upper case extension or image sub-sizes may be overwritten.
				while (file_exists($dir . "/$filename") || file_exists($dir . "/$filename2")) {
					$new_number = $number + 1;
					$filename = str_replace("$number$ext", "$new_number$ext", $filename);
					$filename2 = str_replace("$number$ext2", "$new_number$ext2", $filename2);
					$number = $new_number;
				}
				return $filename2;
			}

			while (file_exists($dir . "/$filename")) {
				if ('' == "$number$ext") {
					$filename = $filename . ++$number . $ext;
				} else {
					$filename = str_replace("$number$ext", ++$number . $ext, $filename);
				}
			}
		}

		return $filename;
	}

	/**
	 * Calculates and compares the MD5 of a file to its expected value.
	 *
	 * @param string $filename The filename to check the MD5 of.
	 * @param string $expected_md5 The expected MD5 of the file, either a base64 encoded raw md5, or a hex-encoded md5
	 * @return bool|object XApp_Error on failure, true on success, false when the MD5 format is unknown/unexpected
	 */
	public static function verify_file_md5($filename, $expected_md5)
	{

		xapp_import('xapp.Commons.Error');

		if (32 == strlen($expected_md5)) {
			$expected_raw_md5 = pack('H*', $expected_md5);
		} elseif (24 == strlen($expected_md5)) {
			$expected_raw_md5 = base64_decode($expected_md5);
		} else {
			return false;
		} // unknown format

		$file_md5 = md5_file($filename, true);

		if ($file_md5 === $expected_raw_md5) {
			return true;
		}

		return new XApp_Error(
				'md5_mismatch',
				sprintf(
						('The checksum of the file (%1$s) does not match the expected checksum value (%2$s).'),
						bin2hex($file_md5),
						bin2hex($expected_raw_md5)
				)
		);
	}
}