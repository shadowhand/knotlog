<?php

declare(strict_types=1);

namespace Knotlog\Tests\Console;

use Knotlog\Console\ConsoleLog;
use Knotlog\Console\LogCommandEvent;
use Knotlog\Log;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(LogCommandEvent::class)]
#[CoversMethod(ConsoleLog::class, 'fromEvent')]
final class LogCommandEventTest extends TestCase
{
    #[Test]
    public function it_logs_command_from_console_event(): void
    {
        $log = new Log();
        $listener = new LogCommandEvent($log);

        $command = new Command('test:command');
        $input = new ArrayInput([]);
        $output = new NullOutput();
        $event = new ConsoleCommandEvent($command, $input, $output);

        $listener($event);

        $this->assertInstanceOf(ConsoleLog::class, $log->all()['console']);
    }
}
