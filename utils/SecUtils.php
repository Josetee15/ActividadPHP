<?php

class SecUtils
{
    private static $ENCRIPTION_KEY_SECRET = 'May the force be with you';

    // Método estático para encriptar una cadena de texto
    public static function encrypt($data)
    {
        // Obtener la clave a partir del secret
        $key = hash('sha256', SecUtils::$ENCRIPTION_KEY_SECRET, true);

        // Generar un IV (vector de inicialización) para el cifrado
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);

        // Encriptar los datos usando AES-256-CBC
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

        // Concatenar el IV con los datos encriptados y codificar en Base64
        return base64_encode($iv . $encryptedData);
    }

    // Método estático para desencriptar una cadena de texto
    public static function decrypt($data)
    {
        // Obtener la clave a partir del secret
        $key = hash('sha256', SecUtils::$ENCRIPTION_KEY_SECRET, true);

        // Decodificar la cadena Base64
        $decodedData = base64_decode($data);

        // Extraer el IV del inicio de los datos decodificados
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decodedData, 0, $ivLength);

        // Extraer los datos encriptados
        $encryptedData = substr($decodedData, $ivLength);

        // Desencriptar los datos usando AES-256-CBC
        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    }
}
