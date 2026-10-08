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

class MbStrPadTest extends TestCase
{
    protected function setUp(): void
    {
        if (!\function_exists('mb_str_pad')) {
            $this->markTestSkipped('mb_str_pad is not available.');
        }
    }

    /**
     * @dataProvider asciiPaddingProvider
     */
    public function testPadsAscii(string $expected, string $string, int $length, string $pad, int $padType)
    {
        $this->assertSame($expected, \mb_str_pad($string, $length, $pad, $padType));
    }

    public static function asciiPaddingProvider(): array
    {
        return [
            ['+Hello+', 'Hello', 7, '+-', \STR_PAD_BOTH],
            ['+-World+-+', 'World', 10, '+-', \STR_PAD_BOTH],
            ['+-Hello', 'Hello', 7, '+-', \STR_PAD_LEFT],
            ['Hello+-', 'Hello', 7, '+-', \STR_PAD_RIGHT],
            ['World+-+-+', 'World', 10, '+-', \STR_PAD_RIGHT],
            ['+Hello+-', 'Hello', 8, '+-', \STR_PAD_BOTH],
        ];
    }

    /**
     * @dataProvider shortLengthProvider
     */
    public function testShortAndNegativeLengthsReturnInput(string $string, int $length, int $padType)
    {
        $this->assertSame($string, \mb_str_pad($string, $length, ' ', $padType));
    }

    public static function shortLengthProvider(): array
    {
        return [
            ['▶▶', 2, \STR_PAD_BOTH],
            ['▶▶', 1, \STR_PAD_BOTH],
            ['▶▶', 0, \STR_PAD_BOTH],
            ['▶▶', -1, \STR_PAD_BOTH],
        ];
    }

    public function testEmptyStringLengthZeroReturnsEmpty()
    {
        $this->assertSame('', \mb_str_pad('', 0, ' ', \STR_PAD_BOTH));
    }

    public function testEmptyStringLengthOnePadsOnce()
    {
        $this->assertSame(' ', \mb_str_pad('', 1, ' ', \STR_PAD_BOTH));
    }

    /**
     * @dataProvider emojiPaddingProvider
     */
    public function testEmojiCyclesByCharacters(string $expected, string $string, int $length, int $padType)
    {
        $this->assertSame($expected, \mb_str_pad($string, $length, '❤❓❇', $padType));
    }

    public static function emojiPaddingProvider(): array
    {
        return [
            ['▶▶❤❓❇❤', '▶▶', 6, \STR_PAD_RIGHT],
            ['❤❓❇❤▶▶', '▶▶', 6, \STR_PAD_LEFT],
            ['❤❓▶▶❤❓', '▶▶', 6, \STR_PAD_BOTH],
            ['▶▶❤❓❇', '▶▶', 5, \STR_PAD_RIGHT],
            ['❤▶▶❤❓', '▶▶', 5, \STR_PAD_BOTH],
            ['▶▶❤', '▶▶', 3, \STR_PAD_BOTH],
        ];
    }

    public function testDefaultArguments()
    {
        $previous = \mb_internal_encoding();
        \mb_internal_encoding('UTF-8');

        try {
            $this->assertSame('▶▶  ', \mb_str_pad('▶▶', 4));
        } finally {
            \mb_internal_encoding($previous);
        }
    }

    /**
     * @dataProvider encodingRoundTripProvider
     */
    public function testEncodingRoundTrip(string $encoding, string $expectedUtf8, string $input, int $length, int $padType)
    {
        $pad = \mb_convert_encoding('▶', $encoding, 'UTF-8');
        $result = \mb_str_pad($input, $length, $pad, $padType, $encoding);
        $utf8 = \mb_convert_encoding($result, 'UTF-8', $encoding);
        $this->assertIsString($utf8);
        $this->assertSame($expectedUtf8, $utf8);
    }

    public static function encodingRoundTripProvider(): array
    {
        $string = 'Σὲ γνωρίζω ἀπὸ τὴν κόψη Зарегистрируйтесь';
        $cases = [];
        foreach (['UTF-8'] as $encoding) {
            $input = \mb_convert_encoding($string, $encoding, 'UTF-8');
            $cases[] = [$encoding, $string.'▶▶▶', $input, 44, \STR_PAD_RIGHT];
            $cases[] = [$encoding, '▶▶▶'.$string, $input, 44, \STR_PAD_LEFT];
            $cases[] = [$encoding, '▶'.$string.'▶▶', $input, 44, \STR_PAD_BOTH];
        }

        return $cases;
    }

    /** Guard for production fix D (early return before pad validation). */
    public function testNoPaddingSkipsPadValidation()
    {
        $this->assertSame('ab', \mb_str_pad('ab', 1, '', 99));
        $this->assertSame('ab', \mb_str_pad('ab', 2, '', 99));
        $this->assertSame('ab', \mb_str_pad('ab', 0, '', \STR_PAD_LEFT));
        $this->assertSame('ab', \mb_str_pad('ab', -1, '', \STR_PAD_LEFT));
        $this->assertSame('', \mb_str_pad('', 0, ''));
    }

    /** Guard for production fix D (encoding checked first). */
    public function testInvalidEncodingWinsOverPadType()
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #5 ($encoding) must be a valid encoding');

        \mb_str_pad('ab', 4, '', 99, 'no-such-encoding');
    }

    /** Guard for production fix D (encoding checked first when padding required). */
    public function testInvalidEncodingWinsWhenPaddingRequired()
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #5 ($encoding) must be a valid encoding');

        \mb_str_pad('ab', 1, '', 99, 'no-such-encoding');
    }

    public function testEmptyPadWhenPaddingIsRequired()
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #3 ($pad_string) must be a non-empty string');

        \mb_str_pad('ab', 4, '', \STR_PAD_RIGHT);
    }

    public function testBadPadTypeWhenPaddingIsRequired()
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Argument #4 ($pad_type) must be STR_PAD_LEFT, STR_PAD_RIGHT, or STR_PAD_BOTH');

        \mb_str_pad('ab', 4, ' ', 99);
    }

    /** Guard for production fix D (intdiv avoids float deprecation on PHP 8.1+). */
    public function testBothSidesDoesNotPassFloatLength()
    {
        if (\PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Float-to-int deprecation applies on PHP 8.1+.');
        }

        $this->assertSame('+Hello+-', \mb_str_pad('Hello', 8, '+-', \STR_PAD_BOTH));
    }
}
