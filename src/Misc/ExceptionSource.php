<?php

declare(strict_types=1);

namespace Knotlog\Misc;

final readonly class ExceptionSource
{
    public function __construct(
        public string $file,
        public int $line,
    ) {}
}
