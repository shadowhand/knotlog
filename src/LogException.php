<?php

declare(strict_types=1);

namespace Knotlog;

use Exception;

final class LogException extends Exception
{
    public static function cannotAppendToKey(string $key): self
    {
        return new self(message: "Unable to append to '{$key}', it is not a list");
    }
}
