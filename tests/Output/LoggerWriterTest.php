<?php

declare(strict_types=1);

namespace Knotlog\Tests\Output;

use Knotlog\Log;
use Knotlog\Output\LoggerWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[CoversClass(LoggerWriter::class)]
final class LoggerWriterTest extends TestCase
{
    #[Test]
    public function it_writes_to_info_level_for_non_error_logs(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $log = new Log();
        $log->set('message', 'test');

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR);

        $logger->expects($this->once())
            ->method('info')
            ->with($expectedJson);

        $logger->expects($this->never())
            ->method('error');

        $writer = new LoggerWriter($logger);
        $writer->write($log);
    }

    #[Test]
    public function it_writes_to_error_level_for_error_logs(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $log = new Log();
        $log->set('error', 'Something went wrong');

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR);

        $logger->expects($this->once())
            ->method('error')
            ->with($expectedJson);

        $logger->expects($this->never())
            ->method('info');

        $writer = new LoggerWriter($logger);
        $writer->write($log);
    }

    #[Test]
    public function it_writes_to_error_level_for_exception_logs(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $log = new Log();
        $log->set('exception', 'RuntimeException');

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR);

        $logger->expects($this->once())
            ->method('error')
            ->with($expectedJson);

        $logger->expects($this->never())
            ->method('info');

        $writer = new LoggerWriter($logger);
        $writer->write($log);
    }

    #[Test]
    public function it_uses_custom_json_flags(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $log = new Log();
        $log->set('user_id', 123);
        $log->set('action', 'login');

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $logger->expects($this->once())
            ->method('info')
            ->with($expectedJson);

        $writer = new LoggerWriter($logger, JSON_PRETTY_PRINT);
        $writer->write($log);
    }
}
