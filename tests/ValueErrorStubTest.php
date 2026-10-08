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

/**
 * Guard for production fix B: ValueError stub on PHP 7.
 */
class ValueErrorStubTest extends TestCase
{
    public function testValueErrorIsCatchableFromJsonValidateOnPhp7()
    {
        if (\PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('ValueError stub is only required on PHP 7.');
        }

        try {
            \json_validate('{}', 0);
            $this->fail('Expected ValueError.');
        } catch (\ValueError $e) {
            $this->assertStringContainsString('Argument #2 ($depth)', $e->getMessage());
        }
    }
}
