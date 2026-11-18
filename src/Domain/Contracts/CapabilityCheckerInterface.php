<?php

namespace MWS\WPFixToolkit\Domain\Contracts;

interface CapabilityCheckerInterface
{
    /**
     * Return true only when the user has every requested capability.
     */
    public function has_all($user_id, array $capabilities): bool;
}
