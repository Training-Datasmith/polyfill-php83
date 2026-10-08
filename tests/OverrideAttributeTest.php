<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Polyfill\Php83\Tests;

use PHPUnit\Framework\TestCase;

class OverrideAttributeTest extends TestCase
{
    public function testOverrideAttributeTargetsMethods()
    {
        if (\PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('PHP 8+ required for attribute syntax.');
        }

        require __DIR__.'/fixtures/override_user.php';

        $reflection = new \ReflectionClass(\Override::class);
        $this->assertTrue($reflection->isFinal());
        $attributes = $reflection->getAttributes(\Attribute::class);
        $this->assertCount(1, $attributes);
        $instance = $attributes[0]->newInstance();
        $this->assertSame(\Attribute::TARGET_METHOD, $instance->flags);

        $child = new \OverrideChild();
        $this->assertSame('child-marker', $child->overridden());
    }
}
