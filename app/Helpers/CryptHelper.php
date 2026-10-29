<?php

namespace App\Helpers;

/**
 * Cibersegurança e LGPD (Item 5): Criptografia Autenticada de Dados Sensíveis (PII)
 * 
 * Utiliza OpenSSL com AES-256-GCM (criptografia autenticada AEAD) para proteger
 * informações pessoalmente identificáveis (ex: Telefone, E-mail) em repouso no banco,
 * garantindo confidencialidade e integridade conforme boas práticas da LGPD.
 */
class CryptHelper {
    
    private static string $cipher = 'aes-256-gcm';
    private static string $prefix = 'enc:v1:';

    private static function getKey(): string {
        $rawKey = $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: '';
        if (empty($rawKey)) {
            // Fallback determinístico por instalação caso APP_KEY não esteja definida no .env
            $rawKey = 'stockflow-lgpd-default-key|' . __DIR__;
        }
        return hash('sha256', $rawKey, true);
    }
    
    public static function encrypt($data) {
        if ($data === null || $data === '') {
            return $data;
        }
        $data = (string)$data;
        if (str_starts_with($data, self::$prefix)) {
            return $data;
        }
        
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = random_bytes($ivlen);
        $tag = '';
        
        $encrypted = openssl_encrypt($data, self::$cipher, self::getKey(), OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        if ($encrypted === false) {
            return $data;
        }
        
        return self::$prefix . base64_encode($iv . $tag . $encrypted);
    }
    
    public static function decrypt($data) {
        if ($data === null || $data === '') {
            return $data;
        }
        $data = (string)$data;
        if (!str_starts_with($data, self::$prefix)) {
            // Compatibilidade retroativa com registros antigos em texto plano
            return $data;
        }
        
        $decoded = base64_decode(substr($data, strlen(self::$prefix)), true);
        if ($decoded === false) {
            return '';
        }
        
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $taglen = 16;
        if (strlen($decoded) < ($ivlen + $taglen)) {
            return '';
        }
        
        $iv = substr($decoded, 0, $ivlen);
        $tag = substr($decoded, $ivlen, $taglen);
        $ciphertext = substr($decoded, $ivlen + $taglen);
        
        $decrypted = openssl_decrypt($ciphertext, self::$cipher, self::getKey(), OPENSSL_RAW_DATA, $iv, $tag);
        return $decrypted !== false ? $decrypted : '';
    }
}
