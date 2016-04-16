<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;
use app\models\File;
use app\models\FileMetadata;
use tests\codeception\helper\FileHelper;
use app\models\data\FileRepositoryFS;

class FileMetadataTest extends TestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $fileNameTest = 'test.txt';
    
    private $metadata = NULL;

    protected function setUp()
    {
        parent::setUp();
        // create file for test\createFile
        $handle = FileHelper::createFileInMemory('test file');
        $this->metadata = FileRepositoryFS::createFileFromStream($handle, $this->fileNameTest, 1);
    }

    protected function tearDown() {
        unlink(File::getFullPathFile($this->fileNameTest));
        unlink(File::getFullPathMetadata($this->fileNameTest));
        parent::tearDown();
    }    

    public function testEtagInMetadata() {
        $pathToFile = File::getFullPathFile($this->fileNameTest);
        $md5 = md5_file($pathToFile);
        
        $this->metadata->setEtag();
        
        $this->assertEquals($md5, $this->metadata->Etag);
    }
    
    public function testMimeTypeInMetadata() {
        $pathToFile = File::getFullPathFile($this->fileNameTest);
        $handle = fopen($pathToFile, 'rb');
        $this->metadata->setType($handle);
        fclose($handle);
        //gzip becose fopen
        $this->assertEquals('application/x-gzip; charset=binary', $this->metadata->Type);
    }
    
    public function testCreateMetadata() {
        $metadata = new FileMetadata();
        $modified = \DateTime::createFromFormat(\DateTime::ISO8601, $metadata->Created);
        $this->assertEquals($metadata->Created, $metadata->Modified);
        $this->assertEquals((new \DateTime('now'))->format('Y-M-D HH:MM'), $modified->format('Y-M-D HH:MM'));
        $this->assertNull($metadata->Name);
        $this->assertNull($metadata->Size);
        $this->assertNull($metadata->Type);
        $this->assertNull($metadata->Etag);
        $this->assertNull($metadata->Owner);        
    }
    
    public function testUpdateMetadata() {
        $pathToFile = File::getFullPathFile($this->fileNameTest);
        $gz = gzopen($pathToFile, 'wb');
        gzwrite($gz, 'change text');
        gzclose($gz);
        
        $metadata = $this->metadata;
        $metadata->update(100, 'text/plane', TRUE);
        //check change
        $this->assertEquals(100, $metadata->Size);
        $this->assertEquals('text/plane', $metadata->Type);
        $this->assertEquals(md5_file($pathToFile), $metadata->Etag);
        $modified = \DateTime::createFromFormat(\DateTime::ISO8601, $metadata->Created);
        $this->assertEquals((new \DateTime('now'))->format('Y-M-D HH:MM'), $modified->format('Y-M-D HH:MM'));
        
        //check other filed
        $oldMetadata = FileRepositoryFS::getFileMetadata($this->fileNameTest, 1);
        $this->assertEquals($oldMetadata->Name, $metadata->Name);
        $this->assertEquals($oldMetadata->Owner, $metadata->Owner);
        $this->assertEquals($oldMetadata->Created, $metadata->Created);
    }
}