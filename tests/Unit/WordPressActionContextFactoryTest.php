<?php

namespace MWS\WPFixToolkit\Tests\Unit;

use MWS\WPFixToolkit\Domain\Context\ActionContext;
use MWS\WPFixToolkit\Infrastructure\WordPress\WordPressActionContextFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class WordPressActionContextFactoryTest extends TestCase
{
    public function test_it_builds_context_from_the_authenticated_wordpress_user()
    {
        $factory = new WordPressActionContextFactory(
            function () { return 27; },
            function () { return 'a8098c1a-f86e-4f7b-9c3d-7a5d3f8e1b20'; },
            function () { return 1794950400; }
        );

        $context = $factory->create('mws/cache/clear', '1.0.0', array('scope' => 'object'));

        $this->assertInstanceOf(ActionContext::class, $context);
        $this->assertSame(27, $context->get_requested_by());
        $this->assertSame('a8098c1a-f86e-4f7b-9c3d-7a5d3f8e1b20', $context->get_execution_uuid());
        $this->assertSame(1794950400, $context->get_created_at());
        $this->assertSame(array('scope' => 'object'), $context->get_parameters());
    }

    public function test_it_rejects_when_no_authenticated_user_is_available()
    {
        $factory = new WordPressActionContextFactory(
            function () { return 0; },
            function () { return 'a8098c1a-f86e-4f7b-9c3d-7a5d3f8e1b20'; },
            function () { return 1794950400; }
        );

        $this->expectException(RuntimeException::class);
        $factory->create('mws/cache/clear', '1.0.0', array());
    }

    public function test_it_rejects_an_invalid_uuid()
    {
        $factory = new WordPressActionContextFactory(
            function () { return 27; },
            function () { return 'not-a-uuid'; },
            function () { return 1794950400; }
        );

        $this->expectException(RuntimeException::class);
        $factory->create('mws/cache/clear', '1.0.0', array());
    }

    public function test_it_rejects_an_invalid_timestamp()
    {
        $factory = new WordPressActionContextFactory(
            function () { return 27; },
            function () { return 'a8098c1a-f86e-4f7b-9c3d-7a5d3f8e1b20'; },
            function () { return 0; }
        );

        $this->expectException(RuntimeException::class);
        $factory->create('mws/cache/clear', '1.0.0', array());
    }
}
