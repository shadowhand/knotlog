<?php

declare(strict_types=1);

namespace Knotlog\Http;

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
    ) {
    }

    public function createErrorResponse(Throwable $throwable): ResponseInterface
    {
        $body = [
            'error' => new ReflectionClass($throwable)->getShortName(),
            'message' => 'An internal server error has occurred',
        ];

        $stream = $this->streamFactory->createStream(json_encode($body, JSON_THROW_ON_ERROR));

        return $this->responseFactory->createResponse(500)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }
}
