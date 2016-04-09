<?php

namespace tests\codeception\unit\models\data;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\data\FileRepositoryFS;
use tests\codeception\fake\FakeFileRepository;
use tests\codeception\helper\FileHelper;
use app\models\FileMetadata;

class FileRepositoryFSCreateFileTest extends TestCase
{
    use Specify;
    
    private $fileName = 'test_create.txt';
    
    private $fileWithData = 'test_update.txt';

    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function setUp()
    {
        parent::setUp();
        
        $path = File::getFullPathFile($this->fileName);
        $pathUpdate = File::getFullPathFile($this->fileWithData);
        FileHelper::createFile($path, 'test');
        FileHelper::createFile($pathUpdate, 'append text');
    }
    
    protected function tearDown() {
        $path = File::getFullPathFile($this->fileName);
        unlink($path);
        unlink(File::getFullPathFile($this->fileWithData));
        parent::tearDown();
    }
    
    public function testCreateFileFromStreamOk() {
        $pathToFile = File::getFullPathFile($this->fileName);
        $handle = fopen($pathToFile, 'r');
        $copyFileName = 'copy_' . $this->fileName;
        $this->assertTrue(FileRepositoryFS::createFileFromStream($handle, $copyFileName));
        //check file content
        $pathToCopyFile = File::getFullPathFile($copyFileName);
        $this->assertFileEquals($pathToFile, $pathToCopyFile);

        unlink($pathToCopyFile);
    }
    
    public function testUpdateFileFormStreamOk() {
        $pathToOriginFile = File::getFullPathFile($this->fileName);
        $updatedFileSize = filesize($pathToOriginFile);
        $pathToFileData = File::getFullPathFile($this->fileWithData);
        $handle = fopen($pathToFileData, 'r');
        
        $this->assertTrue(FileRepositoryFS::updateFileFromStream($handle, $this->fileName, $updatedFileSize));
        
        clearstatcache();
        // 'test_create.txt' + 'test_update.txt'
        $this->assertEquals(15, filesize($pathToOriginFile));
    }
}