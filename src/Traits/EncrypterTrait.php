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

trait EncrypterTrait
{
    /**
     * @var object
     */
    protected $encrypter = null;
    /**
     * @var bool
     */
    protected $encryptionEnabled = false;

    /**
     * @param object $encrypter
     *
     * @return $this
     */
    public function setEncrypter($encrypter): self
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * @return bool
     */
    protected function encryptionIsEnabled(): bool
    {
        return $this->encryptionEnabled && $this->encrypter !== null;
    }

    /**
     * @return $this
     */
    public function enableEncryption(): self
    {
        $this->encryptionEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableEncryption(): self
    {
        $this->encryptionEnabled = false;

        return $this;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function decryptValue($value)
    {
        if ( ! $this->encryptionIsEnabled()) {
            return $value;
        }
        try {
            if (method_exists($this->encrypter, 'decrypt')) {
                return $this->encrypter->decrypt($value);
            }

            return $value;
        } catch (\Exception $e) {
            return $value;
        }
    }

    protected function encryptValue($value)
    {
        if (method_exists($this->encrypter, 'encrypt')) {
            return $this->encrypter->encrypt($value);
        }

        return $value;
    }
}
