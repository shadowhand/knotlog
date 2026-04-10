<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;

interface LogFormatter
{
    public function format(Log $log): string;
}
