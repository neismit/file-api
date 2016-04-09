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
     * @return Array[string]|NULL
     */
    public static function getFiles($userId);
    
    /**
     * Create file and metadata for file from stream.
     * It doesn't update the file. For updates, use updateFileFromStream
     * @param resource $inputFileHandler
     * @param string $fileName
     * @param integer $blockSizeForRead
     * @return boolean
     */
    public static function createFileFromStream($inputFileHandler, $fileName, $blockSizeForRead = 1024);
    
    /**
     * Update the file from strem, 
     * @param resource $inputFileHandler
     * @param string $fileName
     * @param integer $startPosition
     * @param integer $blockSizeForRead
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public static function updateFileFromStream($inputFileHandler, $fileName, $startPosition = 0, $blockSizeForRead = 1014);
}
