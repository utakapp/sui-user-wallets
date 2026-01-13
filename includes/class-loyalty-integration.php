<?php
/**
 * Integration mit Sui Course Loyalty Plugin
 *
 * Automatische Verwendung der User-Wallet für Badge-Erstellung
 */

if (!defined('ABSPATH')) {
    exit;
}

class SUW_Loyalty_Integration {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Prüfe ob Loyalty Plugin aktiv ist
        if (!$this->is_loyalty_plugin_active()) {
            return;
        }

        // Hooks für automatische Wallet-Integration
        add_filter('scl_badge_request_wallet_address', array($this, 'auto_populate_wallet_address'), 10, 2);
        add_filter('scl_get_user_wallet_address', array($this, 'get_user_wallet_address'), 10, 1);
        add_action('scl_badge_request_created', array($this, 'ensure_user_has_wallet'), 5, 1);

        // Hook für automatische Wallet-Erstellung bei Badge-Request
        add_action('pmpro_after_checkout', array($this, 'ensure_wallet_on_purchase'), 5, 2);
    }

    /**
     * Prüfe ob Loyalty Plugin aktiv ist
     */
    private function is_loyalty_plugin_active() {
        return class_exists('Sui_Course_Loyalty');
    }

    /**
     * Auto-Populate Wallet Address für Badge Requests
     */
    public function auto_populate_wallet_address($wallet_address, $user_id) {
        // Wenn bereits eine Adresse vorhanden ist, nicht überschreiben
        if (!empty($wallet_address) && $this->is_valid_sui_address($wallet_address)) {
            return $wallet_address;
        }

        // Hole User Wallet
        $wallet_manager = new SUW_Wallet_Manager();
        $user_wallet = $wallet_manager->get_user_wallet_address($user_id);

        if ($user_wallet) {
            return $user_wallet;
        }

        // Keine Wallet gefunden - Log für Admin
        error_log('[SUW] No wallet found for user ' . $user_id . ' when creating badge request');

        return $wallet_address;
    }

    /**
     * Hole User Wallet Address
     */
    public function get_user_wallet_address($user_id) {
        $wallet_manager = new SUW_Wallet_Manager();
        return $wallet_manager->get_user_wallet_address($user_id);
    }

    /**
     * Stelle sicher, dass User eine Wallet hat bevor Badge erstellt wird
     */
    public function ensure_user_has_wallet($badge_request) {
        $user_id = isset($badge_request['user_id']) ? intval($badge_request['user_id']) : 0;

        if ($user_id <= 0) {
            return;
        }

        // Prüfe ob User bereits Wallet hat
        $wallet_manager = new SUW_Wallet_Manager();
        $wallet = $wallet_manager->get_user_wallet($user_id);

        if ($wallet) {
            // Wallet bereits vorhanden
            return;
        }

        // Erstelle Wallet automatisch
        error_log('[SUW] Auto-creating wallet for user ' . $user_id . ' (triggered by badge request)');

        $result = $wallet_manager->create_wallet_for_user($user_id);

        if ($result['success']) {
            error_log('[SUW] Successfully created wallet: ' . $result['address']);

            // Update Badge Request mit neuer Wallet-Adresse
            if (isset($badge_request['request_id'])) {
                $this->update_badge_request_wallet($badge_request['request_id'], $result['address']);
            }
        } else {
            error_log('[SUW] Failed to create wallet: ' . $result['error']);
        }
    }

    /**
     * Ensure Wallet on PMPro Purchase
     */
    public function ensure_wallet_on_purchase($user_id, $order) {
        if ($user_id <= 0) {
            return;
        }

        // Prüfe ob User bereits Wallet hat
        $wallet_manager = new SUW_Wallet_Manager();
        $wallet = $wallet_manager->get_user_wallet($user_id);

        if ($wallet) {
            return;
        }

        // Erstelle Wallet
        error_log('[SUW] Auto-creating wallet for user ' . $user_id . ' (triggered by PMPro purchase)');

        $result = $wallet_manager->create_wallet_for_user($user_id);

        if ($result['success']) {
            error_log('[SUW] Successfully created wallet: ' . $result['address']);
        } else {
            error_log('[SUW] Failed to create wallet: ' . $result['error']);
        }
    }

    /**
     * Update Badge Request mit Wallet-Adresse
     */
    private function update_badge_request_wallet($request_id, $wallet_address) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_badge_requests';

        $wpdb->update(
            $table_name,
            array('wallet_address' => $wallet_address),
            array('id' => $request_id)
        );
    }

    /**
     * Validiere Sui Address
     */
    private function is_valid_sui_address($address) {
        return preg_match('/^0x[a-fA-F0-9]{64}$/', $address);
    }
}

// Initialisieren
add_action('plugins_loaded', array('SUW_Loyalty_Integration', 'get_instance'), 20);
