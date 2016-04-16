<?php

namespace app\models;

use yii\base\Model;
use app\models\File;

/**
 * Metadata file
 * When create file all field FileMetadata must be fill
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
     * Hash file
     * @var string
     */
    public $Etag;

    /**
     * Calculate and set up Etag
     * @param string $pathToFile
     */
    public function setEtag() {
        assert('!is_null($this->Name) || !empty(trim($this->Name))', 'Name is null or empty');
        $pathToFile = File::getFullPathFile($this->Name);
        $this->Etag = FileMetadata::calcEtag($pathToFile);
    }

    /**
     * Calculate hash of file for etag
     * @param string $pathToFile
     * @return string Hash file
     */
    public static function calcEtag($pathToFile) {
        assert('file_exists($pathToFile)', 'File not found in calcEtag');
        return md5_file($pathToFile);
    }
    
    public function setType($handle) {
        $this->Type = FileMetadata::getMimeType($handle);
    }

    /**
     * Get mime type on stream
     * @param resource $handle not close handle, not change pointer position, resource must be access for read
     * @return string mime type with encoding
     */
    public static function getMimeType($handle) {
        $position = ftell($handle);
        fseek($handle, 0);
        $str = fgets($handle, 100);
        $finfo = new \finfo(FILEINFO_MIME);
        $type = $finfo->buffer($str);
        fseek($handle, $position);
        return $type;
    }

    /**
     * Update metadata when file changed
     * @param integer $size
     * @param string|NULL $mimeType
     * @param boolean $calcEtag Calculate etag for current file
     */
    public function update($size, $mimeType = NULL, $calcEtag = TRUE) {
        $this->Modified = (new \DateTime('now'))->format(\DateTime::ISO8601);
        $this->Size = $size;
        if (!is_null($mimeType)) {
            $this->Type = $mimeType;
        }
        if ($calcEtag) {
            $this->setEtag();
        }
    }
}
