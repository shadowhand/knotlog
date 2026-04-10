<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;
use Override;
use Symfony\Component\Filesystem\Filesystem;

final readonly class FileWriter implements LogWriter
{
    public function __construct(
        private Filesystem $filesystem = new Filesystem(),
        private LogFormatter $formatter = new JsonFormatter(),
        private string $path = 'php://stderr',
    ) {}

    #[Override]
    public function write(Log $log): void
    {
        $line = $this->formatter->format($log);

        // @mago-ignore analysis:unhandled-thrown-type
        $this->filesystem->appendToFile($this->path, "{$line}\n");
    }
}
