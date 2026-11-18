<?php

namespace MWS\WPFixToolkit\Infrastructure\WordPress;

use InvalidArgumentException;
use RuntimeException;
use MWS\WPFixToolkit\Domain\Context\ActionContext;

/**
 * Creates immutable action contexts from the authenticated WordPress request.
 */
final class WordPressActionContextFactory
{
    private $current_user_id_resolver;
    private $uuid_generator;
    private $timestamp_provider;

    public function __construct($current_user_id_resolver = null, $uuid_generator = null, $timestamp_provider = null)
    {
        foreach (array($current_user_id_resolver, $uuid_generator, $timestamp_provider) as $callback) {
            if ($callback !== null && !is_callable($callback)) {
                throw new InvalidArgumentException('Context factory dependencies must be callable.');
            }
        }

        $this->current_user_id_resolver = $current_user_id_resolver ?: function () {
            return function_exists('get_current_user_id') ? \get_current_user_id() : 0;
        };

        $this->uuid_generator = $uuid_generator ?: function () {
            return function_exists('wp_generate_uuid4') ? \wp_generate_uuid4() : '';
        };

        $this->timestamp_provider = $timestamp_provider ?: function () {
            return time();
        };
    }

    public function create($action_id, $action_version, array $parameters)
    {
        $user_id = call_user_func($this->current_user_id_resolver);
        if (!is_int($user_id) || $user_id < 1) {
            throw new RuntimeException('An authenticated WordPress user is required.');
        }

        $execution_uuid = call_user_func($this->uuid_generator);
        if (!is_string($execution_uuid) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $execution_uuid)) {
            throw new RuntimeException('WordPress did not provide a valid execution UUID.');
        }

        $created_at = call_user_func($this->timestamp_provider);
        if (!is_int($created_at) || $created_at < 1) {
            throw new RuntimeException('The execution timestamp is invalid.');
        }

        return new ActionContext(
            $execution_uuid,
            $action_id,
            $action_version,
            $user_id,
            $parameters,
            $created_at
        );
    }
}
