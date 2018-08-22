<?php

declare(strict_types=1);

namespace PedroTroller\ImageResize;

final class FileSizeFormatter
{
    public function bytes(float $bytes): string
    {
        if ($bytes < 1000) {
            return $this->format($bytes, 'B');
        }

        return $this->kiloBytes($bytes / 1000);
    }

    public function kiloBytes(float $kiloBytes): string
    {
        if ($kiloBytes < 1000) {
            return $this->format($kiloBytes, 'KB');
        }

        return $this->megaBytes($kiloBytes / 1000);
    }

    public function megaBytes(float $megaBytes): string
    {
        if ($megaBytes < 1000) {
            return $this->format($megaBytes, 'MB');
        }

        return $this->gigaBytes($megaBytes / 1000);
    }

    public function gigaBytes(float $gigaBytes): string
    {
        if ($gigaBytes < 1000) {
            return $this->format($gigaBytes, 'GB');
        }

        return $this->teraBytes($gigaBytes / 1000);
    }

    public function teraBytes(float $teraBytes): string
    {
        return $this->format($teraBytes, 'TB');
    }

    private function format(float $value, string $unit): string
    {
        if (ceil($value) === $value) {
            return sprintf('%d %s', $value, $unit);
        }

        return sprintf('%s %s', number_format($value, 2), $unit);
    }
}
