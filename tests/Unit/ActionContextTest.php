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
}
