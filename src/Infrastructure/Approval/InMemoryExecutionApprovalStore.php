<?php

namespace MWS\WPFixToolkit\Infrastructure\Approval;

use MWS\WPFixToolkit\Domain\Approval\ExecutionApproval;
use MWS\WPFixToolkit\Domain\Context\ActionContext;
use MWS\WPFixToolkit\Domain\Contracts\ExecutionApprovalStoreInterface;

/**
 * Process-local approval store for tests and non-persistent integrations.
 *
 * This implementation is not durable and does not coordinate multiple PHP
 * workers. Production integrations must provide an atomic persistent store.
 */
final class InMemoryExecutionApprovalStore implements ExecutionApprovalStoreInterface
{
    private $consumed = array();

    public function consume(
        ExecutionApproval $approval,
        ActionContext $context,
        int $current_time
    ): bool {
        if (!$approval->is_valid_for($context, $current_time)) {
            return false;
        }

        $key = hash('sha256', serialize(array(
            $approval->get_execution_uuid(),
            $approval->get_action_id(),
            $approval->get_action_version(),
            $approval->get_operation_fingerprint(),
            $approval->get_approved_by(),
            $approval->get_approved_at(),
        )));

        if (isset($this->consumed[$key])) {
            return false;
        }

        $this->consumed[$key] = true;

        return true;
    }
}
