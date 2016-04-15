<?php

namespace tests\codeception\unit\models\data\fileRepositoryFS;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\data\FileRepositoryFS;
use app\models\FileMetadata;
use tests\codeception\helper\FileHelper;
use tests\codeception\fake\FakeFileRepository;

class DeleteTest extends TestCase
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
        $metadata = FakeFileRepository::getFileMetadata('t1.txt', 1);
        $metadata->Name = $this->fileName;
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
     * @expectedException app\models\data\NotFound
     */
    public function testDeleteFileInvalidFileName() {
        FileRepositoryFS::deleteFile('fake_file', 1);
    }
    
    /**
     * Testing delete file, Access denied
     * @expectedException app\models\data\AccessDenied
     */
    public function testDeleteFileAccessDenied() {
        FileRepositoryFS::deleteFile($this->fileName, 2);
    }    
    
       /**
     * Testing delete file
     * @expectedException \app\models\data\NotFound
     */
    public function testDeleteFileNotFoundMetadatas() {
        $pathToMetadata = File::getFullPathMetadata($this->fileName);
        unlink($pathToMetadata);
        FileRepositoryFS::deleteFile($this->fileName, 1);
    }    
}
    