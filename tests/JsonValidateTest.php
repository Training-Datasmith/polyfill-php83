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

class JsonValidateTest extends TestCase
{
    /**
     * @dataProvider validJsonProvider
     */
    public function testAcceptsValidDocuments(string $json)
    {
        $this->assertTrue(\json_validate($json));
        $this->assertSame(\JSON_ERROR_NONE, \json_last_error());
    }

    public static function validJsonProvider(): array
    {
        return [
            ['{}'],
            ['[]'],
            ['null'],
            ['true'],
            ['false'],
            ['0'],
            ['"a"'],
            ['{ "test": { "foo": "bar" } }'],
            ['{ "": { "": "" } }'],
            ['{ "a": 1, "a": 2 }'],
            ['{ "test": {"foo": "bar"}, "test2": {"foo" : "bar" }, "test3": {"foo" : "bar" } }'],
        ];
    }

    public function testAcceptsNulKeyWhenDecodedAsArray()
    {
        $this->assertTrue(\json_validate('{ "\u0000null": "test" }'));
        $this->assertSame(\JSON_ERROR_NONE, \json_last_error());
    }

    /**
     * @dataProvider invalidJsonProvider
     */
    public function testRejectsInvalidDocuments(string $json, int $expectedError, string $messagePrefix, int $depth = 512)
    {
        $this->assertFalse(\json_validate($json, $depth));
        $this->assertSame($expectedError, \json_last_error());
        $this->assertStringStartsWith($messagePrefix, $this->normalizeJsonErrorMessage(\json_last_error_msg()));
    }

    public static function invalidJsonProvider(): array
    {
        return [
            ['', \JSON_ERROR_SYNTAX, 'Syntax error'],
            ['.', \JSON_ERROR_SYNTAX, 'Syntax error'],
            [' ', \JSON_ERROR_SYNTAX, 'Syntax error'],
            [';', \JSON_ERROR_SYNTAX, 'Syntax error'],
            ['blah', \JSON_ERROR_SYNTAX, 'Syntax error'],
            ['{ "": "": "" } }', \JSON_ERROR_SYNTAX, 'Syntax error'],
            ['{"a":{"b":1}}', \JSON_ERROR_DEPTH, 'Maximum stack depth exceeded', 1],
            ["\"a\xb0b\"", \JSON_ERROR_UTF8, 'Malformed UTF-8'],
        ];
    }

    public function testInvalidUtf8IgnoredWithFlag()
    {
        if (!\defined('JSON_INVALID_UTF8_IGNORE')) {
            $this->markTestSkipped('JSON_INVALID_UTF8_IGNORE is not defined.');
        }

        $this->assertTrue(\json_validate("\"a\xb0b\"", 512, \JSON_INVALID_UTF8_IGNORE));
        $this->assertSame(\JSON_ERROR_NONE, \json_last_error());
    }

    public function testDepth2147483647IsAccepted()
    {
        $this->assertTrue(\json_validate('{}', 2147483647));
        $this->assertSame(\JSON_ERROR_NONE, \json_last_error());
    }

    public function testDepthAboveIntMaxThrows()
    {
        if (\PHP_INT_MAX <= 2147483647) {
            $this->markTestSkipped('Requires 64-bit PHP_INT_MAX.');
        }

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('json_validate(): Argument #2 ($depth) must be less than 2147483647');

        \json_validate('{}', 2147483648);
    }

    public function testDepthZeroThrowsForNonEmptyJson()
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #2 ($depth) must be greater than 0');

        \json_validate('{}', 0);
    }

    public function testInvalidFlagThrows()
    {
        if (!\defined('JSON_BIGINT_AS_STRING')) {
            $this->markTestSkipped('JSON_BIGINT_AS_STRING is not defined.');
        }

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #3 ($flags) must be a valid flag');

        \json_validate('{}', 512, \JSON_BIGINT_AS_STRING);
    }

    /** Guard for production fix A. */
    public function testEmptyStringWithInvalidDepthDoesNotThrow()
    {
        $this->assertFalse(\json_validate('', 0));
        $this->assertSame(\JSON_ERROR_SYNTAX, \json_last_error());
    }

    /** Guard for production fix A. */
    public function testInvalidDepthClearsLastError()
    {
        \json_decode('{');
        $this->assertSame(\JSON_ERROR_SYNTAX, \json_last_error());

        try {
            \json_validate('{}', 0);
            $this->fail('Expected ValueError.');
        } catch (\ValueError $e) {
            $this->assertStringContainsString('Argument #2 ($depth)', $e->getMessage());
        }

        $this->assertSame(\JSON_ERROR_NONE, \json_last_error());
    }

    public function testInvalidFlagDoesNotClearLastError()
    {
        if (!\defined('JSON_BIGINT_AS_STRING')) {
            $this->markTestSkipped('JSON_BIGINT_AS_STRING is not defined.');
        }

        \json_decode('{');
        $this->assertSame(\JSON_ERROR_SYNTAX, \json_last_error());

        try {
            \json_validate('{}', 512, \JSON_BIGINT_AS_STRING);
            $this->fail('Expected ValueError.');
        } catch (\ValueError $e) {
            $this->assertStringContainsString('Argument #3 ($flags)', $e->getMessage());
        }

        $this->assertSame(\JSON_ERROR_SYNTAX, \json_last_error());
    }

    public function testEmptyStringWithInvalidFlagStillThrows()
    {
        if (!\defined('JSON_BIGINT_AS_STRING')) {
            $this->markTestSkipped('JSON_BIGINT_AS_STRING is not defined.');
        }

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #3 ($flags) must be a valid flag');

        \json_validate('', 512, \JSON_BIGINT_AS_STRING);
    }

    private function normalizeJsonErrorMessage(string $message): string
    {
        return preg_replace('/ near location \d+:\d+/', '', $message);
    }
}
