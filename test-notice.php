<?php
/**
 * Test v1.0.4 Success Notice
 *
 * ANLEITUNG:
 * 1. Lade diese Datei ins WordPress-Root hoch
 * 2. Rufe auf: https://deine-domain.de/test-notice.php
 * 3. Lösche die Datei danach
 */

require_once('wp-load.php');

if (!current_user_can('manage_options')) {
    die('Keine Berechtigung!');
}

echo "<h1>Test v1.0.4 Success Notice</h1>";

// Prüfe aktuelle Version
if (defined('SUW_VERSION')) {
    echo "<p><strong>Installierte Version:</strong> " . SUW_VERSION . "</p>";
} else {
    echo "<p style='color: red;'>❌ SUW_VERSION nicht definiert - Plugin nicht geladen?</p>";
    die();
}

// Prüfe ob Notice bereits dismissed wurde
$dismissed = get_option('suw_v104_notice_dismissed', false);
echo "<p><strong>Notice dismissed?</strong> " . ($dismissed ? 'Ja' : 'Nein') . "</p>";

// Reset die Option damit Notice wieder angezeigt wird
if ($dismissed) {
    echo "<h2>Notice wurde bereits dismissed</h2>";
    echo "<p>Setze zurück...</p>";
    delete_option('suw_v104_notice_dismissed');
    echo "<p style='color: green;'>✅ Option zurückgesetzt!</p>";
}

echo "<hr>";
echo "<h2>Nächste Schritte:</h2>";
echo "<ol>";
echo "<li>Lösche diese Datei (test-notice.php)</li>";
echo "<li>Gehe zu: <a href='" . admin_url() . "'>WordPress Dashboard</a></li>";
echo "<li>Du solltest jetzt die grüne 🎉 Erfolgsmeldung sehen</li>";
echo "</ol>";

echo "<p><a href='" . admin_url() . "' class='button button-primary'>→ Zum Dashboard</a></p>";
