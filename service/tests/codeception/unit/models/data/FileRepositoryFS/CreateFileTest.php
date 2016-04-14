<?php

namespace tests\codeception\unit\models\data\FileRepositoryFS;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\data\FileRepositoryFS;
use tests\codeception\fake\FakeFileRepository;
use tests\codeception\helper\FileHelper;
use app\models\FileMetadata;

class CreateFileTest extends TestCase
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
        FileHelper::createFile($path, 'test');
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
        $metadata = FileRepositoryFS::createFileFromStream($handle, $copyFileName, 1, TRUE);
        fclose($handle);
        //check file content
        $pathToCopyFile = File::getFullPathFile($copyFileName);
        $this->assertFileEquals($pathToFile, $pathToCopyFile);
        
        //check metadata
        $this->assertInstanceOf(FileMetadata::class, $metadata);
        $this->assertEquals($copyFileName, $metadata->Name);        
        $this->assertEquals(4, $metadata->Size);
        $modified = \DateTime::createFromFormat(\DateTime::ISO8601, $metadata->Modified);
        $this->assertEquals((new \DateTime('now'))->format('Y-M-D'), $modified->format('Y-M-D'));
        $this->assertEquals($metadata->Modified, $metadata->Created);
        $this->assertEquals('text/plain', $metadata->Type);
        $this->assertEquals(1, $metadata->Owner);
        
        unlink($pathToCopyFile);
        unlink(File::getFullPathMetadata($copyFileName));
    }
}