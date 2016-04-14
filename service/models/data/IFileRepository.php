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
     * @param integer $position position in file
     * @param integer $length length return content
     * @return resource
     * @throws NotFound
     * @throws AccessDenied
     */
    public static function getFileStream($fileName, $userId, $compression = TRUE, $position = 0, $length = 0);

    /**
     * Create file and metadata for file from stream.
     * It doesn't update the file. For updates, use updateFileFromStream
     * @param resource $inputFileHandler
     * @param string $fileName
     * @param integer $userId
     * @param boolean $compression if $inputFileHandler is contains compressed stream
     * @return app\models\FileMetadata | NULL
     */
    public static function createFileFromStream($inputFileHandler, $fileName, $userId, $compression = FALSE);
    
    /**
     * Update the file from strem, 
     * @param resource $inputFileHandler
     * @param string $fileName
     * @param integer $userId
     * @param boolean $compression if $inputFileHandler is contains compressed stream
     * @param integer $startPosition position in existing file for write data from $inputFileHandler
     * @return app\models\FileMetadata
     * @throws \InvalidArgumentException if startPosition not int or more than file size
     * @throws NotFound
     * @throws AccessDenied
     */
    public static function updateFileFromStream($inputFileHandler, $fileName, $userId, $compression = FALSE, $startPosition = 0);
    
    /**
     * Deletes the file and metadata file
     * @param string $fileName
     * @param integer $userId
     * @return boolean
     * @throws NotFound if file doesn't exist
     */
    public static function deleteFile($fileName, $userId);
}
