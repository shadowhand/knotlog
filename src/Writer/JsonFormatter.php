<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;
use Override;

final readonly class JsonFormatter implements LogFormatter
{
    public function __construct(
        private int $flags = 0,
    ) {}

    #[Override]
    public function format(Log $log): string
    {
        return json_encode($log, JSON_THROW_ON_ERROR | $this->flags);
    }
}
