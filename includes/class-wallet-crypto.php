<?php
/**
 * Wallet Crypto - Verschlüsselung für Private Keys
 */

if (!defined('ABSPATH')) {
    exit;
}

class SUW_Wallet_Crypto {

    private $method = 'AES-256-CBC';

    /**
     * Verschlüssle Daten
     */
    public function encrypt($data) {
        if (get_option('suw_encryption_enabled', '1') !== '1') {
            // Verschlüsselung deaktiviert - speichere im Klartext (NICHT EMPFOHLEN!)
            error_log('[SUW] WARNING: Encryption is disabled!');
            return base64_encode($data);
        }

        $key = $this->get_encryption_key();
        $iv = $this->generate_iv();

        $encrypted = openssl_encrypt(
            $data,
            $this->method,
            $key,
            0,
            $iv
        );

        if ($encrypted === false) {
            error_log('[SUW] Encryption failed: ' . openssl_error_string());
            return false;
        }

        // Kombiniere IV und verschlüsselte Daten
        return base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Entschlüssle Daten
     */
    public function decrypt($encrypted_data) {
        if (get_option('suw_encryption_enabled', '1') !== '1') {
            // Verschlüsselung deaktiviert - Daten sind im Klartext
            return base64_decode($encrypted_data);
        }

        $decoded = base64_decode($encrypted_data);
        $parts = explode('::', $decoded, 2);

        if (count($parts) !== 2) {
            error_log('[SUW] Invalid encrypted data format');
            return false;
        }

        list($iv, $encrypted) = $parts;

        $key = $this->get_encryption_key();

        $decrypted = openssl_decrypt(
            $encrypted,
            $this->method,
            $key,
            0,
            $iv
        );

        if ($decrypted === false) {
            error_log('[SUW] Decryption failed: ' . openssl_error_string());
            return false;
        }

        return $decrypted;
    }

    /**
     * Generiere oder hole Encryption Key
     */
    private function get_encryption_key() {
        // Verwende WordPress AUTH_KEY + SECURE_AUTH_KEY als Basis
        if (defined('AUTH_KEY') && defined('SECURE_AUTH_KEY')) {
            $base = AUTH_KEY . SECURE_AUTH_KEY;
        } else {
            // Fallback: Verwende DB Credentials (nicht optimal, aber besser als nichts)
            $base = DB_NAME . DB_USER . DB_PASSWORD . DB_HOST;
            error_log('[SUW] WARNING: Using DB credentials as encryption base. Define AUTH_KEY in wp-config.php!');
        }

        // Erstelle 256-bit Key
        return hash('sha256', $base, true);
    }

    /**
     * Generiere Initialization Vector (IV)
     */
    private function generate_iv() {
        $iv_length = openssl_cipher_iv_length($this->method);
        return openssl_random_pseudo_bytes($iv_length);
    }

    /**
     * Teste Verschlüsselung
     */
    public function test_encryption() {
        $test_data = 'test_wallet_key_' . time();

        $encrypted = $this->encrypt($test_data);
        if (!$encrypted) {
            return array(
                'success' => false,
                'error' => 'Encryption failed'
            );
        }

        $decrypted = $this->decrypt($encrypted);
        if ($decrypted !== $test_data) {
            return array(
                'success' => false,
                'error' => 'Decryption mismatch'
            );
        }

        return array(
            'success' => true,
            'message' => 'Encryption working correctly'
        );
    }
}
