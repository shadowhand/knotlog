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
            source: new ExceptionSource($exception->getFile(), $exception->getLine()),
            // @mago-ignore analysis:possibly-invalid-argument
            trace: $exception->getTrace(),
        );
    }

    public function __construct(
        public string $type,
        public string $message,
        public int|string $code,
        public ExceptionSource $source,
        /** @var list<array<string, mixed>> */
        public array $trace = [],
    ) {}
}
