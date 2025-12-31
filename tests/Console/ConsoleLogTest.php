<?php

declare(strict_types=1);

namespace Knotlog\Tests\Console;

use Knotlog\Console\ConsoleLog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

#[CoversClass(ConsoleLog::class)]
final class ConsoleLogTest extends TestCase
{
    #[Test]
    public function it_captures_command_context(): void
    {
        $command = new Command('app:deploy');
        $command->setDefinition(new InputDefinition([
            new InputArgument('version', InputArgument::REQUIRED),
            new InputOption('env', null, InputOption::VALUE_REQUIRED),
            new InputOption('force', 'f', InputOption::VALUE_NONE),
        ]));

        $input = new ArrayInput([
            'version' => '100.1.5',
            '--env' => 'production',
            '--force' => true,
        ], $command->getDefinition());

        $log = ConsoleLog::fromCommand($command, $input);

        $this->assertSame('app:deploy', $log->name);
        $this->assertArrayHasKey('version', $log->arguments);
        $this->assertArrayHasKey('env', $log->options);
        $this->assertArrayHasKey('force', $log->options);
        $this->assertSame($input->getArgument('version'), $log->arguments['version']);
        $this->assertSame($input->getOption('env'), $log->options['env']);
        $this->assertTrue($log->options['force']);
    }
}
