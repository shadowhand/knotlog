<?php

declare(strict_types=1);

namespace Knotlog\Writer;

use Knotlog\Log;

interface LogWriter
{
    public function write(Log $log): void;
}
