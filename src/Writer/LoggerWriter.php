<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;
use Psr\Log\LoggerInterface;

final readonly class LoggerWriter implements LogWriter
{
    public function __construct(
        private LoggerInterface $logger,
        private string $messageKey = 'message',
        private string $errorKey = 'error',
    ) {
    }

    public function write(Log $log): void
    {
        $context = $log->all();

        if ($log->hasError()) {
            $this->logger->error('{' . $this->errorKey . '}', $context);
        } else {
            $this->logger->info('{' . $this->messageKey . '}', $context);
        }
    }
}
