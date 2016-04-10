<?php

namespace tests\codeception\unit\models\data;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\data\FileRepositoryFS;
use tests\codeception\fake\FakeFileRepository;
use app\models\FileMetadata;
use tests\codeception\helper\FileHelper;

class FileRepositoryFSDeleteTest extends TestCase
{
    use Specify;
    
    private $fileName = 'test_delete.txt';

    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function setUp()
    {
        parent::setUp();
        $fullPathToFile = File::getFullPathFile($this->fileName);
        FileHelper::createFile($fullPathToFile, 'content');
        $metadata = FileMetadata::createMetadata($this->fileName, 1);
        FileRepositoryFS::saveFileMetadata($metadata);
    }
    
    protected function tearDown() {
        $pathToFile = File::getFullPathFile($this->fileName);
        if (file_exists($pathToFile)) {
            unlink($pathToFile);
        }
        
        $pathToMetadata = File::getFullPathMetadata($this->fileName);
        if (file_exists($pathToMetadata)) {
            unlink($pathToMetadata);
        }
        parent::tearDown();
    }
    
    public function testDeleteFileOk() {
        $this->assertTrue(FileRepositoryFS::deleteFile($this->fileName, 1));
        $path = File::getFullPathFile($this->fileName);
        $this->assertFileNotExists($path);
        
        $pathToMetadata = File::getFullPathMetadata($this->fileName);
        $this->assertFileNotExists($pathToMetadata);
    }
    
    /**
     * Testing delete file
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteFileInvalidFileName() {
        FileRepositoryFS::deleteFile('fake_file', 1);
    }
    
    /**
     * Testing delete file
     * @expectedException \app\models\data\AccessDenied
     */
    public function testDeleteFileAccessDenied() {
        FileRepositoryFS::deleteFile($this->fileName, 2);
    }    
}
    