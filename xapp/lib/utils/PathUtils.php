<?php
/**
 * @version 0.1.0
 * @package Xapp\String
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
class XApp_Util_Path{
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
     * @brief Fix common problems with a file path
     * @param string $path
     * @param bool $stripTrailingSlash
     * @return string
     */
    public static function normalizePath($path, $stripTrailingSlash = true) {
        if ($path == '') {
            return '/';
        }
        //no windows style slashes
        $path = str_replace('\\', '/', $path);

        //add leading slash
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        // remove '/./'
        // ugly, but str_replace() can't replace them all in one go
        // as the replacement itself is part of the search string
        // which will only be found during the next iteration
        while (strpos($path, '/./') !== false) {
            $path = str_replace('/./', '/', $path);
        }
        // remove sequences of slashes
        $path = preg_replace('#/{2,}#', '/', $path);

        //remove trailing slash
        if ($stripTrailingSlash and strlen($path) > 1 and substr($path, -1, 1) === '/') {
            $path = substr($path, 0, -1);
        }

        // remove trailing '/.'
        if (substr($path, -2) == '/.') {
            $path = substr($path, 0, -2);
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
}
