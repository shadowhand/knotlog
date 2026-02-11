<?php

declare(strict_types=1);

namespace Knotlog\Http;

use Override;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use ReflectionClass;
use Throwable;

use function json_encode;

use const JSON_THROW_ON_ERROR;

final readonly class ServerErrorResponseFactory implements ErrorResponseFactory
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {}

    #[Override]
    public function createErrorResponse(Throwable $throwable): ResponseInterface
    {
        $body = [
            // @mago-ignore analysis:unhandled-thrown-type
            'error' => new ReflectionClass($throwable)->getShortName(),
            'message' => 'An internal server error has occurred',
        ];

        // @mago-ignore analysis:unhandled-thrown-type
        $stream = $this->streamFactory->createStream(json_encode($body, JSON_THROW_ON_ERROR));

        return $this->responseFactory
            ->createResponse(500)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }
}
