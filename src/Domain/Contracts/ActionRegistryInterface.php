<?php

namespace MWS\WPFixToolkit\Domain\Contracts;

interface ActionRegistryInterface
{
    public function register(FixActionInterface $action);

    public function has($action_id, $version);

    public function get($action_id, $version);
}
