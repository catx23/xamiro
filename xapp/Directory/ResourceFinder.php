<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp-Commander
 */

class XApp_Directory_ResourceFinder
{
    public  static function getMembers($workspace,$path,$removePathSegment=''){

        $dirList = self::getFilteredDirList($path,null,null,false);
        $result = array();
        if($dirList && count($dirList)){


            foreach($dirList as $item){

                $member = array();
                $member['name'] = str_replace($workspace,'',$item);
                $member['name'] = str_replace($removePathSegment,'',$member['name']);
                $member['name'] = str_replace('/','',$member['name']);
                $member['isDir'] = is_dir($item);
                $member['readOnly'] = is_writable($item) ? true : false ;
                $member['isDirty'] = false;
                $result[]=$member;

            }

        }
        return $result;
    }

    public  static function getParents($workspace,$path){

        $parentPath = $workspace . DIRECTORY_SEPARATOR . $path . '..';

        $result =array();

        /*echo('$parentPath : ' . $parentPath . '<br/>') ;*/
        $originalPath = XApp_Path_Utils::normalizePath($path);
        $completePath = realpath($workspace . DIRECTORY_SEPARATOR . $path);
        if(is_file($completePath)){

            /*echo('is file : complete path ' . $path . '<br/>') ;*/

            /***
             * maqetta wants the root item for the file
             */
            $firstItem = array();
            $parts = explode('/',$path);
            $firstItem['name'] = '';
            $firstItemPath = realpath($workspace . DIRECTORY_SEPARATOR . dirname($path));
            /*echo('<br/>first item path '.$workspace . DIRECTORY_SEPARATOR . dirname($path).'<br/>');*/

            $firstItem['isDir'] = is_dir($firstItemPath);

            $firstItem['readOnly'] = is_writable($firstItemPath) ? true : false ;

            $firstItem['members'] = self::getMembers($workspace,realpath($workspace . DIRECTORY_SEPARATOR));


            $firstItem['isDirty'] = false;
            $result[]=$firstItem;

            /***
             * as next we need the full list for the first item;
             */
            $firstItemList = array();
            $firstItemList['name']=$parts[0];
            $firstItemList['members'] = self::getMembers($workspace,realpath($workspace . DIRECTORY_SEPARATOR . $parts[0]),$parts[0]);
            $result[]=$firstItemList;



        }elseif(is_dir($completePath)){
            $parentPath = realpath($workspace . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . '..');
            $result =array();
            $item =array();
            $item['name'] = str_replace($parentPath,'',$workspace);
            $item['members'] = self::getMembers($workspace,$parentPath);
            $result[]=$item;
            return $result;
        }

        /*echo('workspace : ' . $workspace);*/

        //echo('$parentPath : ' . $parentPath . '<br/>');

        /*xapp_dump($dirList);*/

        /*
        $result =array();
        $item =array();
        $item['name'] = str_replace($parentPath,'',$workspace);
        $item['members'] = self::getMembers($workspace,$parentPath);
        $result[]=$item;
        */
        return $result;

    }

    public static function getList($workspace,$path,$inFolder=null){

        $result = array();
        $result['file'] = $path;
        $result['parents'] = self::getParents($workspace,$path);
        return $result;

    }
    /**
     * @param $path                 : expects sanitized absolute directory
     * @param array $inclusionMask  : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param array $exclusionMask  : null means all, if its a string : it must compatible to a scandir query, if its a string its a regular expression
     * @param bool $recursive       : if false, only all items on the first level, means no sub directory will be listed
     * @param bool $removedirs      : if true, don't include dirs into the list
     * @return array|bool           : filtered list, or false if error
     */
    public static function getFilteredDirList($path,$inclusionMask = Array(),$exclusionMask = Array(),$recursive=false,$removedirs=false) {
        $list=self::scanDirForList($path,$inclusionMask);
        if ($list===FALSE)
            return false;
        else {
            // If we have exclusionMask, apply it
            if ((Count($exclusionMask)) || (is_string($exclusionMask))) {
                $exclude_list=self::scanDirForList($path,$exclusionMask);
                if ($exclude_list!==FALSE) $list=array_diff($list,$exclude_list);
            }

            // Apply recursion
            if ($recursive) {
                foreach($list as $n=>$direntry) {
                    if (is_dir($direntry)) {
                        // TODO: check the "removedirs" option -> is returning dirs
                        if ($removedirs) unset($list[$n]);
                        $list=array_merge($list,self::getFilteredDirList($direntry,$inclusionMask,$exclusionMask,$recursive));
                    }
                }
            }

            return $list;
        }
    }

    /**
     *
     *  Returns a list of files into a given path, filtered by a mask
     *
     * @param $srcDir               : sanitized path
     * @param array|string $Mask    : Mask, array or string. If array, it should be compatible with grep. If string, it should be a reg. expression
     * @return array|bool           : returns the list if success, FALSE if not
     */
    public static function scanDirForList($srcDir,$Mask=Array()) {
        $error_find=false;

        $listmask=Array("*",".*");
        if (is_array($Mask)) {
            if (Count($Mask)>0)
                $listmask=$Mask;
            $Mask="";
        }
        $groblist=Array();
        foreach($listmask as $globqry) {
            $qryres=@glob($srcDir.DIRECTORY_SEPARATOR.$globqry);
            if ($qryres===FALSE)
                $error_find=true;
            else
                $groblist=array_merge($groblist,$qryres);
        }

        $groblist=array_diff($groblist,Array($srcDir.DIRECTORY_SEPARATOR.".",$srcDir.DIRECTORY_SEPARATOR.".."));

        if ($error_find)
            return FALSE;
        else {
            if ($Mask!='')
                $groblist=preg_grep($Mask,$groblist);
            return $groblist;
        }
    }
}