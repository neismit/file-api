<?php

namespace tests\codeception\api\fileController;

use \ApiTester;

use tests\codeception\fake\FakeFileRepository;
use app\models\FileMetadata;

class FilesListCest
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

        $I->wantTo('get 200, list of metadata');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $metadataList = FakeFileRepository::getFilesMetadata(1);
        $jsonDecode = json_decode(json_encode($metadataList), TRUE);
        $I->seeResponseContainsJson($jsonDecode);
//            [ 
//                0 => [ 'Name' => 't1.txt' ],
//                1 => [ 'Name' => 't2' ],
//                2 => [ 'Name' => 'test img' ],
//            ]);
    }
    
    public function getEmptyFilesList(ApiTester $I) {
        //$I->wantTo('get empty files list on user 5');
        //throw new \Exception();
    }
    
    public function headListFilesOnNameOk(ApiTester $I) {
        $I->wantTo('HEAD files');

        //Здесь добавить аутентификацию по пользователю

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendHEAD('api/v1/file'); 

        $I->wantTo('head, response 200, file metadata');
        $metadataList = FakeFileRepository::getFilesMetadata(1);
        $I->seeHttpHeader('X-File-Metadata', json_encode($metadataList));
        $I->seeResponseCodeIs(200);
    }
}