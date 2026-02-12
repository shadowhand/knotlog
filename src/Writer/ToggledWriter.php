<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;
use Override;

final class ToggledWriter implements LogWriter
{
    public function __construct(
        private readonly LogWriter $logWriter,
        private bool $writeLogs = true,
    ) {}

    public function enable(): void
    {
        $this->writeLogs = true;
    }

    public function disable(): void
    {
        $this->writeLogs = false;
    }

    #[Override]
    public function write(Log $log): void
    {
        if ($this->writeLogs) {
            $this->logWriter->write($log);
        }
    }
}
