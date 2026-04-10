<?php

declare(strict_types=1);

namespace Knotlog\Console;

use Knotlog\Log;
use Symfony\Component\Console\Event\ConsoleErrorEvent;

final readonly class LogCommandError
{
    public function __construct(
        private Log $log,
    ) {}

    public function __invoke(ConsoleErrorEvent $event): void
    {
        $throwable = $event->getError();

        $this->log->set('error', sprintf('%s %s', $throwable::class, $throwable->getMessage()));
    }
}
