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
     * @throws NotFound
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
     * @return Array[string]|NULL
     * @throws NotFound
     */
    public static function getFilesMetadata($userId);
    
    /**
     * Return file stream, only read
     * @param string $fileName
     * @param integer $userId
     * @param boolean $compression TRUE for returns compressed stream
     * @return resource
     * @throws NotFound
     * @throws AccessDenied
     */
    public static function getFileStream($fileName, $userId, $compression = TRUE);

    /**
     * Create file and metadata for file from stream.
     * It doesn't update the file. For updates, use updateFileFromStream
     * @param resource $inputFileHandler
     * @param string $fileName
     * @param integer $userId
     * @return app\models\FileMetadata | NULL
     */
    public static function createFileFromStream($inputFileHandler, $fileName, $userId);
    
    /**
     * Update the file from strem, 
     * @param resource $inputFileHandler
     * @param string $fileName
     * @param integer $userId
     * @param boolean $overwriteAllFile If TRUE overwrite an existing file from $inputFileHandler, $startPosition ingored
     * @param integer $startPosition position in existing file for write data from $inputFileHandler, ignored when $overwriteAllFile = TRUE
     * @return app\models\FileMetadata
     * @throws \InvalidArgumentException if startPosition not int or more than file size
     * @throws NotFound
     * @throws AccessDenied
     */
    public static function updateFileFromStream($inputFileHandler, $fileName, $userId, $overwriteAllFile = TRUE, $startPosition = 0);
    
    /**
     * Deletes the file and metadata file
     * @param string $fileName
     * @param integer $userId
     * @return boolean
     * @throws NotFound if file doesn't exist
     */
    public static function deleteFile($fileName, $userId);
}
