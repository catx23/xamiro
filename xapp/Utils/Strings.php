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
xapp_import('xapp.Commons.Exceptions');

class XApp_Utils_Strings
{

    const SANITIZE_HTML =  1;
    const SANITIZE_HTML_STRICT = 2;
    const SANITIZE_ALPHANUM = 3;
    const SANITIZE_EMAILCHARS=4;

    /**
     * Static class - cannot be instantiated.
     */
    final public function __construct()
    {
        throw new Exception('string utils : no constructor allowed');
    }


    /**
     * Checks if the string is valid for the specified encoding.
     * @param  string  byte stream to check
     * @param  string  expected encoding
     * @return bool
     */
    public static function checkEncoding($s, $encoding = 'UTF-8')
    {
        return $s === self::fixEncoding($s, $encoding);
    }


    /**
     * Returns correctly encoded string.
     * @param  string  byte stream to fix
     * @param  string  encoding
     * @return string
     */
    public static function fixEncoding($s, $encoding = 'UTF-8')
    {
        // removes xD800-xDFFF, x110000 and higher
        if (PHP_VERSION_ID >= 50400 && function_exists('mb_convert_encoding')) {
            ini_set('mbstring.substitute_character', 'none');
            if(function_exists('mb_convert_encoding')){
                return mb_convert_encoding($s, $encoding, $encoding);
            }else{
                return $s;
            }
        } else {
            return @iconv('UTF-16', 'UTF-8//IGNORE', iconv($encoding, 'UTF-16//IGNORE', $s)); // intentionally @
        }
    }


    /***
     * @param $regex
     * @return bool
     */
    public static function isRegEx($regex){
        return preg_match("/^\/.*\/[a-z]+$/",$regex) ===true;
    }

    /**
     * Returns a specific character.
     * @param  int     codepoint
     * @param  string  encoding
     * @return string
     */
    public static function chr($code, $encoding = 'UTF-8')
    {
        return iconv('UTF-32BE', $encoding . '//IGNORE', pack('N', $code));
    }


