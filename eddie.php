<?php

use EddieLogger\Config\LoggerConfig;
use EddieLogger\Formatter\BacktraceFormatter;
use EddieLogger\Logger\Logger;
use EddieLogger\Storage\FileStorage;
use EddieLogger\Timer\Timer;

require_once __DIR__ . '/src/Config/LoggerConfig.php';
require_once __DIR__ . '/src/Formatter/BacktraceFormatter.php';
require_once __DIR__ . '/src/Logger/Logger.php';
require_once __DIR__ . '/src/Storage/FileStorage.php';
require_once __DIR__ . '/src/Timer/Timer.php';

if (!function_exists('eddie')) {
    function eddie(): Logger
    {
        static $logger = null;

        if ($logger !== null) {
            return $logger;
        }

        return new Logger(
            new Timer(),
            new BacktraceFormatter(),
            new FileStorage(new LoggerConfig(file_get_contents('config.json')))
        );
    }
}
