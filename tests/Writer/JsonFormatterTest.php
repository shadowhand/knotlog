<?php

declare(strict_types=1);

namespace Knotlog\Tests\Writer;

use Knotlog\Log;
use Knotlog\Writer\JsonFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[CoversClass(JsonFormatter::class)]
final class JsonFormatterTest extends TestCase
{
    #[Test]
    public function it_formats_log_as_json(): void
    {
        $formatter = new JsonFormatter();

        $log = new Log();
        $log->set('message', 'test');

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR);

        $this->assertSame($expectedJson, $formatter->format($log));
    }

    #[Test]
    public function it_allows_custom_json_flags(): void
    {
        $formatter = new JsonFormatter(flags: JSON_PRETTY_PRINT);

        $log = new Log();
        $log->set('message', 'test');

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $this->assertSame($expectedJson, $formatter->format($log));
    }
}
