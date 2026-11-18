<?php

namespace MWS\WPFixToolkit\Domain\Actions;

final class ActionReversibility
{
    const REVERSIBLE = 'reversible';
    const PARTIALLY_REVERSIBLE = 'partially_reversible';
    const IRREVERSIBLE = 'irreversible';
    const NOT_APPLICABLE = 'not_applicable';

    public static function all()
    {
        return array(
            self::REVERSIBLE,
            self::PARTIALLY_REVERSIBLE,
            self::IRREVERSIBLE,
            self::NOT_APPLICABLE
        );
    }
}