    /**
     * Starts the $haystack string with the prefix $needle?
     * @param  string
     * @param  string
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }


    /**
     * Ends the $haystack string with the suffix $needle?
     * @param  string
     * @param  string
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return strlen($needle) === 0 || substr($haystack, -strlen($needle)) === $needle;
    }


    /**
     * Does $haystack contain $needle?
     * @param  string
     * @param  string
     * @return bool
     */
    public static function contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== FALSE;
    }


    /**
     * Returns a part of UTF-8 string.
     * @param  string
     * @param  int
     * @param  int
     * @return string
     */
    public static function substring($s, $start, $length = NULL)
    {
        if ($length === NULL) {
            $length = self::length($s);
        }
        if (function_exists('mb_substr')) {
            return mb_substr($s, $start, $length, 'UTF-8'); // MB is much faster
        }
        return iconv_substr($s, $start, $length, 'UTF-8');
    }


    /**
     * Removes special controls characters and normalizes line endings and spaces.
     * @param  string  UTF-8 encoding or 8-bit
     * @return string
     */
    public static function normalize($s)
    {
        $s = self::normalizeNewLines($s);

        // remove control characters; leave \t + \n
        $s = preg_replace('#[\x00-\x08\x0B-\x1F\x7F]+#', '', $s);

        // right trim
        $s = preg_replace('#[\t ]+$#m', '', $s);

        // leading and trailing blank lines
        $s = trim($s, "\n");

        return $s;
    }


    /**
     * Standardize line endings to unix-like.
     * @param  string  UTF-8 encoding or 8-bit
     * @return string
     */
    public static function normalizeNewLines($s)
    {
        return str_replace(array("\r\n", "\r"), "\n", $s);
    }


    /**
     * Converts to ASCII.
     * @param  string  UTF-8 encoding
     * @return string  ASCII
     */
    public static function toAscii($s)
    {
        $s = preg_replace('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{2FF}\x{370}-\x{10FFFF}]#u', '', $s);
        $s = strtr($s, '`\'"^~', "\x01\x02\x03\x04\x05");
        if (ICONV_IMPL === 'glibc') {
            $s = @iconv('UTF-8', 'WINDOWS-1250//TRANSLIT', $s); // intentionally @
            $s = strtr($s, "\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e"
                . "\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3"
                . "\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8"
                . "\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe\x96",
                "ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt-");
        } else {
            $s = @iconv('UTF-8', 'ASCII//TRANSLIT', $s); // intentionally @
        }
        $s = str_replace(array('`', "'", '"', '^', '~'), '', $s);
        return strtr($s, "\x01\x02\x03\x04\x05", '`\'"^~');
    }


    /**
     * Converts to web safe characters [a-z0-9-] text.
     * @param  string  UTF-8 encoding
     * @param  string  allowed characters
     * @param  bool
     * @return string
     */
    public static function webalize($s, $charlist = NULL, $lower = TRUE)
    {
        $s = self::toAscii($s);
        if ($lower) {
            $s = strtolower($s);
        }
        $s = preg_replace('#[^a-z0-9' . preg_quote($charlist, '#') . ']+#i', '-', $s);
        $s = trim($s, '-');
        return $s;
    }


    /**
     * Truncates string to maximal length.
     * @param  string  UTF-8 encoding
     * @param  int
     * @param  string  UTF-8 encoding
     * @return string
     */
    public static function truncate($s, $maxLen, $append = "\xE2\x80\xA6")
    {
        if (self::length($s) > $maxLen) {
            $maxLen = $maxLen - self::length($append);
            if ($maxLen < 1) {
                return $append;

            } elseif ($matches = self::match($s, '#^.{1,'.$maxLen.'}(?=[\s\x00-/:-@\[-`{-~])#us')) {
                return $matches[0] . $append;

            } else {
                return self::substring($s, 0, $maxLen) . $append;
            }
        }
        return $s;
    }


    /**
     * Indents the content from the left.
     * @param  string  UTF-8 encoding or 8-bit
     * @param  int
     * @param  string
     * @return string
     */
    public static function indent($s, $level = 1, $chars = "\t")
    {
        if ($level > 0) {
            $s = self::replace($s, '#(?:^|[\r\n]+)(?=[^\r\n])#', '$0' . str_repeat($chars, $level));
        }
        return $s;
    }


    /**
     * Convert to lower case.
     * @param  string  UTF-8 encoding
     * @return string
     */
    public static function lower($s)
    {
        return mb_strtolower($s, 'UTF-8');
    }


    /**
     * Convert to upper case.
     * @param  string  UTF-8 encoding
     * @return string
     */
    public static function upper($s)
    {
        return mb_strtoupper($s, 'UTF-8');
    }


    /**
     * Convert first character to upper case.
     * @param  string  UTF-8 encoding
     * @return string
     */
    public static function firstUpper($s)
    {
        return self::upper(self::substring($s, 0, 1)) . self::substring($s, 1);
    }


    /**
     * Capitalize string.
     * @param  string  UTF-8 encoding
     * @return string
     */
    public static function capitalize($s)
    {
        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }


    /**
     * Case-insensitive compares UTF-8 strings.
     * @param  string
     * @param  string
     * @param  int
     * @return bool
     */
    public static function compare($left, $right, $len = NULL)
    {
        if ($len < 0) {
            $left = self::substring($left, $len, -$len);
            $right = self::substring($right, $len, -$len);
        } elseif ($len !== NULL) {
            $left = self::substring($left, 0, $len);
            $right = self::substring($right, 0, $len);
        }
        return self::lower($left) === self::lower($right);
    }


    /**
     * Finds the length of common prefix of strings.
     * @param  string|array
     * @param  string
     * @return string
     */
    public static function findPrefix($strings, $second = NULL)
    {
        if (!is_array($strings)) {
            $strings = func_get_args();
        }
        $first = array_shift($strings);
        for ($i = 0; $i < strlen($first); $i++) {
            foreach ($strings as $s) {
                if (!isset($s[$i]) || $first[$i] !== $s[$i]) {
                    while ($i && $first[$i-1] >= "\x80" && $first[$i] >= "\x80" && $first[$i] < "\xC0") {
                        $i--;
                    }
                    return substr($first, 0, $i);
                }
            }
        }
        return $first;
    }


    /**
     * Returns UTF-8 string length.
     * @param  string
     * @return int
     */
    public static function length($s)
    {
        return strlen(utf8_decode($s)); // fastest way
    }


    /**
     * Strips whitespace.
     * @param  string  UTF-8 encoding
     * @param  string
     * @return string
     */
    public static function trim($s, $charlist = " \t\n\r\0\x0B\xC2\xA0")
    {
        $charlist = preg_quote($charlist, '#');
        return self::replace($s, '#^['.$charlist.']+|['.$charlist.']+\z#u', '');
    }


    /**
     * Pad a string to a certain length with another string.
     * @param  string  UTF-8 encoding
     * @param  int
     * @param  string
     * @return string
     */
    public static function padLeft($s, $length, $pad = ' ')
    {
        $length = max(0, $length - self::length($s));
        $padLen = self::length($pad);
        return str_repeat($pad, $length / $padLen) . self::substring($pad, 0, $length % $padLen) . $s;
    }


    /**
     * Pad a string to a certain length with another string.
     * @param  string  UTF-8 encoding
     * @param  int
     * @param  string
     * @return string
     */
    public static function padRight($s, $length, $pad = ' ')
    {
        $length = max(0, $length - self::length($s));
        $padLen = self::length($pad);
        return $s . str_repeat($pad, $length / $padLen) . self::substring($pad, 0, $length % $padLen);
    }


    /**
     * Reverse string.
     * @param  string  UTF-8 encoding
     * @return string
     */
    public static function reverse($s)
    {
        return @iconv('UTF-32LE', 'UTF-8', strrev(@iconv('UTF-8', 'UTF-32BE', $s)));
    }


    /**
     * Generate random string.
     * @param  int
     * @param  string
     * @return string
     */
    public static function random($length = 10, $charlist = '0-9a-z')
    {
        $charlist = str_shuffle(preg_replace_callback('#.-.#', function($m) {
            return implode('', range($m[0][0], $m[0][2]));
        }, $charlist));
        $chLen = strlen($charlist);

        if (function_exists('openssl_random_pseudo_bytes')
            && (PHP_VERSION_ID >= 50400 || !defined('PHP_WINDOWS_VERSION_BUILD')) // slow in PHP 5.3 & Windows
        ) {
            $rand3 = openssl_random_pseudo_bytes($length);
        }
        if (empty($rand3) && function_exists('mcrypt_create_iv')) {
            $rand3 = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        }
        if (empty($rand3) && @is_readable('/dev/urandom')) {
            $rand3 = file_get_contents('/dev/urandom', FALSE, NULL, -1, $length);
        }
        if (empty($rand3)) {
            static $cache;
            $rand3 = $cache ?: $cache = md5(serialize($_SERVER), TRUE);
        }

        $s = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i % 5 === 0) {
                list($rand, $rand2) = explode(' ', microtime());
                $rand += lcg_value();
            }
            $rand *= $chLen;
            $s .= $charlist[($rand + $rand2 + ord($rand3[$i % strlen($rand3)])) % $chLen];
            $rand -= (int) $rand;
        }
        return $s;
    }


    /**
     * Splits string by a regular expression.
     * @param  string
     * @param  string
     * @param  int
     * @return array
     */
    public static function split($subject, $pattern, $flags = 0)
    {
        set_error_handler(function($severity, $message) use ($pattern) { // preg_last_error does not return compile errors
            restore_error_handler();
            throw new XApp_RegexpException("$message in pattern: $pattern");
        });
        $res = preg_split($pattern, $subject, -1, $flags | PREG_SPLIT_DELIM_CAPTURE);
        restore_error_handler();
        if (preg_last_error()) { // run-time error
            throw new XApp_RegexpException(NULL, preg_last_error(), $pattern);
        }
        return $res;
    }


    /**
     * Performs a regular expression match.
     * @param  string
     * @param  string
     * @param  int  can be PREG_OFFSET_CAPTURE (returned in bytes)
     * @param  int  offset in bytes
     * @return mixed
     */
    public static function match($subject, $pattern, $flags = 0, $offset = 0)
    {
        if ($offset > strlen($subject)) {
            return NULL;
        }
        set_error_handler(function($severity, $message) use ($pattern) { // preg_last_error does not return compile errors
            restore_error_handler();
            throw new XApp_RegexpException("$message in pattern: $pattern");
        });
        $res = preg_match($pattern, $subject, $m, $flags, $offset);
        restore_error_handler();
        if (preg_last_error()) { // run-time error
            throw new XApp_RegexpException(NULL, preg_last_error(), $pattern);
        }
        if ($res) {
            return $m;
        }
	    return NULL;
    }


    /**
     * Performs a global regular expression match.
     * @param  string
     * @param  string
     * @param  int  can be PREG_OFFSET_CAPTURE (returned in bytes); PREG_SET_ORDER is default
     * @param  int  offset in bytes
     * @return array
     */
    public static function matchAll($subject, $pattern, $flags = 0, $offset = 0)
    {
        if ($offset > strlen($subject)) {
            return array();
        }
        set_error_handler(function($severity, $message) use ($pattern) { // preg_last_error does not return compile errors
            restore_error_handler();
            throw new XApp_RegexpException("$message in pattern: $pattern");
        });
        preg_match_all(
            $pattern, $subject, $m,
            ($flags & PREG_PATTERN_ORDER) ? $flags : ($flags | PREG_SET_ORDER),
            $offset
        );
        restore_error_handler();
        if (preg_last_error()) { // run-time error
            throw new XApp_RegexpException(NULL, preg_last_error(), $pattern);
        }
        return $m;
    }


    /**
     * Perform a regular expression search and replace.
     * @param  string
     * @param  string|array
     * @param  string|callable
     * @param  int
     * @return string
     */
    public static function replace($subject, $pattern, $replacement = NULL, $limit = -1)
    {
        if (is_object($replacement) || is_array($replacement)) {
            if ($replacement instanceof Nette\Callback) {
                $replacement = $replacement->getNative();
            }
            if (!is_callable($replacement, FALSE, $textual)) {
                throw new Exception("Callback '$textual' is not callable.");
            }

            set_error_handler(function($severity, $message) use (& $tmp) { // preg_last_error does not return compile errors
                restore_error_handler();
                throw new XApp_RegexpException("$message in pattern: $tmp");
            });
            foreach ((array) $pattern as $tmp) {
                preg_match($tmp, '');
            }
            restore_error_handler();

            $res = preg_replace_callback($pattern, $replacement, $subject, $limit);
            if ($res === NULL && preg_last_error()) { // run-time error
                throw new XApp_RegexpException(NULL, preg_last_error(), $pattern);
            }
            return $res;

        } elseif ($replacement === NULL && is_array($pattern)) {
            $replacement = array_values($pattern);
            $pattern = array_keys($pattern);
        }

        set_error_handler(function($severity, $message) use ($pattern) { // preg_last_error does not return compile errors
            restore_error_handler();
            throw new XApp_RegexpException("$message in pattern: " . implode(' or ', (array) $pattern));
        });
        $res = preg_replace($pattern, $replacement, $subject, $limit);
        restore_error_handler();
        if (preg_last_error()) { // run-time error
            throw new XApp_RegexpException(NULL, preg_last_error(), implode(' or ', (array) $pattern));
        }
        return $res;
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
    public static function sanitize(
        $s,
        $level = self::SANITIZE_HTML,
        $expand = 'script|style|noframes|select|option')
    {
        $s = str_replace('./','',$s);
        //prep the string
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


	/**
	 * Parses a string into variables to be stored in an array.
	 *
	 * Uses {@link http://www.php.net/parse_str parse_str()} and stripslashes if
	 * {@link http://www.php.net/magic_quotes magic_quotes_gpc} is on.
	 *
	 * @param string $string The string to be parsed.
	 * @param array $array Variables will be stored in this array.
	 */
	public static function parse_str( $string, &$array ) {
		parse_str( $string, $array );
		if ( get_magic_quotes_gpc() )
			$array = self::stripslashes_deep( $array );
	}

	/**
	 * Navigates through an array and removes slashes from the values.
	 *
	 * If an array is passed, the array_map() function causes a callback to pass the
	 * value back to the function. The slashes from this value will removed.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value The value to be stripped.
	 * @return mixed Stripped value.
	 */
	public static function stripslashes_deep($value) {
		if ( is_array($value) ) {
			$value = array_map('stripslashes_deep', $value);
		} elseif ( is_object($value) ) {
			$vars = get_object_vars( $value );
			foreach ($vars as $key=>$data) {
				$value->{$key} = self::stripslashes_deep( $data );
			}
		} elseif ( is_string( $value ) ) {
			$value = stripslashes($value);
		}

		return $value;
	}

	/**
	 * Removes trailing forward slashes and backslashes if they exist.
	 *
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 *
	 * @param string $string What to remove the trailing slashes from.
	 * @return string String without the trailing slashes.
	 */
	public static function untrailingslashit( $string ) {
		return rtrim( $string, '/\\' );
	}
	/**
	 * Appends a trailing slash.
	 *
	 * Will remove trailing forward and backslashes if it exists already before adding
	 * a trailing forward slash. This prevents double slashing a string or path.
	 *
	 * The primary use of this is for paths and thus should be used for paths. It is
	 * not restricted to paths and offers no specific path support.
	 * @param string $string What to add the trailing slash to.
	 * @return string String with trailing slash added.
	 */
	public static function trailingslashit( $string ) {
		return self::untrailingslashit( $string ) . '/';
	}

	/**
	 * @param bool $reset
	 */
	public static function mbstring_binary_safe_encoding( $reset = false ) {
		static $encodings = array();
		static $overloaded = null;

		if ( is_null( $overloaded ) )
			$overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );

		if ( false === $overloaded )
			return;

		if ( ! $reset ) {
			$encoding = mb_internal_encoding();
			array_push( $encodings, $encoding );
			mb_internal_encoding( 'ISO-8859-1' );
		}

		if ( $reset && $encodings ) {
			$encoding = array_pop( $encodings );
			mb_internal_encoding( $encoding );
		}
	}

	/**
	 * Reset the mbstring internal encoding to a users previously set encoding.
	 *
	 * @see mbstring_binary_safe_encoding()
	 *
	 * @since 3.7.0
	 */
	public static function reset_mbstring_encoding() {
		self::mbstring_binary_safe_encoding( true );
	}

	/**
	 * Normalize a unicode string
	 * @param string $value a not normalized string
	 * @return bool|string
	 */
	public static function normalizeUnicode($value) {

		xapp_import('xapp.Utils.Normalizer');

		if(class_exists('XApp_Normalizer')) {
			$normalizedValue = XApp_Normalizer::normalize($value);
			if($normalizedValue === false) {
				return null;
			} else {
				$value = $normalizedValue;
			}
		}
		return $value;
	}

}