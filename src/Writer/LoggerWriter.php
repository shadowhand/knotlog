<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;
use Psr\Log\LoggerInterface;
use Stringable;

use function is_string;

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
            $this->logger->error($this->getError($context), $context);
        } else {
            $this->logger->info($this->getInfo($context), $context);
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getError(array $context): string|Stringable
    {
        $message = $context[$this->errorKey] ?? null;

        if (is_string($message) || $message instanceof Stringable) {
            return $message;
        }

        return 'Knotlog error';
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getInfo(array $context): string|Stringable
    {
        $message = $context[$this->messageKey] ?? null;

        if (is_string($message) || $message instanceof Stringable) {
            return $message;
        }

        return 'Knotlog entry';
    }
}
