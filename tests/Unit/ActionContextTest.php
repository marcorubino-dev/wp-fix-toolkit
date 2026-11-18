<?php

namespace MWS\WPFixToolkit\Tests\Unit;

use MWS\WPFixToolkit\Domain\Context\ActionContext;
use PHPUnit\Framework\TestCase;

final class ActionContextTest extends TestCase
{
    public function test_parameter_arrays_are_returned_as_copies()
    {
        $context = new ActionContext(
            '123e4567-e89b-12d3-a456-426614174000',
            'mws/example/noop',
            '1.0.0',
            7,
            array('nested' => array('value' => 'original')),
            1700000000
        );

        $parameters = $context->get_parameters();
        $parameters['nested']['value'] = 'changed';

        $this->assertSame('original', $context->get_parameters()['nested']['value']);
    }

    public function test_operation_fingerprint_is_independent_of_associative_key_order()
    {
        $first = new ActionContext(
            '123e4567-e89b-12d3-a456-426614174000',
            'mws/example/noop',
            '1.0.0',
            7,
            array('outer' => array('a' => 1, 'b' => 2)),
            1700000000
        );
        $second = new ActionContext(
            '123e4567-e89b-12d3-a456-426614174000',
            'mws/example/noop',
            '1.0.0',
            7,
            array('outer' => array('b' => 2, 'a' => 1)),
            1700000000
        );

        $this->assertSame($first->get_operation_fingerprint(), $second->get_operation_fingerprint());
    }

    public function test_operation_fingerprint_changes_when_parameters_change()
    {
        $first = new ActionContext(
            '123e4567-e89b-12d3-a456-426614174000',
            'mws/example/noop',
            '1.0.0',
            7,
            array('target' => 'cache-a'),
            1700000000
        );
        $second = new ActionContext(
            '123e4567-e89b-12d3-a456-426614174000',
            'mws/example/noop',
            '1.0.0',
            7,
            array('target' => 'cache-b'),
            1700000000
        );

        $this->assertNotSame($first->get_operation_fingerprint(), $second->get_operation_fingerprint());
    }
}
