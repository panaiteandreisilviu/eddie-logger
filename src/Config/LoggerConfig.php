<?php

namespace EddieLogger\Config;

class LoggerConfig {
    public function __construct(false|string|null $config) {
        if ($config === null) {
            throw new \RuntimeException('Logger configuration file is empty.');
        }
    }
}