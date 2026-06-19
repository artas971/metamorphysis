<?php

namespace App\Service;

class EncryptionService
{
    private string $key;
    private string $cipher = 'aes-256-cbc';

    public function __construct(string $encryptionKey)
    {
        $this->key = $encryptionKey;
    }

    public function encrypt(?string $data): ?string
    {
        if (!$data) {
            return null;
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    public function decrypt(?string $data): ?string
    {
        if (!$data) {
            return null;
        }

        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        $decrypted = openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
        
        return $decrypted !== false ? $decrypted : null;
    }
}