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

use Countable;

interface Context extends Countable
{
    public function get(string $name);
    public function has(string $name): bool;
    public function remove(string $name): self;
    public function set(string $name, $value): self;
}
