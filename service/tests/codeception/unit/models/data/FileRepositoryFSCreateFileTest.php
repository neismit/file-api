<?php

namespace tests\codeception\unit\models\data;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\data\FileRepositoryFS;
use tests\codeception\fake\FakeFileRepository;

class FileRepositoryFSCreateFileTest extends TestCase
{
    use Specify;
    
    private $fileName = 'test_create.txt';

    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function setUp()
    {
        parent::setUp();
        $path = File::getFullPathFile($this->fileName);
        $handle = fopen($path, 'w');
        fwrite($handle, 'many many many words, very very very very very very very very very very very very long text');
        fflush($handle);
        fclose($handle);
    }
    
    protected function tearDown() {
        $path = File::getFullPathFile($this->fileName);
        unlink($path);
        parent::tearDown();
    }
    
    public function testCreateFileFromStreamOk() {
        $pathToFile = File::getFullPathFile($this->fileName);
        $handle = fopen($pathToFile, 'r');
        $copyFileName = 'copy_' . $this->fileName;
        $this->assertTrue(FileRepositoryFS::createFileFromStream($handle, $copyFileName, 1));
        //check file content
        $pathToCopyFile = File::getFullPathFile($copyFileName);
        $this->assertFileEquals($pathToFile, $pathToCopyFile);
        
        //check metadata file
        $metadata = FileRepositoryFS::getFileMetadata($copyFileName, 1);
        $this->assertEquals($metadata->Name, $copyFileName);
        $this->assertEquals($metadata->Owner, 1);
        $this->assertEquals($metadata->Type, 'text/plain');
        $this->assertEquals(filesize($pathToFile), filesize($pathToCopyFile));
        
        $modified = \DateTime::createFromFormat(\DateTime::ISO8601, $metadata->Modified);
        $this->assertEquals((new \DateTime('now'))->format('Y-M-D'), $modified->format('Y-M-D'));
        $this->assertEquals($metadata->Modified, $metadata->Created);
        
        $pathToMetadata = File::getFullPathMetadata($copyFileName);
        unlink($pathToMetadata);
        unlink($pathToCopyFile);
    }
}