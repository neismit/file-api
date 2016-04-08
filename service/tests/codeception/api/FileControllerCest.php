<?php
use \ApiTester;

use tests\codeception\fake\FakeFileRepository;
use app\models\FileMetadata;

class FileControllerCest
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
        $I->sendGET('api/v1/files');

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
        $I->wantTo('get files list on user 1');
        throw new Exception();
    }

    /**
     * Test GET file, not found
     * @param ApiTester $I
     */
    public function getFileOnNameNotFound(ApiTester $I)
    {
        $I->wantTo('GET file 123');

        //Здесь добавить аутентификацию по пользователю

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('api/v1/files', ['name' => '123']);

        $I->wantTo('response 404');
        $I->seeResponseCodeIs(404);
    }
    
    
    
    
}