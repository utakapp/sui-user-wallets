<?php
/**
 * Force Update Check
 *
 * ANLEITUNG:
 * 1. Lade diese Datei ins WordPress-Root-Verzeichnis hoch (neben wp-config.php)
 * 2. Rufe auf: https://deine-domain.de/force-update-check.php
 * 3. Lösche die Datei nach erfolgreicher Ausführung!
 */

// WordPress laden
require_once('wp-load.php');

// Prüfe Admin-Berechtigung
if (!current_user_can('manage_options')) {
    die('Keine Berechtigung!');
}

echo "<h1>Force Update Check - Sui User Wallets</h1>";

// 1. Aktuelle Version prüfen
if (defined('SUW_VERSION')) {
    echo "<p><strong>Aktuell installierte Version:</strong> " . SUW_VERSION . "</p>";
} else {
    echo "<p style='color: orange;'><strong>Plugin nicht geladen!</strong> Ist es aktiviert?</p>";
}

// 2. Update-Cache löschen
echo "<h2>1. Update-Cache löschen</h2>";
$deleted = delete_transient('suw_github_update_check');
if ($deleted) {
    echo "<p style='color: green;'>✅ Cache erfolgreich gelöscht!</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Cache existierte nicht (oder war bereits gelöscht)</p>";
}

// 3. WordPress Update-Cache löschen
echo "<h2>2. WordPress Update-Cache löschen</h2>";
delete_site_transient('update_plugins');
echo "<p style='color: green;'>✅ WordPress Update-Cache gelöscht!</p>";

// 4. Manuell GitHub prüfen
echo "<h2>3. GitHub Release prüfen</h2>";
$api_url = "https://api.github.com/repos/utakapp/sui-user-wallets/releases/latest";
$response = wp_remote_get($api_url, array(
    'timeout' => 10,
    'headers' => array(
        'Accept' => 'application/vnd.github.v3+json',
    )
));

if (is_wp_error($response)) {
    echo "<p style='color: red;'>❌ Fehler: " . $response->get_error_message() . "</p>";
} else {
    $release = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($release['tag_name'])) {
        $latest_version = ltrim($release['tag_name'], 'v');
        echo "<p><strong>Neueste Version auf GitHub:</strong> " . $latest_version . "</p>";

        if (defined('SUW_VERSION')) {
            if (version_compare(SUW_VERSION, $latest_version, '<')) {
                echo "<p style='color: green; font-weight: bold;'>✅ Update verfügbar: " . SUW_VERSION . " → " . $latest_version . "</p>";
            } else if (version_compare(SUW_VERSION, $latest_version, '=')) {
                echo "<p style='color: blue;'>✅ Du hast bereits die neueste Version!</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Deine Version ist neuer als GitHub?</p>";
            }
        }

        // Zeige Assets
        if (!empty($release['assets'])) {
            echo "<h3>Verfügbare Downloads:</h3>";
            echo "<ul>";
            foreach ($release['assets'] as $asset) {
                echo "<li>";
                echo "<strong>" . esc_html($asset['name']) . "</strong> ";
                echo "(" . round($asset['size'] / 1024, 2) . " KB) - ";
                echo "<a href='" . esc_url($asset['browser_download_url']) . "' target='_blank'>Download</a>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ Keine ZIP-Assets gefunden in Release</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Keine Release-Daten empfangen</p>";
        echo "<pre>" . print_r($release, true) . "</pre>";
    }
}

// 5. WordPress Plugin Updater manuell triggern
echo "<h2>4. WordPress Plugin Update Checker triggern</h2>";
wp_update_plugins();
echo "<p style='color: green;'>✅ WordPress Plugin Update Checker wurde getriggert!</p>";

echo "<hr>";
echo "<h2>Nächste Schritte:</h2>";
echo "<ol>";
echo "<li><strong>Lösche diese Datei!</strong> (force-update-check.php)</li>";
echo "<li>Gehe zu: <a href='" . admin_url('plugins.php') . "'>WordPress Admin → Plugins</a></li>";
echo "<li>Suche nach 'Sui User Wallets'</li>";
echo "<li>Du solltest jetzt sehen: <strong>'Update verfügbar'</strong></li>";
echo "<li>Klicke <strong>'Update Now'</strong></li>";
echo "<li>Nach Update solltest du die grüne 🎉 Erfolgsmeldung sehen</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='" . admin_url('plugins.php') . "' class='button button-primary'>→ Zu den Plugins</a></p>";
