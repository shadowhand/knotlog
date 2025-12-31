<?php

declare(strict_types=1);

namespace Knotlog\Output;

use Knotlog\Log;
use Psr\Log\LoggerInterface;

use function json_encode;

use const JSON_THROW_ON_ERROR;

final readonly class LoggerWriter implements LogWriter
{
    public function __construct(
        private LoggerInterface $logger,
        private int $flags = 0,
    ) {
    }

    public function write(Log $log): void
    {
        $data = json_encode($log, $this->flags | JSON_THROW_ON_ERROR);

        if ($log->hasError()) {
            $this->logger->error($data);
        } else {
            $this->logger->info($data);
        }
    }
}
