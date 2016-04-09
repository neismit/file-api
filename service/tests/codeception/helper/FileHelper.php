<?php

namespace tests\codeception\helper;

/**
 * Help do file operation
 *
 * @author andrey
 */
class FileHelper {
    
    public static function createFile($fullPathToFile, $content) {
        $handle = fopen($fullPathToFile, 'w');
        fwrite($handle, $content);
        fflush($handle);
        fclose($handle);
    }
    
}
