<?php

namespace MWS\\WPFixToolkit\\Infrastructure\\WordPress;

use InvalidArgumentException;
use Throwable;
use MWS\\WPFixToolkit\\Domain\\Contracts\\CapabilityCheckerInterface;

/**
 * Checks capabilities for the currently authenticated WordPress user.
 *
 * The requested user ID must match WordPress's current user. This prevents a
 * caller from asking the adapter to evaluate another user's permissions.
 */
final class WordPressCapabilityChecker implements CapabilityCheckerInterface
{
    private $current_user_id_resolver;
    private $current_user_capability_checker;

    /**
     * Optional callables make the adapter testable without bootstrapping WordPress.
     */
    public function __construct($current_user_id_resolver = null, $current_user_capability_checker = null)
    {
        if ($current_user_id_resolver !== null && !is_callable($current_user_id_resolver)) {
            throw new InvalidArgumentException('Current user ID resolver must be callable.');
        }

        if ($current_user_capability_checker !== null && !is_callable($current_user_capability_checker)) {
            throw new InvalidArgumentException('Current user capability checker must be callable.');
        }

        $this->current_user_id_resolver = $current_user_id_resolver ?: function () {
            return function_exists('get_current_user_id') ? \\get_current_user_id() : 0;
        };

        $this->current_user_capability_checker = $current_user_capability_checker ?: function ($capability) {
            return function_exists('current_user_can') && \\current_user_can($capability);
        };
    }

    public function has_all($user_id, array $capabilities): bool
    {
        if (!is_int($user_id) || $user_id < 1) {
            return false;
        }

        try {
            $current_user_id = call_user_func($this->current_user_id_resolver);
        } catch (Throwable $exception) {
            return false;
        }

        if (!is_int($current_user_id) || $current_user_id !== $user_id) {
            return false;
        }

        foreach ($capabilities as $capability) {
            if (!is_string($capability) || $capability === '') {
                return false;
            }

            try {
                if (!call_user_func($this->current_user_capability_checker, $capability)) {
                    return false;
                }
            } catch (Throwable $exception) {
                return false;
            }
        }

        return true;
    }
}
