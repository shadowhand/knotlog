<?php

declare(strict_types=1);

namespace Knotlog;

use JsonSerializable;

/**
 * Wide logging context container
 *
 * Accumulates context throughout the lifecycle of an invocation/request.
 * Provides a single, comprehensive rich log message.
 */
final class Log implements JsonSerializable
{
    /** @var array<string, mixed> */
    private array $context = [];

    /**
     * Set a value in the context
     */
    public function set(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * Check if a key exists in the context
     */
    public function has(string $key): bool
    {
        return isset($this->context[$key]);
    }

    /**
     * Check if the context has an error or exception
     */
    public function hasError(): bool
    {
        return $this->has('error')
            || $this->has('exception');
    }

    /**
     * Get all context
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->context;
    }

    /**
     * Prepare the log context for JSON serialization
     */
    public function jsonSerialize(): object
    {
        return (object) $this->all();
    }
}
