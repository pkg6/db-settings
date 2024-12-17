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

namespace Pkg6\DB\Settings;

use Pkg6\DB\Settings\Contracts\Context;

class KeyGenerator implements \Pkg6\DB\Settings\Contracts\KeyGenerator
{
    public function serialize(Context $context = null): string
    {
        return serialize($context);
    }

    public function generate($key, Context $context = null): string
    {
        return md5($key . $this->serialize($context));
    }
}
