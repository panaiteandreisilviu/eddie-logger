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

        public function clean_backtrace(int|string $channel = null): void
        {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            //duplicate lines
            $ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            $requestUrl = $_SERVER['REQUEST_URI'] . " - " . ($ajax ? 'AJAX' : "NON-AJAX") . "\n";
            $location = $backtrace[1]['class'] . $backtrace[1]['type'] . $backtrace[1]['function'] . "@" . $backtrace[0]['line'];

            $backtrace = array_reverse($backtrace);

            $cleanBacktrace = "<br><b>" . date("[H:i:s]  :  ") . $requestUrl . "</b><br>$location<br>";
            foreach ($backtrace as $item) {
                if ($item['class']) {
                    $cleanBacktrace .= $item['file'] . "@" . $item['line'] . "<br>" . $item['class'] . $item['type'] . $item['function'] . "<br><br>";
                } else {
                    $cleanBacktrace .= $item['file'] . "@" . $item['line'] . "<br>" . $item['function'] . "<br><br>";
                }
            }
            $this->addSfDumpAssets(self::getLogFileFullPath($channel));
            file_put_contents(self::getLogFileFullPath($channel), $cleanBacktrace, FILE_APPEND);
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
