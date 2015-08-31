<?php

$buiderPathParts = [
    dirname(__DIR__),
    'dev',
    'provision',
    'rebuild.sh',
];
$builderPath = implode(DIRECTORY_SEPARATOR, $buiderPathParts);

exec($builderPath);
