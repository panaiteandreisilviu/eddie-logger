<?php

if (!class_exists('Eddie_Logger')) {
    class Eddie_Logger
    {
        private static ?Eddie_Logger $instance = null;

        private const string LOG_FILE_LOCATION = '/var/www/config/eddie/logs/';
        private const string SFDUMP_ASSETS_FILE_LOCATION = '/var/www/config/eddie/assets/sfdump/';
        private const string LOG_FILE_NAME = 'e_';
        private const string LOG_FILE_EXTENSION = '.html';

        public static function getInstance(): self
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function timers(): array
        {
            return $GLOBALS['e_ts']['results'];
        }

        public function dump_timers(int|string $channel = null, string $dumpName = null): void
        {
            $this->dump($this->timers(), $channel, $dumpName);
        }

        public function dump(mixed $debug, int|string $channel = null, string $dumpName = null, string $export_type = 'dump'): void
        {
            $dump = '';

            if ($export_type == 'dump' && function_exists('dump')) {
                ob_start();
                dump($debug);
                $dump = ob_get_contents();
                ob_end_clean();
            } else if ($export_type == 'var_export') {
                $dump = var_export($debug, true);
            } else if ($export_type == 'print_r') {
                $dump = print_r($debug, true);
            } else {
                $dump = print_r($debug, true);
            }

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $location = $backtrace[1]['class'] . $backtrace[1]['type'] . $backtrace[1]['function'] . "@" . $backtrace[0]['line'];
            $locationFile = $backtrace[0]['file'] . "@" . $backtrace[0]['line'];

            $ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? 'AJAX' : "NON-AJAX";
            $requestUrl = urldecode($_SERVER['REQUEST_URI']);

            $contents =
                "<br><b>" . date("H:i:s") . "<br>" . "<span style='white-space: nowrap'>$requestUrl</span>" . "</b><br>$ajax<br>$location<br>" . "$locationFile<br>"
                . (!empty($dumpName) ? "<br><span style='color:#5397da; font-weight: bold;'>$dumpName</span><br>" : '')
                . $dump
                . "<br><b>____________________________________________</b><br>";

            $this->addSfDumpAssets(self::getLogFileFullPath($channel));
            file_put_contents(self::getLogFileFullPath($channel), $contents, FILE_APPEND);
        }

        public function dump_v2(mixed $debug, int|string $channel = null, string $dumpName = null, string $export_type = 'dump'): void
        {
            if ($export_type == 'dump' && function_exists('dump')) {
                ob_start();
                dump($debug);
                $dump = ob_get_contents();
                ob_end_clean();
            } else if ($export_type == 'var_export') {
                $dump = var_export($debug, true);
            } else if ($export_type == 'print_r') {
                $dump = print_r($debug, true);
            } else {
                $dump = print_r($debug, true);
            }
            $dumpNameFormatted = !empty($dumpName) ? "<span style='color:#5397da; font-weight: bold;'>$dumpName</span>" : '';

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $formattedBacktrace = $this->formatDebugBacktrace($backtrace);

            $backtraceClassExploded = explode('\\', $backtrace[1]['class']) ?: [];

            $hrefText = end($backtraceClassExploded) . $backtrace[1]['type'] . $backtrace[1]['function'] . "@" . $backtrace[0]['line'];
            $remoteFile = $backtrace[0]['file'] ?? '';
            $remoteLine = (int)($backtrace[0]['line'] ?? 0);
            $phpStormHref = $this->getPhpStormHref($remoteFile, $remoteLine, $hrefText);

            $project = $this->getProjectName($backtrace[0]['file']);

            $ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? 'AJAX' : "NON-AJAX";
            $requestUrl = urldecode($_SERVER['REQUEST_URI']);

            $dumpInfo = "<span style='white-space: nowrap'><b>$ajax - $requestUrl</b></span><br>";
            $dumpDate = date("H:i:s");

            $contents = <<<EOHTML

<style>
/*pre.sf-dump, pre.sf-dump .sf-dump-default {*/
/*    background-color: #4d4d4d !important;*/
/*}*/

/*pre.sf-dump .sf-dump-str {*/
/*    color: white !important;*/
/*}*/
</style>

  <div style="display: flex; flex-wrap: nowrap; height: auto; border-bottom: 2px solid #c2c2c2">
    <div style="width: 50%; padding: 10px; background-color: #f0f0f0;">
      <b>{$dumpDate}</b> {$dumpNameFormatted} - {$phpStormHref} - $project
      {$dump}
    </div>
    <div style="width: 50%; padding: 10px; background-color: #e0e0e0; overflow: auto; white-space: nowrap; font-size: 13px">
       {$dumpInfo}
       <br>
       {$formattedBacktrace}
    </div>
  </div>

EOHTML;

            $this->addSfDumpAssets(self::getLogFileFullPath($channel));
            file_put_contents(self::getLogFileFullPath($channel), $contents, FILE_APPEND);
        }

        public function formatDebugBacktrace(array $backtrace): string
        {
            $formattedBacktrace = "";
            $traceCount = count($backtrace);

            foreach ($backtrace as $index => $item) {
                if($index === 0) {
                    $traceCount--;
                    continue;
                }
                if ($item['class']) {
                    $remoteFile = $backtrace[$index-1]['file'] ?? '';
                    $remoteLine = (int)($backtrace[$index-1]['line'] ?? 0);
                    $hrefText = $item['class'] . $item['type'] . $item['function'] . "@" . ($backtrace[$index-1]['line'] ?? '');
                    $phpStormHref = $this->getPhpStormHref($remoteFile, $remoteLine, $hrefText);
                    $projectName = $this->getProjectName($remoteFile);
                } else {
                    $phpStormHref = $this->getPhpStormHref(
                        remoteFile: $item['file'],
                        remoteLine: $backtrace[$index-1]['line'] ?? '',
                        hrefText: $item['function'] . "@" . ($backtrace[$index-1]['line'] ?? '')
                    );
                    $projectName = $this->getProjectName($item['file']);
                }
                $projectFormatted = "<span style='width: 57px; background-color: #b5e1c2; display: inline-block; text-align: center; border: 1px solid #a6a6a6;'>$projectName</span>";
                $traceCountFormatted = "<span style='width: 20px; display: inline-block'>#$traceCount</span>";
                $formattedBacktrace .= "$projectFormatted $traceCountFormatted $phpStormHref <br>";
                $traceCount--;
            }

            return $formattedBacktrace;
        }

        public function getProjectName(string $remoteFile): string
        {
            if (str_contains($remoteFile, 'vendor/eos-lib/sapi-core')) {
                return 'sapi-core';
            }
            elseif (str_contains($remoteFile, 'vendor/eos/lib-eos')) {
                return 'lib-eos';
            }
            elseif (str_contains($remoteFile, 'vendor/eos-lib/sapi-client')) {
                return 'sapi-client';
            }
            elseif (str_contains($remoteFile, '/sapi/') && !str_contains($remoteFile, '/vendor/')) {
                return 'sapi';
            }
            elseif (str_contains($remoteFile, '/front/') && !str_contains($remoteFile, '/vendor/')) {
                return 'front';
            }
            elseif (str_contains($remoteFile, '/www/') && !str_contains($remoteFile, '/vendor/')) {
                return 'www';
            }
            elseif (str_contains($remoteFile, '/vendor/')) {
                return 'vendor';
            }
            return 'N/A';
        }


        private function getPhpStormHref(mixed $remoteFile, int $remoteLine, string $hrefText): string
        {
            $phpstormUrl = $this->getPhpstormUrl($remoteFile, $remoteLine);
            return $phpstormUrl
                ? "<a href='$phpstormUrl' title='$remoteFile@$remoteLine'>$hrefText</a>"
                : "<span title='$remoteFile@$remoteLine' style='cursor: hand;'>$hrefText</span>";
        }

        public function getPhpstormUrl(string $remoteFile, int $line): ?string
        {
            $localPath = null;
            if (str_contains($remoteFile, 'vendor/eos-lib/sapi-core')) {
                $localPath = '/Users/andrei.panaite/projects/sapi-core' . explode('vendor/eos-lib/sapi-core', $remoteFile)[1];
            }
            elseif (str_contains($remoteFile, 'vendor/eos/lib-eos')) {
                $localPath = '/Users/andrei.panaite/projects/lib-eos' . explode('vendor/eos/lib-eos', $remoteFile)[1];
            }
            elseif (str_contains($remoteFile, 'vendor/eos-lib/sapi-client')) {
                $localPath = '/Users/andrei.panaite/projects/sapi-client' . explode('eos-lib/sapi-client', $remoteFile)[1];
            }
            elseif (str_contains($remoteFile, '/sapi/') && !str_contains($remoteFile, '/vendor/')) {
                if (preg_match("~tags/[^/]+/data/(.*)~", $remoteFile, $matches)) {
                    $localPath = '/Users/andrei.panaite/projects/sapi/' . $matches[1];
                }
            }
            elseif (str_contains($remoteFile, '/front/') && !str_contains($remoteFile, '/vendor/')) {
                if (preg_match("~tags/[^/]+/data/(.*)~", $remoteFile, $matches)) {
                    $localPath = '/Users/andrei.panaite/projects/front/' . $matches[1];
                }
            }
            elseif (str_contains($remoteFile, '/www/') && !str_contains($remoteFile, '/vendor/')) {
                if (preg_match("~tags/[^/]+/data/(.*)~", $remoteFile, $matches)) {
                    $localPath = '/Users/andrei.panaite/projects/www/' . $matches[1];
                }
            }
            return $localPath ? "phpstorm://open?file={$localPath}&line={$line}"  : null;
        }


        public function clean_backtrace(int|string $channel = null): void
        {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            //duplicate lines
            $ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            $requestUrl = $_SERVER['REQUEST_URI'] . " - " . ($ajax ? 'AJAX' : "NON-AJAX") . "\n";
            $location = $backtrace[1]['class'] . $backtrace[1]['type'] . $backtrace[1]['function'] . "@" . $backtrace[0]['line'];

            $backtrace = array_reverse($backtrace);
            $toDump = "<br><b>" . date("[H:i:s]  :  ") . $requestUrl . "</b><br>$location<br><br><br>";

            foreach ($backtrace as $index => $item) {
                $fileLocation = $item['file'] . "@" . $item['line'];
                $nextItem = $backtrace[$index+1] ?? null;
                $calledItemLineNumber = $nextItem ? ("@" . $nextItem['line']) : '';
                if ($item['class']) {
                    $calledItem = ($item['class'] . $item['type'] . $item['function'] . "$calledItemLineNumber");
                } else {
                    $calledItem = ($item['function'] . "@$calledItemLineNumber");
                }
                $toDump .= "$fileLocation<br><b>$calledItem</b><br><br>";
            }
            $this->addSfDumpAssets(self::getLogFileFullPath($channel));
            file_put_contents(self::getLogFileFullPath($channel), $toDump, FILE_APPEND);
        }

        public function clear_log(int|string $channel = null): void
        {
            file_put_contents(self::getLogFileFullPath($channel), '');
        }

        public function timer_start($timerName): void
        {
            if (!isset($GLOBALS['e_ts'])) {
                $GLOBALS['e_ts'] = [
                    'start' => [],
                    'stop' => [],
                    'results' => [],
                ];
            }
            $GLOBALS['e_ts']['start'][$timerName] = microtime(true);
        }

        public function timer_stop($timerName): void
        {
            $GLOBALS['e_ts']['results'][$timerName] = $GLOBALS['e_ts']['results'][$timerName] ?? 0;
            $GLOBALS['e_ts']['stop'][$timerName] = microtime(true);
            $GLOBALS['e_ts']['results'][$timerName] += round(
                ($GLOBALS['e_ts']['stop'][$timerName] - $GLOBALS['e_ts']['start'][$timerName]) * 1000,
                4
            );
        }

        private static function getLogFileFullPath(int|string $channel = null): string
        {
            return self::LOG_FILE_LOCATION
                . self::LOG_FILE_NAME . (!is_null($channel) ? ("_" . $channel) : '')
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
}

if (!function_exists('eddie')) {
    function eddie(): Eddie_Logger
    {
        return Eddie_Logger::getInstance();
    }
}
