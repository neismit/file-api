<?php

namespace tests\codeception\unit\models\data\FileRepositoryFS;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\data\FileRepositoryFS;
use tests\codeception\fake\FakeFileRepository;
use tests\codeception\helper\FileHelper;

class GetStreamTest extends TestCase
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
        $path = File::getFullPathFile($this->fileName);
        FileHelper::createFile($path, 'test 1234567890 12345678');
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
        if (file_exists($pathMeta)) {
            unlink($pathMeta);
        }
        unlink(File::getFullPathFile($this->fileName));
        
        parent::tearDown();        
    }
    
    public function testGetUncompressStreamFileOk() {

        $handle = FileRepositoryFS::getFileStream($this->fileName, 1, FALSE);
        $str = fgets($handle);
        $this->assertEquals('test 1234567890 12345678', $str);
        fclose($handle);
    }
    
    public function testGetCompressStreamFileOk() {

        $handle = FileRepositoryFS::getFileStream($this->fileName, 1, TRUE);
        $str = fgets($handle);
        
        $originFile = fopen(File::getFullPathFile($this->fileName), 'rb');
        $originCompressionStr = fgets($originFile);
        
        $this->assertEquals($originCompressionStr, $str);
        fclose($originFile);
        fclose($handle);
    }

    /**
     * @expectedException \app\models\data\AccessDenied
     */
    public function testGetStreamFileAccessDenied() {
        $handle = FileRepositoryFS::getFileStream($this->fileName, 2, FALSE);
    }
    
    /**
     * @expectedException \app\models\data\NotFound
     */
    public function testGetStreamFileNotFound() {    
        $pathMeta = File::getFullPathMetadata($this->fileName);
        unlink($pathMeta);
        $handle = FileRepositoryFS::getFileStream($this->fileName, 1, FALSE);
    }
}