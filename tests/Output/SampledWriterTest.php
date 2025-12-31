<?php

declare(strict_types=1);

namespace Knotlog\Tests\Output;

use Knotlog\Log;
use Knotlog\Output\LogWriter;
use Knotlog\Output\SampledWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PHP_INT_MAX;

#[CoversClass(SampledWriter::class)]
final class SampledWriterTest extends TestCase
{
    #[Test]
    public function it_always_writes_error_logs_regardless_of_sampling(): void
    {
        $innerWriter = $this->createMock(LogWriter::class);

        $log = new Log();
        $log->set('error', 'Something went wrong');

        $innerWriter->expects($this->once())
            ->method('write')
            ->with($log);

        $writer = new SampledWriter($innerWriter, PHP_INT_MAX);
        $writer->write($log);
    }

    #[Test]
    public function it_always_writes_exception_logs_regardless_of_sampling(): void
    {
        $innerWriter = $this->createMock(LogWriter::class);

        $log = new Log();
        $log->set('exception', 'RuntimeException');

        $innerWriter->expects($this->once())
            ->method('write')
            ->with($log);

        $writer = new SampledWriter($innerWriter, PHP_INT_MAX);
        $writer->write($log);
    }

    public function it_samples_non_error_logs(): void
    {
        $innerWriter = $this->createMock(LogWriter::class);

        $log = new Log();
        $log->set('message', 'This is a regular log message');

        $innerWriter->expects($this->once())
                    ->method('write')
                    ->with($log);

        $writer = new SampledWriter($innerWriter, 1);
        $writer->write($log);
    }
}
