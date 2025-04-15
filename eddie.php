<?php

require_once __DIR__ . '/src/Config/LoggerConfig.php';
require_once __DIR__ . '/src/Formatter/BacktraceFormatter.php';
require_once __DIR__ . '/src/Logger/Logger.php';
require_once __DIR__ . '/src/Storage/FileStorage.php';
require_once __DIR__ . '/src/Timer/Timer.php';

if (!function_exists('eddie')) {
    function eddie(): \EddieLogger\Logger\Logger
    {
        static $logger = null;

        if ($logger !== null) {
            return $logger;
        }

        return new \EddieLogger\Logger\Logger(
            new \EddieLogger\Timer\Timer(),
            new \EddieLogger\Formatter\BacktraceFormatter(),
            new \EddieLogger\Storage\FileStorage(
                new \EddieLogger\Config\LoggerConfig(file_get_contents('config.json'))
            )
        );
    }
}
