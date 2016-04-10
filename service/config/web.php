<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => yii\web\Response::FORMAT_JSON,
                'application/xml' => yii\web\Response::FORMAT_XML,                
            ],
            'languages' => [
                'en',
            ],
        ],
    ],
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'japtxVafrKvV6eb1FgNHQ7RhMT5jx4DU',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'enableSession' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'flushInterval' => 1,
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'trace', 'info'],
                    'exportInterval' => 1,
                    'logFile' => '@runtime/logs/app1.log',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule', 
                    'controller' => 'api/v1/file', 
                    'pluralize' => false,
                    'patterns' => [
                        'OPTIONS' => 'options',
                        'GET,HEAD' => 'index', 
                        'PUT' => 'upload',
//                        'PUT /<name:\w+>' => 'upload',
                    ],
//                    'extraPatterns' => [
////                      'DELETE files/<id>' => 'file/delete',
////                        'GET,HEAD api/v1/files/<name:\w+>' => 'file/view',
////                        'POST api/v1/files' => 'file/create',
//                        'GET,HEAD api/v1/files/<name:\w+>' => 'file/index',
////                        'OPTIONS api/v1/files/<name:\w+>' => 'file/options',
//                        'PUT api/v1/files/<name:\w+>' => 'file/upload',
//                    ],                    
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
