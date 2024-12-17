<?php

/*
 * This file is part of the pkg6/db-settings
 *
 * (c) pkg6 <https://github.com/pkg6>
 *
 * (L) Licensed <https://opensource.org/license/MIT>
 *
 * (A) zhiqiang <https://www.zhiqiang.wang>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Pkg6\DB\Settings\Test\Support;

use PHPUnit\Framework\TestCase;
use Pkg6\DB\Settings\Support\Str;

class StrTest extends TestCase
{
    public function testStartsWithWithSingleNeedle()
    {
        $this->assertTrue(Str::startsWith('hello world', 'hello'), 'String should start with "hello".');
        $this->assertFalse(Str::startsWith('hello world', 'world'), 'String should not start with "world".');
    }

    public function testStartsWithWithMultipleNeedles()
    {
        $this->assertTrue(Str::startsWith('hello world', ['hi', 'hello']), 'String should start with one of the needles.');
        $this->assertFalse(Str::startsWith('hello world', ['hi', 'world']), 'String should not start with any of the needles.');
        $this->assertTrue(Str::startsWith('hello world', ['hello', 'hi']), 'String should start with the first needle.');
    }

    public function testStartsWithWithNumericNeedles()
    {
        $this->assertTrue(Str::startsWith('12345', 123), 'String should start with the numeric needle.');
        $this->assertFalse(Str::startsWith('12345', 456), 'String should not start with the numeric needle.');
    }

    public function testStartsWithEdgeCases()
    {
        $this->assertFalse(Str::startsWith('', 'hello'), 'Empty string should not start with a non-empty needle.');
        $this->assertTrue(Str::startsWith('0hello', '0'), 'String should start with the numeric string "0".');
        $this->assertFalse(Str::startsWith('hello', null), 'String should not start with null.');
    }
}
