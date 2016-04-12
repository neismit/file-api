<?php

namespace tests\codeception\fake;

use app\models\data\IFileRepository;
use app\models\FileMetadata;
use app\models\data\FileRepositoryFS;
use app\models\data\NotFound;

class FakeFileRepository implements IFileRepository {

    private $content = [
        't1.txt' => 'Give me BANANA!!!! Mort.',
        't2' => 'Dinosaur! George.',
    ];

    public static function getFileMetadata($fileName, $userId) {
        if ($fileName === 't1.txt' && $userId === 1) {
            return new FileMetadata([
                'Name' => 't1.txt',
                'Size' => 24,
                'Modified' => '2016-04-06T06:34:46+0000',
                'Created' => '2016-04-06T06:34:46+0000',
                'Type' => 'text/plain',
                'Owner' => 1
            ]);
        }
        if ($fileName === 't2' && $userId === 1) {
            return new FileMetadata([
                'Name' => 't2',
                'Size' => 17,
                'Modified' => '2016-04-06T06:34:46+0000',
                'Created' => '2016-04-06T06:34:46+0000',
                'Type' => 'text/plain',
                'Owner' => 1
            ]);
        }
        if ($fileName === 'test img' && $userId === 1) {
            return new FileMetadata([
                'Name' => 'test img',
                'Size' => 1024,
                'Modified' => '2016-04-06T06:34:46+0000',
                'Created' => '2016-04-06T06:34:46+0000',
                'Type' => 'image/jpg',
                'Owner' => 1
            ]);
        }
        if ($fileName === 'test2img' && $userId === 2) {
            return new FileMetadata([
                'Name' => 'test img',
                'Size' => 1024,
                'Modified' => '2016-04-06T06:34:46+0000',
                'Created' => '2016-04-06T06:34:46+0000',
                'Type' => 'image/jpg',
                'Owner' => 2
            ]);
        }
        if (($fileName === 't1.txt' || $fileName === 't2' || $fileName === 'test img') 
                && $userId === 2) {
            throw new \app\models\data\AccessDenied();
        }
        if ($fileName === 'test2img' && $userId === 1) {
            throw new \app\models\data\AccessDenied();
        }
        if ($fileName === 'testEmptyMetadata' && $userId = 2) {
            return NULL;
        }
        throw new NotFound();        
    }
    
    public static function saveFileMetadata($metadata) {
        return FileRepositoryFS::saveFileMetadata($metadata);
    }
    
    public static function getFilesMetadata($userId) {
        switch ($userId) {
            case 1: {
                $files = [];
                $files[] = FakeFileRepository::getFileMetadata('t1.txt', 1);
                $files[] = FakeFileRepository::getFileMetadata('t2', 1);
                $files[] = FakeFileRepository::getFileMetadata('test img', 1);
                return $files;
            }
            case 2: {
                return FakeFileRepository::getFileMetadata('test2img', 2);
            }
            case 3: return [];
            default: NULL;
        }
    }
    
    public static function getFileStream($fileName, $userId) {
        return NULL;
    }
    
    public static function createFileFromStream($inputFileHandler, $fileName, $blockSizeForRead = 1024) {
        return FileRepositoryFS::createFileFromStream($inputFileHandler, $fileName, $blockSizeForRead);
    }

    public static function updateFileFromStream($inputFileHandler, $fileName, $startPosition = 0, $blockSizeForRead = 1014) {
        return FileRepositoryFS::updateFileFromStream($inputFileHandler, $fileName, $startPosition, $blockSizeForRead);
    }
    
    public static function deleteFile($fileName, $userId) {
        if ($fileName === 't1.txt' && $userId === 1) {
            return TRUE;
        }
        if ($fileName === 'test2img' && $userId !== 2) {
            throw new \app\models\data\AccessDenied();
        }
        if ($fileName === 'accessdenid' && $userId !== 1) {
            throw new \app\models\data\AccessDenied();
        }
        
        throw new NotFound();
    }
}

