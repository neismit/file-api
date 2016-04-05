<?php

namespace app\models\data;

use app\models\data\IFileRepository;
use app\models\File;

class FileRepositoryFS implements \app\models\data\IFileRepository {
    
    public static function getFileMetadata($fileName, $userId) {
        $pathMetadata = File::getFullPathMetadata($fileName);
        
        
        
        return null;
    }
    
    public static function getFiles($userId) {
        return ['1', '2', 't1'];
    }
    
    public static function createFile($fileMetadata, $userId) {
        return null;
    }
}
