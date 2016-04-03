<?php

namespace app\models;

use yii\base\Model;

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
}
