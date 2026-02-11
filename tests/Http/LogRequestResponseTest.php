<?php

declare(strict_types=1);

namespace Knotlog\Tests\Http;

use Knotlog\Http\LogRequestResponse;
use Knotlog\Http\RequestLog;
use Knotlog\Http\ResponseLog;
use Knotlog\Log;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(LogRequestResponse::class)]
final class LogRequestResponseTest extends TestCase
{
    #[Test]
    public function it_captures_request_and_response_context(): void
    {
        $log = new Log();
        $middleware = new LogRequestResponse($log);

        $request = new ServerRequest('POST', 'https://api.example.com/checkout?session=abc123', [
            'Content-Type' => 'application/json',
        ]);

        $response = new Response(201, ['Location' => '/orders/456'], '{"order_id": 456}');

        $handler = $this->createMockHandler($response);

        $result = $middleware->process($request, $handler);

        $context = $log->all();

        // Verify request was logged
        // @mago-ignore analysis:mixed-assignment
        $requestLog = $context['request'] ?? null;
        $this->assertInstanceOf(RequestLog::class, $requestLog);
        $this->assertSame('POST', $requestLog->method);
        $this->assertSame('api.example.com', $requestLog->host);
        $this->assertSame('/checkout', $requestLog->path);

        // Verify response was logged
        // @mago-ignore analysis:mixed-assignment
        $responseLog = $context['response'] ?? null;
        $this->assertInstanceOf(ResponseLog::class, $responseLog);
        $this->assertSame(201, $responseLog->status);
        $this->assertArrayHasKey('Location', $responseLog->headers);

        // Verify response was returned
        $this->assertSame($response, $result);
    }

    private function createMockHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new readonly class($response) implements RequestHandlerInterface {
            public function __construct(
                private ResponseInterface $response,
            ) {}

            #[Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
