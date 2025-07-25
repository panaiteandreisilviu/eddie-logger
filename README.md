# Eddie Logger

Simple, pluggable logging utility for PHP applications.

## Installation

```bash
cd installation_dir
bash <(curl -fsSL https://raw.githubusercontent.com/panaiteandreisilviu/eddie-logger/master/install.sh)
```

This will download and set up the `eddie.phar` archive in the `eddie` directory.

## Usage

```php
require_once('installation_dir/eddie/eddie.phar');
eddie()->dump('what to debug', 'channel', 'dump_name');
```