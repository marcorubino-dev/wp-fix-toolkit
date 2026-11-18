<?php

namespace MWS\WPFixToolkit\Tests\Unit;

use MWS\WPFixToolkit\Infrastructure\WordPress\WordPressCapabilityChecker;
use PHPUnit\Framework\TestCase;

final class WordPressCapabilityCheckerTest extends TestCase
{
    public function test_it_accepts_only_when_the_current_user_has_every_capability()
    {
        $checked = array();
        $checker = new WordPressCapabilityChecker(
            function () { return 12; },
            function ($capability) use (&$checked) {
                $checked[] = $capability;
                return in_array($capability, array('manage_options', 'edit_posts'), true);
            }
        );

        $this->assertTrue($checker->has_all(12, array('manage_options', 'edit_posts')));
        $this->assertSame(array('manage_options', 'edit_posts'), $checked);
    }

    public function test_it_rejects_a_different_user_without_checking_capabilities()
    {
        $capability_checks = 0;
        $checker = new WordPressCapabilityChecker(
            function () { return 12; },
            function () use (&$capability_checks) {
                $capability_checks++;
                return true;
            }
        );

        $this->assertFalse($checker->has_all(13, array('manage_options')));
        $this->assertSame(0, $capability_checks);
    }

    public function test_it_rejects_when_any_required_capability_is_missing()
    {
        $checker = new WordPressCapabilityChecker(
            function () { return 12; },
            function ($capability) { return $capability === 'manage_options'; }
        );

        $this->assertFalse($checker->has_all(12, array('manage_options', 'edit_users')));
    }

    public function test_it_fails_closed_when_wordpress_user_context_is_unavailable()
    {
        $checker = new WordPressCapabilityChecker(
            function () { return 0; },
            function () { return true; }
        );

        $this->assertFalse($checker->has_all(12, array('manage_options')));
    }

    public function test_it_fails_closed_when_a_capability_check_throws()
    {
        $checker = new WordPressCapabilityChecker(
            function () { return 12; },
            function () { throw new \RuntimeException('Capability API unavailable.'); }
        );

        $this->assertFalse($checker->has_all(12, array('manage_options')));
    }

    public function test_it_rejects_invalid_capability_values()
    {
        $checker = new WordPressCapabilityChecker(
            function () { return 12; },
            function () { return true; }
        );

        $this->assertFalse($checker->has_all(12, array('manage_options', '')));
    }
}
