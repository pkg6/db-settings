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

use OutOfBoundsException;

class Context implements \Pkg6\DB\Settings\Contracts\Context
{
    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Context constructor.
     *
     * @param array $arguments
     */
    public function __construct(array $arguments = [])
    {
        foreach ($arguments as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        if ( ! $this->has($name)) {
            throw new OutOfBoundsException(
                sprintf('"%s" is not part of the context.', $name)
            );
        }

        return $this->arguments[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function remove(string $name): self
    {
        unset($this->arguments[$name]);

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function set(string $name, $value): self
    {
        $this->arguments[$name] = $value;

        return $this;
    }

    /**
     * Count elements of an object.
     *
     * @see https://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     *
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->arguments);
    }
}
