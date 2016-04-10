<?php

namespace tests\codeception\api\fileController;

use \ApiTester;
use app\models\File;
use tests\codeception\helper\FileHelper;
use app\models\FileMetadata;
use app\models\data\FileRepositoryFS;

class DeleteFileCest
{
    private $fileName = 't1.txt';
    
    public function _before(ApiTester $I)
    {
//        FileHelper::createFile(File::getFullPathFile($this->fileName), 'psnfadsf qwer qwer qwer qewr qwer qwer qewr');
//        $metadata = FileMetadata::createMetadata($this->fileName, 1);
//        FileRepositoryFS::saveFileMetadata($metadata);
    }

    public function _after(ApiTester $I)
    {
//        $pathToFile = File::getFullPathFile($this->fileName);
//        if (file_exists($pathToFile)) {
//            unlink($pathToFile);
//        }
//        $pathToMetadata = File::getFullPathMetadata($this->fileName);
//        if (file_exists($pathToMetadata)) {
//            unlink($pathToMetadata);
//        }
    }
    
    public function testDeleteFileOk(ApiTester $I) {
        $I->wantTo('DELETE file');

        //Здесь добавить аутентификацию по пользователю
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('api/v1/file?name=' . $this->fileName);
        
        $I->wantTo('200, file deleted');
        $I->seeResponseCodeIs(200);
    }
    
    public function testDeleteFile404(ApiTester $I) {
        $I->wantTo('DELETE file, file does not exists');

        //Здесь добавить аутентификацию по пользователю
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('api/v1/file?name=fakefile');
        
        $I->wantTo('404, file not found');
        $I->seeResponseCodeIs(404);
    }
    
    public function testDeleteFileAccessDenied(ApiTester $I) {
        $I->wantTo('DELETE file test2img, userId = 1, access denied ');

        //Здесь добавить аутентификацию по пользователю
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendDELETE('api/v1/file?name=test2img');
        
        $I->wantTo('403, access denied for test2img, userId = 1');
        $I->seeResponseCodeIs(403);
    }
}