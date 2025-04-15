<?php

namespace EddieLogger\Facade;

require_once __DIR__ . '/EddieInterface.php';

class NullEddie implements EddieInterface
{
    private string $exceptionMessage;

    public function __construct(string $exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;
        $this->logError($exceptionMessage);
    }

    private function logError(string $message): void
    {
        $eddieErrorLog = dirname(__DIR__, 2) . '/eddie_error.log';
        file_put_contents($eddieErrorLog, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
    }

    public function dump(mixed $debug, string $channel, string $dumpName = '-'): void
    {
        $this->logError("Unable to dump: {$this->exceptionMessage}");
    }

    public function dump_v2(mixed $debug, string $channel, string $dumpName = '-'): void
    {
        $this->logError("Unable to dump_v2: {$this->exceptionMessage}");
    }

    public function clear(string $channel): void
    {
        $this->logError("Unable to clear: {$this->exceptionMessage}");
    }

    public function trace(string $channel): void
    {
        $this->logError("Unable to trace: {$this->exceptionMessage}");
    }

    public function timers(string $channel, string $dumpName = null): void
    {
        $this->logError("Unable to show timers: {$this->exceptionMessage}");
    }

    public function timer_start(string $timerName): void
    {
        $this->logError("Unable to start timer: {$this->exceptionMessage}");
    }

    public function timer_stop(string $timerName): void
    {
        $this->logError("Unable to stop timer: {$this->exceptionMessage}");
    }
}