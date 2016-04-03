<?php

namespace app\models;

/**
 * File operations
 *
 * @author andrey
 */
class File {
    
    public static function getFileStream($filePath) {
        if (!file_exists($filePath)) {
            return NULL;
        }
        $handle = fopen($filePath, 'r');
        return $handle;
    }            
    
}
