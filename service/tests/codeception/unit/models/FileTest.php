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
        $fullFilePath = File::getFullPathFile('testfilename.ss');        
        verify($fullFilePath)->equals(Yii::$app->params['dataFolder'] . '/testfilename.ss');
    }
    
    public function testGetFullPathMetadataFile()
    {
        $fullFilePath = File::getFullPathMetadata('testfilename.ss');        
        verify($fullFilePath)->equals(Yii::$app->params['metadataFolder'] . '/testfilename.ss');
    }

}