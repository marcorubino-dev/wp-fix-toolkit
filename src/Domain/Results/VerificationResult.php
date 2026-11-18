<?php

namespace MWS\WPFixToolkit\Domain\Results;

use InvalidArgumentException;

final class VerificationResult
{
    const PASSED = 'passed';
    const FAILED = 'failed';
    const INCONCLUSIVE = 'inconclusive';

    private $status;
    private $code;
    private $message;
    private $evidence;

    public function __construct($status, $code, $message, array $evidence = array())
    {
        if (!in_array($status, array(self::PASSED, self::FAILED, self::INCONCLUSIVE), true)) {
            throw new InvalidArgumentException('Unsupported verification status.');
        }
        if (!is_string($code) || trim($code) === '' || !is_string($message) || trim($message) === '') {
            throw new InvalidArgumentException('Verification code and message are required.');
        }
        $this->status = $status;
        $this->code = $code;
        $this->message = $message;
        $this->evidence = $evidence;
    }

    public function get_status() { return $this->status; }
    public function get_code() { return $this->code; }
    public function get_message() { return $this->message; }
    public function get_evidence() { return $this->evidence; }
}
