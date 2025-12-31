<?php

declare(strict_types=1);

namespace Knotlog\Tests\Http;

use Knotlog\Http\ServerErrorResponseFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function json_decode;

#[CoversClass(ServerErrorResponseFactory::class)]
final class ServerErrorResponseFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_generic_error_message(): void
    {
        $factory = new Psr17Factory();
        $errorFactory = new ServerErrorResponseFactory($factory, $factory);

        $exception = new RuntimeException('Sensitive internal details');
        $response = $errorFactory->createErrorResponse($exception);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));

        $body = (string) $response->getBody();

        $this->assertJson($body);
        $this->assertStringNotContainsString($exception->getMessage(), $body);

        $data = json_decode($body, true);

        $this->assertIsArray($data);
        $this->assertSame('RuntimeException', $data['error']);
        $this->assertSame('An internal server error has occurred', $data['message']);
    }
}
