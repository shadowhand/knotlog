<?php

declare(strict_types=1);

namespace Knotlog\Tests;

use Knotlog\LogList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

use function json_encode;

#[CoversClass(LogList::class)]
final class LogListTest extends TestCase
{
    #[Test]
    public function it_serializes_empty_list_to_empty_json_array(): void
    {
        $logList = new LogList();

        $json = json_encode($logList);

        $this->assertSame('[]', $json);
    }

    #[Test]
    public function it_pushes_single_object(): void
    {
        $logList = new LogList();
        $item = new stdClass();
        $item->id = 1;
        $item->message = 'test';

        $logList->push($item);

        $json = json_encode($logList);
        $expected = '[{"id":1,"message":"test"}]';
        $this->assertSame($expected, $json);
    }

    #[Test]
    public function it_maintains_insertion_order(): void
    {
        $logList = new LogList();

        $first = new stdClass();
        $first->position = 'first';

        $second = new stdClass();
        $second->position = 'second';

        $third = new stdClass();
        $third->position = 'third';

        $logList->push($first);
        $logList->push($second);
        $logList->push($third);

        $json = json_encode($logList);
        $expected = '[{"position":"first"},{"position":"second"},{"position":"third"}]';
        $this->assertSame($expected, $json);
    }

    #[Test]
    public function it_handles_objects_with_different_properties(): void
    {
        $logList = new LogList();

        $simple = new stdClass();
        $simple->name = 'simple';

        $complex = new stdClass();
        $complex->id = 42;
        $complex->data = ['key' => 'value'];
        $complex->active = true;

        $logList->push($simple);
        $logList->push($complex);

        $json = json_encode($logList);
        $expected = '[{"name":"simple"},{"id":42,"data":{"key":"value"},"active":true}]';
        $this->assertSame($expected, $json);
    }

    #[Test]
    public function it_serializes_objects_with_nested_data(): void
    {
        $logList = new LogList();

        $item = new stdClass();
        $item->user = [
            'id' => 123,
            'name' => 'John Doe',
            'roles' => ['admin', 'editor'],
        ];
        $item->metadata = [
            'timestamp' => '2024-01-01T00:00:00Z',
            'source' => 'api',
        ];

        $logList->push($item);

        $json = json_encode($logList);
        $expected = '[{"user":{"id":123,"name":"John Doe","roles":["admin","editor"]},"metadata":{"timestamp":"2024-01-01T00:00:00Z","source":"api"}}]';
        $this->assertSame($expected, $json);
    }

    #[Test]
    public function json_serialize_returns_array(): void
    {
        $logList = new LogList();

        $item1 = new stdClass();
        $item1->id = 1;

        $item2 = new stdClass();
        $item2->id = 2;

        $logList->push($item1);
        $logList->push($item2);

        $result = $logList->jsonSerialize();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame($item1, $result[0]);
        $this->assertSame($item2, $result[1]);
    }

    #[Test]
    public function json_serialize_returns_empty_array_for_empty_list(): void
    {
        $logList = new LogList();

        $result = $logList->jsonSerialize();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
