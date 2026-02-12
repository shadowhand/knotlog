<?php

declare(strict_types=1);

namespace Knotlog\Tests;

use Knotlog\LogException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogException::class)]
final class LogExceptionTest extends TestCase
{
    #[Test]
    public function it_creates_cannot_append_to_key_exception(): void
    {
        $exception = LogException::cannotAppendToKey('tags');

        $this->assertSame("Unable to append to 'tags', it is not a list", $exception->getMessage());
    }
}
