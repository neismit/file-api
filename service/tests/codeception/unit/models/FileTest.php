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
        Yii::$app->params['dataFolder'] = '/test';
    }

    // tests
    public function testGetFullPathFile()
    {
        $fullFilePath = File::getFullPathFile('testfilename.ss');        
        $this->assertEquals($fullFilePath, '/test/testfilename.ss');
    }

}