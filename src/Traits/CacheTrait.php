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

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

trait CacheTrait
{
    /**
     * @var bool
     */
    protected $cacheEnabled = false;
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $cacheKey = 'db.settings.';

    /**
     * @param CacheInterface $cache
     *
     * @return $this
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @param $cacheKey
     *
     * @return $this
     */
    public function setCacheKey(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @return bool
     */
    protected function cacheIsEnabled(): bool
    {
        return $this->cacheEnabled && $this->cache !== null;
    }

    /**
     * @return CacheTrait
     */
    public function enableCache(): self
    {
        $this->cacheEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableCache(): self
    {
        $this->cacheEnabled = false;

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @param null $expire
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     * @throws \throwable
     */
    protected function cacheRemember(string $key, $value, $expire = null)
    {
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }
        $time = time();
        while ($time + 5 > time() && $this->cache->has($key . '_lock')) {
            // 存在锁定则等待
            usleep(200000);
        }
        try {
            // 锁定
            $this->cache->set($key . '_lock', true);
            if ($value instanceof \Closure) {
                // 获取缓存数据
                $value = $value();
            }
            // 缓存数据
            $this->cache->set($key, $value, $expire);
            // 解锁
            $this->cache->delete($key . '_lock');
        } catch (\Exception|\throwable $e) {
            $this->cache->delete($key . '_lock');
            throw $e;
        }

        return $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    protected function cacheDelete(string $key)
    {
        return $this->cache->delete($key);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        return $this->cacheKey . $key;
    }
}
