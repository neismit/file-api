<?php

namespace tests\codeception\helper;

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
        if ($compression) {
            $handle = gzopen($fullPathToFile, 'wb' . \Yii::$app->params['compressionLevel']);
            gzwrite($handle, $content);
            gzclose($handle);
        } else {
            $handle = fopen($fullPathToFile, 'wb');
            fwrite($handle, $content);
            fclose($handle);
        }
    }
    
}
