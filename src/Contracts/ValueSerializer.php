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

namespace Pkg6\DB\Settings\Contracts;

interface ValueSerializer
{
    /**
     * @param $value
     *
     * @return string
     */
    public function serialize($value): string;

    /**
     * @param $serialized
     *
     * @return mixed
     */
    public function unserialize($serialized);
}