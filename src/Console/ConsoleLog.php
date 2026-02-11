<?php

declare(strict_types=1);

namespace Knotlog\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;

final readonly class ConsoleLog
{
    public static function fromCommand(?Command $command, InputInterface $input): self
    {
        return new self(
            name: $command?->getName(),
            // @mago-ignore analysis:less-specific-argument
            arguments: $input->getArguments(),
            // @mago-ignore analysis:less-specific-argument
            options: $input->getOptions(),
        );
    }

    public static function fromEvent(ConsoleCommandEvent $event): self
    {
        return self::fromCommand(command: $event->getCommand(), input: $event->getInput());
    }

    public function __construct(
        public ?string $name,
        /** @var array<string, mixed> */
        public array $arguments = [],
        /** @var array<string, mixed> */
        public array $options = [],
    ) {}
}
