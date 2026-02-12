<?php

declare(strict_types=1);

namespace Knotlog\Tests;

use Knotlog\Log;
use Knotlog\LogException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(Log::class)]
final class LogTest extends TestCase
{
    #[Test]
    public function it_starts_with_empty_context(): void
    {
        $log = new Log();

        $this->assertSame([], $log->all());
    }

    #[Test]
    public function it_sets_values(): void
    {
        $log = new Log();
        $log->set('user_id', '12345');
        $log->set('status_code', 200);
        $log->set('duration_ms', 123.45);
        $log->set('is_authenticated', true);
        $log->set('tags', ['production', 'api', 'v2']);

        $expected = [
            'user_id' => '12345',
            'status_code' => 200,
            'duration_ms' => 123.45,
            'is_authenticated' => true,
            'tags' => ['production', 'api', 'v2'],
        ];

        $this->assertTrue($log->has('user_id'));
        $this->assertTrue($log->has('status_code'));
        $this->assertTrue($log->has('duration_ms'));
        $this->assertTrue($log->has('is_authenticated'));
        $this->assertTrue($log->has('tags'));
        $this->assertSame($expected, $log->all());
    }

    #[Test]
    public function it_overwrites_existing_keys(): void
    {
        $log = new Log();
        $log->set('status', 'pending');
        $log->set('status', 'completed');

        $this->assertSame(['status' => 'completed'], $log->all());
    }

    #[Test]
    public function has_returns_false_for_missing_keys_and_null_values(): void
    {
        $log = new Log();
        $log->set('empty', null);

        $this->assertFalse($log->has('noop'));
        $this->assertFalse($log->has('empty'));
    }

    #[Test]
    public function has_error_returns_false_when_no_error(): void
    {
        $log = new Log();
        $log->set('status', 200);

        $this->assertFalse($log->hasError());
    }

    #[Test]
    public function has_error_returns_true_when_error_or_exception_is_set(): void
    {
        $log = new Log();

        $this->assertFalse($log->hasError());

        $log = new Log();
        $log->set('error', 'Something went wrong');

        $this->assertTrue($log->hasError());

        $log = new Log();
        $log->set('exception', 'RuntimeException');

        $this->assertTrue($log->hasError());
    }

    #[Test]
    public function it_appends_multiple_values(): void
    {
        $log = new Log();
        $log->append('tags', 'first');
        $log->append('tags', 'second');
        $log->append('tags', 'third');

        $this->assertSame(['tags' => ['first', 'second', 'third']], $log->all());
    }

    #[Test]
    public function it_throws_when_appending_to_a_non_list(): void
    {
        $log = new Log();
        $log->set('status', 'active');

        $this->expectException(LogException::class);
        $this->expectExceptionMessage("Unable to append to 'status', it is not a list");

        $log->append('status', 'value');
    }

    #[Test]
    public function it_throws_when_appending_to_an_associative_array(): void
    {
        $log = new Log();
        $log->set('meta', ['key' => 'value']);

        $this->expectException(LogException::class);

        $log->append('meta', 'value');
    }

    #[Test]
    public function it_serializes_to_json(): void
    {
        $log = new Log();

        $json = json_encode($log, JSON_THROW_ON_ERROR);

        $this->assertSame('{}', $json);

        $log->set('request_id', 'xyz-789');
        $log->set('status', 200);
        $log->set('duration_ms', 34.5);

        $expected = '{"request_id":"xyz-789","status":200,"duration_ms":34.5}';
        $json = json_encode($log);

        $this->assertSame($expected, $json);
    }
}
