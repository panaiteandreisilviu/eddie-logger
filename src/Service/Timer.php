<?php

namespace EddieLogger\Service;

class Timer {

    // TODO stop using GLOBALS
    public function timers(): array
    {
        return $GLOBALS['e_ts']['results'];
    }

    public function start(string $timerName): void
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

    public function stop(string $timerName): void
    {
        $GLOBALS['e_ts']['results'][$timerName] = $GLOBALS['e_ts']['results'][$timerName] ?? 0;
        $GLOBALS['e_ts']['stop'][$timerName] = microtime(true);
        $GLOBALS['e_ts']['results'][$timerName] += round(
            ($GLOBALS['e_ts']['stop'][$timerName] - $GLOBALS['e_ts']['start'][$timerName]) * 1000,
            4
        );
    }

}