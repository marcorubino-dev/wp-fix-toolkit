<?php

namespace MWS\WPFixToolkit\Tests\Unit;

use InvalidArgumentException;
use MWS\WPFixToolkit\Domain\Actions\ActionDefinition;
use MWS\WPFixToolkit\Domain\Actions\ActionRiskLevel;
use MWS\WPFixToolkit\Domain\Actions\ActionReversibility;
use PHPUnit\Framework\TestCase;

final class ActionDefinitionTest extends TestCase
{
    public function test_it_exposes_validated_definition_values()
    {
        $definition = new ActionDefinition(
            'mws/cache/clear',
            '1.2.0',
            'Clear cache',
            'Clears a selected cache.',
            ActionRiskLevel::LOW,
            array('manage_options', 'manage_options'),
            ActionReversibility::REVERSIBLE
        );

        $this->assertSame('mws/cache/clear', $definition->get_id());
        $this->assertSame('1.2.0', $definition->get_version());
        $this->assertSame(array('manage_options'), $definition->get_required_capabilities());
    }

    public function test_it_rejects_non_namespaced_ids()
    {
        $this->expectException(InvalidArgumentException::class);
        new ActionDefinition('clear-cache', '1.0.0', 'Clear', 'Description', ActionRiskLevel::LOW, array(), ActionReversibility::REVERSIBLE);
    }
}
