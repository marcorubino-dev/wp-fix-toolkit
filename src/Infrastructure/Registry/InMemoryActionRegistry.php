<?php

namespace MWS\WPFixToolkit\Infrastructure\Registry;

use InvalidArgumentException;
use LogicException;
use MWS\WPFixToolkit\Domain\Contracts\ActionRegistryInterface;
use MWS\WPFixToolkit\Domain\Contracts\FixActionInterface;

final class InMemoryActionRegistry implements ActionRegistryInterface
{
    private $actions = array();

    public function register(FixActionInterface $action)
    {
        $definition = $action->get_definition();
        $id = $definition->get_id();
        $version = $definition->get_version();

        if ($this->has($id, $version)) {
            throw new LogicException(sprintf('Action "%s" version "%s" is already registered.', $id, $version));
        }

        if (!isset($this->actions[$id])) {
            $this->actions[$id] = array();
        }
        $this->actions[$id][$version] = $action;
    }

    public function has($action_id, $version)
    {
        return isset($this->actions[$action_id]) && isset($this->actions[$action_id][$version]);
    }

    public function get($action_id, $version)
    {
        if (!is_string($action_id) || !is_string($version)) {
            throw new InvalidArgumentException('Action ID and version must be strings.');
        }
        if (!$this->has($action_id, $version)) {
            throw new InvalidArgumentException(sprintf('Action "%s" version "%s" is not registered.', $action_id, $version));
        }
        return $this->actions[$action_id][$version];
    }
}
