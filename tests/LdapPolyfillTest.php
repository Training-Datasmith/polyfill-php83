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

class LdapPolyfillTest extends TestCase
{
    public function testExopSyncDoesNotFailArity()
    {
        if (!\extension_loaded('ldap')) {
            $this->markTestSkipped('The ldap extension is required.');
        }

        if (\PHP_VERSION_ID >= 80300) {
            $this->markTestSkipped('Native ldap_exop_sync on PHP 8.3+.');
        }

        $socket = \stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        $this->assertNotFalse($socket);
        $address = \stream_socket_get_name($socket, false);
        \fclose($socket);
        $port = (int) substr($address, strrpos($address, ':') + 1);

        $ldap = \ldap_connect('ldap://127.0.0.1:'.$port);
        $this->assertNotFalse($ldap);
        \ldap_set_option($ldap, \LDAP_OPT_NETWORK_TIMEOUT, 1);

        $warnings = [];
        \set_error_handler(static function ($errno, $errstr) use (&$warnings) {
            $warnings[] = $errstr;

            return true;
        });

        try {
            $result = \ldap_exop_sync($ldap, \LDAP_EXOP_WHO_AM_I, null, [['oid' => '1.2.3', 'value' => 'x']]);
            $this->assertFalse($result);
        } finally {
            \restore_error_handler();
        }

        $joined = implode("\n", $warnings);
        $this->assertStringNotContainsString('expects at most', $joined);
        $this->assertStringNotContainsString('expects exactly', $joined);
    }

    public function testConnectWalletOnlyWhenSignatureCanAcceptIt()
    {
        if (!\extension_loaded('ldap')) {
            $this->markTestSkipped('The ldap extension is required.');
        }

        if (\PHP_VERSION_ID >= 80300) {
            $this->markTestSkipped('Native ldap_connect_wallet on PHP 8.3+.');
        }

        if ((new \ReflectionFunction('ldap_connect'))->getNumberOfParameters() < 4) {
            $this->markTestSkipped('ldap_connect does not accept wallet parameters on this build.');
        }

        $warnings = [];
        \set_error_handler(static function ($errno, $errstr) use (&$warnings) {
            $warnings[] = $errstr;

            return true;
        });

        try {
            $result = \ldap_connect_wallet('ldap://127.0.0.1', '/tmp/polyfill-php83-no-such-wallet', 'secret');
            $ok = false === $result || \is_resource($result);
            if (\PHP_VERSION_ID >= 80100 && \class_exists(\LDAP\Connection::class, false)) {
                $ok = $ok || $result instanceof \LDAP\Connection;
            }
            $this->assertTrue($ok);
        } finally {
            \restore_error_handler();
        }

        $joined = implode("\n", $warnings);
        $this->assertStringNotContainsString('expects at most', $joined);
        $this->assertStringNotContainsString('expects exactly', $joined);
    }
}
