<?php

declare(strict_types=1);

namespace Knotlog\Http;

use Knotlog\Log;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that logs the request and response metadata
 */
final readonly class LogRequestResponse implements MiddlewareInterface
{
    public function __construct(
        private Log $log,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->log->set('request', RequestLog::fromRequest($request));

        $response = $handler->handle($request);

        $this->log->set('response', ResponseLog::fromResponse($response));

        return $response;
    }
}
