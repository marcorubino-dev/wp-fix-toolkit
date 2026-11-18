<?php

namespace MWS\WPFixToolkit\Domain\Contracts;

use MWS\WPFixToolkit\Domain\Actions\ActionDefinition;
use MWS\WPFixToolkit\Domain\Context\ActionContext;
use MWS\WPFixToolkit\Domain\Results\ExecutionResult;
use MWS\WPFixToolkit\Domain\Results\ValidationResult;
use MWS\WPFixToolkit\Domain\Results\VerificationResult;

interface FixActionInterface
{
    public function get_definition(): ActionDefinition;

    public function validate(ActionContext $context): ValidationResult;

    public function execute(ActionContext $context): ExecutionResult;

    public function verify(
        ActionContext $context,
        ExecutionResult $result
    ): VerificationResult;
}
