<?php

namespace MWS\WPFixToolkit\Domain\Contracts;

use MWS\WPFixToolkit\Domain\Approval\ExecutionApproval;
use MWS\WPFixToolkit\Domain\Context\ActionContext;

interface ExecutionApprovalStoreInterface
{
    /**
     * Atomically validates and consumes an approval for one exact operation.
     *
     * Durable implementations must make the consume operation atomic across
     * concurrent requests. A successful approval must never be consumed twice.
     */
    public function consume(
        ExecutionApproval $approval,
        ActionContext $context,
        int $current_time
    ): bool;
}
