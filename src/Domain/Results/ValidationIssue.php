<?php

namespace MWS\WPFixToolkit\Domain\Results;

use InvalidArgumentException;

final class ValidationIssue
{
    private $code;
    private $message;
    private $field;

    public function __construct($code, $message, $field = null)
    {
        if (!is_string($code) || trim($code) === '') {
            throw new InvalidArgumentException('Validation issue code cannot be empty.');
        }
        if (!is_string($message) || trim($message) === '') {
            throw new InvalidArgumentException('Validation issue message cannot be empty.');
        }
        if ($field !== null && (!is_string($field) || trim($field) === '')) {
            throw new InvalidArgumentException('Validation issue field must be a non-empty string or null.');
        }
        $this->code = $code;
        $this->message = $message;
        $this->field = $field;
    }

    public function get_code() { return $this->code; }
    public function get_message() { return $this->message; }
    public function get_field() { return $this->field; }
}
