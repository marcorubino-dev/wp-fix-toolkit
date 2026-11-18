<?php

namespace MWS\WPFixToolkit\Infrastructure\Clock;

use MWS\WPFixToolkit\Domain\Contracts\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): int
    {
        return time();
    }
}
