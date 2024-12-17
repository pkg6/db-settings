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

namespace Pkg6\DB\Settings\Test\Context;

use PHPUnit\Framework\TestCase;
use Pkg6\DB\Settings\Context;
use Pkg6\DB\Settings\KeyGenerator;

class KeyGeneratorTest extends TestCase
{
    /** @test */
    public function it_accepts_a_context_argument(): void
    {
        $context = (new Context)->set('a', 'a');

        $serializer = new KeyGenerator;
        self::assertEquals(
            serialize($context),
            $serializer->serialize($context)
        );
    }

    /** @test */
    public function it_serializes_null_values(): void
    {
        $serializer = new KeyGenerator;

        self::assertEquals(
            serialize(null),
            $serializer->serialize(null)
        );
    }
}
