# Eddie Logger

Simple logging utility for PHP applications.

## One-Line Installation

```bash
curl -s -L https://raw.githubusercontent.com/panaiteandreisilviu/eddie-logger/master/install.sh | bash
```

## Usage

```php
require_once('/var/www/config/eddie/eddie.php');
eddie()->dump('what to debug', 'channel', 'dump_name');
```