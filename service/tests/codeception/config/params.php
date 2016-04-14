<?php

// if you change these params, change path in unit/_bootstrap for initialization folder fo unit test
return [
    'dataFolder' => __DIR__ . '/../dataTest/files',
    'metadataFolder' => __DIR__ . '/../dataTest/metadata',
    'compressionParameters' => ['level' => -1, 'window' => 15, 'memory' => 5],
    'blockSize' => 1024,
    'tempMaxmemory' => 5242880, // 5 mb 5 * 1024 * 1024
];
