<?php

namespace EddieLogger\Storage;

use EddieLogger\Config\LoggerConfig;

class FileStorage {
    public function __construct(private LoggerConfig $config) {}
}