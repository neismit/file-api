<?php

namespace tests\codeception\unit\models\data;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\data\FileRepositoryFS;
use tests\codeception\fake\FakeFileRepository;

class FileRepositoryFSListTest extends TestCase
{
    use Specify;
    
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $fileNameList = ['list_t1.txt', 'list_t2.txt', 'list_t3.txt'];
    private $userId = 5;
    
    protected function setUp()
    {
        parent::setUp();
        $this->createMetadataFile($this->fileNameList);
    }
    
    private function createMetadataFile($fileNameList) {
        $pathMeta = File::getFullPathMetadata('t1.txt');
        $metadata = FakeFileRepository::getFileMetadata('t1.txt', 1);
        foreach ($fileNameList as $name) {
            $metadata->Name = $name;
            $metadata->Owner = $this->userId;
            $pathMeta = File::getFullPathMetadata($name);
            $handle = fopen($pathMeta, 'w');
            $json = json_encode($metadata);
            fwrite($handle, $json . PHP_EOL);
            fflush($handle);
            fclose($handle);
        }
    }
    
    protected function tearDown() {
        foreach ($this->fileNameList as $name) {
            $pathMeta = File::getFullPathMetadata($name);
            unlink($pathMeta);
        }
                
        parent::tearDown();        
    }

    /**
     * Files list testing, ok
     */
    public function testGetListFiles() {
        $filesMetadata = FileRepositoryFS::getFiles($this->userId);
        verify(is_array($filesMetadata))->true();
        verify($filesMetadata)->count(3);
        foreach ($filesMetadata as $meta) {
            verify($meta)->isInstanceOf('app\models\FileMetadata');
            verify(in_array($meta->Name, $this->fileNameList))->true();
        }
    }
    
    /**
     * Get list files, userId = 4
     */
    public function testGetEmptyListFiles() {
        $filesMetadata = FileRepositoryFS::getFiles(4);
        verify(is_array($filesMetadata))->true();
        verify($filesMetadata)->isEmpty();
    }
}