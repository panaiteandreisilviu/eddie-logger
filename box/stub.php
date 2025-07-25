<?php

Phar::mapPhar('eddie.phar');

require 'phar://eddie.phar/eddie.php';

__HALT_COMPILER();