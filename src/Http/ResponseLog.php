<?php

declare(strict_types=1);

namespace Knotlog\Http;

use Psr\Http\Message\ResponseInterface;

final readonly class ResponseLog
{
    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(
            status: $response->getStatusCode(),
            // @mago-ignore analysis:less-specific-argument
            headers: $response->getHeaders(),
            size: $response->getBody()->getSize(),
        );
    }

    public function __construct(
        public int $status,
        /** @var array<string, list<string>> */
        public array $headers,
        public ?int $size,
    ) {}
}
