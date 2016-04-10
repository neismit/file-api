<?php

namespace tests\codeception\api\fileController;

use \ApiTester;
use app\models\File;
use tests\codeception\helper\FileHelper;
use app\models\FileMetadata;

class CreateFileCest
{
    private $originFileName = 'test_file.txt';
    private $createdFileName = 'test_file_created';
    
    public function _before(ApiTester $I)
    {
        FileHelper::createFile(File::getFullPathFile($this->originFileName), 'psnfadsf qwer qwer qwer qewr qwer qwer qewr');
    }

    public function _after(ApiTester $I)
    {
        unlink(File::getFullPathFile($this->originFileName));
        $pathToCreatedFile = File::getFullPathFile($this->createdFileName);
        if (file_exists($pathToCreatedFile)) {
            unlink($pathToCreatedFile);
        }
        $pathToCreatedMetadata = File::getFullPathMetadata($this->createdFileName);
        if (file_exists($pathToCreatedMetadata)) {
            unlink($pathToCreatedMetadata);
        }
    }

    public function testCreateFileOk(ApiTester $I)
    {
        $I->wantTo('PUT file test_create');

        //Здесь добавить аутентификацию по пользователю
        $pathToOriginFile = File::getFullPathFile($this->originFileName);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Content-Length', filesize($pathToOriginFile));
        $I->sendPUT('api/v1/file?name=' . $this->createdFileName, [], ['file' => $this->originFileName]);
        $I->wantTo('201');
        $I->seeResponseCodeIs(201);
        
        //check metadata header
        $metadata = FileMetadata::createMetadata($this->originFileName, 1);
        
        // change attributes, becose sendPut not send File on server, php://input is null
        $metadata->Name = $this->createdFileName;
        $metadata->Size = 0;
        $metadata->Type = 'inode/x-empty';
        
        $I->seeHttpHeader('X-File-Metadata', json_encode($metadata));
    }
    
    public function testCreateFileMissingName (ApiTester $I) {
        $I->wantTo('400, missing name in request');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('api/v1/file');
        $I->wantTo('400, there is no option name');
        $I->seeResponseCodeIs(400);
    }
}