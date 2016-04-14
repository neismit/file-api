<?php

namespace tests\codeception\helper;

use app\models\StreamHelper;

/**
 * Help do file operation
 *
 * @author andrey
 */
class FileHelper {
    
    /**
     * Create compressed file
     * @param string $fullPathToFile
     * @param string $content
     * @param boolean $compression
     */
    public static function createFile($fullPathToFile, $content, $compression = TRUE) {
        $handle = fopen($fullPathToFile, 'wb');
        if ($compression) {
            StreamHelper::atachCompressionFilter($handle);        
        }
        fwrite($handle, $content);
        fflush($handle);
        fclose($handle);
    }
    
}
