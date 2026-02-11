<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;
use Override;
use Psr\Log\LoggerInterface;

final readonly class LoggerWriter implements LogWriter
{
    public function __construct(
        private LoggerInterface $logger,
        private string $messageKey = 'message',
        private string $errorKey = 'error',
    ) {}

    #[Override]
    public function write(Log $log): void
    {
        $log->hasError() ? $this->writeError($log) : $this->writeInfo($log);
    }

    private function writeError(Log $log): void
    {
        $context = $log->all();

        $message = (string) ($context[$this->errorKey] ?? 'Error');

        $this->logger->error($message, $context);
    }

    private function writeInfo(Log $log): void
    {
        $context = $log->all();

        $message = (string) ($context[$this->messageKey] ?? 'Success');

        $this->logger->info($message, $context);
    }
}
