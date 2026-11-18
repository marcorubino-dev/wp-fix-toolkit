<?php

namespace MWS\WPFixToolkit\Domain\Actions;

use InvalidArgumentException;

final class ActionDefinition
{
    private $id;
    private $version;
    private $name;
    private $description;
    private $risk_level;
    private $required_capabilities;
    private $reversibility;

    public function __construct(
        $id,
        $version,
        $name,
        $description,
        $risk_level,
        array $required_capabilities,
        $reversibility
    ) {
        if (!is_string($id) || !preg_match('/^[a-z0-9][a-z0-9._-]*\/[a-z0-9][a-z0-9._-]*$/', $id)) {
            throw new InvalidArgumentException('Action ID must use a vendor/action namespace.');
        }
        if (!is_string($version) || !preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][a-zA-Z0-9.-]+)?$/', $version)) {
            throw new InvalidArgumentException('Action version must be a valid semantic version.');
        }
        if (!is_string($name) || trim($name) === '') {
            throw new InvalidArgumentException('Action name cannot be empty.');
        }
        if (!is_string($description) || trim($description) === '') {
            throw new InvalidArgumentException('Action description cannot be empty.');
        }
        if (!in_array($risk_level, ActionRiskLevel::all(), true)) {
            throw new InvalidArgumentException('Unsupported action risk level.');
        }
        if (!in_array($reversibility, ActionReversibility::all(), true)) {
            throw new InvalidArgumentException('Unsupported action reversibility value.');
        }

        foreach ($required_capabilities as $capability) {
            if (!is_string($capability) || trim($capability) === '') {
                throw new InvalidArgumentException('Capabilities must be non-empty strings.');
            }
        }

        $this->id = $id;
        $this->version = $version;
        $this->name = trim($name);
        $this->description = trim($description);
        $this->risk_level = $risk_level;
        $this->required_capabilities = array_values(array_unique($required_capabilities));
        $this->reversibility = $reversibility;
    }

    public function get_id() { return $this->id; }
    public function get_version() { return $this->version; }
    public function get_name() { return $this->name; }
    public function get_description() { return $this->description; }
    public function get_risk_level() { return $this->risk_level; }
    public function get_required_capabilities() { return $this->required_capabilities; }
    public function get_reversibility() { return $this->reversibility; }
}
