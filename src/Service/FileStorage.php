<?php

namespace EddieLogger\Service;

use EddieLogger\Config\Config;

readonly class FileStorage
{

    public function __construct(private Config $config) {}

    public function put(string $channel, string $contents): void
    {
        $this->addSfDumpAssets(self::getLogFileFullPath($channel));
        file_put_contents(self::getLogFileFullPath($channel), $contents, FILE_APPEND);
    }

    private function getLogFileFullPath(string $channel): string
    {
        return $this->config->logFilesPath . DIRECTORY_SEPARATOR . "e_$channel.html";
    }

    public function addSfDumpAssets(string $logFileFullPath): void
    {
        if (file_exists($logFileFullPath)) {
            return;
        }
        if (!file_exists($this->config->sfDumpAssetsPath . DIRECTORY_SEPARATOR . 'sfdump.js')) {
            return;
        }
        if (!file_exists($this->config->sfDumpAssetsPath . DIRECTORY_SEPARATOR . 'sfdump.css')) {
            return;
        }

        $assetsPath = $this->config->sfDumpAssetsPath;

        $sfDumpJs = "<script>" . file_get_contents($assetsPath . DIRECTORY_SEPARATOR . 'sfdump.js') . "</script>";
        $sfDumpCss = "<style>" .file_get_contents($assetsPath . DIRECTORY_SEPARATOR . 'sfdump.css') ."</style>";

        file_put_contents($logFileFullPath, $sfDumpCss . $sfDumpJs, FILE_APPEND);
    }

}