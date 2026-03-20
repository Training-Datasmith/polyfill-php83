<?php

declare(strict_types=1);

/**
 * Example: Using symfony/polyfill-php83 functions.
 *
 * This polyfill provides PHP 8.3 functions for PHP 8.1 and 8.2.
 * On PHP 8.3+, native implementations are used automatically.
 *
 * Install:
 *   composer require symfony/polyfill-php83
 */

// --- json_validate(): validate JSON without decoding ---
$valid = json_validate('{"name":"Alice","age":30}');
var_dump($valid); // bool(true)

$invalid = json_validate('{bad json}');
var_dump($invalid); // bool(false)

// --- json_validate() with depth limit ---
$nested = '{"a":{"b":{"c":1}}}';
$withinDepth = json_validate($nested, depth: 4);
var_dump($withinDepth); // bool(true)

$tooDeep = json_validate($nested, depth: 2);
var_dump($tooDeep); // bool(false)

// --- mb_str_pad(): multibyte-safe string padding ---
$text = 'héllo';
$padded = mb_str_pad($text, 10);
var_dump($padded); // string(10) "héllo     "

$leftPadded = mb_str_pad($text, 10, '-', STR_PAD_LEFT);
var_dump($leftPadded); // string(10) "-----héllo"

$bothPadded = mb_str_pad($text, 11, '+-', STR_PAD_BOTH);
var_dump($bothPadded); // string(11) "+-+héllo+-+"

// --- str_increment(): increment an alphanumeric string ---
$next = str_increment('a');
var_dump($next); // string(1) "b"

$next = str_increment('Az');
var_dump($next); // string(2) "Ba"

$next = str_increment('zz');
var_dump($next); // string(3) "aaa"

// --- str_decrement(): decrement an alphanumeric string ---
$prev = str_decrement('b');
var_dump($prev); // string(1) "a"

$prev = str_decrement('Ba');
var_dump($prev); // string(2) "Az"

// --- PHP 8.3 stubs: #[Override] attribute ---
// Available as a stub class for static analysis on PHP < 8.3
// class MyChild extends MyParent {
//     #[\Override]
//     public function myMethod(): void { ... }
// }
