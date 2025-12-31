<?php

declare(strict_types=1);

namespace Knotlog\Tests\Http;

use Knotlog\Http\LogResponseError;
use Knotlog\Log;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function random_int;

#[CoversClass(LogResponseError::class)]
final class LogResponseErrorTest extends TestCase
{
    #[Test]
    public function it_sets_error_flag_for_4xx_status(): void
    {
        $log = new Log();
        $middleware = new LogResponseError($log);

        $status = random_int(400, 499);

        $request = new ServerRequest('GET', 'https://api.example.com/users');
        $response = new Response($status);
        $handler = $this->createMockHandler($response);

        $middleware->process($request, $handler);

        $this->assertTrue($log->hasError());
    }

    #[Test]
    public function it_sets_error_flag_for_5xx_status(): void
    {
        $log = new Log();
        $middleware = new LogResponseError($log);

        $status = random_int(500, 599);

        $request = new ServerRequest('GET', 'https://api.example.com/error');
        $response = new Response($status);
        $handler = $this->createMockHandler($response);

        $middleware->process($request, $handler);

        $this->assertTrue($log->hasError());
    }

    #[Test]
    public function it_does_not_set_error_flag_for_2xx_status(): void
    {
        $log = new Log();
        $middleware = new LogResponseError($log);

        $status = random_int(200, 299);

        $request = new ServerRequest('GET', 'https://api.example.com/users');
        $response = new Response($status);
        $handler = $this->createMockHandler($response);

        $middleware->process($request, $handler);

        $this->assertFalse($log->hasError());
    }

    #[Test]
    public function it_does_not_set_error_flag_for_3xx_status(): void
    {
        $log = new Log();
        $middleware = new LogResponseError($log);

        $status = random_int(300, 399);

        $request = new ServerRequest('GET', 'https://api.example.com/users');
        $response = new Response($status);
        $handler = $this->createMockHandler($response);

        $middleware->process($request, $handler);

        $this->assertFalse($log->hasError());
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
}
