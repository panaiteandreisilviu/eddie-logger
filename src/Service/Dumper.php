<?php

namespace EddieLogger\Service;

use EddieLogger\ValueObject\Backtrace;

readonly class Dumper
{

    public function __construct(private FileStorage $fileStorage) {}

    public function dump_v1(mixed $debug, string $channel, string $dumpName = null): void
    {
        if (function_exists('dump')) {
            ob_start();
            dump($debug);
            $dump = ob_get_contents();
            ob_end_clean();
        } else {
            $dump = print_r($debug, true);
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $location = $backtrace[1]['class'] . $backtrace[1]['type'] . $backtrace[1]['function'] . "@" . $backtrace[0]['line'];
        $locationFile = $backtrace[0]['file'] . "@" . $backtrace[0]['line'];

        $ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? 'AJAX' : "NON-AJAX";
        $requestUrl = urldecode($_SERVER['REQUEST_URI']);

        $contents =
            "<br><b>" . date("H:i:s") . "<br>" . "<span style='white-space: nowrap'>$requestUrl</span>" . "</b><br>$ajax<br>$location<br>" . "$locationFile<br>"
            . (!empty($dumpName) ? "<br><span style='color:#5397da; font-weight: bold;'>$dumpName</span><br>" : '')
            . $dump
            . "<br><b>____________________________________________</b><br>";

        $this->fileStorage->put($channel, $contents);
    }

    public function dump_v2(mixed $debug, string $channel, string $dumpName = '-'): void
    {
        $backtrace = new Backtrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        if (function_exists('dump')) {
            ob_start();
            dump($debug);
            $dump = ob_get_contents();
            ob_end_clean();
        } else {
            $dump = print_r($debug, true);
        }

        $ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') == 'xmlhttprequest' ? 'AJAX' : "NON-AJAX";
        $requestUrl = urldecode($_SERVER['REQUEST_URI']);
        $dumpDate = date("H:i:s");

        $contents = <<<EOHTML
  <div style="display: flex; flex-wrap: nowrap; height: auto; border-bottom: 2px solid #c2c2c2">
    <div style="width: 50%; padding: 10px; background-color: #f0f0f0;">
      <b>{$dumpDate}</b> <span style='color:#5397da; font-weight: bold;'>$dumpName</span> - {$backtrace->phpstormHref()} - {$backtrace->projectName()}
      {$dump}
    </div>
    <div style="width: 50%; padding: 10px; background-color: #e0e0e0; overflow: auto; white-space: nowrap; font-size: 13px">
       <span style='white-space: nowrap'><b>$ajax - $requestUrl</b></span><br>
       <br>
       {$backtrace->formatted()}
    </div>
  </div>
EOHTML;

        $this->fileStorage->put($channel, $contents);
    }


    public function trace(string $channel): void
    {
        $backtrace = new Backtrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        $this->fileStorage->put($channel, $backtrace->formatted());
    }

    public function clear(string $channel): void
    {
        $this->fileStorage->put($channel, "");
    }

}