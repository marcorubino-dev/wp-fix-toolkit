<?php

namespace MWS\WPFixToolkit\Domain\Contracts;

interface ClockInterface
{
    public function now(): int;
}
