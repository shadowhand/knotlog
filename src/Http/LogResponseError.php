<?php

declare(strict_types=1);

namespace Knotlog\Http;

use Knotlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that flags the log as an error based on response status
 *
 * If sampling is enabled, this middleware will ensure that any response with
 * a status code of 400 or greater will force the log to be captured.
 */
final readonly class LogResponseError implements MiddlewareInterface
{
    public function __construct(
        private Log $log,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response->getStatusCode() >= 400) {
            $this->log->set('error', $response->getReasonPhrase());
        }

        return $response;
    }
}
