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

namespace Pkg6\DB\Settings\Traits;

use Pkg6\DB\Settings\Contracts\KeyGenerator;
use Pkg6\DB\Settings\Contracts\ValueSerializer;

trait SerializerTrait
{
    /**
     * @var KeyGenerator
     */
    protected $keyGenerator;
    /**
     * @var ValueSerializer
     */
    protected $valueSerializer;

    /**
     * @param ValueSerializer $serializer
     *
     * @return $this
     */
    public function setSerializer(ValueSerializer $serializer)
    {
        $this->valueSerializer = $serializer;

        return $this;
    }
    /**
     * @return ValueSerializer
     */
    public function getValueSerializer(): ValueSerializer
    {
        if (is_null($this->valueSerializer)) {
            $this->valueSerializer = new \Pkg6\DB\Settings\ValueSerializer();
        }

        return $this->valueSerializer;
    }

    /**
     * @return KeyGenerator
     */
    public function getKeyGenerator(): KeyGenerator
    {
        if (is_null($this->keyGenerator)) {
            $this->keyGenerator = new \Pkg6\DB\Settings\KeyGenerator();
        }

        return $this->keyGenerator;
    }

    /**
     * @param KeyGenerator $keyGenerator
     *
     * @return $this
     */
    public function setKeyGenerator(KeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;

        return $this;
    }

    /**
     * @param $serialized
     *
     * @return mixed
     */
    protected function unserializeValue($serialized)
    {
        // Attempt to unserialize the value, but return the original value if that fails.
        try {
            return $this->getValueSerializer()->unserialize($serialized);
        } catch (\Throwable $e) {
            return $serialized;
        }
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function serializeValue($value): string
    {
        return $this->getValueSerializer()->serialize($value);
    }
}
