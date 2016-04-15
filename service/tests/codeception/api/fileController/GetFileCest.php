<?php

namespace tests\codeception\api\fileController;

use \ApiTester;
use app\models\File;
use app\models\data\FileRepositoryFS;
use tests\codeception\fake\FakeFileRepository;
use tests\codeception\helper\FileHelper;

class GetFileCest
{
    private $testFileName = 't1.txt';
    private $metadata = NULL;


    public function _before(ApiTester $I)
    {
        $inputFileHandler = FileHelper::createFileInMemory('test string');
        $this->metadata = FileRepositoryFS::createFileFromStream($inputFileHandler, $this->testFileName, 1);
        fclose($inputFileHandler);
    }

    public function _after(ApiTester $I)
    {
        $pathToFile = File::getFullPathFile($this->testFileName);
        if (file_exists($pathToFile)) {
            unlink($pathToFile);
        }
        $pathToMetadata = File::getFullPathMetadata($this->testFileName);
        if (file_exists($pathToMetadata)) {
            unlink($pathToMetadata);
        }
    }
    
    public function getFileOnInvalidName(ApiTester $I) {
        $I->wantTo('GET file t2*/sdf sl.txt');
        $I->amBearerAuthenticated('test1-token');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/file', ['name' => 't2*/sdf sl.txt']);

        $I->wantTo('response 400');
        $I->seeResponseCodeIs(400);
    }

    public function getFileOnNameOk(ApiTester $I) {
        $I->wantTo('GET file t1.txt');
        $I->amBearerAuthenticated('test1-token');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/file', ['name' => $this->testFileName]);

        $I->wantTo('response 200, file, file metadata');
        $jsonMetadata = json_encode(
                FakeFileRepository::getFileMetadata($this->testFileName, 1));
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Content-Type', $this->metadata->Type);
        // 24 - becose that number in FakeFileRepository
        $I->seeHttpHeader('Content-Length', 24);
        $I->seeHttpHeader('X-File-Metadata', $jsonMetadata);
        $I->seeResponseContains('test string');
    }
    
    public function getGzipFileOnNameOk(ApiTester $I) {
        $I->wantTo('GET file t1.txt');
        $I->amBearerAuthenticated('test1-token');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept-encoding', 'gzip');
        $I->sendGET('api/v1/file', ['name' => $this->testFileName]);

        $I->wantTo('response 200, file, file metadata');
        $jsonMetadata = json_encode(
                FakeFileRepository::getFileMetadata($this->testFileName, 1));
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Content-Encoding', 'gzip');
        $I->seeHttpHeader('Content-Type', $this->metadata->Type);
        $pathToFile = File::getFullPathFile($this->testFileName);
        $I->seeHttpHeader('Content-Length', filesize($pathToFile));
        $I->seeHttpHeader('X-File-Metadata', $jsonMetadata);
        
        $handle = FakeFileRepository::getFileStream('t1.txt', 1, TRUE);
        $str = fgets($handle);
        $I->seeResponseContains($str);
    }    
    
    /**
     * Test GET file, not found
     * @param ApiTester $I
     */
    public function getFileOnNameNotFound(ApiTester $I) {
        $I->wantTo('GET file 123 - not found');
        $I->amBearerAuthenticated('test1-token');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/file', ['name' => '123']);

        $I->wantTo('response 404');
        $I->seeResponseCodeIs(404);
    }
    
    public function headFileOnNameOk(ApiTester $I) {
        $I->wantTo('HEAD file t1.txt');
        $I->amBearerAuthenticated('test1-token');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendHEAD('api/v1/file?name=' . $this->testFileName); 
        // если в этот метод передавать запросы параметром, как в GET, он прикрепляет их в 
        // тело запроса, а не в url
        //, ['name' => 't1.txt']

        $I->wantTo('head, response 200, file metadata');
        $jsonMetadata = json_encode(
                FakeFileRepository::getFileMetadata($this->testFileName, 1));
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('X-File-Metadata', $jsonMetadata);
    }
}