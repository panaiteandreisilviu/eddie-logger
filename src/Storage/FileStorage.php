<?php

namespace EddieLogger\Storage;

use EddieLogger\Config\LoggerConfig;

class FileStorage
{

    private const string LOG_FILE_LOCATION = '/var/www/config/eddie/logs/';
    private const string SFDUMP_ASSETS_FILE_LOCATION = '/var/www/config/eddie/assets/sfdump/';
    private const string LOG_FILE_NAME = 'e_';
    private const string LOG_FILE_EXTENSION = '.html';

    public function __construct(private LoggerConfig $config) {}

    public function put(string $channel, string $contents): void
    {
        $this->addSfDumpAssets(self::getLogFileFullPath($channel));
        file_put_contents(self::getLogFileFullPath($channel), $contents, FILE_APPEND);
    }

    private function getLogFileFullPath(string $channel): string
    {
        return self::LOG_FILE_LOCATION
            . self::LOG_FILE_NAME . "_" . $channel
            . self::LOG_FILE_EXTENSION;
    }

    public function addSfDumpAssets(string $logFileFullPath): void
    {
        if (file_exists($logFileFullPath)) {
            return;
        }
        if (!file_exists(self::SFDUMP_ASSETS_FILE_LOCATION . 'sfdump.js')) {
            return;
        }
        if (!file_exists(self::SFDUMP_ASSETS_FILE_LOCATION . 'sfdump.css')) {
            return;
        }

        $sfDumpJs = "<script>" . file_get_contents(self::SFDUMP_ASSETS_FILE_LOCATION . 'sfdump.js') . "</script>";
        $sfDumpCss = "<style>" . file_get_contents(self::SFDUMP_ASSETS_FILE_LOCATION . 'sfdump.css') . "</style>";

        file_put_contents($logFileFullPath, $sfDumpCss . $sfDumpJs, FILE_APPEND);
    }

}