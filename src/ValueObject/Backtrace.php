<?php

namespace EddieLogger\ValueObject;

readonly class Backtrace
{
    private array $backtrace;

    public function __construct(array $backtrace)
    {
        $this->backtrace = array_values(
            array_filter($backtrace, fn($t) => str_contains($t['class'], 'EddieLogger')),
        );
    }

    public function formatted(): string
    {
        $formattedBacktrace = [];
        $traceCount = count($this->backtrace);

        foreach ($this->backtrace as $index => $item) {
            if ($index === 0) {
                $traceCount--;
                continue;
            }
            if ($item['class']) {
                $remoteFile = $this->backtrace[$index - 1]['file'] ?? '';
                $remoteLine = (int)($this->backtrace[$index - 1]['line'] ?? 0);
                $hrefText = $item['class'] . $item['type'] . $item['function'] . "@" . ($this->backtrace[$index - 1]['line'] ?? '');
                $phpStormHref = $this->getPhpStormHref($remoteFile, $remoteLine, $hrefText);
                $projectName = $this->getProjectName($remoteFile);
            } else {
                $phpStormHref = $this->getPhpStormHref(
                    remoteFile: $item['file'],
                    remoteLine: $this->backtrace[$index - 1]['line'] ?? '',
                    hrefText: $item['function'] . "@" . ($this->backtrace[$index - 1]['line'] ?? ''),
                );
                $projectName = $this->getProjectName($item['file']);
            }
            $projectFormatted = "<span style='width: 57px; background-color: #b5e1c2; display: inline-block; text-align: center; border: 1px solid #a6a6a6;'>$projectName</span>";
            $traceCountFormatted = "<span style='width: 20px; display: inline-block'>#$traceCount</span>";
            $formattedBacktrace[] = "$projectFormatted $traceCountFormatted $phpStormHref <br>";
            $traceCount--;
        }

        return implode("", array_reverse($formattedBacktrace));
    }

    private function getProjectName(string $remoteFile): string
    {
        if (str_contains($remoteFile, 'vendor/eos-lib/sapi-core')) {
            return 'sapi-core';
        } elseif (str_contains($remoteFile, 'vendor/eos/lib-eos')) {
            return 'lib-eos';
        } elseif (str_contains($remoteFile, 'vendor/eos-lib/sapi-client')) {
            return 'sapi-client';
        } elseif (str_contains($remoteFile, '/sapi/') && !str_contains($remoteFile, '/vendor/')) {
            return 'sapi';
        } elseif (str_contains($remoteFile, '/front/') && !str_contains($remoteFile, '/vendor/')) {
            return 'front';
        } elseif (str_contains($remoteFile, '/www/') && !str_contains($remoteFile, '/vendor/')) {
            return 'www';
        } elseif (str_contains($remoteFile, '/vendor/')) {
            return 'vendor';
        }
        return 'N/A';
    }


    private function getPhpStormHref(mixed $remoteFile, int $remoteLine, string $hrefText): string
    {
        $phpstormUrl = $this->getPhpstormUrl($remoteFile, $remoteLine);
        return $phpstormUrl
            ? "<a href='$phpstormUrl' title='$remoteFile@$remoteLine'>$hrefText</a>"
            : "<span title='$remoteFile@$remoteLine' style='cursor: hand;'>$hrefText</span>";
    }

    private function getPhpstormUrl(string $remoteFile, int $line): ?string
    {
        $localPath = null;
        if (str_contains($remoteFile, 'vendor/eos-lib/sapi-core')) {
            $localPath = '/Users/andrei.panaite/projects/sapi-core' . explode(
                    'vendor/eos-lib/sapi-core',
                    $remoteFile,
                )[1];
        } elseif (str_contains($remoteFile, 'vendor/eos/lib-eos')) {
            $localPath = '/Users/andrei.panaite/projects/lib-eos' . explode('vendor/eos/lib-eos', $remoteFile)[1];
        } elseif (str_contains($remoteFile, 'vendor/eos-lib/sapi-client')) {
            $localPath = '/Users/andrei.panaite/projects/sapi-client' . explode('eos-lib/sapi-client', $remoteFile)[1];
        } elseif (str_contains($remoteFile, '/sapi/') && !str_contains($remoteFile, '/vendor/')) {
            if (preg_match("~tags/[^/]+/data/(.*)~", $remoteFile, $matches)) {
                $localPath = '/Users/andrei.panaite/projects/sapi/' . $matches[1];
            }
        } elseif (str_contains($remoteFile, '/front/') && !str_contains($remoteFile, '/vendor/')) {
            if (preg_match("~tags/[^/]+/data/(.*)~", $remoteFile, $matches)) {
                $localPath = '/Users/andrei.panaite/projects/front/' . $matches[1];
            }
        } elseif (str_contains($remoteFile, '/www/') && !str_contains($remoteFile, '/vendor/')) {
            if (preg_match("~tags/[^/]+/data/(.*)~", $remoteFile, $matches)) {
                $localPath = '/Users/andrei.panaite/projects/www/' . $matches[1];
            }
        }
        return $localPath ? "phpstorm://open?file={$localPath}&line={$line}" : null;
    }

    private function getHrefText(): string
    {
        $backtraceClassExploded = explode('\\', $this->backtrace[1]['class']) ?: [];
        return end($backtraceClassExploded) .
            $this->backtrace[1]['type'] . $this->backtrace[1]['function'] . "@" . $this->backtrace[0]['line'];
    }

    public function projectName(): string
    {
        return $this->getProjectName($this->backtrace[0]['file']);
    }

    public function phpstormHref(): string
    {
        $hrefText = $this->getHrefText();
        $remoteFile = $backtrace[0]['file'] ?? '';
        $remoteLine = (int)($backtrace[0]['line'] ?? 0);
        return $this->getPhpStormHref($remoteFile, $remoteLine, $hrefText);
    }

}