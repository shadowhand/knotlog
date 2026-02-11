<?php

declare(strict_types=1);

namespace Knotlog\Tests\Http;

use Knotlog\Http\LogResponseError;
use Knotlog\Log;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(LogResponseError::class)]
final class LogResponseErrorTest extends TestCase
{
    /**
     * @return array<string, array{0: int, 1: string}>
     */
    public static function errorStatus(): array
    {
        return [
            '400 Bad Request' => [400, 'Bad Request'],
            '401 Unauthorized' => [401, 'Unauthorized'],
            '403 Forbidden' => [403, 'Forbidden'],
            '404 Not Found' => [404, 'Not Found'],
            '500 Internal Server Error' => [500, 'Internal Server Error'],
            '502 Bad Gateway' => [502, 'Bad Gateway'],
            '503 Service Unavailable' => [503, 'Service Unavailable'],
        ];
    }

    #[DataProvider('errorStatus')]
    #[Test]
    public function it_sets_different_reason_phrases_correctly(int $statusCode, string $reasonPhrase): void
    {
        $log = new Log();
        $middleware = new LogResponseError($log);

        $request = new ServerRequest('GET', 'https://api.example.com/test');
        $response = new Response(status: $statusCode, reason: $reasonPhrase);
        $handler = $this->createMockHandler($response);

        $middleware->process($request, $handler);

        $this->assertTrue($log->hasError());
        $this->assertSame(['error' => $reasonPhrase], $log->all());
    }

    #[Test]
    public function it_does_not_set_error_for_2xx_status(): void
    {
        $log = new Log();
        $middleware = new LogResponseError($log);

        $request = new ServerRequest('GET', 'https://api.example.com/users');
        $response = new Response(200, [], null, '1.1', 'OK');
        $handler = $this->createMockHandler($response);

        $middleware->process($request, $handler);

        $this->assertFalse($log->hasError());
        $this->assertSame([], $log->all());
    }

    #[Test]
    public function it_does_not_set_error_for_3xx_status(): void
    {
        $log = new Log();
        $middleware = new LogResponseError($log);

        $request = new ServerRequest('GET', 'https://api.example.com/users');
        $response = new Response(301, [], null, '1.1', 'Moved Permanently');
        $handler = $this->createMockHandler($response);

        $middleware->process($request, $handler);

        $this->assertFalse($log->hasError());
        $this->assertSame([], $log->all());
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
