<?php

namespace tests\codeception\api;

use \ApiTester;
use app\models\File;
use tests\codeception\fake\FakeFileRepository;

class FileControllerGetFileCest
{
    private $testFileName = 't1.txt';


    public function _before(ApiTester $I)
    {
        $fullPath = File::getFullPathFile($this->testFileName);        
        $handle = fopen($fullPath, 'w');
        fwrite($handle, 'test text');
        fflush($handle);
        fclose($handle);
    }

    public function _after(ApiTester $I)
    {
        unlink(File::getFullPathFile($this->testFileName));
    }
    
    /**
     * Internal server error if metadata load, file not exist
     * @param ApiTester $I
     */
    public function getFileOnName500(ApiTester $I) {
        $I->wantTo('GET file t2');

        //Здесь добавить аутентификацию по пользователю

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/files', ['name' => 't2']);

        $I->wantTo('response 500');
        $I->seeResponseCodeIs(500);
    }

    public function getFileOnNameOk(ApiTester $I) {
        $I->wantTo('GET file t1.txt');

        //Здесь добавить аутентификацию по пользователю

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/files', ['name' => 't1.txt']);

        $I->wantTo('response 200, file, file metadata');
        $jsonMetadata = json_encode(
                FakeFileRepository::getFileMetadata($this->testFileName, 1));
        $I->seeHttpHeader('X-File-Metadata', $jsonMetadata);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('test text');
    }
    
    /**
     * Test GET file, not found
     * @param ApiTester $I
     */
    public function getFileOnNameNotFound(ApiTester $I) {
        $I->wantTo('GET file 123 - not found');

        //Здесь добавить аутентификацию по пользователю

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/files', ['name' => '123']);

        $I->wantTo('response 404');
        $I->seeResponseCodeIs(404);
    }
    
    public function headFileOnNameOk(ApiTester $I) {
        $I->wantTo('GET file t1.txt');

        //Здесь добавить аутентификацию по пользователю

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendHEAD('api/v1/files?name=t1.txt'); 
        // если в этот метод передавать запросы параметром, как в GET, он прикрепляет их в 
        // тело запроса, а не в url
        //, ['name' => 't1.txt']

        $I->wantTo('head, response 200, file metadata');
        $jsonMetadata = json_encode(
                FakeFileRepository::getFileMetadata($this->testFileName, 1));
        $I->seeHttpHeader('X-File-Metadata', $jsonMetadata);
        $I->seeResponseCodeIs(200);
    }
}