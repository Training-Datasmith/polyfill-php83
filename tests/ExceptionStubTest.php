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

class ExceptionStubTest extends TestCase
{
    public function testDateHierarchy()
    {
        $this->assertExceptionHierarchy(\DateError::class, \Error::class);
        $this->assertExceptionHierarchy(\DateObjectError::class, \DateError::class);
        $this->assertExceptionHierarchy(\DateRangeError::class, \DateError::class);
        $this->assertExceptionHierarchy(\DateException::class, \Exception::class);
        $this->assertExceptionHierarchy(\DateInvalidTimeZoneException::class, \DateException::class);
        $this->assertExceptionHierarchy(\DateInvalidOperationException::class, \DateException::class);
        $this->assertExceptionHierarchy(\DateMalformedStringException::class, \DateException::class);
        $this->assertExceptionHierarchy(\DateMalformedIntervalStringException::class, \DateException::class);
        $this->assertExceptionHierarchy(\DateMalformedPeriodStringException::class, \DateException::class);
    }

    public function testSqlite3Exception()
    {
        if (\PHP_VERSION_ID >= 80300 && !\extension_loaded('sqlite3')) {
            $this->markTestSkipped('sqlite3 extension not loaded on PHP 8.3.');
        }

        $this->assertTrue(\class_exists(\SQLite3Exception::class));
        $this->assertExceptionHierarchy(\SQLite3Exception::class, \Exception::class);
    }

    private function assertExceptionHierarchy(string $class, string $parent): void
    {
        $this->assertTrue(\class_exists($class));
        $exception = new $class('bad', 7);
        $this->assertInstanceOf($parent, $exception);
        $this->assertSame('bad', $exception->getMessage());
        $this->assertSame(7, $exception->getCode());

        try {
            throw $exception;
        } catch (\Throwable $caught) {
            $this->assertInstanceOf($class, $caught);
            $this->assertSame('bad', $caught->getMessage());
        }
    }
}
