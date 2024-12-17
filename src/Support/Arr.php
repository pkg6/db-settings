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

namespace Pkg6\DB\Settings\Support;

use ArrayAccess;

class Arr
{
    /**
     * @param $value
     *
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * @param $array
     * @param $key
     *
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        if (is_object($array)) {
            if (method_exists($array, 'has')) {
                return $array->has($key);
            }
        }

        return array_key_exists($key, $array);
    }

    /**
     * @param $array
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if ( ! static::accessible($array)) {
            return Call::value($default);
        }
        if (is_null($key)) {
            return $array;
        }
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        if (strpos($key, '.') === false) {
            return $array[$key] ?? Call::value($default);
        }
        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return Call::value($default);
            }
        }

        return $array;
    }
}
