<?php

declare(strict_types=1);

namespace Knotlog\Misc;

use Throwable;

final readonly class ExceptionLog
{
    public static function fromThrowable(Throwable $exception): self
    {
        return new self(
            type: $exception::class,
            message: $exception->getMessage(),
            code: $exception->getCode(),
            file: $exception->getFile(),
            line: $exception->getLine(),
            trace: $exception->getTrace(),
        );
    }

    public function __construct(
        public string $type,
        public string $message,
        public int $code,
        public string $file,
        public int $line,
        /** @var list<array<string, mixed>> */
        public array $trace = [],
    ) {
    }
}
