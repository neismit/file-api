<?php

namespace tests\codeception\unit\models;

use Yii;
use yii\codeception\TestCase;
use Codeception\Specify;
use app\models\File;
use app\models\FileMetadata;

class FileMetadataTest extends TestCase
{
    use Specify;
    
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $fileNameTest = 'test.txt';


    protected function setUp()
    {
        parent::setUp();
        // create file for test
        $handle = fopen(File::getFullPathFile($this->fileNameTest), 'w');
        fwrite($handle, 'test' . PHP_EOL);
        fflush($handle);
        fclose($handle);
    }
    
    protected function tearDown() {
        unlink(File::getFullPathFile($this->fileNameTest));
        parent::tearDown();
    }

    /**
     * Create file metadata, check all fileds
     */
    public function testCreateMetadata() {
        $metadata = FileMetadata::createMetadata($this->fileNameTest, 'text/plane', 1);
        $this->assertInstanceOf(FileMetadata::class, $metadata);
        //verify($metadata)->isInstanceOf('app\models\FileMetadata');
        
        $this->assertEquals($this->fileNameTest, $metadata->Name);
        //verify($metadata->Name)->equals($this->fileNameTest);
        
        $this->assertEquals(5, $metadata->Size);
        //verify($metadata->Size)->equals(5);

        $modified = \DateTime::createFromFormat(\DateTime::ISO8601, $metadata->Modified);
        $this->assertEquals((new \DateTime('now'))->format('Y-M-D'), $modified->format('Y-M-D'));
        //verify($modified->format('Y-M-D'))->equals((new \DateTime('now'))->format('Y-M-D'));

        $this->assertEquals($metadata->Modified, $metadata->Created);
        $this->assertEquals('text/plane', $metadata->Type);
        $this->assertEquals(1, $metadata->Owner);
//        verify($metadata->Created)->equals($metadata->Modified);
//        verify($metadata->Type)->equals('text/plane');
//        verify($metadata->Owner)->equals(1);
    }
    
    /**
     * Testing create metadata, file not found
     * @expectedException \InvalidArgumentException
     */
    public function testCreateMetadataFileNotFound() {
        FileMetadata::createMetadata('textFail', 'text/plane', 1);
    }

}