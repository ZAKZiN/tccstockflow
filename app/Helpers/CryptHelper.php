<?php

namespace App\Helpers;

/**
 * Cibersegurança e LGPD (Item 5): Criptografia de Dados
 * 
 * Esta classe utiliza OpenSSL com AES-256-CBC para criptografar
 * e descriptografar dados sensíveis de usuários (ex: CPF, Telefone, Email)
 * garantindo a proteção e anonimização exigida pela LGPD.
 * 
 * Uso:
 * $criptografado = CryptHelper::encrypt("11999999999");
 * $original = CryptHelper::decrypt($criptografado);
 */
class CryptHelper {
    
    // Na vida real, esta chave deve vir do .env (ex: $_ENV['APP_KEY'])
    // e NUNCA ser exposta no código. Como exemplo de TCC, definimos aqui.
    private static $key = 'lgpd-stockflow-secure-key-2026!';
    private static $cipher = 'aes-256-cbc';
    
    public static function encrypt($data) {
        if (empty($data)) return $data;
        
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        
        $encrypted = openssl_encrypt($data, self::$cipher, self::$key, 0, $iv);
        
        // Retornamos IV + Data encodados em Base64
        return base64_encode($iv . $encrypted);
    }
    
    public static function decrypt($data) {
        if (empty($data)) return $data;
        
        $data = base64_decode($data);
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        
        $iv = substr($data, 0, $ivlen);
        $encrypted = substr($data, $ivlen);
        
        return openssl_decrypt($encrypted, self::$cipher, self::$key, 0, $iv);
    }
}
