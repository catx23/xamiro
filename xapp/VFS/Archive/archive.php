<?php

if( !class_exists('PEAR')) {
	@include_once 'PEAR.php';
	if( !class_exists('PEAR')) {
		require_once(dirname(__FILE__). DIRECTORY_SEPARATOR . 'PEAR.php');
	}
}

class xFileArchive {
	/**
	 * @param	string	The name of the archive file
	 * @param	string	Directory to unpack into
	 * @return	boolean	True for success
	 */
	function extract( $archivename, $extractdir ) {

		require_once( dirname(__FILE__). '/new/File.php' ) ;
		require_once( dirname(__FILE__). '/new/Folder.php' ) ;
		require_once( dirname(__FILE__). '/new/Archive.php' ) ;


		$archive = new xapp_Archive2();
		try {
			$result = $archive->extract(realpath($archivename), realpath($extractdir));
		}catch (Exception $e){
			xapp_clog('e ' . $e->getMessage());
		}

		return $result;
	}
	
	function &getAdapter( $type ) {
		static $adapters ;
		
		if( ! isset( $adapters ) ) {
			$adapters = array( ) ;
		}
		
		if( ! isset( $adapters[$type] ) ) {
			// Try to load the adapter object
			$class = 'xfileArchive' . ucfirst( $type ) ;
			
			if( ! class_exists( $class ) ) {
				$path = dirname( __FILE__ )  . '/adapter/' . strtolower( $type ) . '.php' ;
				if( file_exists( $path ) ) {
					require_once ($path) ;
				} else {
					echo 'Unknown Archive Type: '.$class;
					ext_Result::sendResult('archive', false, 'Unable to load archive' ) ;
				}
			}
			
			$adapters[$type] = new $class( ) ;
		}
		return $adapters[$type] ;
	}
	
	/**
	 * @param	string	The name of the archive
	 * @param	mixed	The name of a single file or an array of files
	 * @param	string	The compression for the archive
	 * @param	string	Path to add within the archive
	 * @param	string	Path to remove within the archive
	 * @param	boolean	Automatically append the extension for the archive
	 * @param	boolean	Remove for source files
	 */
	function create( $archive, $files, $compress = 'tar', $addPath = '', $removePath, $autoExt = true ) {

        $compress = strtolower( $compress );
		if( $compress == 'tgz' || $compress == 'tbz' || $compress == 'tar') {
			
			require_once( _EXT_PATH.'/libraries/Tar.php' ) ;
			
			if( is_string( $files ) ) {
				$files = array( $files ) ;
			}
			if( $autoExt ) {
				$archive .= '.' . $compress ;
			}
			if( $compress == 'tgz'  ) $compress = 'gz';
			if( $compress == 'tbz'  ) $compress = 'bz2';
			
			$tar = new Archive_Tar( $archive, $compress ) ;
			$tar->setErrorHandling( PEAR_ERROR_PRINT ) ;
			$result = $tar->addModify( $files, $addPath, $removePath ) ;
			
			return $result;
		}
		elseif( $compress == 'zip' ) {
		    $adapter = & xFileArchive::getAdapter( 'zip' ) ;
            if( $adapter ) {
                $result = $adapter->create( $archive, $files, $removePath ) ;
			}
            if($result == false ) {
                return PEAR::raiseError( 'Unrecoverable ZIP Error' );
			}
		}
	}
}