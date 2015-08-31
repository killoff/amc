<?php

$buiderPathParts = [
    dirname(__DIR__),
    'dev',
    'provision',
    'rebuild.sh',
];
$buiderPath = implode(DIRECTORY_SEPARATOR, $buiderPathParts);

exec($buiderPath);
