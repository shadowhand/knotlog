<?php

declare(strict_types=1);

namespace Knotlog\Tests\Misc;

use Knotlog\Misc\ExceptionLog;
use Knotlog\Misc\ExceptionSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ExceptionLog::class)]
#[CoversClass(ExceptionSource::class)]
final class ExceptionLogTest extends TestCase
{
    #[Test]
    public function it_captures_exception_context(): void
    {
        $exception = new RuntimeException('Something went wrong');

        $log = ExceptionLog::fromThrowable($exception);

        $this->assertSame($exception::class, $log->type);
        $this->assertSame($exception->getMessage(), $log->message);
        $this->assertSame($exception->getCode(), $log->code);
        $this->assertSame($exception->getFile(), $log->source->file);
        $this->assertSame($exception->getLine(), $log->source->line);
        $this->assertSame($exception->getTrace(), $log->trace);
    }
}
