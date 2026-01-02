<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;

use function random_int;

final readonly class SampledWriter implements LogWriter
{
    private bool $isSampled;

    public function __construct(
        private LogWriter $logWriter,
        private int $sampleChance = 5,
    ) {
        $this->isSampled = random_int(1, $this->sampleChance) === 1;
    }

    public function write(Log $log): void
    {
        if ($this->isSampled || $log->hasError()) {
            $this->logWriter->write($log);
        }
    }
}
