<?php

namespace MWS\WPFixToolkit\Domain\Approval;

use InvalidArgumentException;
use MWS\WPFixToolkit\Domain\Context\ActionContext;

final class ExecutionApproval
{
    const VALIDITY_SECONDS = 900;

    private $execution_uuid;
    private $action_id;
    private $action_version;
    private $operation_fingerprint;
    private $approved_by;
    private $approved_at;

    public function __construct(
        $execution_uuid,
        $action_id,
        $action_version,
        $operation_fingerprint,
        $approved_by,
        $approved_at
    ) {
        if (!is_string($execution_uuid) || $execution_uuid === ''
            || !is_string($action_id) || $action_id === ''
            || !is_string($action_version) || $action_version === ''
            || !is_string($operation_fingerprint)
            || !preg_match('/^[a-f0-9]{64}$/i', $operation_fingerprint)
            || !is_int($approved_by) || $approved_by < 1
            || !is_int($approved_at) || $approved_at < 1) {
            throw new InvalidArgumentException('Approval identity, operation fingerprint and timestamp values are required.');
        }

        $this->execution_uuid = $execution_uuid;
        $this->action_id = $action_id;
        $this->action_version = $action_version;
        $this->operation_fingerprint = strtolower($operation_fingerprint);
        $this->approved_by = $approved_by;
        $this->approved_at = $approved_at;
    }

    public function is_valid_for(ActionContext $context, $current_time)
    {
        return is_int($current_time)
            && $current_time >= $this->approved_at
            && $current_time < ($this->approved_at + self::VALIDITY_SECONDS)
            && $context->get_execution_uuid() === $this->execution_uuid
            && $context->get_action_id() === $this->action_id
            && $context->get_action_version() === $this->action_version
            && hash_equals($this->operation_fingerprint, $context->get_operation_fingerprint());
    }

    public function get_execution_uuid() { return $this->execution_uuid; }
    public function get_action_id() { return $this->action_id; }
    public function get_action_version() { return $this->action_version; }
    public function get_operation_fingerprint() { return $this->operation_fingerprint; }
    public function get_approved_by() { return $this->approved_by; }
    public function get_approved_at() { return $this->approved_at; }
}
