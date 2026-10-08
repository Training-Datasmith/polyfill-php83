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

class StreamContextSetOptionsTest extends TestCase
{
    public function testReplacesContextOptions()
    {
        $context = \stream_context_create(['http' => ['method' => 'GET']]);

        $this->assertSame(true, \stream_context_set_options($context, [
            'http' => ['method' => 'POST', 'header' => "X-Test: a\r\n"],
            'socket' => ['bindto' => '127.0.0.1:0'],
        ]));

        $options = \stream_context_get_options($context);
        $this->assertSame('POST', $options['http']['method']);
        $this->assertSame("X-Test: a\r\n", $options['http']['header']);
        $this->assertSame('127.0.0.1:0', $options['socket']['bindto']);
    }
}
