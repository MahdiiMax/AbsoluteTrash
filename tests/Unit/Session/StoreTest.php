<?php

declare(strict_types=1);

namespace Tests\Unit\Session;

use Tests\Support\InMemorySessionHandler;
use Tests\TestCase;
use Trash\Session\Store;

class StoreTest extends TestCase
{
    private function makeStore(InMemorySessionHandler $handler, string $id = 'sess123'): Store
    {
        return new Store($id, $handler);
    }

    public function test_start_loads_serialized_attributes_from_handler(): void
    {
        $handler = new InMemorySessionHandler();
        $handler->data['sess123'] = serialize(['foo' => 'bar']);
        $store = $this->makeStore($handler);
        $this->assertTrue($store->start());
        $this->assertSame('bar', $store->get('foo'));
    }

    public function test_start_twice_does_not_reload(): void
    {
        $handler = new InMemorySessionHandler();
        $handler->data['sess123'] = serialize(['foo' => 'bar']);
        $store = $this->makeStore($handler);
        $store->start();
        $store->start();
        $this->assertSame('bar', $store->get('foo'));
    }

    public function test_get_set_has_forget_flush(): void
    {
        $store = $this->makeStore(new InMemorySessionHandler());
        $store->start();
        $this->assertNull($store->get('missing'));
        $this->assertSame('default', $store->get('missing', 'default'));
        $store->set('name', 'Alice');
        $this->assertTrue($store->has('name'));
        $this->assertSame('Alice', $store->get('name'));
        $store->forget('name');
        $this->assertFalse($store->has('name'));
        $store->set('a', 1);
        $store->set('b', 2);
        $store->flush();
        $this->assertSame([], $store->all());
    }

    public function test_has_works_with_null_value(): void
    {
        $store = $this->makeStore(new InMemorySessionHandler());
        $store->start();
        $store->set('null_value', null);
        $this->assertTrue($store->has('null_value'));
    }

    public function test_push_pull_increment_decrement(): void
    {
        $store = $this->makeStore(new InMemorySessionHandler());
        $store->start();
        $store->push('items', 'a');
        $store->push('items', 'b');
        $this->assertSame(['a', 'b'], $store->get('items'));
        $this->assertSame('a', $store->pull('items'));
        $this->assertSame(['b'], $store->get('items'));
        $store->set('count', 5);
        $this->assertSame(6, $store->increment('count'));
        $this->assertSame(4, $store->decrement('count'));
        $this->assertSame(1, $store->increment('fresh'));
    }

    public function test_flash_lifecycle_across_requests(): void
    {
        $handler = new InMemorySessionHandler();
        $first = $this->makeStore($handler);
        $first->start();
        $first->flash('status', 'ok');
        $first->save();
        $this->assertArrayHasKey('status', $handler->data['sess123']);
        $second = $this->makeStore($handler);
        $second->start();
        $this->assertSame('ok', $second->get('status'));
        $second->save();
        $third = $this->makeStore($handler);
        $third->start();
        $this->assertNull($third->get('status'));
    }

    public function test_token_is_random_hex_and_stable(): void
    {
        $store = $this->makeStore(new InMemorySessionHandler());
        $store->start();
        $token = $store->token();
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $token);
        $this->assertSame($token, $store->token());
    }

    public function test_token_persists_after_save(): void
    {
        $handler = new InMemorySessionHandler();
        $first = $this->makeStore($handler);
        $first->start();
        $token = $first->token();
        $first->save();
        $second = $this->makeStore($handler);
        $second->start();
        $this->assertSame($token, $second->token());
    }

    public function test_regenerate_changes_id(): void
    {
        $store = $this->makeStore(new InMemorySessionHandler());
        $store->start();
        $old = $store->getId();
        $store->regenerate();
        $this->assertNotSame($old, $store->getId());
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $store->getId());
    }

    public function test_save_without_start_does_not_write(): void
    {
        $handler = new InMemorySessionHandler();
        $store = $this->makeStore($handler);
        $store->save();
        $this->assertSame([], $handler->data);
    }
}