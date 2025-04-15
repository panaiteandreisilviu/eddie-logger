<?php

use EddieLogger\Config\Config;
use EddieLogger\Exception\ConfigException;
use EddieLogger\Facade\Eddie;
use EddieLogger\Facade\EddieInterface;
use EddieLogger\Facade\NullEddie;
use EddieLogger\Service\Dumper;
use EddieLogger\Service\FileStorage;
use EddieLogger\Service\Timer;

require_once __DIR__ . '/src/Config/Config.php';
require_once __DIR__ . '/src/Exception/ConfigException.php';
require_once __DIR__ . '/src/Facade/Eddie.php';
require_once __DIR__ . '/src/Facade/NullEddie.php';
require_once __DIR__ . '/src/Facade/EddieInterface.php';
require_once __DIR__ . '/src/Service/Timer.php';
require_once __DIR__ . '/src/Service/Dumper.php';
require_once __DIR__ . '/src/Service/FileStorage.php';
require_once __DIR__ . '/src/ValueObject/Backtrace.php';

if (!function_exists('eddie')) {
    function eddie(): EddieInterface
    {
        static $logger = null;

        if ($logger !== null) {
            return $logger;
        }

        try {
            $config = new Config(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'config.json'));
        } catch (ConfigException $e) {
            return new NullEddie($e->getMessage());
        }

        $logger = new Eddie(
            new Dumper(new FileStorage($config)),
            new Timer(),
        );
        return $logger;
    }
}
