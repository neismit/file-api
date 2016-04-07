<?php

namespace tests\codeception\unit\models\data;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\data\FileRepositoryFS;
use tests\codeception\fake\FakeFileRepository;

class FileRepositoryFSTest extends TestCase
{
    use Specify;
    
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $fileName = 't1.txt';
    
    protected function setUp()
    {
        parent::setUp();
        $this->createMetadataFile($this->fileName);
    }
    
    private function createMetadataFile($fileName) {
        $pathMeta = File::getFullPathMetadata($fileName);
        $handle = fopen($pathMeta, 'w');
        $json = json_encode(FakeFileRepository::getFileMetadata($fileName, 1));
        fwrite($handle, $json . PHP_EOL);
        fflush($handle);
        fclose($handle);
    }
    
    protected function tearDown() {
        $pathMeta = File::getFullPathMetadata($this->fileName);
        unlink($pathMeta);
        
        parent::tearDown();        
    }
    
    /**
     * Testing get metadata file
     */
    public function testLoadFileMetadataOk() {
        $metadata = FileRepositoryFS::getFileMetadata($this->fileName, 1);
        $this->assertInstanceOf(\app\models\FileMetadata::class, $metadata);
        $this->assertEquals($this->fileName, $metadata->Name);
        $this->assertEquals(1, $metadata->Owner);
    }
    
    /**
     * Testing access denied exception in get metatada
     * @expectedException app\models\data\AccessDenied
     */
    public function testLoadFileMetadataAccessDenied() {
        FileRepositoryFS::getFileMetadata($this->fileName, 2);
    }

    /**
     * Error in file name, file not found
     */
    public function testLoadFileMetadataNotFound() {
        $metadata = FileRepositoryFS::getFileMetadata('t111.txt', 1);
        $this->assertEquals(NULL, $metadata);
    }
    
    public function testSaveFileMetadata() {
        $metadata = FakeFileRepository::getFileMetadata('t1.txt', 1);
        $metadata->Name = 't1TestSave.txt';
        $this->assertInstanceOf(\app\models\FileMetadata::class, $metadata);
//        verify($metadata)->isInstanceOf('app\models\FileMetadata');

        $this->assertTrue(FileRepositoryFS::saveFileMetadata($metadata));
//        verify(FileRepositoryFS::saveFileMetadata($metadata))->true();

        $pathToFile = File::getFullPathMetadata('t1TestSave.txt');
        $this->assertFileExists($pathToFile);
        //verify(file_exists($pathToFile))->true();

        // delete saved file
        unlink($pathToFile);
    }
    
    // test with specify, many errors and memory!

//    public function testLoadFileMetadataOk() {
//        $this->specify('Load file metadata for t1.txt, userId = 1', function() {
//            $metadata = FileRepositoryFS::getFileMetadata($this->fileName, 1);
//            verify($metadata)->isInstanceOf('app\models\FileMetadata');
//            verify($metadata->Name)->equals($this->fileName);
//            verify($metadata->Owner)->equals(1);
//        });
//    }
//    
//    public function testLoadFileMetadataAccessDenied() {
//        $this->specify('Load file metadata for t1.txt, check access denied', function() {
//            try {
//                $metadata = FileRepositoryFS::getFileMetadata($this->fileName, 2);
//                verify(TRUE)->false();
//            } catch (\app\models\data\AccessDenied $ex) {
//                verify(TRUE)->true();
//            }
//        });
//    }
    
//    public function testLoadFileMetadataNotFound() {
//        $this->specify('Load file metadata for t111.txt, file not found, return NULL', function() {
//            $metadata = FileRepositoryFS::getFileMetadata('t111.txt', 1);
//            verify($metadata)->equals(NULL);
//        });
//    }

//    public function testSaveFileMetadata() {
//        $this->specify('Test save FileMetadata', function() {
//            $metadata = FakeFileRepository::getFileMetadata('t1.txt', 1);
//            $metadata->Name = 't1TestSave.txt';
//            verify($metadata)->isInstanceOf('app\models\FileMetadata');
//
//            verify(FileRepositoryFS::saveFileMetadata($metadata))->true();
//
//            $pathToFile = File::getFullPathMetadata('t1TestSave.txt');
//            verify(file_exists($pathToFile))->true();
//
//            // delete saved file
//            unlink($pathToFile);
//        });
//    }
}