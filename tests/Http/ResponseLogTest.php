<?php

declare(strict_types=1);

namespace Knotlog\Tests\Http;

use Knotlog\Http\ResponseLog;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;
use function strlen;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ResponseLog::class)]
final class ResponseLogTest extends TestCase
{
    #[Test]
    public function it_captures_response_context(): void
    {
        $body = json_encode(['data' => ['id' => 1, 'name' => 'John']], JSON_THROW_ON_ERROR);

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Length' => (string) strlen($body),
            'X-Request-ID' => 'req_xyz789',
            'X-Rate-Limit-Remaining' => '99',
        ];

        $response = new Response(200, $headers, $body);

        $log = ResponseLog::fromResponse($response);

        $this->assertSame(200, $log->status);
        $this->assertArrayHasKey('Content-Type', $log->headers);
        $this->assertArrayHasKey('Content-Length', $log->headers);
        $this->assertArrayHasKey('X-Request-ID', $log->headers);
        $this->assertArrayHasKey('X-Rate-Limit-Remaining', $log->headers);
        $this->assertContains($headers['Content-Type'], $log->headers['Content-Type']);
        $this->assertContains($headers['Content-Length'], $log->headers['Content-Length']);
        $this->assertContains($headers['X-Request-ID'], $log->headers['X-Request-ID']);
        $this->assertContains($headers['X-Rate-Limit-Remaining'], $log->headers['X-Rate-Limit-Remaining']);
        $this->assertSame(strlen($body), $log->size);
    }
}
