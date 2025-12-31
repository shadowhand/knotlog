<?php

declare(strict_types=1);

namespace Knotlog\Tests\Http;

use Exception;
use Knotlog\Http\ErrorResponseFactory;
use Knotlog\Http\LogRequestError;
use Knotlog\Log;
use Knotlog\Misc\ExceptionLog;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

#[CoversClass(LogRequestError::class)]
final class LogRequestErrorTest extends TestCase
{
    #[Test]
    public function it_returns_response_from_handler(): void
    {
        $errorFactory = $this->createMockErrorFactory();
        $log = new Log();
        $middleware = new LogRequestError($errorFactory, $log);

        $request = new ServerRequest('GET', 'https://api.example.com/users');
        $expectedResponse = new Response(200, [], '{"users": []}');

        $handler = $this->createMockHandler($expectedResponse);

        $actualResponse = $middleware->process($request, $handler);

        $this->assertFalse($log->hasError());
        $this->assertSame($expectedResponse, $actualResponse);
    }

    #[Test]
    public function it_catches_exception_from_handler(): void
    {
        $errorFactory = $this->createMockErrorFactory();
        $log = new Log();
        $middleware = new LogRequestError($errorFactory, $log);

        $request = new ServerRequest('GET', 'https://api.example.com/error');
        $exception = new Exception('Something went wrong');

        $handler = $this->createThrowingHandler($exception);

        $middleware->process($request, $handler);

        $this->assertTrue($log->hasError());
        $this->assertInstanceOf(ExceptionLog::class, $log->all()['exception']);
    }

    private function createMockErrorFactory(): ErrorResponseFactory
    {
        $response = new Response(500);

        return new readonly class ($response) implements ErrorResponseFactory {
            public function __construct(private ResponseInterface $response)
            {
            }

            public function createErrorResponse(Throwable $throwable): ResponseInterface
            {
                return $this->response;
            }
        };
    }

    private function createMockHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new readonly class ($response) implements RequestHandlerInterface {
            public function __construct(private ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }

    private function createThrowingHandler(Throwable $exception): RequestHandlerInterface
    {
        return new readonly class ($exception) implements RequestHandlerInterface {
            public function __construct(private Throwable $exception)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                throw $this->exception;
            }
        };
    }
}
