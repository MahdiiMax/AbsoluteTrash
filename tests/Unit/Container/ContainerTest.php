<?php

declare(strict_types=1);

namespace Tests\Unit\Container;

use stdClass;
use Tests\Support\ContainerConsumer;
use Tests\Support\ContainerService;
use Tests\TestCase;
use Trash\Container\Container;
use Trash\Container\Exceptions\NotFoundException;

class ContainerTest extends TestCase
{
    public function test_bind_closure_receives_container(): void
    {
        $container = new Container();
        $received = null;
        $container->bind('answer', function (Container $c) use (&$received): int {
            $received = $c;
            return 42;
        });
        $this->assertSame(42, $container->make('answer'));
        $this->assertSame($container, $received);
    }

    public function test_bind_to_class_name(): void
    {
        $container = new Container();
        $container->bind('service', ContainerService::class);
        $this->assertInstanceOf(ContainerService::class, $container->make('service'));
    }

    public function test_singleton_returns_same_instance(): void
    {
        $container = new Container();
        $container->singleton('service', ContainerService::class);
        $this->assertSame($container->make('service'), $container->make('service'));
    }

    public function test_instance_returns_exact_object(): void
    {
        $container = new Container();
        $obj = new stdClass();
        $container->instance('thing', $obj);
        $this->assertSame($obj, $container->make('thing'));
    }

    public function test_has_checks_bindings_instances_and_classes(): void
    {
        $container = new Container();
        $container->bind('bound', fn() => 1);
        $container->instance('inst', 2);
        $this->assertTrue($container->has('bound'));
        $this->assertTrue($container->has('inst'));
        $this->assertTrue($container->has(ContainerService::class));
        $this->assertFalse($container->has('missing'));
    }

    public function test_unbound_make_throws_not_found(): void
    {
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Target [missing] is not bound in the container.');
        $container->make('missing');
    }

    public function test_uninstantiable_make_throws(): void
    {
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $container->make(\Closure::class);
    }
    public function test_auto_wiring_resolves_nested_dependencies(): void
    {
        $container = new Container();
        $consumer = $container->make(ContainerConsumer::class);
        $this->assertInstanceOf(ContainerConsumer::class, $consumer);
        $this->assertInstanceOf(ContainerService::class, $consumer->service());
    }

    public function test_call_coerces_scalar_arguments(): void
    {
        $container = new Container();
        $result = $container->call(
            function (int $a, float $b, bool $c, string $d) {
                return [$a, $b, $c, $d];
            },
            ['a' => '5', 'b' => '5.5', 'c' => 'true', 'd' => 7]
        );
        $this->assertSame([5, 5.5, true, '7'], $result);
    }

    public function test_call_uses_defaults_when_missing(): void
    {
        $container = new Container();
        $result = $container->call(fn(int $x = 10) => $x * 2);
        $this->assertSame(20, $result);
    }

    public function test_call_missing_required_parameter_throws(): void
    {
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $container->call(fn(int $x) => $x);
    }

    public function test_call_on_class_method_instantiates_via_container(): void
    {
        $container = new Container();
        $service = $container->call([ContainerConsumer::class, 'service']);
        $this->assertInstanceOf(ContainerService::class, $service);
    }

    public function test_get_is_make_alias(): void
    {
        $container = new Container();
        $container->bind('v', fn() => 1);
        $this->assertSame(1, $container->get('v'));
    }
}
