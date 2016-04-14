<?php

namespace app\models;

/**
 * File operations
 *
 * @author andrey
 */
class File {
    
    /**
     * move to IFileRepository
     * @deprecated 2016-04-12
     * @param type $filePath
     * @return type
     */
    public static function getFileStream($filePath) {
        if (!file_exists($filePath)) {
            return NULL;
        }
        $handle = fopen($filePath, 'r');
        return $handle;
    }
    
    public static function getFullPathMetadata($fileName) {
        $pathMetadata = \Yii::$app->params['metadataFolder'];
        return $pathMetadata . '/' . $fileName;
    }            
    
    public static function getFullPathFile($fileName) {
        $dir = \Yii::$app->params['dataFolder'];
        return $dir . '/' . $fileName;
    }            
}
