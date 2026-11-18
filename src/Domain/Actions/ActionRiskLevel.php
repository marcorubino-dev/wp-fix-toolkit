<?php

namespace MWS\WPFixToolkit\Domain\Actions;

final class ActionRiskLevel
{
    const LOW = 'low';
    const MEDIUM = 'medium';
    const HIGH = 'high';
    const CRITICAL = 'critical';

    public static function all()
    {
        return array(self::LOW, self::MEDIUM, self::HIGH, self::CRITICAL);
    }
}
