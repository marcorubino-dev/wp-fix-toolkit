<?php

namespace MWS\WPFixToolkit\Tests\Unit;

use MWS\WPFixToolkit\Application\Execution\ExecutionCoordinator;
use MWS\WPFixToolkit\Domain\Actions\ActionDefinition;
use MWS\WPFixToolkit\Domain\Actions\ActionRiskLevel;
use MWS\WPFixToolkit\Domain\Actions\ActionReversibility;
use MWS\WPFixToolkit\Domain\Approval\ExecutionApproval;
use MWS\WPFixToolkit\Domain\Context\ActionContext;
use MWS\WPFixToolkit\Domain\Contracts\CapabilityCheckerInterface;
use MWS\WPFixToolkit\Domain\Contracts\ClockInterface;
use MWS\WPFixToolkit\Domain\Contracts\FixActionInterface;
use MWS\WPFixToolkit\Domain\Results\ExecutionOutcome;
use MWS\WPFixToolkit\Domain\Results\ExecutionResult;
use MWS\WPFixToolkit\Domain\Results\ValidationResult;
use MWS\WPFixToolkit\Domain\Results\VerificationResult;
use MWS\WPFixToolkit\Infrastructure\Approval\InMemoryExecutionApprovalStore;
use MWS\WPFixToolkit\Infrastructure\Registry\InMemoryActionRegistry;
use PHPUnit\Framework\TestCase;

final class ExecutionCoordinatorTest extends TestCase
{
    private function context($parameters = array(), $requested_by = 7)
    {
        return new ActionContext(
            '123e4567-e89b-12d3-a456-426614174000',
            'mws-test/test-noop',
            '1.0.0',
            $requested_by,
            $parameters,
            1700000000
        );
    }

    private function action($validation = null, $execution = null, $verification = null)
    {
        return new CoordinatorTestAction(
            $validation ?: ValidationResult::valid(),
            $execution ?: new ExecutionResult(ExecutionResult::SUCCEEDED, 'test.ok', 'Executed.', array(), false),
            $verification ?: new VerificationResult(VerificationResult::PASSED, 'test.verified', 'Verified.')
        );
    }

    private function coordinator($action, $allowed = true, $now = 1700000100, $store = null)
    {
        $registry = new InMemoryActionRegistry();
        $registry->register($action);

        $capabilities = $this->createMock(CapabilityCheckerInterface::class);
        $capabilities->method('has_all')->willReturn($allowed);
        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn($now);

        return new ExecutionCoordinator(
            $registry,
            $capabilities,
            $clock,
            $store ?: new InMemoryExecutionApprovalStore()
        );
    }

    private function approval($context = null, $approved_at = 1700000000)
    {
        $context = $context ?: $this->context();
        return new ExecutionApproval(
            $context->get_execution_uuid(),
            $context->get_action_id(),
            $context->get_action_version(),
            $context->get_operation_fingerprint(),
            9,
            $approved_at
        );
    }

    public function test_it_runs_validation_execution_and_verification_in_order()
    {
        $action = $this->action();
        $outcome = $this->coordinator($action)->execute($this->context(), $this->approval());

        $this->assertSame(ExecutionOutcome::COMPLETED, $outcome->get_status());
        $this->assertSame(1, $action->execution_calls);
        $this->assertSame(1, $action->verification_calls);
    }

    public function test_it_does_not_execute_when_validation_fails()
    {
        $action = $this->action(ValidationResult::blocked(array()));
        $outcome = $this->coordinator($action)->execute($this->context(), $this->approval());

        $this->assertSame(ExecutionOutcome::REJECTED, $outcome->get_status());
        $this->assertSame(0, $action->execution_calls);
    }

    public function test_it_does_not_execute_without_capabilities()
    {
        $action = $this->action();
        $outcome = $this->coordinator($action, false)->execute($this->context(), $this->approval());

        $this->assertSame('capability_denied', $outcome->get_code());
        $this->assertSame(0, $action->execution_calls);
    }

    public function test_it_rejects_expired_approval()
    {
        $action = $this->action();
        $outcome = $this->coordinator($action, true, 1700000900)->execute($this->context(), $this->approval());

        $this->assertSame('approval_unavailable', $outcome->get_code());
        $this->assertSame(0, $action->execution_calls);
    }

    public function test_it_rejects_approval_for_another_operation()
    {
        $action = $this->action();
        $wrong = new ExecutionApproval(
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'mws-test/test-noop',
            '1.0.0',
            $this->context()->get_operation_fingerprint(),
            9,
            1700000000
        );
        $outcome = $this->coordinator($action)->execute($this->context(), $wrong);

        $this->assertSame('approval_unavailable', $outcome->get_code());
        $this->assertSame(0, $action->execution_calls);
    }

    public function test_it_rejects_approval_when_operation_parameters_change()
    {
        $approved_context = $this->context(array('target' => 'cache-a'));
        $changed_context = $this->context(array('target' => 'cache-b'));
        $action = $this->action();

        $outcome = $this->coordinator($action)->execute($changed_context, $this->approval($approved_context));

        $this->assertSame('approval_unavailable', $outcome->get_code());
        $this->assertSame(0, $action->execution_calls);
    }

    public function test_it_does_not_allow_an_approval_to_be_consumed_twice()
    {
        $action = $this->action();
        $context = $this->context();
        $approval = $this->approval($context);
        $coordinator = $this->coordinator($action);

        $first = $coordinator->execute($context, $approval);
        $second = $coordinator->execute($context, $approval);

        $this->assertSame(ExecutionOutcome::COMPLETED, $first->get_status());
        $this->assertSame('approval_unavailable', $second->get_code());
        $this->assertSame(1, $action->execution_calls);
    }

    public function test_it_preserves_unknown_when_execution_throws_and_does_not_verify()
    {
        $action = $this->action();
        $action->throw_on_execute = true;
        $outcome = $this->coordinator($action)->execute($this->context(), $this->approval());

        $this->assertSame(ExecutionOutcome::UNKNOWN, $outcome->get_status());
        $this->assertSame(0, $action->verification_calls);
        $this->assertSame(1, $action->execution_calls);
    }
}

final class CoordinatorTestAction implements FixActionInterface
{
    public $execution_calls = 0;
    public $verification_calls = 0;
    public $throw_on_execute = false;
    private $validation;
    private $execution;
    private $verification;

    public function __construct($validation, $execution, $verification)
    {
        $this->validation = $validation;
        $this->execution = $execution;
        $this->verification = $verification;
    }

    public function get_definition(): ActionDefinition
    {
        return new ActionDefinition('mws-test/test-noop', '1.0.0', 'Test action', 'Test action.', ActionRiskLevel::LOW, array('manage_options'), ActionReversibility::REVERSIBLE);
    }

    public function validate(ActionContext $context): ValidationResult { return $this->validation; }

    public function execute(ActionContext $context): ExecutionResult
    {
        $this->execution_calls++;
        if ($this->throw_on_execute) {
            throw new \RuntimeException('Simulated interruption.');
        }
        return $this->execution;
    }

    public function verify(ActionContext $context, ExecutionResult $result): VerificationResult
    {
        $this->verification_calls++;
        return $this->verification;
    }
}
