# Eddie Logger

Simple logging utility for PHP applications.

## Usage

```php
require_once('/var/www/config/eddie/eddie.php');
eddie()->dump('what to debug', 'channel', 'dump_name');
```