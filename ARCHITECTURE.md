# Architecture: polyfill-php83

## Purpose

Backports PHP 8.3 functions, constants, attributes, and exception classes to PHP 8.0, 8.1,
and 8.2. Enables libraries to require PHP 8.3 features while remaining installable on
older PHP 8.x versions.

## Directory Structure

```
Php83.php        # Pure-PHP implementations of new PHP 8.3 functions as static methods
bootstrap.php    # Defines global functions/constants from PHP 8.3 if running on PHP < 8.3
bootstrap81.php  # Variant for PHP 8.1+: skips features already native in 8.1/8.2
Resources/
  stubs/         # Class/interface/exception stubs for new PHP 8.3 types:
    DateError.php, DateException.php, DateInvalidOperationException.php,
    DateInvalidTimeZoneException.php, DateMalformedIntervalStringException.php,
    DateMalformedPeriodStringException.php, DateMalformedStringException.php,
    DateObjectError.php, DateRangeError.php, Override.php, SQLite3Exception.php
```

## Key Design Decisions

### Stubs for New Exception Hierarchy

PHP 8.3 introduced a new exception hierarchy for date/time operations. The `Resources/stubs/`
directory defines these classes on older PHP versions, allowing code to catch or throw
them while remaining compatible. The `#[Override]` attribute stub is particularly useful
for static analysis tooling that supports it.

### Multiple Bootstrap Files

`bootstrap.php` is the general version. `bootstrap81.php` is optimized for PHP 8.1+
and skips features already native in those versions to reduce overhead.

## Extension Points

None — drop-in function and class polyfill.
