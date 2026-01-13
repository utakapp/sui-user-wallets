-- Sui User Wallets - Datenbanktabelle erstellen
-- Führe dieses SQL in phpMyAdmin oder via SSH/MySQL aus

CREATE TABLE IF NOT EXISTS zabl_sui_user_wallets (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    wallet_address varchar(66) NOT NULL,
    encrypted_private_key text NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    last_balance_check datetime DEFAULT NULL,
    cached_balance varchar(50) DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY user_id (user_id),
    UNIQUE KEY wallet_address (wallet_address)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
