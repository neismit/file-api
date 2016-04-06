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
        fwrite($handle, 'test');
        fflush($handle);
        fclose($handle);
    }
    
    protected function tearDown() {
        unlink(File::getFullPathFile($this->fileNameTest));
        parent::tearDown();
    }

    // tests
    public function testCreateMetadata()
    {
        $this->specify('Create metadata object', function() {
            $metadata = FileMetadata::createMetadata($this->fileNameTest, 'text/plane', 1);
            verify($metadata)->isInstanceOf('app\models\FileMetadata');
            verify($metadata->Name)->equals($this->fileNameTest);
            verify($metadata->Size)->equals(4);
            
            $modified = \DateTime::createFromFormat(\DateTime::ISO8601, $metadata->Modified);
            verify($modified->format('Y-M-D'))->equals((new \DateTime('now'))->format('Y-M-D'));
            
            verify($metadata->Created)->equals($metadata->Modified);
            verify($metadata->Type)->equals('text/plane');
            verify($metadata->Owner)->equals(1);
        });
        
        $this->specify('File not exist. Throw invalid argument exception', function() {
            try {
                $metadata = FileMetadata::createMetadata('textFaile', 'text/plane', 1);
                verify(TRUE)->false();
            } catch (\InvalidArgumentException $ex) {
                verify(TRUE)->true();
            }
        });
    }

}