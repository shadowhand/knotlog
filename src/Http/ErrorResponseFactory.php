<?php

declare(strict_types=1);

namespace Knotlog\Http;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface ErrorResponseFactory
{
    public function createErrorResponse(Throwable $throwable): ResponseInterface;
}
