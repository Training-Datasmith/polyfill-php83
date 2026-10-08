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
 * Only cases where the current polyfill matches PHP 8.3 native behavior.
 * Mismatches are documented in suite-results (DEFERRED); fix C is out of scope.
 */
class StrIncrementDecrementTest extends TestCase
{
    /**
     * @dataProvider incrementProvider
     */
    public function testIncrement(string $expected, string $input)
    {
        $this->assertSame($expected, \str_increment($input));
    }

    public static function incrementProvider(): array
    {
        return [
            ['ABD', 'ABC'],
            ['EB', 'EA'],
            ['AAA', 'ZZ'],
            ['Ba', 'Az'],
            ['bA', 'aZ'],
            ['B0', 'A9'],
            ['b0', 'a9'],
            ['AAa', 'Zz'],
            ['aaA', 'zZ'],
            ['10a', '9z'],
            ['10A', '9Z'],
            ['5e7', '5e6'],
            ['e', 'd'],
            ['E', 'D'],
            ['5', '4'],
            ['10', '9'],
            ['100', '99'],
            ['1000', '999'],
        ];
    }

    /**
     * @dataProvider decrementProvider
     */
    public function testDecrement(string $expected, string $input)
    {
        $this->assertSame($expected, \str_decrement($input));
    }

    public static function decrementProvider(): array
    {
        return [
            ['Ay', 'Az'],
            ['aY', 'aZ'],
            ['A8', 'A9'],
            ['a8', 'a9'],
            ['Yz', 'Za'],
            ['yZ', 'zA'],
            ['Y9', 'Z0'],
            ['y9', 'z0'],
            ['Z', 'aA'],
            ['9', 'A0'],
            ['9', 'a0'],
            ['9', '10'],
            ['Z', '1A'],
            ['z', '1a'],
            ['9z', '10a'],
            ['5e5', '5e6'],
            ['C', 'D'],
            ['c', 'd'],
            ['3', '4'],
            ['99', '100'],
            ['Az9', 'Ba0'],
            ['0', '1'],
        ];
    }

    /**
     * @dataProvider invalidIncrementProvider
     */
    public function testIncrementRejects(string $messagePart, string $input)
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage($messagePart);

        \str_increment($input);
    }

    public static function invalidIncrementProvider(): array
    {
        return [
            ['cannot be empty', ''],
            ['must be composed only of alphanumeric ASCII characters', '-cc'],
            ['must be composed only of alphanumeric ASCII characters', 'Z '],
            ['must be composed only of alphanumeric ASCII characters', 'é'],
            ['must be composed only of alphanumeric ASCII characters', 'foo1.txt'],
        ];
    }

    /**
     * @dataProvider invalidDecrementProvider
     */
    public function testDecrementRejects(string $messagePart, string $input)
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage($messagePart);

        \str_decrement($input);
    }

    public static function invalidDecrementProvider(): array
    {
        return [
            ['cannot be empty', ''],
            ['must be composed only of alphanumeric ASCII characters', 'é'],
            ['is out of decrement range', '0'],
            ['is out of decrement range', 'a'],
            ['is out of decrement range', 'A'],
            ['is out of decrement range', '00'],
            ['is out of decrement range', '0a'],
            ['is out of decrement range', '0A'],
        ];
    }
}
