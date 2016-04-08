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
    
    /**
     * File name
     * @var string 
     */
    public $Name;
    
    /**
     * File size in bytes
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
     * Create metadate from file
     * @param string $fileName
     * @param string $type Mime file type
     * @param integer $userId
     * @return \app\models\FileMetadata
     * @throws \InvalidArgumentException if the file doesn't exist
     */
    public static function createMetadata($fileName, $userId) {
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
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPathFile);
        finfo_close($finfo);
        $metadata->Type = $mimeType;
        return $metadata;
    }
}
