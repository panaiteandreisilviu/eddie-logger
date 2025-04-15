<?php

namespace EddieLogger\Facade;

interface EddieInterface
{
    public function dump(mixed $debug, string $channel, string $dumpName = '-'): void;

    public function dump_v2(mixed $debug, string $channel, string $dumpName = '-'): void;

    public function clear(string $channel): void;

    public function trace(string $channel): void;

    public function timers(string $channel, string $dumpName = null): void;

    public function timer_start(string $timerName): void;

    public function timer_stop(string $timerName): void;
}