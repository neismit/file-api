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
        $I->amBearerAuthenticated('test1-token');
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
        $I->wantTo('get empty files list on user 3');
        $I->amBearerAuthenticated('test3-token');
        $I->sendGET('api/v1/file');
        $I->wantTo('get 200, empty list of metadata');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([]);        
    }
    
    public function headListFilesOnNameOk(ApiTester $I) {
        $I->wantTo('HEAD files');
        $I->amBearerAuthenticated('test1-token');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendHEAD('api/v1/file'); 

        $I->wantTo('head, response 200, file metadata');
        $metadataList = FakeFileRepository::getFilesMetadata(1);
        $I->seeHttpHeader('X-File-Metadata', json_encode($metadataList));
        $I->seeResponseCodeIs(200);
    }
}