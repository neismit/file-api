<?php
/**
 * Application configuration shared by all test types
 */
$params = require(__DIR__ . '/params.php');

return [
    'language' => 'en-US',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            'fixtureDataPath' => '@tests/codeception/fixtures',
            'templatePath' => '@tests/codeception/templates',
            'namespace' => 'tests\codeception\fixtures',
        ],
    ],
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
//        'urlManager' => [
//            'showScriptName' => false,
//        ],
    ],
    'params' => $params,
];
