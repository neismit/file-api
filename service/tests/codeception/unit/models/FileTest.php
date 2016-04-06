<?php

namespace tests\codeception\unit\models;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;

class FileTest extends TestCase
{
    use Specify;
    
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function setUp()
    {
        parent::setUp();
    }

    // tests
    public function testGetFullPathFile()
    {
        $this->specify('Check get full path file from config', function() {
            $fullFilePath = File::getFullPathFile('testfilename.ss');        
            verify($fullFilePath)->equals(Yii::$app->params['dataFolder'] . '/testfilename.ss');
        }); 
    }
    
    public function testGetFullPathMetadataFile()
    {
        $this->specify('Check get full path metadata file from config', function() {
            $fullFilePath = File::getFullPathMetadata('testfilename.ss');        
            verify($fullFilePath)->equals(Yii::$app->params['metadataFolder'] . '/testfilename.ss');
        }); 
    }

}