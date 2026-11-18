<?php

namespace MWS\WPFixToolkit\Domain\Context;

use InvalidArgumentException;

final class ActionContext
{
    private $execution_uuid;
    private $action_id;
    private $action_version;
    private $requested_by;
    private $parameters;
    private $created_at;

    public function __construct(
        $execution_uuid,
        $action_id,
        $action_version,
        $requested_by,
        array $parameters,
        $created_at
    ) {
        if (!is_string($execution_uuid) || !preg_match('/^[0-9a-f-]{36}$/i', $execution_uuid)) {
            throw new InvalidArgumentException('Execution UUID must be a UUID.');
        }
        if (!is_string($action_id) || $action_id === '') {
            throw new InvalidArgumentException('Action ID cannot be empty.');
        }
        if (!is_string($action_version) || $action_version === '') {
            throw new InvalidArgumentException('Action version cannot be empty.');
        }
        if (!is_int($requested_by) || $requested_by < 1) {
            throw new InvalidArgumentException('Requested-by user ID must be a positive integer.');
        }
        if (!is_int($created_at) || $created_at < 1) {
            throw new InvalidArgumentException('Creation timestamp must be a positive Unix timestamp.');
        }

        $this->execution_uuid = $execution_uuid;
        $this->action_id = $action_id;
        $this->action_version = $action_version;
        $this->requested_by = $requested_by;
        $this->parameters = self::copy_array($parameters);
        $this->created_at = $created_at;
    }

    private static function copy_array(array $value)
    {
        $copy = array();
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $copy[$key] = self::copy_array($item);
            } elseif (is_object($item) || is_resource($item)) {
                throw new InvalidArgumentException('Action parameters must contain scalar values and arrays only.');
            } else {
                $copy[$key] = $item;
            }
        }
        return $copy;
    }

    private static function canonicalize(array $value)
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = self::canonicalize($item);
            }
        }

        // Preserve list order; sort associative keys for stable fingerprints.
        if ($value !== array_values($value)) {
            ksort($value);
        }

        return $value;
    }

    public function get_operation_fingerprint()
    {
        $operation = array(
            'execution_uuid' => $this->execution_uuid,
            'action_id' => $this->action_id,
            'action_version' => $this->action_version,
            'requested_by' => $this->requested_by,
            'parameters' => self::canonicalize($this->parameters),
            'created_at' => $this->created_at,
        );

        return hash('sha256', serialize($operation));
    }

    public function get_execution_uuid() { return $this->execution_uuid; }
    public function get_action_id() { return $this->action_id; }
    public function get_action_version() { return $this->action_version; }
    public function get_requested_by() { return $this->requested_by; }
    public function get_parameters() { return self::copy_array($this->parameters); }
    public function get_created_at() { return $this->created_at; }
}
