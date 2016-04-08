<?php

namespace app\models\data;

use app\models\data\IFileRepository;
use app\models\File;
use app\models\FileMetadata;

class FileRepositoryFS implements \app\models\data\IFileRepository {
    
    public static function getFileMetadata($fileName, $userId) {
        assert('!is_null($fileName)', 'getFileMetadata, $fileName is null');
        $pathMetadata = File::getFullPathMetadata($fileName);
        
        $metadata = FileRepositoryFS::loadFileMetadata($pathMetadata);
        if (is_null($metadata)) {
            return NULL;
        }
        if ($userId === $metadata->Owner) {
            return $metadata;
        }
        else {
            throw new AccessDenied();
        }
    }
    
    public static function saveFileMetadata($metadata) {
        assert('!is_null($metadata)', 'saveFileMetadata, metadata is null');
        $pathMetadata = File::getFullPathMetadata($metadata->Name);
        $jsonMetadata = json_encode($metadata);
        $handle = fopen($pathMetadata, 'w');
        if (is_null($handle)) {
            return FALSE;
        }
        if (!fwrite($handle, $jsonMetadata . PHP_EOL)) {
            fclose($handle);
            return FALSE;
        }
        fflush($handle);
        fclose($handle);
        return TRUE;
    }
    
    public static function getFiles($userId) {
        $pathMetadataDir = \Yii::$app->params['metadataFolder'];
        $metaFiles = scandir($pathMetadataDir);
        $filesList = [];
        if (!$metaFiles) {
            return NULL;
        }
        foreach ($metaFiles as $fileName) {
            $fullPath = File::getFullPathMetadata($fileName);
            $metadata = FileRepositoryFS::loadFileMetadata($fullPath);
            if (is_null($metadata)) {
                continue;
            }
            if ($metadata->Owner === $userId) {
                $filesList[] = $metadata;
            }
        }
        return $filesList;
    }
    
    public static function createFileFromStream($inputFileHandler, $fileName, $userId, $blockSizeForRead = 1024) {
        assert('!is_null($inputFileHandler)', 'createFileFormStream, inputFileHandler in null');
        assert('!is_null($fileName)', 'createFileFormStream, $fileName is null');
        
        $pathToFile = File::getFullPathFile($fileName);
        $saveFileHandler = fopen($pathToFile, 'w');
        while ($data = fread($inputFileHandler, $blockSizeForRead)) {
            fwrite($saveFileHandler, $data);
        }
        fclose($inputFileHandler);
        fflush($saveFileHandler);
        fclose($saveFileHandler);
                
        $metadata = FileMetadata::createMetadata($fileName, $userId);
        if (!FileRepositoryFS::saveFileMetadata($metadata)) {
            unlink($pathToFile);
            return FALSE;
        }
        
        return TRUE;
    }
    
    public static function updateFileFromStream($inputFileHandler, $fileName, $startPosition, $userId) {
//        if (is_null($inputFileHandler)) {
//            return FALSE;
//        }
//        $pathToFile = File::getFullPathFile($fileName);
//        $pathToMetadata = File::getFullPathMetadata($fileName);
//        $metadata = NULL;
//        if (file_exists($pathToMetadata)) {
//            $metadata = FileRepositoryFS::loadFileMetadata($pathToMetadata);
//            if ($metadata->Owner !== $userId) {
//                throw new AccessDenied();
//            }
//        }
//        $saveFileHandler = fopen($pathToFile, 'w');
//        while ($data = fread($inputFileHandler, 1024)) {
//            fwrite($saveFileHandler, $data);
//        }
//        fclose($inputFileHandler);
//        fflush($saveFileHandler);
//        fclose($saveFileHandler);
//        
//        if (is_null($metadata)) {
//            
//        } else {
//            
//        }
//        return TRUE;
    }

    public static function createFile($fileMetadata, $userId) {
        return null;
    }
    
    private static function loadFileMetadata($fullMetadataPath) {
        if (!file_exists($fullMetadataPath)) {
            return NULL;
        }
        $handle = fopen($fullMetadataPath, "r");
        $jsonMetadata = fgets($handle);
        fclose($handle);
        $metadata = new FileMetadata(json_decode($jsonMetadata, TRUE));
        return $metadata;
    }
}
