<?php

declare(strict_types=1);

namespace Knotlog\Tests\Writer;

use Knotlog\Log;
use Knotlog\Writer\LogWriter;
use Knotlog\Writer\ToggledWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToggledWriter::class)]
final class ToggledWriterTest extends TestCase
{
    #[Test]
    public function it_is_enabled_by_default(): void
    {
        $log = new Log();

        $innerWriter = $this->createMock(LogWriter::class);
        $innerWriter->expects($this->once())->method('write')->with($log);

        $writer = new ToggledWriter($innerWriter);
        $writer->write($log);
    }

    #[Test]
    public function it_can_be_constructed_as_disabled(): void
    {
        $log = new Log();

        $innerWriter = $this->createMock(LogWriter::class);
        $innerWriter->expects($this->never())->method('write');

        $writer = new ToggledWriter($innerWriter, writeLogs: false);
        $writer->write($log);
    }

    #[Test]
    public function it_does_not_write_when_disabled(): void
    {
        $log = new Log();

        $innerWriter = $this->createMock(LogWriter::class);
        $innerWriter->expects($this->never())->method('write');

        $writer = new ToggledWriter($innerWriter);
        $writer->disable();
        $writer->write($log);
    }

    #[Test]
    public function it_can_be_re_enabled_after_disabling(): void
    {
        $log = new Log();

        $innerWriter = $this->createMock(LogWriter::class);
        $innerWriter->expects($this->once())->method('write')->with($log);

        $writer = new ToggledWriter($innerWriter);
        $writer->disable();
        $writer->enable();
        $writer->write($log);
    }
}
