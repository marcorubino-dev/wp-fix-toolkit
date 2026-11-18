<?php

namespace MWS\WPFixToolkit\Domain\Results;

use InvalidArgumentException;

final class ValidationResult
{
    const VALID = 'valid';
    const INVALID = 'invalid';
    const BLOCKED = 'blocked';

    private $status;
    private $issues;

    public function __construct($status, array $issues = array())
    {
        if (!in_array($status, array(self::VALID, self::INVALID, self::BLOCKED), true)) {
            throw new InvalidArgumentException('Unsupported validation status.');
        }
        foreach ($issues as $issue) {
            if (!$issue instanceof ValidationIssue) {
                throw new InvalidArgumentException('Validation issues must be ValidationIssue objects.');
            }
        }
        if ($status === self::VALID && count($issues) > 0) {
            throw new InvalidArgumentException('A valid result cannot contain validation issues.');
        }
        $this->status = $status;
        $this->issues = array_values($issues);
    }

    public static function valid() { return new self(self::VALID); }
    public static function invalid(array $issues) { return new self(self::INVALID, $issues); }
    public static function blocked(array $issues) { return new self(self::BLOCKED, $issues); }
    public function get_status() { return $this->status; }
    public function is_valid() { return $this->status === self::VALID; }
    public function get_issues() { return $this->issues; }
}
