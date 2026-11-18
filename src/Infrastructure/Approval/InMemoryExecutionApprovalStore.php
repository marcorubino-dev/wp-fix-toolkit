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

        // Consume the operation identity, not an individual approval instance.
        // Issuing a replacement approval must not make the operation executable twice.
        $key = $approval->get_operation_fingerprint();

        if (isset($this->consumed[$key])) {
            return false;
        }

        $this->consumed[$key] = true;

        return true;
    }
}
