<?php

namespace app\models\data;

use app\models\data\IFileRepository;

class FileRepositoryFS implements \app\models\data\IFileRepository {
    
    public static function getFileMetadata($fileName, $userId) {
        return null;
    }
    
    public static function getFiles($userId) {
        return null;
    }
    
    public static function createFile($fileMetadata, $userId) {
        return null;
    }
}
