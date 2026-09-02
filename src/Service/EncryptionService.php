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

    /**
     * Chiffre une chaîne de données.
     * @param string|null $data La chaîne à chiffrer
     * @param bool $deterministic Si true, génère un IV déterministe pour permettre les recherches exactes (indexation/unicité/recherche par email).
     */
    public function encrypt(?string $data, bool $deterministic = false): ?string
    {
        if ($data === null || $data === '') {
            return null;
        }

        // Si la donnée est déjà chiffrée, on ne la rechiffre pas
        if ($this->isEncrypted($data)) {
            return $data;
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);
        
        if ($deterministic) {
            // IV déterministe dérivé de manière sécurisée de la clé et de la donnée
            $iv = substr(hash_hmac('sha256', mb_strtolower($data), $this->key, true), 0, $ivLength);
        } else {
            // IV aléatoire pour une sécurité cryptographique maximale
            $iv = openssl_random_pseudo_bytes($ivLength);
        }
        
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    public function decrypt(?string $data): ?string
    {
        if ($data === null || $data === '') {
            return null;
        }

        if (!$this->isEncrypted($data)) {
            return $data;
        }

        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return $data;
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);
        if (strlen($decoded) <= $ivLength) {
            return $data;
        }
        
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);
        
        $decrypted = openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
        
        return ($decrypted !== false) ? $decrypted : $data;
    }

    /**
     * Vérifie si une chaîne semble déjà chiffrée en base64
     */
    public function isEncrypted(?string $data): bool
    {
        if (!$data || strlen($data) < 30) {
            return false;
        }

        $decoded = base64_decode($data, true);
        return $decoded !== false && strlen($decoded) > 16;
    }
}