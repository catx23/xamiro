<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('XAPP') || require_once(dirname(__FILE__) . '/../../Core/core.php');

/**
 * Interface Xapp_VFS_Interface_Access
 */
interface Xapp_VFS_Interface_Access
{
    /**
     * Return file content, supports browser dowload
     * @param $mount
     * @param $relativePath
     * @param bool $attachment
     * @return mixed
     */
    public function get($mount,$relativePath,$attachment=false);

    /**
     * method set will write content into a file
     * @param $mount
     * @param $relativePath
     * @param $content
     * @return mixed
     */
    public function set($mount,$relativePath,$content);

    /**
     * method mkNode creates a new file
     * @param $mount
     * @param $relativePath
     * @return mixed
     */
    public function mkFile($mount,$relativePath);

    /***
     * ls, directory listing, '/' means all mounted directories are visible
     *
     * output format for an item :
     *
     * item['isReadOnly]=
     * item['isDirectory]=
     * item['owner']=
     * item['mime']=
     * item['size']=
     * item['permissions']=
     *
     * Look in src/server/xfile/File or src/server/stores/cbtree/cbTreeFileStoreStandalone.
     * There are many methods about owner,permissions,.... Copy them into File/Utils
     *
     * options['fields'] = isReadOnly|mime|owner,... specifies the fields we want in the output!
     * Define those fields as string constants in File/Utils.php for now.
     *
     * @param string $path
     * @param $recursive
     * @param $options
     * @return array
     */

     public function ls($path='/',$recursive=false,$options=Array());
    /**
     *  Copies $srcDir into $dstDirectory across multiple mount points
     *
     * @param $srcDir : expects sanitized absolute directory
     * @param $dstDirectory : expects sanitized absolute directory, if it doesn't exists, create it!
     * @param array $options : [recursive (true/false) default true, timeout (seconds) default 60, overwriteModus : XAPP_XFILE_OVERWRITE_NONE | XAPP_XFILE_OVERWRITE_ALL | XAPP_XFILE_OVERWRITE_IF_SIZE_DIFFERS
     * @param array|string $inclusionMask : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array|string $exclusionMask : null means all, otherwise it must compatible to a scandir query,if its a string its a regular expression
     * @param $error : a pointer to an array reference, please track all errors and don't abort! Check __copyOrMoveFile below how to write the error messages right!
     * @param $success : track all copied items here
     */

    public function copyDirectory($srcDir,$dstDirectory,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success);
    /**
     * @param $path
     * @param array $options
     * @param array $inclusionMask
     * @param array $exclusionMask
     * @param $error
     * @param $success
     * @return mixed
     */

    public function deleteDirectory($path,$options=Array(),$inclusionMask = Array(),$exclusionMask = Array(),&$error,&$success);
    /***
     * @param $path
     * @param array $options
     * @param $error
     * @param $success
     * @return mixed
     */
    public function deleteFile($path,$options=Array(),&$error,&$success);
}