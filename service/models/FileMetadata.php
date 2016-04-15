<?php

namespace app\models;

use yii\base\Model;
use app\models\File;

/**
 * Description of file metadata
 *
 * @author andrey
 */
class FileMetadata extends Model {
    
    public function __construct($config = array()) {
        $this->Created = (new \DateTime('now'))->format(\DateTime::ISO8601);
        $this->Modified = $this->Created;
        parent::__construct($config);
    }

        /**
     * File name
     * @var string 
     */
    public $Name;
    
    /**
     * File size in bytes
     * ToDo: This is compressed file size
     * @var integer 
     */
    public $Size;
    
    /**
     * The last time the file was modified
     * @var string 
     */
    public $Modified;
    
    /**
     * Created date
     * @var string 
     */
    public $Created;
    
    /**
     * Owner
     * @var integer
     */
    public $Owner;
    
    /**
     * Mime file type
     * @var string
     */
    public $Type;
    
    /**
     * @deprecated 2016-04-13 Metadata created in IFileRepository/createFileFromStream
     * Create metadate from file
     * @param string $fileName
     * @param string $type Mime file type
     * @param integer $userId
     * @return \app\models\FileMetadata
     * @throws \InvalidArgumentException if the file doesn't exist
     */
    public static function createMetadata($fileName, $userId, $size, $mimeType) {
        $fullPathFile = File::getFullPathFile($fileName);
        if (!file_exists($fullPathFile)) {
            throw new \InvalidArgumentException();
        }
        $metadata = new FileMetadata();
        $metadata->Name = $fileName;
        $metadata->Size = filesize($fullPathFile);
        $metadata->Created = (new \DateTime('now'))->format(\DateTime::ISO8601);
        $metadata->Modified = $metadata->Created;
        $metadata->Owner = $userId;
        
//        $finfo = finfo_open(FILEINFO_MIME_TYPE);
//        $mimeType = finfo_file($finfo, $fullPathFile);
//        finfo_close($finfo);
        $metadata->Type = FileMetadata::getMimeType($fullPathFile);
        return $metadata;
    }
    
    /**
     * @deprecated 2016-04-13 Metadata created in IFileRepository/createFileFromStream
     * 
     */
    private static function getMimeType($pathToFile) {
        $handle = fopen($pathToFile, 'rb');
        $params = \Yii::$app->params['compressionParameters'];
        stream_filter_append($handle, 'zlib.inflate', STREAM_FILTER_READ, $params);        
        
        $str = fgets($handle, 100);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->buffer($str);
        fclose($handle);
        return $type;
    }
    
    /**
     * Update metadata when file changed
     * @param integer $size
     * @param string|NULL $mimeType
     */
    public function update($size, $mimeType = NULL) {
        $this->Modified = (new \DateTime('now'))->format(\DateTime::ISO8601);
        $this->Size = $size;
        if (!is_null($mimeType)) {
            $this->Type = $mimeType;
        }
    }
}
