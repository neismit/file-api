<?php

// if you change these params, change path in unit/_bootstrap for initialization folder fo unit test
return [
    'dataFolder' => __DIR__ . '/../dataTest/files',
    'metadataFolder' => __DIR__ . '/../dataTest/metadata',
    'compressionLevel' => 6,
    'tempMaxmemory' => 5242880, // 5 mb 5 * 1024 * 1024
];
