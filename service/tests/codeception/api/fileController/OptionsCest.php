<?php

namespace tests\codeception\api\fileController;

use \ApiTester;
use app\models\File;
use tests\codeception\fake\FakeFileRepository;

class OptionsCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }
    
    public function getOptionsForApi(ApiTester $I) {
        $I->wantTo('Send OPTIONS for api/v1/file');
        
        $I->sendOPTIONS('api/v1/file');
        
        $I->seeHttpHeader('Allow', 'OPTIONS, HEAD, GET, PUT, PATCH, DELETE');
    }
}
    