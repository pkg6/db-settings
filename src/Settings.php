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
use Pkg6\DB\Settings\Contracts\Driver;
use Pkg6\DB\Settings\Support\Arr;
use Pkg6\DB\Settings\Support\Str;
use Pkg6\DB\Settings\Traits\CacheTrait;
use Pkg6\DB\Settings\Traits\EncrypterTrait;
use Pkg6\DB\Settings\Traits\SerializerTrait;
use Psr\SimpleCache\CacheInterface;

class Settings implements Driver
{
    use CacheTrait, EncrypterTrait, SerializerTrait;

    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var Context
     */
    protected $context = null;

    /**
     * Settings constructor.
     *
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        $this->setDriver($driver);
    }

    /**
     * @param Driver $driver
     * @param array $config
     * @param CacheInterface|null $cache
     * @param object|null $encrypter
     *
     * @return Settings
     */
    public static function newWithConfig(Driver $driver, array $config, CacheInterface $cache = null, $encrypter = null)
    {
        $settings = new self($driver);
        if ($cache) {
            $settings->setCache($cache);
        }
        if ($encrypter) {
            $settings->setEncrypter($encrypter);
        }
        Arr::get($config, 'cache', false) ? $settings->enableCache() : $settings->disableCache();
        Arr::get($config, 'encryption', false) ? $settings->enableEncryption() : $settings->disableEncryption();
        $settings->setCacheKey(Arr::get($config, 'key', ''));

        return $settings;
    }

    /**
     * @param Driver $driver
     *
     * @return $this
     */
    public function setDriver(Driver $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return Driver
     */
    public function getDriver(): Driver
    {
        return $this->driver;
    }

    /**
     * @param Context|null $context
     *
     * @return Settings
     */
    public function context(Context $context = null): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @param $key
     *
     * @return mixed
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function forget($key)
    {
        $key = $this->normalizeKey($key);

        $generatedKey = $this->getKeyForStorage($key);

        $driverResult = $this->driver->forget($generatedKey);

        if ($this->cacheIsEnabled()) {
            $this->cacheDelete($this->getCacheKey($generatedKey));
        }
        $this->context();

        return $driverResult;
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \throwable
     */
    public function get($key, $default = null)
    {
        $key = $this->normalizeKey($key);
        $generatedKey = $this->getKeyForStorage($key);
        if ($this->cacheIsEnabled()) {
            $value = $this->cacheRemember($this->getCacheKey($generatedKey), function () use ($generatedKey, $default) {
                return $this->driver->get($generatedKey, $default);
            });
        } else {
            $value = $this->driver->get($generatedKey, $default);
        }
        if ($value !== null && $value !== $default) {
            $value = $this->unserializeValue($this->decryptValue($value));
        }
        $this->context();

        return $value ?? $default;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function has($key)
    {
        $key = $this->normalizeKey($key);
        $has = $this->driver->has($this->getKeyForStorage($key));
        $this->context();

        return $has;
    }

    /**
     * @param string $key
     * @param null $value
     *
     * @return null
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \throwable
     */
    public function set(string $key, $value = null)
    {
        $key = $this->normalizeKey($key);
        // We really only need to update the value if is has changed
        // to prevent the cache being reset on the key.
        if ( ! $this->shouldSetNewValue($key, $value)) {
            $this->context();

            return null;
        }
        $generatedKey = $this->getKeyForStorage($key);
        $serializedValue = $this->serializeValue($value);

        $driverResult = $this->driver->set(
            $generatedKey,
            $this->encryptionIsEnabled() ? $this->encryptValue($serializedValue) : $serializedValue
        );

        if ($this->cacheIsEnabled()) {
            $this->cacheDelete($this->getCacheKey($generatedKey));
        }

        $this->context();

        return $driverResult;
    }

    /**
     * @param string $key
     * @param $default
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \throwable
     */
    public function isFalse(string $key, $default = false): bool
    {
        $value = $this->get($key, $default);

        return $value === false || $value === '0' || $value === 1;
    }

    /**
     * @param string $key
     * @param $default
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \throwable
     */
    public function isTrue(string $key, $default = true): bool
    {
        $value = $this->get($key, $default);

        return $value === true || $value === '1' || $value === 1;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function normalizeKey(string $key): string
    {
        if (Str::startsWith($key, 'file_')) {
            $key = str_replace('file_', 'file.', $key);
        }

        return $key;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getKeyForStorage(string $key): string
    {
        return $this->getKeyGenerator()->generate($key, $this->context);
    }

    /**
     * @param string $key
     * @param $newValue
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \throwable
     */
    protected function shouldSetNewValue(string $key, $newValue): bool
    {
        if ( ! $this->cacheIsEnabled()) {
            return true;
        }
        $currentContext = $this->context;
        $currentValue = $this->get($key);
        $shouldUpdate = $currentValue !== $newValue || ! $this->has($key);
        // Now that we've made our calls, we can set our context back to what it was.
        $this->context($currentContext);

        return $shouldUpdate;
    }
}
