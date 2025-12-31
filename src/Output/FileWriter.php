<?php

declare(strict_types=1);

namespace Knotlog\Output;

use Knotlog\Log;
use RuntimeException;

use function fclose;
use function fopen;
use function fwrite;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final readonly class FileWriter implements LogWriter
{
    public function __construct(
        private string $path = 'php://stderr',
        private int $flags = 0,
    ) {
    }

    public function write(Log $log): void
    {
        $f = @fopen($this->path, 'a');

        if ($f === false) {
            throw new RuntimeException("Unable to open log file: {$this->path}");
        }

        $status = match ($log->hasError()) {
            true => 'ERROR',
            false => 'INFO',
        };

        $line = json_encode($log, $this->flags | JSON_THROW_ON_ERROR);

        try {
            fwrite($f, "$status $line \n");
        } finally {
            fclose($f);
        }
    }
}
