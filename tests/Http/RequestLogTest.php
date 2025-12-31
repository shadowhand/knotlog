<?php

declare(strict_types=1);

namespace Knotlog\Tests\Http;

use Knotlog\Http\RequestLog;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequestLog::class)]
final class RequestLogTest extends TestCase
{
    #[Test]
    public function it_captures_request_context(): void
    {
        $uri = new Uri('https://api.example.com/api/v2/checkout?session_id=abc123');

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer secret_token',
            'User-Agent' => 'AcmeClient/1.0',
            'X-Request-ID' => 'req_xyz789',
        ];

        $request = new Request('POST', $uri, $headers);

        $log = RequestLog::fromRequest($request);

        $this->assertSame('POST', $log->method);
        $this->assertSame('api.example.com', $log->host);
        $this->assertSame('/api/v2/checkout', $log->path);
        $this->assertSame(['session_id' => 'abc123'], $log->query);
        $this->assertArrayHasKey('Content-Type', $log->headers);
        $this->assertArrayHasKey('Authorization', $log->headers);
        $this->assertArrayHasKey('User-Agent', $log->headers);
        $this->assertArrayHasKey('X-Request-ID', $log->headers);
        $this->assertContains($headers['Content-Type'], $log->headers['Content-Type']);
        $this->assertContains($headers['Authorization'], $log->headers['Authorization']);
        $this->assertContains($headers['User-Agent'], $log->headers['User-Agent']);
        $this->assertContains($headers['X-Request-ID'], $log->headers['X-Request-ID']);
    }
}
