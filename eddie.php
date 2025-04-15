<?php

use EddieLogger\Config\LoggerConfig;
use EddieLogger\Dumper\Dumper;
use EddieLogger\Eddie;
use EddieLogger\Formatter\BacktraceFormatter;
use EddieLogger\Storage\FileStorage;
use EddieLogger\Timer\Timer;

require_once __DIR__ . '/src/Config/LoggerConfig.php';
require_once __DIR__ . '/src/Formatter/BacktraceFormatter.php';
require_once __DIR__ . '/src/Storage/FileStorage.php';
require_once __DIR__ . '/src/Timer/Timer.php';

if (!function_exists('eddie')) {
    function eddie(): Eddie
    {
        static $logger = null;

        if ($logger !== null) {
            return $logger;
        }

        return new Eddie(
            new Dumper(
                new FileStorage(new LoggerConfig(file_get_contents('config.json'))),
                new BacktraceFormatter(),
            ),
            new Timer(),
        );
    }
}
