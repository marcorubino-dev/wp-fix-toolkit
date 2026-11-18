<?php

namespace MWS\WPFixToolkit\Examples\External;

use MWS\WPFixToolkit\Domain\Actions\ActionDefinition;
use MWS\WPFixToolkit\Domain\Actions\ActionRiskLevel;
use MWS\WPFixToolkit\Domain\Actions\ActionReversibility;
use MWS\WPFixToolkit\Domain\Context\ActionContext;
use MWS\WPFixToolkit\Domain\Contracts\FixActionInterface;
use MWS\WPFixToolkit\Domain\Results\ExecutionResult;
use MWS\WPFixToolkit\Domain\Results\ValidationResult;
use MWS\WPFixToolkit\Domain\Results\VerificationResult;

/**
 * Example extension action. It deliberately performs no WordPress changes.
 */
final class ExampleExternalAction implements FixActionInterface
{
    public function get_definition(): ActionDefinition
    {
        return new ActionDefinition(
            'example-vendor/example-noop',
            '1.0.0',
            'Example no-op action',
            'Demonstrates registration by an external extension without changing the site.',
            ActionRiskLevel::LOW,
            array('manage_options'),
            ActionReversibility::NOT_APPLICABLE
        );
    }

    public function validate(ActionContext $context): ValidationResult
    {
        return ValidationResult::valid();
    }

    public function execute(ActionContext $context): ExecutionResult
    {
        return new ExecutionResult(
            ExecutionResult::SUCCEEDED,
            'example.noop',
            'Example action completed without modifying the site.',
            array('operation' => 'no-op'),
            false
        );
    }

    public function verify(ActionContext $context, ExecutionResult $result): VerificationResult
    {
        if ($result->get_status() !== ExecutionResult::SUCCEEDED || $result->has_modified_site()) {
            return new VerificationResult(
                VerificationResult::FAILED,
                'example.noop_unexpected_effect',
                'The no-op action returned an unexpected execution result.'
            );
        }

        return new VerificationResult(
            VerificationResult::PASSED,
            'example.noop_verified',
            'The example action completed without reporting site modifications.',
            array('modified' => false)
        );
    }
}
