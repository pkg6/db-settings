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

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Pkg6\DB\Settings\Support\Arr;

class ArrTest extends TestCase
{
    public function testAccessible()
    {
        $this->assertTrue(Arr::accessible([]), 'An empty array should be accessible.');
        $this->assertTrue(Arr::accessible(new ArrayObject()), 'An ArrayObject should be accessible.');
        $this->assertFalse(Arr::accessible(null), 'Null should not be accessible.');
        $this->assertFalse(Arr::accessible(123), 'An integer should not be accessible.');
        $this->assertFalse(Arr::accessible('string'), 'A string should not be accessible.');
    }

    public function testExists()
    {
        $array = ['key' => 'value'];
        $arrayObject = new ArrayObject(['key' => 'value']);

        $this->assertTrue(Arr::exists($array, 'key'), 'Key should exist in the array.');
        $this->assertFalse(Arr::exists($array, 'missing'), 'Non-existent key should return false.');
        $this->assertTrue(Arr::exists($arrayObject, 'key'), 'Key should exist in the ArrayObject.');
        $this->assertFalse(Arr::exists($arrayObject, 'missing'), 'Non-existent key in ArrayObject should return false.');
    }

    public function testGetWithSimpleKeys()
    {
        $array = ['key' => 'value', 'foo' => 'bar'];

        $this->assertEquals('value', Arr::get($array, 'key'), 'Should return the value for an existing key.');
        $this->assertEquals('bar', Arr::get($array, 'foo'), 'Should return the value for another existing key.');
        $this->assertNull(Arr::get($array, 'missing'), 'Should return null for a missing key.');
        $this->assertEquals('default', Arr::get($array, 'missing', 'default'), 'Should return default for a missing key.');
    }

    public function testGetWithNestedKeys()
    {
        $array = ['user' => ['profile' => ['name' => 'John']]];

        $this->assertEquals('John', Arr::get($array, 'user.profile.name'), 'Should return the value for a nested key.');
        $this->assertNull(Arr::get($array, 'user.profile.age'), 'Should return null for a missing nested key.');
        $this->assertEquals('default', Arr::get($array, 'user.profile.age', 'default'), 'Should return default for a missing nested key.');
    }

    public function testGetWithNullKey()
    {
        $array = ['key' => 'value'];
        $this->assertEquals($array, Arr::get($array, null), 'Should return the entire array if the key is null.');
    }

    public function testGetWithNonArray()
    {
        $this->assertNull(Arr::get(null, 'key'), 'Should return null if the array is not accessible.');
        $this->assertEquals('default', Arr::get(null, 'key', 'default'), 'Should return default if the array is not accessible.');
    }

    public function testGetWithDefaultCallback()
    {
        $defaultCallback = function () {
            return 'computed-default';
        };

        $array = ['key' => 'value'];
        $this->assertEquals('computed-default', Arr::get($array, 'missing', $defaultCallback), 'Should call the default callback if the key is missing.');
    }
}
