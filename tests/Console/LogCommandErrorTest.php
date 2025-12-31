<?php

declare(strict_types=1);

namespace Knotlog\Tests\Console;

use Knotlog\Console\LogCommandError;
use Knotlog\Log;
use Knotlog\Misc\ExceptionLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(LogCommandError::class)]
final class LogCommandErrorTest extends TestCase
{
    #[Test]
    public function it_logs_exception_from_console_error_event(): void
    {
        $log = new Log();
        $listener = new LogCommandError($log);

        $command = new Command('test:command');
        $input = new ArrayInput([]);
        $output = new NullOutput();
        $exception = new RuntimeException('Command failed');

        $event = new ConsoleErrorEvent($input, $output, $exception, $command);

        $listener($event);

        $this->assertTrue($log->hasError());
        $this->assertInstanceOf(ExceptionLog::class, $log->all()['exception']);
    }
}
