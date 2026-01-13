<?php
/**
 * Table Creation Helper
 *
 * ANLEITUNG:
 * 1. Lade diese Datei in dein WordPress-Root-Verzeichnis hoch (neben wp-config.php)
 * 2. Rufe auf: https://deine-domain.de/fix-table.php
 * 3. Lösche die Datei nach erfolgreicher Ausführung!
 */

// WordPress laden
require_once('wp-load.php');

// Prüfe Admin-Berechtigung
if (!current_user_can('manage_options')) {
    die('Keine Berechtigung!');
}

global $wpdb;

echo "<h1>Sui User Wallets - Tabelle erstellen</h1>";

$table_name = $wpdb->prefix . 'sui_user_wallets';
$charset_collate = $wpdb->get_charset_collate();

echo "<p><strong>Tabelle:</strong> $table_name</p>";

// Prüfe ob Tabelle existiert
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");

if ($table_exists) {
    echo "<p style='color: green;'>✅ Tabelle existiert bereits!</p>";

    // Zeige Struktur
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    echo "<h2>Aktuelle Struktur:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Feld</th><th>Typ</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col->Field}</td>";
        echo "<td>{$col->Type}</td>";
        echo "<td>{$col->Null}</td>";
        echo "<td>{$col->Key}</td>";
        echo "<td>{$col->Default}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Zeige Einträge
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "<p><strong>Einträge:</strong> $count</p>";

} else {
    echo "<p style='color: red;'>❌ Tabelle existiert NICHT!</p>";
    echo "<p>Erstelle Tabelle...</p>";

    $sql = "CREATE TABLE $table_name (
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
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $result = dbDelta($sql);

    echo "<h2>Ergebnis:</h2>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";

    // Prüfe erneut
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if ($table_exists) {
        echo "<p style='color: green; font-weight: bold;'>✅ Tabelle erfolgreich erstellt!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Fehler beim Erstellen!</p>";
        echo "<p>Versuche manuelle SQL-Erstellung:</p>";
        echo "<pre>$sql</pre>";
    }
}

echo "<hr>";
echo "<h2>Nächste Schritte:</h2>";
echo "<ol>";
echo "<li>Lösche diese Datei (fix-table.php) aus Sicherheitsgründen!</li>";
echo "<li>Erstelle einen neuen Test-User in WordPress</li>";
echo "<li>Prüfe debug.log ob Wallet erstellt wurde</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='" . admin_url('tools.php?page=debug-log-viewer') . "'>→ Zum Debug Log</a></p>";
echo "<p><a href='" . admin_url('users.php') . "'>→ Zu den Benutzern</a></p>";
