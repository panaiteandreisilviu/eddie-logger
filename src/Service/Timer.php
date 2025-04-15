<?php

namespace EddieLogger\Service;

class Timer {
    private array $timerStarts = [];
    private array $timerStops = [];
    private array $timerResults = [];

    public function timers(): array
    {
        return $this->timerResults;
    }

    public function start(string $timerName): void
    {
        $this->timerStarts[$timerName] = microtime(true);
    }

    public function stop(string $timerName): void
    {
        if (!isset($this->timerStarts[$timerName])) {
            $this->timerResults[$timerName] = 'not started';
        }

        $this->timerResults[$timerName] = ($this->timerResults[$timerName] ?? 0);
        $this->timerStops[$timerName] = microtime(true);
        $this->timerResults[$timerName] += round(
            ($this->timerStops[$timerName] - $this->timerStarts[$timerName]) * 1000,
            4
        );
    }

    public function reset(): void
    {
        $this->timerStarts = [];
        $this->timerStops = [];
        $this->timerResults = [];
    }
}