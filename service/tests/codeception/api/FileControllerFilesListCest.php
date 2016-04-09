<?php

namespace tests\codeception\api;

use \ApiTester;

use tests\codeception\fake\FakeFileRepository;
use app\models\FileMetadata;

class FileControllerFilesList
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }
    
    /**
     * Test GET list file for $userId = 1
     * @param ApiTester $I
     */
    public function getFilesList(ApiTester $I) {
        $I->wantTo('get files list on user 1');

        //Здесь добавить аутентификацию по пользователю
        //$I->amHttpAuthenticated('service_user', '123456');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/file');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [ 
                0 => [ 'Name' => 't1.txt' ],
                1 => [ 'Name' => 't2' ],
                2 => [ 'Name' => 'test img' ],
            ]);
    }
    
    public function getEmptyFilesList(ApiTester $I) {
        $I->wantTo('get empty files list on user 5');
        throw new \Exception();
    }
}