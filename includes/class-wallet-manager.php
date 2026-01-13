<?php
/**
 * Wallet Manager - Erstellt und verwaltet Sui Wallets für WordPress User
 */

if (!defined('ABSPATH')) {
    exit;
}

class SUW_Wallet_Manager {

    /**
     * Erstelle Wallet für einen User
     */
    public function create_wallet_for_user($user_id) {
        // Prüfe ob User bereits Wallet hat
        $existing = $this->get_user_wallet($user_id);
        if ($existing) {
            return array(
                'success' => false,
                'error' => 'User hat bereits eine Wallet'
            );
        }

        // Generiere neue Wallet
        $wallet_data = $this->generate_wallet();

        if (!$wallet_data['success']) {
            return $wallet_data;
        }

        // Verschlüssle Private Key
        $crypto = new SUW_Wallet_Crypto();
        $encrypted_key = $crypto->encrypt($wallet_data['private_key']);

        if (!$encrypted_key) {
            return array(
                'success' => false,
                'error' => 'Fehler beim Verschlüsseln des Private Keys'
            );
        }

        // Speichere in DB
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        $result = $wpdb->insert($table_name, array(
            'user_id' => $user_id,
            'wallet_address' => $wallet_data['address'],
            'encrypted_private_key' => $encrypted_key,
            'created_at' => current_time('mysql'),
            'cached_balance' => '0'
        ));

        if ($result === false) {
            return array(
                'success' => false,
                'error' => 'Fehler beim Speichern in der Datenbank'
            );
        }

        // Hook für andere Plugins
        do_action('suw_wallet_created', $user_id, $wallet_data['address']);

        return array(
            'success' => true,
            'address' => $wallet_data['address'],
            'user_id' => $user_id
        );
    }

    /**
     * Generiere neue Sui Wallet
     */
    private function generate_wallet() {
        // Methode 1: Via Vercel API (empfohlen)
        $api_url = get_option('suw_vercel_api_url', '');
        $api_key = get_option('suw_vercel_api_key', '');

        if (!empty($api_url) && !empty($api_key)) {
            return $this->generate_wallet_via_api($api_url, $api_key);
        }

        // Methode 2: Lokale Generierung (fallback)
        return $this->generate_wallet_locally();
    }

    /**
     * Generiere Wallet via Vercel API
     */
    private function generate_wallet_via_api($api_url, $api_key) {
        $url = rtrim($api_url, '/') . '/api/generate-wallet';

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $api_key
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => 'API Fehler: ' . $response->get_error_message()
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($status_code === 200 && isset($json['success']) && $json['success']) {
            return array(
                'success' => true,
                'address' => $json['data']['address'],
                'private_key' => $json['data']['privateKey']
            );
        }

        return array(
            'success' => false,
            'error' => isset($json['error']) ? $json['error'] : 'Unknown API error'
        );
    }

    /**
     * Generiere Wallet lokal (fallback - ohne echte Krypto)
     * WARNUNG: Dies ist nur ein Platzhalter! Nicht für Production verwenden!
     */
    private function generate_wallet_locally() {
        // Generiere zufällige Adresse (NUR FÜR DEVELOPMENT!)
        // In Production MUSS die Vercel API verwendet werden!

        $random_bytes = random_bytes(32);
        $hex = bin2hex($random_bytes);
        $address = '0x' . $hex;

        // Fake Private Key (Platzhalter)
        $private_key = 'suiprivkey1' . bin2hex(random_bytes(32));

        error_log('[SUW] WARNING: Using local wallet generation (DEVELOPMENT ONLY)');

        return array(
            'success' => true,
            'address' => $address,
            'private_key' => $private_key,
            'warning' => 'Local generation used - not suitable for production'
        );
    }

    /**
     * Hole User Wallet
     */
    public function get_user_wallet($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        $wallet = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d",
            $user_id
        ), ARRAY_A);

        if (!$wallet) {
            return null;
        }

        return array(
            'address' => $wallet['wallet_address'],
            'created_at' => $wallet['created_at'],
            'cached_balance' => $wallet['cached_balance'],
            'last_balance_check' => $wallet['last_balance_check']
        );
    }

    /**
     * Exportiere Private Key
     */
    public function export_private_key($user_id) {
        // Prüfe ob Export erlaubt ist
        if (get_option('suw_allow_private_key_export', '1') !== '1') {
            return array(
                'success' => false,
                'error' => 'Private Key Export ist deaktiviert'
            );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        $wallet = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d",
            $user_id
        ), ARRAY_A);

        if (!$wallet) {
            return array(
                'success' => false,
                'error' => 'Keine Wallet gefunden'
            );
        }

        // Entschlüssle Private Key
        $crypto = new SUW_Wallet_Crypto();
        $private_key = $crypto->decrypt($wallet['encrypted_private_key']);

        if (!$private_key) {
            return array(
                'success' => false,
                'error' => 'Fehler beim Entschlüsseln'
            );
        }

        // Log Export (Sicherheit)
        error_log('[SUW] Private key exported for user ' . $user_id . ' by admin ' . get_current_user_id());

        return array(
            'success' => true,
            'private_key' => $private_key,
            'address' => $wallet['wallet_address']
        );
    }

    /**
     * Hole Wallet Balance von Blockchain
     */
    public function get_wallet_balance($user_id) {
        $wallet = $this->get_user_wallet($user_id);

        if (!$wallet) {
            return array(
                'success' => false,
                'error' => 'Keine Wallet gefunden'
            );
        }

        // Vercel API verwenden
        $api_url = get_option('suw_vercel_api_url', '');
        $api_key = get_option('suw_vercel_api_key', '');

        if (empty($api_url) || empty($api_key)) {
            return array(
                'success' => false,
                'error' => 'Vercel API nicht konfiguriert'
            );
        }

        $url = rtrim($api_url, '/') . '/api/get-balance';

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $api_key
            ),
            'body' => json_encode(array(
                'address' => $wallet['address']
            )),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => 'API Fehler: ' . $response->get_error_message()
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if ($status_code === 200 && isset($json['success']) && $json['success']) {
            $balance = $json['data']['balance'];

            // Update Cache in DB
            global $wpdb;
            $table_name = $wpdb->prefix . 'sui_user_wallets';
            $wpdb->update(
                $table_name,
                array(
                    'cached_balance' => $balance,
                    'last_balance_check' => current_time('mysql')
                ),
                array('user_id' => $user_id)
            );

            return array(
                'success' => true,
                'balance' => $balance,
                'address' => $wallet['address']
            );
        }

        return array(
            'success' => false,
            'error' => isset($json['error']) ? $json['error'] : 'Unknown error'
        );
    }

    /**
     * Hole Wallet Address für User
     */
    public function get_user_wallet_address($user_id) {
        $wallet = $this->get_user_wallet($user_id);
        return $wallet ? $wallet['address'] : null;
    }
}
