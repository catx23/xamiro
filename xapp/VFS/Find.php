<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */

/***
 * Class Xapp_FileService
 */
class Xapp_File_Find
{
    const FILE_TYPE           = "XAPP_FILE_FIND_FILE_TYPE";
    const DIRECTORY_TYPE      = "XAPP_FILE_FIND_DIRECTORY_TYPE";

    function directoryScan($dir, $onlyfiles = false, $fullpath = true) {
        if (isset($dir) && is_readable($dir)) {
            $dlist = Array();
            $dir = realpath($dir);
            if ($onlyfiles) {
                $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            } else {
                $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
            }

            foreach($objects as $entry => $object){
                if (!$fullpath) {
                    $entry = str_replace($dir, '', $entry);
                }

                $dlist[] = $entry;
            }

            return $dlist;
        }
    }
    /***
     * File iterate
     * @param $directory
     * @return array
     */
    function getallfiles($directory){

        $res =  $this->directoryScan($directory);
        return $res;
    }

    /**
     * Simple search with usual suspects
     * @param $mixed
     * @return array
     */
    public function find($mixed)
    {
        $s_p = $mixed['searchIn'];

        $searchType = isset($mixed['type']) ? $mixed['type'] : "file";
        $fileName = (!empty($mixed['file']['name'])) ? $mixed['file']['name'] : '';
        $directoryName = (!empty($mixed['directory']['name'])) ? $mixed['directory']['name'] : '';
        $contains = (!empty($mixed['content']['name'])) ? $mixed['content']['name'] : '';
        $permissions = (!empty($mixed['permissions'])) ? intval($mixed['permissions']) : 0;

        $fileNameRegex = $fileNameCase = $directoryNameRegex = $directoryNameCase = $containsRegex = $containsCase = $writeable = $readable = $executable = false;

        $fileNameRegex = $mixed['file']['regex'];
        $fileNameCase = $mixed['file']['caseSensitive'];
        $directoryNameRegex = $mixed['directory']['regex'];
        $directoryNameCase = $mixed['directory']['caseSensitive'];
        $containsRegex = $mixed['content']['regex'];
        $containsCase = $mixed['content']['caseSensitive'];

        if ( $permissions & 1<<2  ){
            $writeable = true;
        }
        if ($permissions & 1<<4) {
            $readable = true;
        }
        if ($permissions & 1<<8) {
            $executable = true;
        }




        $searchResultItem = $this->getallfiles($s_p);
        if ($searchType == 'file'){
            $searchResultItem = @array_filter($searchResultItem, "is_file");
        }
        elseif ($searchType == 'dir'){
            $searchResultItem = @array_filter($searchResultItem, "is_dir");
        }

        foreach ($searchResultItem as $s_a) {
            if ($searchType == 'dir') {
                if (!empty($directoryName)) {
                    if ($directoryNameRegex) {
                        if ($directoryNameCase) {
                            if (!preg_match("/" . $directoryName . "/i", basename($s_a))) $searchResultItem = array_diff($searchResultItem, array($s_a));
                        } else {
                            if (!preg_match("/" . $directoryName . "/", basename($s_a))) $searchResultItem = array_diff($searchResultItem, array($s_a));
                        }
                    } else {
                        if ($directoryNameCase) {
                            if (strpos(strtolower(basename($s_a)), strtolower($directoryName)) === false) $searchResultItem = array_diff($searchResultItem, array($s_a));
                        } else {
                            if (strpos(basename($s_a), $directoryName) === false) $searchResultItem = array_diff($searchResultItem, array($s_a));
                        }
                    }
                }
            } elseif ($searchType == 'file') {
                if (!empty($fileName)) {
                    if ($fileNameRegex) {
                        if ($fileNameCase) {
                            if (!preg_match("/" . $fileName . "/i", basename($s_a))) $searchResultItem = array_diff($searchResultItem, array($s_a));
                        } else {
                            if (!preg_match("/" . $fileName . "/", basename($s_a))) $searchResultItem = array_diff($searchResultItem, array($s_a));
                        }
                    } else {
                        if ($fileNameCase) {
                            if (strpos(strtolower(basename($s_a)), strtolower($fileName)) === false) $searchResultItem = array_diff($searchResultItem, array($s_a));
                        } else {
                            if (strpos(basename($s_a), $fileName) === false) $searchResultItem = array_diff($searchResultItem, array($s_a));
                        }
                    }
                }
                if (!empty($contains) && strlen($contains)>2) {
                    $fileContent = file_get_contents($s_a);

                    if ($containsRegex) {
                        if ($containsCase) {
                            if (!preg_match("/" . $contains . "/i", $fileContent))
                                $searchResultItem = array_diff($searchResultItem, array($s_a));
                        } else {
                            if (!preg_match("/" . $contains . "/", $fileContent))
                                $searchResultItem = array_diff($searchResultItem, array($s_a));
                        }
                    } else {
                        if ($containsCase) {
                            if (strpos(strtolower($fileContent), strtolower($contains)) === false)
                                $searchResultItem = array_diff($searchResultItem, array($s_a));
                        } else {
                            if (strpos($fileContent, $contains) === false)
                                $searchResultItem = array_diff($searchResultItem, array($s_a));
                        }
                    }
                }
            }
        }

        $results = array();
        foreach($searchResultItem as $s_c){
            $s_c = trim($s_c);
            if($writeable && !@is_writable($s_c)) continue;
            if($readable && !@is_readable($s_c)) continue;
            if($executable && !@is_executable($s_c)) continue;

            $results[]=$s_c;
        }

        return $results;
    }


}