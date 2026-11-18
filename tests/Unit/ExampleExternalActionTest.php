<?php

namespace MWS\WPFixToolkit\Tests\Unit;

use MWS\WPFixToolkit\Domain\Context\ActionContext;
use MWS\WPFixToolkit\Domain\Results\ExecutionResult;
use MWS\WPFixToolkit\Domain\Results\VerificationResult;
use MWS\WPFixToolkit\Examples\External\ExampleExternalAction;
use PHPUnit\Framework\TestCase;

final class ExampleExternalActionTest extends TestCase
{
    public function test_example_action_executes_and_verifies_without_modifying_site()
    {
        $action = new ExampleExternalAction();
        $context = new ActionContext(
            '123e4567-e89b-12d3-a456-426614174000',
            'example-vendor/example-noop',
            '1.0.0',
            7,
            array(),
            1700000000
        );

        $validation = $action->validate($context);
        $execution = $action->execute($context);
        $verification = $action->verify($context, $execution);

        $this->assertTrue($validation->is_valid());
        $this->assertSame(ExecutionResult::SUCCEEDED, $execution->get_status());
        $this->assertFalse($execution->has_modified_site());
        $this->assertSame(VerificationResult::PASSED, $verification->get_status());
    }
}
