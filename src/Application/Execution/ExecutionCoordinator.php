<?php

namespace MWS\WPFixToolkit\Application\Execution;

use Throwable;
use MWS\WPFixToolkit\Domain\Approval\ExecutionApproval;
use MWS\WPFixToolkit\Domain\Context\ActionContext;
use MWS\WPFixToolkit\Domain\Contracts\ActionRegistryInterface;
use MWS\WPFixToolkit\Domain\Contracts\CapabilityCheckerInterface;
use MWS\WPFixToolkit\Domain\Contracts\ClockInterface;
use MWS\WPFixToolkit\Domain\Contracts\ExecutionApprovalStoreInterface;
use MWS\WPFixToolkit\Domain\Results\ExecutionOutcome;
use MWS\WPFixToolkit\Domain\Results\ExecutionResult;
use MWS\WPFixToolkit\Domain\Results\ValidationResult;
use MWS\WPFixToolkit\Domain\Results\VerificationResult;

final class ExecutionCoordinator
{
    private $registry;
    private $capabilities;
    private $clock;
    private $approval_store;

    public function __construct(
        ActionRegistryInterface $registry,
        CapabilityCheckerInterface $capabilities,
        ClockInterface $clock,
        ExecutionApprovalStoreInterface $approval_store
    ) {
        $this->registry = $registry;
        $this->capabilities = $capabilities;
        $this->clock = $clock;
        $this->approval_store = $approval_store;
    }

    public function execute(ActionContext $context, ExecutionApproval $approval): ExecutionOutcome
    {
        try {
            $action = $this->registry->get($context->get_action_id(), $context->get_action_version());
        } catch (\InvalidArgumentException $exception) {
            return $this->rejected('action_not_registered', 'The requested action version is not registered.');
        }

        $definition = $action->get_definition();
        if ($definition->get_id() !== $context->get_action_id()
            || $definition->get_version() !== $context->get_action_version()) {
            return $this->rejected('action_identity_mismatch', 'The registered action identity does not match the execution context.');
        }

        $validation = $action->validate($context);
        if (!$validation->is_valid()) {
            return new ExecutionOutcome(ExecutionOutcome::REJECTED, 'validation_rejected', 'Action validation did not pass; execution was not started.', $validation);
        }

        if (!$this->capabilities->has_all($context->get_requested_by(), $definition->get_required_capabilities())) {
            return new ExecutionOutcome(ExecutionOutcome::REJECTED, 'capability_denied', 'The requesting user does not have all required capabilities.', $validation);
        }

        if (!$this->approval_store->consume($approval, $context, $this->clock->now())) {
            return new ExecutionOutcome(ExecutionOutcome::REJECTED, 'approval_unavailable', 'The approval is invalid, expired, belongs to another operation, or has already been consumed.', $validation);
        }

        try {
            $execution = $action->execute($context);
        } catch (Throwable $exception) {
            $execution = new ExecutionResult(
                ExecutionResult::UNKNOWN,
                'execution.exception',
                'Execution raised an exception; the resulting site state requires reconciliation.',
                array('exception' => get_class($exception)),
                true
            );

            return new ExecutionOutcome(
                ExecutionOutcome::UNKNOWN,
                'execution_outcome_unknown',
                'Execution could not be confirmed. It must not be retried automatically.',
                $validation,
                $execution
            );
        }

        try {
            $verification = $action->verify($context, $execution);
        } catch (Throwable $exception) {
            $verification = new VerificationResult(
                VerificationResult::INCONCLUSIVE,
                'verification.exception',
                'Verification raised an exception and could not establish the resulting state.',
                array('exception' => get_class($exception))
            );
        }

        return new ExecutionOutcome(
            $this->resolve_outcome_status($execution, $verification),
            'execution_processed',
            'The action execution and verification stages have finished.',
            $validation,
            $execution,
            $verification
        );
    }

    private function resolve_outcome_status(ExecutionResult $execution, VerificationResult $verification)
    {
        if ($execution->get_status() === ExecutionResult::UNKNOWN
            || $verification->get_status() === VerificationResult::INCONCLUSIVE) {
            return ExecutionOutcome::UNKNOWN;
        }

        if ($execution->get_status() === ExecutionResult::FAILED
            || $verification->get_status() === VerificationResult::FAILED) {
            return ExecutionOutcome::FAILED;
        }

        if ($execution->get_status() === ExecutionResult::PARTIAL) {
            return ExecutionOutcome::PARTIAL;
        }

        return ExecutionOutcome::SUCCEEDED;
    }

    private function rejected($code, $message)
    {
        return new ExecutionOutcome(ExecutionOutcome::REJECTED, $code, $message, ValidationResult::blocked(array()));
    }
}
