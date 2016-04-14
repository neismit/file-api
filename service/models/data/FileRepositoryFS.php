<?php

namespace app\models\data;

use app\models\data\IFileRepository;
use app\models\File;
use app\models\FileMetadata;
use app\models\StreamHelper;

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
        fwrite($handle, $jsonMetadata);
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
    
    public static function getFileStream($fileName, $userId, $compression = TRUE, $position = 0, $length = 0) {
        $pathToMetadata = File::getFullPathMetadata($fileName);
        $metadata = FileRepositoryFS::loadFileMetadata($pathToMetadata);
        if ($metadata->Owner !== $userId) {
            throw new AccessDenied();
        }
        $pathToFile = File::getFullPathFile($fileName);
        $handle = fopen($pathToFile, 'r');
        if (!$compression) {
            StreamHelper::atachDecompressionFilter($handle);
        }
        return $handle;
    }
    
    public static function createFileFromStream($inputFileHandler, $fileName, $userId, $compression = FALSE) {
        //ToDo: pass empty string
        assert('!is_null($inputFileHandler) || !empty($inputFileHandler)', 
                'createFileFormStream, inputFileHandler in null');
        assert('!is_null($fileName) || !empty($fileName)', 'createFileFormStream, $fileName is null');
        assert('!is_null($userId) || !empty($userId)', 'createFileFormStream, $userId is null');
        
        $metadata = new FileMetadata();
        $metadata->Name = $fileName;
        $metadata->Owner = $userId;
        
        $blockSizeForRead = \Yii::$app->params['blockSize'];
        // read input stream in tmp
        $maxMemory = \Yii::$app->params['tempMaxmemory'];
        $tmpStream = fopen("php://temp/maxmemory:$maxMemory", 'r+');
        if ($compression) {
            StreamHelper::atachDecompressionFilter($inputFileHandler);
        }
        while ($data = fread($inputFileHandler, $blockSizeForRead)) {
            fwrite($tmpStream, $data);           
        }
//        fclose($inputFileHandler);
        $stat = fstat($tmpStream);
        $metadata->Size = $stat['size'];
        $metadata->Type = FileRepositoryFS::getMimeType($tmpStream);
        
        fseek($tmpStream, 0);
        
        $pathToFile = File::getFullPathFile($fileName);
        $saveFileHandler = fopen($pathToFile, 'w');

        StreamHelper::atachCompressionFilter($saveFileHandler);
        while ($data = fread($tmpStream, $blockSizeForRead)) {
            fwrite($saveFileHandler, $data);
        }        
        
        fflush($saveFileHandler);
        fclose($saveFileHandler);
        
        FileRepositoryFS::saveFileMetadata($metadata);
        
        return $metadata;
    }
    
    /**
     * Get mime type on stream
     * @param resource $handle not close handle, not change pointer position
     * @return string mime type
     */
    private static function getMimeType($handle) {
        $position = ftell($handle);
        fseek($handle, 0);
        $str = fgets($handle, 100);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->buffer($str);
        fseek($handle, $position);
        return $type;
    }

    public static function updateFileFromStream($inputFileHandler, $fileName, $userId, $compression = FALSE, $startPosition = 0) {
        //ToDo: more checks the input parameters
        assert('!is_null($inputFileHandler)', 'updateFileFormStream, inputFileHandler in null');
        // check conditions
        if (!is_int($startPosition)) {
            throw new \InvalidArgumentException();
        }
        $pathToFile = File::getFullPathFile($fileName);
        if (!file_exists($pathToFile)) {
            throw new NotFound();
        }
        $metadata = FileRepositoryFS::loadFileMetadata(File::getFullPathMetadata($fileName));
        if ($metadata->Owner !== $userId) {
            throw new AccessDenied();
        }
        
        $savedFileHandler = fopen($pathToFile, 'rb');
        StreamHelper::atachDecompressionFilter($savedFileHandler);
        // create tmp stream from exist file
        $maxMemory = \Yii::$app->params['tempMaxmemory'];
        $tmpStream = fopen("php://temp/maxmemory:$maxMemory", 'r+');
        $blockSizeForRead = \Yii::$app->params['blockSize'];
        // extract saved file
        while ($data = fread($savedFileHandler, $blockSizeForRead)) {
            fwrite($tmpStream, $data);
        }
        fclose($savedFileHandler);
                       
        if ($startPosition > $metadata->Size) {
            throw new \InvalidArgumentException();
        }
        
        // update tmp stream
        fseek($tmpStream, $startPosition);
        
        if ($compression) {
            StreamHelper::atachDecompressionFilter($inputFileHandler);
        }        
        while ($data = fread($inputFileHandler, $blockSizeForRead)) {
            fwrite($tmpStream, $data);
        }
//        fclose($inputFileHandler);
        // update metadata
        $stat = fstat($tmpStream);
        $metadata->update($stat['size']);
        FileRepositoryFS::saveFileMetadata($metadata);
        
        // write updated file
        fseek($tmpStream, 0);
        $updateFile = fopen($pathToFile, 'wb');
        StreamHelper::atachCompressionFilter($updateFile);
        while ($data = fread($tmpStream, $blockSizeForRead)) {
            fwrite($updateFile, $data);
        }
        fclose($tmpStream);
        fflush($updateFile);
        fclose($updateFile);
        
        return $metadata;
    }
    
    public static function deleteFile($fileName, $userId) {
        $path = File::getFullPathFile($fileName);
        if (!file_exists($filename)) {
            throw new NotFound();
        }
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
            \Yii::error('loadFileMetadata: not found ' . $fullMetadataPath);
            throw new NotFound();
        }
        $handle = fopen($fullMetadataPath, "r");
        $jsonMetadata = fgets($handle);
        fclose($handle);
        $metadata = new FileMetadata(json_decode($jsonMetadata, TRUE));
        return $metadata;
    }
}
