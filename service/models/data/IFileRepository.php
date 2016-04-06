<?php

namespace app\models\data;

use app\models\FileMetadata;

/**
 * Interface for file operation
 * 
 * @author andrey
 */
interface IFileRepository {
    
    /**
     * Retrieves file metadata
     * @param string $fileName
     * @param integer $userId
     * @return FileMetadata|NULL 
     * @throws AccessDenied where user is't owner file
     */
    public static function getFileMetadata($fileName, $userId);
    
    /**
     * Save file metadata
     * @param FileMetadata $metadata
     * @retur boolean
     */
    public static function saveFileMetadata($metadata);

        /**
     * List of files by user
     * @param integer $userId
     * @return Array[string]
     */
    public static function getFiles($userId);
    
    public static function createFile($fileMetadata, $userId);
}
