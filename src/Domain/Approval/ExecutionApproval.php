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
    private $approved_by;
    private $approved_at;

    public function __construct($execution_uuid, $action_id, $action_version, $approved_by, $approved_at)
    {
        if (!is_string($execution_uuid) || $execution_uuid === ''
            || !is_string($action_id) || $action_id === ''
            || !is_string($action_version) || $action_version === ''
            || !is_int($approved_by) || $approved_by < 1
            || !is_int($approved_at) || $approved_at < 1) {
            throw new InvalidArgumentException('Approval identity and timestamp values are required.');
        }

        $this->execution_uuid = $execution_uuid;
        $this->action_id = $action_id;
        $this->action_version = $action_version;
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
            && $context->get_action_version() === $this->action_version;
    }

    public function get_approved_by() { return $this->approved_by; }
    public function get_approved_at() { return $this->approved_at; }
}
