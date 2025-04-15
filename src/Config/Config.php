<?php

namespace EddieLogger\Config;


use EddieLogger\Exception\ConfigException;

use function json_decode;
use function json_validate;

readonly class Config
{

    public string $logFilesLocation;
    public string $sfDumpAssetsLocation;

    /**
     * @throws ConfigException
     */
    public function __construct(false|string|null $config)
    {
        if (!json_validate($config)) {
            throw new ConfigException('Invalid configuration json');
        }
        $configDecoded = json_decode($config, true);

        $this->logFilesLocation = $configDecoded['logFilesLocation']
            ?? throw new ConfigException('Invalid logFileLocation');
        $this->sfDumpAssetsLocation = $configDecoded['sfDumpAssetsLocation']
            ?? throw new ConfigException('Invalid sfDumpAssetsLocation');
    }
}