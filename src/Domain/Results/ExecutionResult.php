<?php

namespace MWS\WPFixToolkit\Domain\Results;

use InvalidArgumentException;

final class ExecutionResult
{
    const SUCCEEDED = 'succeeded';
    const FAILED = 'failed';
    const PARTIAL = 'partial';
    const UNKNOWN = 'unknown';

    private $status;
    private $code;
    private $message;
    private $metadata;
    private $modified;

    public function __construct($status, $code, $message, array $metadata = array(), $modified = false)
    {
        if (!in_array($status, array(self::SUCCEEDED, self::FAILED, self::PARTIAL, self::UNKNOWN), true)) {
            throw new InvalidArgumentException('Unsupported execution status.');
        }
        if (!is_string($code) || trim($code) === '' || !is_string($message) || trim($message) === '') {
            throw new InvalidArgumentException('Execution code and message are required.');
        }
        if (!is_bool($modified)) {
            throw new InvalidArgumentException('Modified flag must be boolean.');
        }
        $this->status = $status;
        $this->code = $code;
        $this->message = $message;
        $this->metadata = $metadata;
        $this->modified = $modified;
    }

    public function get_status() { return $this->status; }
    public function get_code() { return $this->code; }
    public function get_message() { return $this->message; }
    public function get_metadata() { return $this->metadata; }
    public function has_modified_site() { return $this->modified; }
}
