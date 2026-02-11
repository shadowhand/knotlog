<?php

declare(strict_types=1);

namespace Knotlog\Console;

use Knotlog\Log;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

final readonly class LogCommandEvent
{
    public function __construct(
        private Log $log,
    ) {}

    public function __invoke(ConsoleCommandEvent $event): void
    {
        $this->log->set('console', ConsoleLog::fromEvent($event));
    }
}
