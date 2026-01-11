<?php

declare(strict_types=1);

namespace Knotlog;

use JsonSerializable;

final class LogList implements JsonSerializable
{
    /** @var list<object> */
    private array $items = [];

    public function push(object $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return list<object>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
