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
    
    public static function getFileStream($fileName, $userId, $compression = TRUE) {
        $pathToMetadata = File::getFullPathMetadata($fileName);
        $metadata = FileRepositoryFS::loadFileMetadata($pathToMetadata);
        if ($metadata->Owner !== $userId) {
            throw new AccessDenied();
        }
        $pathToFile = File::getFullPathFile($fileName);
        $handle = NULL;
        // return the full file
//        if ($position === 0 && $length === 0) {
            if ($compression) {
                $handle = fopen($pathToFile, 'rb');
            } else {
                $handle = gzopen($pathToFile, 'rb');
            }
            return $handle;
//        }
        //ToDo:
        // return part of the file
//        if ($position + $length > $metadata->Size) {
//            throw new \InvalidArgumentException();
//        }
//        $handle = gzopen($pathToFile, 'rb');
//        $maxMemory = \Yii::$app->params['tempMaxmemory'];
//        $tmpPartFile = fopen("php://temp/maxmemory:$maxMemory", 'r+b');
//        stream_copy_to_stream($handle, $tmpPartFile, $length, $position);
//        gzclose($handle);
//        if (!$compression) {
//            fseek($tmpPartFile, 0);
//            return $tmpPartFile;
//        }
//        
//                
//        return NULL;
    }
    
    public static function createFileFromStream($inputFileHandler, $fileName, $userId) {
        //ToDo: pass empty string
        assert('!is_null($inputFileHandler) || !empty($inputFileHandler)', 
                'createFileFormStream, inputFileHandler in null');
        assert('!is_null($fileName) || !empty($fileName)', 'createFileFormStream, $fileName is null');
        assert('!is_null($userId) || !empty($userId)', 'createFileFormStream, $userId is null');
        
        $metadata = new FileMetadata();
        $metadata->Name = $fileName;
        $metadata->Owner = $userId;
        
        $pathToFile = File::getFullPathFile($fileName);
        
        $level = \Yii::$app->params['compressionLevel'];
        $gzipFile = gzopen($pathToFile, 'wb'.$level);
        $size = stream_copy_to_stream($inputFileHandler, $gzipFile);
        $metadata->Size = $size;
        gzclose($gzipFile);
        
        $gzipFileOpen = gzopen($pathToFile, 'rb'.$level);
        $metadata->Type = FileMetadata::getMimeType($gzipFileOpen);
        gzclose($gzipFileOpen);
        $metadata->setEtag();
        
        FileRepositoryFS::saveFileMetadata($metadata);
        
        return $metadata;
    }
    
    /**
     * Get mime type on stream
     * @param resource $handle not close handle, not change pointer position, resource must be acces for read
     * @return string mime type
     */
//    private static function getMimeType($handle) {
//        $position = ftell($handle);
//        fseek($handle, 0);
//        $str = fgets($handle, 100);
//        $finfo = new \finfo(FILEINFO_MIME);
//        $type = $finfo->buffer($str);
//        fseek($handle, $position);
//        return $type;
//    }

    public static function updateFileFromStream($inputFileHandler, $fileName, $userId, $overwriteAllFile = FALSE, $startPosition = 0) {
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
        // read file
        $maxMemory = \Yii::$app->params['tempMaxmemory'];
        $tmpStream = fopen("php://temp/maxmemory:$maxMemory", 'r+');
        if (!$overwriteAllFile) {
            if ($startPosition > $metadata->Size) {
                fclose($tmpStream);
                throw new \InvalidArgumentException();
            }
            $existFile = gzopen($pathToFile, 'rb');        
            stream_copy_to_stream($existFile, $tmpStream);
            fseek($tmpStream, $startPosition);
            gzclose($existFile);
        }
        stream_copy_to_stream($inputFileHandler, $tmpStream);
        fseek($tmpStream, 0);
        
        $level = \Yii::$app->params['compressionLevel'];
        $updatedFile = gzopen($pathToFile, 'wb' . $level);
        $newSize = stream_copy_to_stream($tmpStream, $updatedFile);
        fclose($tmpStream);
        gzclose($updatedFile);
        
        // update metadata
        $file = gzopen($pathToFile, 'rw');
        $type = FileMetadata::getMimeType($file);
        gzclose($file);                
        $metadata->update($newSize, $type, TRUE);
        FileRepositoryFS::saveFileMetadata($metadata);
        
        return $metadata;
    }
    
    public static function deleteFile($fileName, $userId) {
        $path = File::getFullPathFile($fileName);
        if (!file_exists($path)) {
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
