<?php

declare(strict_types=1);

namespace Knotlog;

use JsonSerializable;
use Override;

use function array_is_list;
use function is_array;

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
     * @throws LogException if the current value of the key is not a list.
     */
    public function append(string $key, mixed $value): void
    {
        $this->context[$key] ??= [];

        if (!is_array($this->context[$key]) || !array_is_list($this->context[$key])) {
            throw LogException::cannotAppendToKey($key);
        }

        $this->context[$key][] = $value;
    }

    /**
     * Check if a key exists in the context
     */
    public function has(string $key): bool
    {
        return ($this->context[$key] ?? null) !== null;
    }

    /**
     * Check if the context has an error or exception
     */
    public function hasError(): bool
    {
        return $this->has('error') || $this->has('exception');
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
    #[Override]
    public function jsonSerialize(): object
    {
        return (object) $this->all();
    }
}
