<?php

namespace MWS\WPFixToolkit\Domain\Results;

final class ExecutionOutcome
{
    const REJECTED = 'rejected';
    const COMPLETED = 'completed';
    const UNKNOWN = 'unknown';

    private $status;
    private $code;
    private $message;
    private $validation;
    private $execution;
    private $verification;

    public function __construct($status, $code, $message, ValidationResult $validation, ExecutionResult $execution = null, VerificationResult $verification = null)
    {
        $this->status = $status;
        $this->code = $code;
        $this->message = $message;
        $this->validation = $validation;
        $this->execution = $execution;
        $this->verification = $verification;
    }

    public function get_status() { return $this->status; }
    public function get_code() { return $this->code; }
    public function get_message() { return $this->message; }
    public function get_validation() { return $this->validation; }
    public function get_execution() { return $this->execution; }
    public function get_verification() { return $this->verification; }
}
