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
    
    public static function getFilesMetadata($userId) {
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
    
    public static function getFileStream($fileName, $userId) {
        $pathToMetadata = File::getFullPathMetadata($fileName);
        $metadata = FileRepositoryFS::loadFileMetadata($pathToMetadata);
        if ($metadata->Owner !== $userId) {
            throw new AccessDenied();
        }
        $pathToFile = File::getFullPathFile($fileName);
        $handle = fopen($pathToFile, 'r');
        return $handle;
    }
    
    public static function createFileFromStream($inputFileHandler, $fileName, $blockSizeForRead = 1024) {
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
        
        return TRUE;
    }

    public static function updateFileFromStream($inputFileHandler, $fileName, $startPosition = 0, $blockSizeForRead = 1014) {
        assert('!is_null($inputFileHandler)', 'updateFileFormStream, inputFileHandler in null');
        if (!is_int($startPosition)) {
            throw new \InvalidArgumentException();
        }
        $pathToFile = File::getFullPathFile($fileName);
        if ($startPosition > filesize($pathToFile)) {
            throw new \InvalidArgumentException();
        }
        $saveFileHandler = fopen($pathToFile, 'a');
        fseek($saveFileHandler, $startPosition);
        while ($data = fread($inputFileHandler, $blockSizeForRead)) {
            fwrite($saveFileHandler, $data);
        }
        fclose($inputFileHandler);
        fflush($saveFileHandler);
        fclose($saveFileHandler);
        return TRUE;
    }
    
    public static function deleteFile($fileName, $userId) {
        $path = File::getFullPathFile($fileName);
        $metadata = FileRepositoryFS::getFileMetadata($fileName, $userId);
        if ($metadata->Owner !== $userId) {
            throw new AccessDenied();
        }
        unlink($path);
        $pathToMetadata = File::getFullPathMetadata($fileName);
        unlink($pathToMetadata);
        return TRUE;
    }
    
    /**
     * Load the metadata file from FS and decode it
     * @param type $fullMetadataPath
     * @return FileMetadata
     * @throws NotFound file metadata not found
     */
    private static function loadFileMetadata($fullMetadataPath) {
        if (!file_exists($fullMetadataPath)) {
            throw new NotFound();
        }
        $handle = fopen($fullMetadataPath, "r");
        $jsonMetadata = fgets($handle);
        fclose($handle);
        $metadata = new FileMetadata(json_decode($jsonMetadata, TRUE));
        return $metadata;
    }
}
