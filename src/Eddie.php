<?php

namespace EddieLogger;

use EddieLogger\Dumper\Dumper;
use EddieLogger\Timer\Timer;

readonly class Eddie
{
    public function __construct(
        public Dumper $dumper,
        public Timer $timer,
    ) {}

    public function dump(mixed $debug, string $channel, string $dumpName = '-'): void
    {
        $this->dumper->dump_v1($debug, $channel, $dumpName);
    }

    public function dump_v2(mixed $debug, string $channel, string $dumpName = '-'): void
    {
        $this->dumper->dump_v2($debug, $channel, $dumpName);
    }

    public function clear(string $channel): void
    {
        $this->dumper->clear($channel);
    }

    public function trace(string $channel): void
    {
        $this->dumper->trace($channel);
    }

    public function timers(string $channel, string $dumpName = null): void
    {
        $this->dumper->dump_v1($this->timer->timers(), $channel, $dumpName);
    }

    public function timer_start(string $timerName): void
    {
        $this->timer->start($timerName);
    }

    public function timer_stop(string $timerName): void
    {
        $this->timer->stop($timerName);
    }

}