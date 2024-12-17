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

namespace Pkg6\DB\Settings\Drivers;

use Exception;
use Pkg6\DB\Settings\Contracts\Driver;

/**
 * CREATE TABLE `settings` (
 * `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 * `key` varchar(255) NOT NULL,
 * `value` longtext DEFAULT NULL,
 * PRIMARY KEY (`id`),
 * UNIQUE KEY `settings_key_unique` (`key`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;.
 */
class PDODriver implements Driver
{
    /**
     * @var \PDO
     */
    protected $pdo;
    /**
     * @var string
     */
    protected $table;

    /**
     * PDODriver constructor.
     *
     * @param \PDO $pdo
     * @param string $table
     */
    public function __construct(\PDO $pdo, $table = 'settings')
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * @param $key
     */
    public function forget($key): void
    {
        $sql = sprintf("DELETE FROM `%s` WHERE `key` = :key", $this->table);
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        // 准备查询语句
        $sql = sprintf("SELECT `value` FROM `%s` WHERE `key` = :key LIMIT 1", $this->table);
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        // 尝试获取结果
        $value = $stmt->fetchColumn();
        // 返回结果或默认值
        return $value !== false ? $value : $default;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        // 准备查询语句
        $sql = sprintf("SELECT 1 FROM `%s` WHERE `key` = :key LIMIT 1", $this->table);
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        // 检查是否有结果
        return $stmt->fetchColumn() !== false;
    }

    /**
     * @param string $key
     * @param null $value
     *
     * @return bool
     *
     * @throws Exception
     */
    public function set(string $key, $value = null)
    {
        $sql = sprintf("INSERT INTO `%s` (`key`, `value`) VALUES (:key, :value)", $this->table);
        if ($this->has($key)) {
            $sql = sprintf("UPDATE `%s` SET `value` = :value WHERE `key` = :key", $this->table);
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':value', $value);
        return $stmt->execute();
    }
}
