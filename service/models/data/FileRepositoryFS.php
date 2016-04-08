<?php

namespace app\models\data;

use app\models\data\IFileRepository;
use app\models\File;
use app\models\FileMetadata;

class FileRepositoryFS implements \app\models\data\IFileRepository {
    
    public static function getFileMetadata($fileName, $userId) {
        $pathMetadata = File::getFullPathMetadata($fileName);
        //assert('!is_null($pathMetadata)', 'Check params metadataFolder');
        
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
        $pathMetadata = File::getFullPathMetadata($metadata->Name);
        assert('!is_null($pathMetadata)', 'Check params metadataFolder');
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
