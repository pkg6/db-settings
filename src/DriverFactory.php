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

use Closure;
use InvalidArgumentException;
use Pkg6\DB\Settings\Contracts\Driver;
use Pkg6\DB\Settings\Drivers\PDODriver;
use Pkg6\DB\Settings\Support\Arr;

class DriverFactory
{
    /**
     * @var array
     */
    protected $drivers = [];
    /**
     * @var array
     */
    protected $customCreators = [];
    /**
     * @var array
     */
    protected $config = [
        'cache' => true,
        'cache_key_prefix' => 'db.settings.',
        'encryption' => true,
        "drivers" => [
            "pdo" => [
                'driver' => 'pdo',
                "table" => "settings",
                "dns" => 'mysql:host=localhost;dbname=test;port=3306;charset=utf8',
                "username" => 'root',
                "password" => 'root',
            ]
        ]
    ];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string|null $driver
     *
     * @return Driver
     */
    public function driver(string $driver = null): Driver
    {
        return $this->resolveDriver($driver);
    }

    public function extend(string $driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    protected function createPdoDriver($driverConfig): PdoDriver
    {
        $pdo = new \PDO(
            Arr::get($driverConfig, 'dns'),
            Arr::get($driverConfig, 'username'),
            Arr::get($driverConfig, 'password')
        );

        return new PdoDriver($pdo, Arr::get($driverConfig, 'table', 'settings'));
    }

    protected function getDefaultDriver(): string
    {
        return Arr::get($this->config, 'driver', 'pdo');
    }

    protected function getDriverConfig(string $driver): ?array
    {
        return Arr::get($this->config, 'drivers.' . $driver);
    }

    protected function resolveDriver(string $driver = null): Driver
    {
        $driver = $driver ?: $this->getDefaultDriver();

        return $this->drivers[$driver] = $this->resolve($driver);
    }

    protected function resolve(string $driver): Driver
    {
        if (isset($this->drivers[$driver])) {
            return $this->drivers[$driver];
        }
        $driverConfig = $this->getDriverConfig($driver);
        if ( ! $driverConfig) {
            throw new InvalidArgumentException("Missing settings driver config for '{$driver}'.");
        }
        if (isset($this->customCreators[$driverConfig['driver']])) {
            return $this->callCustomCreator($driverConfig);
        }
        $method = 'create' . ucfirst($driverConfig['driver']) . 'Driver';
        if ( ! method_exists($this, $method)) {
            throw new InvalidArgumentException("Unsupported settings driver: {$driverConfig['driver']}.");
        }

        return $this->$method($driverConfig);
    }

    protected function callCustomCreator(array $config): Driver
    {
        return $this->customCreators[$config['driver']]($config);
    }
}
