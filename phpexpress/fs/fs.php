<?php

namespace PHPExpress\FS;

require_once __DIR__ . "\\file.php";

/**
 * Returns subfolders of given directory as array
 * @param mixed $path Path to directory
 * 
 * @return array
 */
function getSubDirs($path): array {    
    $output = array();
    if ($handle = opendir($path)) {

        while (false !== ($subDir = readdir($handle))) {
            if($subDir != "." && $subDir != ".." && is_dir($path . "\\" . $subDir)) {
                array_push($output, $subDir);
            }
        }

        closedir($handle);
    }

    return $output;
}