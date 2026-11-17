<?php

namespace MWS\WPFixToolkit\Tests\Unit;

use InvalidArgumentException;
use LogicException;
use MWS\WPFixToolkit\Examples\External\ExampleExternalAction;
use MWS\WPFixToolkit\Infrastructure\Registry\InMemoryActionRegistry;
use PHPUnit\Framework\TestCase;

final class InMemoryActionRegistryTest extends TestCase
{
    public function test_it_registers_and_resolves_an_external_action_by_exact_version()
    {
        $registry = new InMemoryActionRegistry();
        $action = new ExampleExternalAction();

        $registry->register($action);

        $this->assertTrue($registry->has('example-vendor/example-noop', '1.0.0'));
        $this->assertSame($action, $registry->get('example-vendor/example-noop', '1.0.0'));
    }

    public function test_it_does_not_resolve_another_version_implicitly()
    {
        $registry = new InMemoryActionRegistry();
        $registry->register(new ExampleExternalAction());

        $this->expectException(InvalidArgumentException::class);
        $registry->get('example-vendor/example-noop', '1.0');
    }

    public function test_it_rejects_duplicate_action_id_and_version()
    {
        $registry = new InMemoryActionRegistry();
        $registry->register(new ExampleExternalAction());

        $this->expectException(LogicException::class);
        $registry->register(new ExampleExternalAction());
    }
}
