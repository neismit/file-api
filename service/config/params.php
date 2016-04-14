<?php

return [
    'dataFolder' => __DIR__ . '/../data/files',
    'metadataFolder' => __DIR__ . '/../data/metadata',
    'compressionParameters' => ['level' => -1, 'window' => 15, 'memory' => 5],
    'blockSize' => 1024,
    'tempMaxmemory' => 5242880, // 5 mb 5 * 1024 * 1024
];
