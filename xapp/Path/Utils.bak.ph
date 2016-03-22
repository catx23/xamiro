<?php
/**
 * @version 0.1.0
 * @package Xapp\String
 * @link https://github.com/mc007
 * @author XApp-Studio.com support@xapp-studio.com
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
class XApp_Path_Utils{

    const SANITIZE_HTML =  1;
    const SANITIZE_HTML_STRICT = 2;
    const SANITIZE_ALPHANUM = 3;
    const SANITIZE_EMAILCHARS=4;

    /**
     * Is path absolute?
     * @return bool
     */
    public static function isAbsolute($path)
    {
        return (bool) preg_match('#[/\\\\]|[a-zA-Z]:[/\\\\]|[a-z][a-z0-9+.-]*://#Ai', $path);
    }
    /**
     * check if the requested path is valid
     *
     * @param string $path
     * @return bool
     */
    public static function isValidPath($path) {
        $path = self::normalizePath($path);
        if (!$path || $path[0] !== '/') {
            $path = '/' . $path;
        }
        if (strstr($path, '/../') || strrchr($path, '/') === '/..') {
            return false;
        }
        return true;
    }

    /**
     * Check windows
     * @return bool
     */
    protected static  function isWindows(){
        $os = PHP_OS;
        switch($os)
        {
            case "WINNT": {
                return true;
            }
        }
        return false;
    }
    /**
     * @brief Fix common problems with a file path
     * @param string $path
     * @param bool $stripTrailingSlash
     * @return string
     */
    public static function normalizePath($path, $stripTrailingSlash = false,$addTrailingSlash=true) {

        if ($path == '') {
            return '/';
        }
        if(!is_string($path)){
            return '';
        }

        //no windows style slashes
        $path = str_replace('\\', '/', $path);

        //add leading slash
        if ( !self::isWindows() && $path[0] !== '/') {
            $path = '/' . $path;
        }

        // remove '/./'
        // ugly, but str_replace() can't replace them all in one go
        // as the replacement itself is part of the search string
        // which will only be found during the next iteration
        $max = 0;
        while (strpos($path, '/./') !== false) {

            $path = str_replace('/./', '/', $path);
            if($max>100){
                break;
            }
            $max++;
        }

        //remove '/../'
        while (strpos($path, '/../') !== false) {
            $path = str_replace('/../', '/', $path);
        }
        //remove '..'
        while (strpos($path, '..') !== false) {
            $path = str_replace('..', '/', $path);
        }

        // remove sequences of slashes
        $path = preg_replace('#/{2,}#', '/', $path);

        if($addTrailingSlash and strlen($path) > 1 and substr($path, -1, 1) !== '/') {
            $path = $path.'/';
        }

        //remove trailing slash
        if ($stripTrailingSlash and strlen($path) > 1 and substr($path, -1, 1) === '/') {
            $path = substr($path, 0, -1);
        }

        // remove trailing '/.'
        if (substr($path, -2) == '/.') {
            $path = substr($path, 0, -2);
        }
	    while (preg_match('/\/\//', $path))
	    {
		    $path = str_replace('//', '/', $path);
	    }

        //normalize unicode if possible
        /*$path = \OC_Util::normalizeUnicode($path);*/
        return $path;
    }

    /**
     * Modifies a string to remove all non ASCII characters and spaces.
     *
     * @param string $text
     * @return string
     */
    public static function slugify($text)
    {
        // replace non letter or digits or dots by -
        $text = preg_replace('~[^\\pL\d\.]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w\.]+~', '', $text);

        // trim ending dots (for security reasons and win compatibility)
        $text = preg_replace('~\.+$~', '', $text);

        if (empty($text)) {
            return uniqid();
        }
        return $text;
    }
    /***
     * @param $path
     * @return mixed|string
     */
    public static function securePath($path)
    {
        if ($path == null) $path = "";
        //
        // REMOVE ALL "../" TENTATIVES
        //
        $path = str_replace(chr(0), "", $path);
        $dirs = explode('/', $path);
        for ($i = 0; $i < count($dirs); $i++)
        {
            if ($dirs[$i] == '.' or $dirs[$i] == '..') {
                $dirs[$i] = '';
            }
        }
        // rebuild safe directory string
        $path = implode('/', $dirs);

        //
        // REPLACE DOUBLE SLASHES
        //
        while (preg_match('/\/\//', $path))
        {
            $path = str_replace('//', '/', $path);
        }
        return $path;
    }

    private static function longest_common_substring($words)
    {
        $words = array_map('strtolower', array_map('trim', $words));
        $sort_by_strlen = create_function('$a, $b', 'if (strlen($a) == strlen($b)) { return strcmp($a, $b); } return (strlen($a) < strlen($b)) ? -1 : 1;');
        usort($words, $sort_by_strlen);
        // We have to assume that each string has something in common with the first
        // string (post sort), we just need to figure out what the longest common
        // string is. If any string DOES NOT have something in common with the first
        // string, return false.
        $longest_common_substring = array();
        $shortest_string = str_split(array_shift($words));
        while (sizeof($shortest_string)) {
            array_unshift($longest_common_substring, '');
            foreach ($shortest_string as $ci => $char) {
                foreach ($words as $wi => $word) {
                    if (!strstr($word, $longest_common_substring[0] . $char)) {
                        // No match
                        break 2;
                    } // if
                } // foreach
                // we found the current char in each word, so add it to the first longest_common_substring element,
                // then start checking again using the next char as well
                $longest_common_substring[0].= $char;
            } // foreach
            // We've finished looping through the entire shortest_string.
            // Remove the first char and start all over. Do this until there are no more
            // chars to search on.
            array_shift($shortest_string);
        }
        // If we made it here then we've run through everything
        usort($longest_common_substring, $sort_by_strlen);
        return array_pop($longest_common_substring);
    }

    /***
     * @param $leadingPath
     * @param $pathToMerge
     * @return string
     */
    public static function merge($leadingPath,$pathToMerge){
        $diff = self::longest_common_substring(array(
                $leadingPath,
                $pathToMerge
            ));
        //now remove the common shared part from B and append it to A
        //error_log('merge leading :' . $leadingPath);
        //error_log('merge diff :' . $diff);
        return self::normalizePath($leadingPath .DIRECTORY_SEPARATOR. str_replace($diff,'',$pathToMerge),true,false);
    }

    /**
     * Function to clean a string from specific characters
     *
     * @static
     * @param string $s
     * @param int $level Can be SANITIZE_ALPHANUM, SANITIZE_EMAILCHARS, SANITIZE_HTML, SANITIZE_HTML_STRICT
     * @param string $expand
     * @return mixed|string
     */
    public static function sanitizeEx($s, $level = self::SANITIZE_HTML, $expand = 'script|style|noframes|select|option')
    {
        $s = str_replace('./','',$s);
        /**/ //prep the string
        $s = ' ' . $s;
        if ($level == self::SANITIZE_ALPHANUM) {
            return preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $s);
        } else if ($level == self::SANITIZE_EMAILCHARS) {
            return preg_replace("/[^a-zA-Z0-9_\-\.@!%\+=|~\?]/", "", $s);
        }

        //begin removal
        //remove comment blocks
        while (stripos($s, '<!--') > 0) {
            $pos[1] = stripos($s, '<!--');
            $pos[2] = stripos($s, '-->', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 3;
            $x = substr($s, $pos[1], $len[1]);
            $s = str_replace($x, '', $s);
        }

        //remove tags with content between them
        if (strlen($expand) > 0) {
            $e = explode('|', $expand);
            for ($i = 0; $i < count($e); $i++) {
                while (stripos($s, '<' . $e[$i]) > 0) {
                    $len[1] = strlen('<' . $e[$i]);
                    $pos[1] = stripos($s, '<' . $e[$i]);
                    $pos[2] = stripos($s, $e[$i] . '>', $pos[1] + $len[1]);
                    $len[2] = $pos[2] - $pos[1] + $len[1];
                    $x = substr($s, $pos[1], $len[2]);
                    $s = str_replace($x, '', $s);
                }
            }
        }

        $s = strip_tags($s);
        if ($level == self::SANITIZE_HTML_STRICT) {
            $s = preg_replace("/[\",;\/`<>:\*\|\?!\^\\\]/", "", $s);
        } else {
            $s = str_replace(array("<", ">"), array("&lt;", "&gt;"), $s);
        }
        return trim($s);
    }

    public static function sanitize($path){
        xapp_import('xapp.Utils.SystemTextEncoding');
        return XApp_SystemTextEncoding::fromUTF8(self::sanitize(self::securePath(XApp_SystemTextEncoding::magicDequote($path)), self::SANITIZE_HTML));
    }
    /***
     * Do standard checks : urldecode, sanitization, secure path and magicDequote
     * @param $data
     * @param int $sanitizeLevel
     * @return string
     */
    public static function decodeSecureMagic($data, $sanitizeLevel = self::SANITIZE_HTML)
    {
        xapp_import('xapp.Utils.SystemTextEncoding');
        return XApp_SystemTextEncoding::fromUTF8(self::sanitizeEx(self::securePath(XApp_SystemTextEncoding::magicDequote($data)), $sanitizeLevel));
    }
	/**
	 * @param $path
	 * @return mixed
	 */
	public static function getMount($path){

		if(strpos($path,'://')!==false){
	        $path_parts_0 = explode('://', $path);
	        if(count($path_parts_0)){
	            return $path_parts_0[0];
	        }
		}
		//echo($path);
		$path = self::sanitizeEx(self::normalizePath($path,false,false));
		//echo($path);
		$parsed = parse_url($path);
		//xapp_dump($parsed);
		$path = $parsed['path'];
		$path_parts = explode('/', $path);
		if(count($path_parts)==2){
			return str_replace(":","",$path_parts[1]);
		}
		return str_replace(":","",$path_parts[0]);

	}

	/**
	 * @param $path
	 * @return mixed
	 */
	public static function getRelativePart($path){

		$path = self::sanitizeEx(self::normalizePath($path,false,false));
		$parsed = parse_url($path);
		$path = $parsed['path'];
		$path_parts = explode('/', $path);
        if(!self::isWindows()){
		  array_shift($path_parts);//remove first and empty element
        }
		array_shift($path_parts);//remove mount part
		return implode('/',$path_parts);
	}

}
