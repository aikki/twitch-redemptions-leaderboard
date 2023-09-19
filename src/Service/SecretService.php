<?php

namespace App\Service;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class SecretService
{
    private Key $key;
    public function __construct(
        private readonly string $keyPath,
    )
    {
        $this->key = Key::loadFromAsciiSafeString(file_get_contents($this->keyPath));
    }

    public function encrypt(string $string): string
    {
        return Crypto::encrypt($string, $this->key);
    }

    public function decrypt(string $string): string
    {
        return Crypto::decrypt($string, $this->key);
    }
}