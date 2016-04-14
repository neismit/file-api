<?php

namespace tests\codeception\unit\models;

use yii\codeception\TestCase;
use app\models\File;
use app\models\FileMetadata;
use tests\codeception\helper\FileHelper;

class FileMetadataTest extends TestCase
{
    /**
     * @var \UnitTester
     */
    protected $tester;

//    private $fileNameTest = 'test.txt';
//
//    private $fileNameUpdateTest = 'test_update';
//    
//    protected function setUp()
//    {
//        parent::setUp();
//        // create file for test\createFile
//        FileHelper::createFile(File::getFullPathFile($this->fileNameTest), 'test');
//        FileHelper::createFile(File::getFullPathFile($this->fileNameUpdateTest), 'test');
//    }
//
//    protected function tearDown() {
//        unlink(File::getFullPathFile($this->fileNameTest));
//        unlink(File::getFullPathFile($this->fileNameUpdateTest));
//        parent::tearDown();
//    }
//
//    /**
//     * Create file metadata, check all fileds
//     */
//    public function testCreateMetadata() {
//        $metadata = FileMetadata::createMetadata($this->fileNameTest, 1);
//        $this->assertInstanceOf(FileMetadata::class, $metadata);
//        
//        $this->assertEquals($this->fileNameTest, $metadata->Name);
//        
//        $this->assertEquals(12, $metadata->Size);
//
//        $modified = \DateTime::createFromFormat(\DateTime::ISO8601, $metadata->Modified);
//        $this->assertEquals((new \DateTime('now'))->format('Y-M-D'), $modified->format('Y-M-D'));
//
//        $this->assertEquals($metadata->Modified, $metadata->Created);
//        $this->assertEquals('text/plain', $metadata->Type);
//        $this->assertEquals(1, $metadata->Owner);
//    }
//    
//    /**
//     * Testing create metadata, file not found
//     * @expectedException \InvalidArgumentException
//     */
//    public function testCreateMetadataFileNotFound() {
//        FileMetadata::createMetadata('textFail', 1);
//    }
//    
//    public function testUpdateMetadataOk() {
//        $metadata = FileMetadata::createMetadata($this->fileNameUpdateTest, 1);
//        $dateCreate = new \DateTime('now');
//        $dateCreate->setDate(2015, 1, 1);
//        $dateCreateString = $dateCreate->format(\DateTime::ISO8601);
//        $metadata->Created = $dateCreateString;
//        $metadata->Modified = $dateCreateString;
//        // change file
//        $pathToFile = File::getFullPathFile($this->fileNameUpdateTest);
//        $handle = fopen($pathToFile, 'w');
//        fwrite($handle, '1');
//        fflush($handle);
//        fclose($handle);
//        
//        $metadata->update();
//        
//        // check changes
//        $this->assertEquals(filesize($pathToFile), $metadata->Size);
//        $this->assertNotEquals($metadata->Created, $metadata->Modified);
//        $this->assertNotEquals($dateCreateString, $metadata->Modified);
//    }

}