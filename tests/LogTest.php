<?php

declare(strict_types=1);

namespace Knotlog\Tests;

use Knotlog\Log;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

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
    public function it_sets_string_values(): void
    {
        $log = new Log();
        $log->set('user_id', '12345');

        $this->assertTrue($log->has('user_id'));
        $this->assertSame(['user_id' => '12345'], $log->all());
    }

    #[Test]
    public function it_sets_integer_values(): void
    {
        $log = new Log();
        $log->set('status_code', 200);

        $this->assertTrue($log->has('status_code'));
        $this->assertSame(['status_code' => 200], $log->all());
    }

    #[Test]
    public function it_sets_float_values(): void
    {
        $log = new Log();
        $log->set('duration_ms', 123.45);

        $this->assertTrue($log->has('duration_ms'));
        $this->assertSame(['duration_ms' => 123.45], $log->all());
    }

    #[Test]
    public function it_sets_boolean_values(): void
    {
        $log = new Log();
        $log->set('is_authenticated', true);

        $this->assertTrue($log->has('is_authenticated'));
        $this->assertSame(['is_authenticated' => true], $log->all());
    }

    #[Test]
    public function it_sets_array_values(): void
    {
        $log = new Log();
        $log->set('tags', ['production', 'api', 'v2']);

        $this->assertTrue($log->has('tags'));
        $this->assertSame(['tags' => ['production', 'api', 'v2']], $log->all());
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
    public function it_accumulates_multiple_context_values(): void
    {
        $log = new Log();
        $log->set('request_id', 'abc-123');
        $log->set('user_id', '456');
        $log->set('method', 'POST');
        $log->set('path', '/api/users');
        $log->set('duration_ms', 45.2);

        $expected = [
            'request_id' => 'abc-123',
            'user_id' => '456',
            'method' => 'POST',
            'path' => '/api/users',
            'duration_ms' => 45.2,
        ];

        $this->assertSame($expected, $log->all());
    }

    #[Test]
    public function has_returns_false_for_missing_keys(): void
    {
        $log = new Log();

        $this->assertFalse($log->has('nonexistent'));
    }

    #[Test]
    public function has_returns_false_for_null_values(): void
    {
        $log = new Log();
        $log->set('nullable', null);

        $this->assertFalse($log->has('nullable'));
    }

    #[Test]
    public function has_error_returns_false_when_no_error(): void
    {
        $log = new Log();
        $log->set('status', 200);

        $this->assertFalse($log->hasError());
    }

    #[Test]
    public function has_error_returns_true_when_error_is_set(): void
    {
        $log = new Log();
        $log->set('error', 'Something went wrong');

        $this->assertTrue($log->hasError());
    }

    #[Test]
    public function has_error_returns_true_when_exception_is_set(): void
    {
        $log = new Log();
        $log->set('exception', 'RuntimeException');

        $this->assertTrue($log->hasError());
    }

    #[Test]
    public function it_serializes_to_json(): void
    {
        $log = new Log();
        $log->set('request_id', 'xyz-789');
        $log->set('status', 200);
        $log->set('duration_ms', 34.5);

        $json = json_encode($log);

        $expected = '{"request_id":"xyz-789","status":200,"duration_ms":34.5}';
        $this->assertSame($expected, $json);
    }

    #[Test]
    public function it_serializes_empty_context_to_empty_json_object(): void
    {
        $log = new Log();

        $json = json_encode($log);

        $this->assertSame('{}', $json);
    }

    #[Test]
    public function it_serializes_nested_arrays(): void
    {
        $log = new Log();
        $log->set('user', [
            'id' => 123,
            'email' => 'user@example.com',
            'roles' => ['admin', 'editor'],
        ]);

        $json = json_encode($log);

        $expected = '{"user":{"id":123,"email":"user@example.com","roles":["admin","editor"]}}';
        $this->assertSame($expected, $json);
    }
}
