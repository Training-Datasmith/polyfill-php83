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

class PolyfillWiringTest extends TestCase
{
    public function testJsonValidateIsUserlandBelow83()
    {
        if (\PHP_VERSION_ID >= 80300) {
            $this->assertFalse((new \ReflectionFunction('json_validate'))->isUserDefined());

            return;
        }

        $this->assertTrue((new \ReflectionFunction('json_validate'))->isUserDefined());
    }

    public function testStrIncrementIsUserlandBelow83()
    {
        if (\PHP_VERSION_ID >= 80300) {
            $this->assertFalse((new \ReflectionFunction('str_increment'))->isUserDefined());

            return;
        }

        $this->assertTrue((new \ReflectionFunction('str_increment'))->isUserDefined());
    }

    public function testStrDecrementIsUserlandBelow83()
    {
        if (\PHP_VERSION_ID >= 80300) {
            $this->assertFalse((new \ReflectionFunction('str_decrement'))->isUserDefined());

            return;
        }

        $this->assertTrue((new \ReflectionFunction('str_decrement'))->isUserDefined());
    }

    public function testStreamContextSetOptionsIsUserlandBelow83()
    {
        if (\PHP_VERSION_ID >= 80300) {
            $this->assertFalse((new \ReflectionFunction('stream_context_set_options'))->isUserDefined());

            return;
        }

        $this->assertTrue((new \ReflectionFunction('stream_context_set_options'))->isUserDefined());
    }

    public function testMbStrPadIsUserlandBelow83()
    {
        if (!\function_exists('mb_str_pad')) {
            $this->markTestSkipped('mb_str_pad is not available.');
        }

        if (\PHP_VERSION_ID >= 80300) {
            $this->assertFalse((new \ReflectionFunction('mb_str_pad'))->isUserDefined());

            return;
        }

        $this->assertTrue((new \ReflectionFunction('mb_str_pad'))->isUserDefined());
    }

    public function testValueErrorStubOnlyOnPhp7()
    {
        if (\PHP_VERSION_ID >= 80000) {
            require __DIR__.'/../Resources/stubs/ValueError.php';

            $reflection = new \ReflectionClass(\ValueError::class);
            $this->assertTrue($reflection->isInternal());
            $this->assertFalse($reflection->getFileName());

            return;
        }

        $this->assertTrue((new \ReflectionClass(\ValueError::class))->isUserDefined());
        $this->assertInstanceOf(\Error::class, new \ValueError('test'));
    }

    public function testLdapSymbolsWhenExtensionPresent()
    {
        if (!\extension_loaded('ldap')) {
            $this->markTestSkipped('The ldap extension is required.');
        }

        if (\PHP_VERSION_ID >= 80300) {
            $this->assertFalse((new \ReflectionFunction('ldap_exop_sync'))->isUserDefined());

            return;
        }

        $this->assertTrue((new \ReflectionFunction('ldap_exop_sync'))->isUserDefined());
        $this->assertTrue((new \ReflectionFunction('ldap_connect_wallet'))->isUserDefined());
    }
}
