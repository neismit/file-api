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
        fwrite($handle, $json);
        fflush($handle);
        fclose($handle);
    }
    
    protected function tearDown() {
        $pathMeta = File::getFullPathMetadata($this->fileName);
        unlink($pathMeta);
        
        parent::tearDown();        
    }

    // tests
    public function testGetFileMetadata() {
        $this->specify('Load file metadata for t1.txt, userId = 1', function() {
            $metadata = FileRepositoryFS::getFileMetadata($this->fileName, 1);
            verify($metadata)->isInstanceOf('app\models\FileMetadata');
            verify($metadata->Name)->equals($this->fileName);
            verify($metadata->Owner)->equals(1);
        });
        
        $this->specify('Load file metadata for t1.txt, check access denied', function() {
            try {
                $metadata = FileRepositoryFS::getFileMetadata($this->fileName, 2);
                verify(TRUE)->false();
            } catch (\app\models\data\AccessDenied $ex) {
                verify(TRUE)->true();
            }
        });
        
        $this->specify('Load file metadata for t111.txt, file not found, return NULL', function() {
            $metadata = FileRepositoryFS::getFileMetadata('t111.txt', 1);
            verify($metadata)->equals(NULL);
        });
    }
    
    public function testSaveFileMetadata() {
        
    }

}