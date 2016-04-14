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

class UpdateFileTest extends TestCase
{
    use Specify;
    
    private $fileName = 'test_create.txt';
    
    private $fileWithData = 'test_update.txt';
    
    private $metadata = NULL;
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function setUp()
    {
        parent::setUp();
        $pathUpdate = File::getFullPathFile($this->fileWithData);
        FileHelper::createFile($pathUpdate, ' append text');
        
        $path = File::getFullPathFile($this->fileName);
        FileHelper::createFile($path, 'test');
        $metadata = FakeFileRepository::getFileMetadata('t1.txt', 1);
        $metadata->Name = $this->fileName;
        $metadata->Size = 4;
        FileRepositoryFS::saveFileMetadata($metadata);
        $this->metadata = $metadata;
    }
    
    protected function tearDown() {
        $path = File::getFullPathFile($this->fileName);
        unlink($path);
        unlink(File::getFullPathFile($this->fileWithData));
        $pathToMetadata = File::getFullPathMetadata($this->fileName);
        if (file_exists($pathToMetadata)) {
            unlink($pathToMetadata);
        }        
        parent::tearDown();
    }

    
    public function testUpdateFileFormStreamOk() {
        $pathToOriginFile = File::getFullPathFile($this->fileName);
        
        $metadata = $this->metadata;
        // change data for testing purpose
        $dateCreate = new \DateTime('now');
        $dateCreate->setDate(2015, 1, 1);
        $dateCreateString = $dateCreate->format(\DateTime::ISO8601);
        $metadata->Created = $dateCreateString;
        $metadata->Modified = $dateCreateString;
        FileRepositoryFS::saveFileMetadata($metadata);
        
        $pathToFileData = File::getFullPathFile($this->fileWithData);
        $handle = fopen($pathToFileData, 'r');
        
        $updateMetadata = FileRepositoryFS::updateFileFromStream($handle, $this->fileName, 1, TRUE, $metadata->Size);
        fclose($handle);
        
        // check metadata
        $this->assertEquals($metadata->Created, $updateMetadata->Created);
        $this->assertNotEquals($metadata->Modified, $updateMetadata->Modified);
        
        $dateUpdate = new \DateTime('now');
        $dateUpdateString = $dateUpdate->format(\DateTime::ISO8601);
        $this->assertEquals($dateUpdateString, $updateMetadata->Modified);
        
        $this->assertEquals($metadata->Name, $updateMetadata->Name);
        $this->assertEquals($metadata->Owner, $updateMetadata->Owner);
        $this->assertNotEquals($metadata->Size, $updateMetadata->Size);
        $this->assertEquals(16, $updateMetadata->Size);
        // check file content
        $handleUpdated = FileRepositoryFS::getFileStream($this->fileName, 1, FALSE);
        $str = fgets($handleUpdated);
        fclose($handleUpdated);
        
        $this->assertEquals('test append text', $str);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUpdateFileFormStreamParameterIsString() {
        $handle = fopen('php://temp', 'r+');
        $updateMetadata = FileRepositoryFS::updateFileFromStream($handle, $this->fileName, 1, TRUE, 'fa123');
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUpdateFileFormStreamParameterMoThanFileSize() {
        $handle = fopen('php://temp', 'r+');
        $updateMetadata = FileRepositoryFS::updateFileFromStream($handle, $this->fileName, 1, TRUE, 10000);
    }
    
    /**
     * @expectedException \app\models\data\NotFound
     */
    public function testUpdateFileFormStreamParameterNotFoundFile() {
        $handle = fopen('php://temp', 'r+');
        $updateMetadata = FileRepositoryFS::updateFileFromStream($handle, '111', 1, TRUE, 4);
    }
    
    /**
     * @expectedException \app\models\data\NotFound
     */
    public function testUpdateFileFormStreamParameterNotFoundMetadata() {
        unlink(File::getFullPathMetadata($this->fileName));
        $handle = fopen('php://temp', 'r+');
        $updateMetadata = FileRepositoryFS::updateFileFromStream($handle, '111', 1, TRUE, 4);
    }
    
    /**
     * @expectedException \app\models\data\AccessDenied
     */
    public function testUpdateFileFormStreamParameterAccesDenied() {
        $handle = fopen('php://temp', 'r+');
        $updateMetadata = FileRepositoryFS::updateFileFromStream($handle, $this->fileName, 2, TRUE, 4);
    }
}