<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;
use Override;
use Symfony\Component\Filesystem\Filesystem;

use function json_encode;

use const JSON_THROW_ON_ERROR;

final readonly class FileWriter implements LogWriter
{
    public function __construct(
        private string $path = 'php://stderr',
        private int $flags = 0,
        private Filesystem $filesystem = new Filesystem(),
    ) {}

    #[Override]
    public function write(Log $log): void
    {
        $status = $log->hasError() ? 'ERROR' : 'INFO';

        $line = json_encode($log, JSON_THROW_ON_ERROR | $this->flags);

        // @mago-ignore analysis:unhandled-thrown-type
        $this->filesystem->appendToFile($this->path, "{$status} {$line}\n");
    }
}
