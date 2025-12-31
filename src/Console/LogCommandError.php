<?php

declare(strict_types=1);

namespace Knotlog\Console;

use Knotlog\Log;
use Knotlog\Misc\ExceptionLog;
use Symfony\Component\Console\Event\ConsoleErrorEvent;

final readonly class LogCommandError
{
    public function __construct(
        private Log $log,
    ) {
    }

    public function __invoke(ConsoleErrorEvent $event): void
    {
        $this->log->set('exception', ExceptionLog::fromThrowable($event->getError()));
    }
}
