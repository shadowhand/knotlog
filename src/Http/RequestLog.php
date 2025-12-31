<?php

declare(strict_types=1);

namespace Knotlog\Http;

use Psr\Http\Message\RequestInterface;

use function parse_str;

final readonly class RequestLog
{
    public static function fromRequest(RequestInterface $request): self
    {
        parse_str($request->getUri()->getQuery(), $query);

        return new self(
            method: $request->getMethod(),
            host: $request->getUri()->getHost(),
            path: $request->getUri()->getPath(),
            query: $query,
            // @phpstan-ignore-next-line
            headers: $request->getHeaders(),
        );
    }

    public function __construct(
        public string $method,
        public string $host,
        public string $path,
        /** @var array<array-key, mixed> */
        public array $query,
        /** @var array<string, list<string>> */
        public array $headers,
    ) {
    }
}
