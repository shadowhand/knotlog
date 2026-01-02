<?php

declare(strict_types=1);

namespace Knotlog\Tests\Writer;

use Knotlog\Log;
use Knotlog\Writer\FileWriter;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function explode;
use function file_get_contents;
use function is_file;
use function json_encode;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[CoversClass(FileWriter::class)]
final class FileWriterTest extends TestCase
{
    private string $tempFile;

    #[Before]
    protected function createTemporaryFile(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'knotlog_test_');
    }

    #[After]
    protected function deleteTemporaryFile(): void
    {
        if (is_file($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    private function readTemporaryFile(): string
    {
        return file_get_contents($this->tempFile) ?: throw new RuntimeException('Failed to read temporary file');
    }

    #[Test]
    public function it_writes_info_status_for_non_error_logs(): void
    {
        $writer = new FileWriter($this->tempFile);

        $log = new Log();
        $log->set('message', 'test');

        $writer->write($log);

        $content = $this->readTemporaryFile();

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString("INFO $expectedJson", $content);
    }

    #[Test]
    public function it_writes_error_status_for_error_logs(): void
    {
        $writer = new FileWriter($this->tempFile);

        $log = new Log();
        $log->set('error', 'Something went wrong');

        $writer->write($log);

        $content = $this->readTemporaryFile();

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString("ERROR $expectedJson", $content);
    }

    #[Test]
    public function it_writes_error_status_for_exception_logs(): void
    {
        $writer = new FileWriter($this->tempFile);

        $log = new Log();
        $log->set('exception', 'RuntimeException');

        $writer->write($log);

        $content = $this->readTemporaryFile();

        $expectedJson = json_encode($log, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString("ERROR $expectedJson", $content);
    }

    #[Test]
    public function it_appends_to_existing_file(): void
    {
        $writer   = new FileWriter($this->tempFile);

        $log1 = new Log();
        $log1->set('message', 'first');
        $writer->write($log1);

        $log2 = new Log();
        $log2->set('message', 'second');
        $writer->write($log2);

        $content = $this->readTemporaryFile();

        $this->assertStringContainsString('first', $content);
        $this->assertStringContainsString('second', $content);
    }

    #[Test]
    public function it_uses_custom_json_flags(): void
    {
        $writer = new FileWriter($this->tempFile, JSON_PRETTY_PRINT);

        $log = new Log();
        $log->set('message', 'test');

        $writer->write($log);

        $content = $this->readTemporaryFile();

        $this->assertStringContainsString("{\n", $content);
    }

    #[Test]
    public function it_writes_each_log_on_new_line(): void
    {
        $writer   = new FileWriter($this->tempFile);

        $log1 = new Log();
        $log1->set('message', 'first');
        $writer->write($log1);

        $log2 = new Log();
        $log2->set('message', 'second');
        $writer->write($log2);

        $content = $this->readTemporaryFile();
        unlink($this->tempFile);

        $lines = explode("\n", trim($content));
        $this->assertCount(2, $lines);
    }

    #[Test]
    public function it_throws_exception_for_invalid_path(): void
    {
        $writer = new FileWriter('/invalid/path.log');

        $log = new Log();
        $log->set('message', 'test');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to open log file: /invalid/path.log');

        $writer->write($log);
    }
}
