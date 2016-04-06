<?php

// add unit testing specific bootstrap code here
$testDataFolder = __DIR__ . '/../dataTest/files';
$testMetadataFolder = __DIR__ . '/../dataTest/metadata';

if (!file_exists($testDataFolder)) {
    mkdir($testDataFolder, 0777, TRUE);
}

if (!file_exists($testMetadataFolder)) {
    mkdir($testMetadataFolder, 0777, TRUE);
}