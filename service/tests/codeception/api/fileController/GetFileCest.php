<?php

namespace tests\codeception\api\fileController;

use \ApiTester;
use app\models\File;
use tests\codeception\fake\FakeFileRepository;

class GetFileCest
{
    private $testFileName = 't1.txt';


    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }
    
    /**
     * Internal server error if metadata load, file not exist
     * @param ApiTester $I
     */
    public function getFileOnName500(ApiTester $I) {
        $I->wantTo('GET file t2');
        $I->amBearerAuthenticated('test2-token');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/file', ['name' => 'test2img']);

        $I->wantTo('response 500');
        $I->seeResponseCodeIs(500);
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
        $I->seeHttpHeader('X-File-Metadata', $jsonMetadata);
        $I->seeResponseContains('test string');
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