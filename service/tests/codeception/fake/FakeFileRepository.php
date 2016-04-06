<?php

namespace tests\codeception\fake;

use app\models\data\IFileRepository;
use app\models\FileMetadata;

class FakeFileRepository implements IFileRepository {

    public static function getFileMetadata($fileName, $userId) {
        if ($fileName == 't1.txt' && $userId == 1) {
            return new FileMetadata([
                'Name' => 't1.txt',
                'Size' => '24',
                'Modified' => '2016-04-06T06:34:46+0000',
                'Created' => '2016-04-06T06:34:46+0000',
                'Type' => 'text/plain',
                'Owner' => 1
            ]);
        }
        if ($fileName == 't1.txt' && $userId == 2) {
            throw new \app\models\data\AccessDenied();
        }
    }
    
    public static function saveFileMetadata($metadata) {
        return false;
    }
    
    public static function getFiles($userId) {
        switch ($userId) {
            case 1: return ['t1.txt', 't3'];
            case 2: return ['t2.txt'];
            case 3: return [];
            default: NULL;
        }
    }
    
    public static function createFile($fileMetadata, $userId) {
        return NULL;
    }       
}

