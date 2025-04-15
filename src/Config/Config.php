<?php

namespace EddieLogger\Config;

use EddieLogger\Exception\ConfigException;

use function json_decode;
use function json_validate;

readonly class Config
{
    public string $logFilesPath;
    public string $sfDumpAssetsPath;

    /**
     * Creates a new Config instance
     *
     * @param string|false|null $config JSON configuration string
     * @throws ConfigException If configuration is invalid
     */
    public function __construct(string|false|null $config)
    {
        if (!$config) {
            throw new ConfigException('Configuration cannot be empty');
        }

        if (!json_validate($config)) {
            throw new ConfigException('Invalid configuration JSON format');
        }

        $configDecoded = json_decode($config, true);
        $this->logFilesPath = $configDecoded['logFilesLocation'] ?? null;
        $this->sfDumpAssetsPath = dirname(__DIR__, 2) . '/assets/sfdump';
        $this->validatePaths();
    }

    /**
     * @throws ConfigException
     */
    private function validatePaths(): void
    {
        if (!is_dir($this->logFilesPath) && !mkdir($this->logFilesPath, 0755, true)) {
            throw new ConfigException("Log directory {$this->logFilesPath} does not exist and could not be created");
        }

        if (!is_writable($this->logFilesPath)) {
            throw new ConfigException("Log directory {$this->logFilesPath} is not writable");
        }
    }
}