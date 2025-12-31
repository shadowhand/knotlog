<?php

declare(strict_types=1);

namespace Knotlog\Http;

use Knotlog\Log;
use Knotlog\Misc\ExceptionLog;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Middleware that logs any exception thrown during request handling
 */
final readonly class LogRequestError implements MiddlewareInterface
{
    public function __construct(
        private ErrorResponseFactory $errorResponseFactory,
        private Log $log,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $throwable) {
            $this->log->set('exception', ExceptionLog::fromThrowable($throwable));

            return $this->errorResponseFactory->createErrorResponse($throwable);
        }
    }
}
