<?php

declare(strict_types=1);

namespace Knotlog\Tests\Writer;

use Knotlog\Log;
use Knotlog\Writer\LoggerWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(LoggerWriter::class)]
final class LoggerWriterTest extends TestCase
{
    #[Test]
    public function it_writes_to_info_level_for_non_error_logs(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $log = new Log();
        $log->set('message', 'test');
        $log->set('user_id', 123);

        $logger->expects($this->once())
            ->method('info')
            ->with('{message}', ['message' => 'test', 'user_id' => 123]);

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
        $log->set('user_id', 123);

        $logger->expects($this->once())
            ->method('error')
            ->with('{error}', ['error' => 'Something went wrong', 'user_id' => 123]);

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
        $log->set('user_id', 123);

        $logger->expects($this->once())
            ->method('error')
            ->with('{error}', ['exception' => 'RuntimeException', 'user_id' => 123]);

        $logger->expects($this->never())
            ->method('info');

        $writer = new LoggerWriter($logger);
        $writer->write($log);
    }

    #[Test]
    public function it_uses_custom_message_key(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $log = new Log();
        $log->set('msg', 'custom message');
        $log->set('user_id', 123);

        $logger->expects($this->once())
            ->method('info')
            ->with('{msg}', ['msg' => 'custom message', 'user_id' => 123]);

        $writer = new LoggerWriter($logger, messageKey: 'msg');
        $writer->write($log);
    }

    #[Test]
    public function it_uses_custom_error_key(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $log = new Log();
        $log->set('error', 'Something went wrong');
        $log->set('err', 'custom error');

        $logger->expects($this->once())
            ->method('error')
            ->with('{err}', ['error' => 'Something went wrong', 'err' => 'custom error']);

        $writer = new LoggerWriter($logger, errorKey: 'err');
        $writer->write($log);
    }

    #[Test]
    public function it_passes_entire_log_context_to_logger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $log = new Log();
        $log->set('user_id', 123);
        $log->set('action', 'login');
        $log->set('cart_items', ['item1', 'item2']);
        $log->set('duration_ms', 45.2);

        $expectedContext = [
            'user_id' => 123,
            'action' => 'login',
            'cart_items' => ['item1', 'item2'],
            'duration_ms' => 45.2,
        ];

        $logger->expects($this->once())
            ->method('info')
            ->with('{message}', $expectedContext);

        $writer = new LoggerWriter($logger);
        $writer->write($log);
    }
}
