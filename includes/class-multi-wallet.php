<?php
/**
 * Multi-Wallet Support Class
 *
 * Allows users to have multiple wallets
 *
 * @package Sui_User_Wallets
 */

if (!defined('ABSPATH')) exit;

class SUW_Multi_Wallet {

    /**
     * Create additional wallet for user
     */
    public function create_additional_wallet($user_id, $wallet_name = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        // Check if table supports multiple wallets
        // Note: Current schema has UNIQUE constraint on user_id
        // This would need schema migration to support multiple wallets

        $wallet_manager = new SUW_Wallet_Manager();
        // Implementation would call Vercel API to generate new wallet
        // Store with wallet_name to distinguish

        return new WP_Error('not_implemented', 'Multi-wallet support requires database schema update');
    }

    /**
     * Get all wallets for user
     */
    public function get_user_wallets($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        // Current implementation only supports one wallet
        // With schema update, would return array of wallets

        $wallet = (new SUW_Wallet_Manager())->get_user_wallet($user_id);
        return $wallet ? array($wallet) : array();
    }

    /**
     * Set primary wallet
     */
    public function set_primary_wallet($user_id, $wallet_id) {
        // Would update is_primary flag in database
        return new WP_Error('not_implemented', 'Multi-wallet support requires database schema update');
    }

    /**
     * Database schema update needed
     */
    public static function get_schema_update_sql() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        return "
        -- Remove UNIQUE constraint on user_id
        ALTER TABLE {$table_name} DROP INDEX user_id;

        -- Add new columns
        ALTER TABLE {$table_name}
        ADD COLUMN wallet_name VARCHAR(100) DEFAULT 'Primary Wallet',
        ADD COLUMN is_primary BOOLEAN DEFAULT 0,
        ADD COLUMN wallet_purpose VARCHAR(50) DEFAULT 'general';

        -- Add new composite unique index
        ALTER TABLE {$table_name}
        ADD UNIQUE KEY user_wallet (user_id, wallet_name);

        -- Add index for primary wallet lookups
        ALTER TABLE {$table_name}
        ADD INDEX idx_primary (user_id, is_primary);
        ";
    }
}
