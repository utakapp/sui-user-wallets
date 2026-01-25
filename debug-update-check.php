<?php
/**
 * Debug Script für Auto-Updater
 *
 * Lade diese Datei in WordPress hoch und rufe sie im Browser auf:
 * https://deine-domain.com/wp-content/plugins/sui-user-wallets/debug-update-check.php
 */

// WordPress Bootstrap
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

// Sicherheitscheck
if (!current_user_can('manage_options')) {
    die('Keine Berechtigung');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sui User Wallets - Update Debug</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; background: #f0f0f1; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { color: #1d2327; border-bottom: 2px solid #2271b1; padding-bottom: 10px; }
        h2 { color: #2271b1; margin-top: 30px; }
        .info-box { background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0; }
        .success { background: #d1f0d1; border-left-color: #00a32a; }
        .warning { background: #fcf3cf; border-left-color: #dba617; }
        .error { background: #fcdddd; border-left-color: #d63638; }
        code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f0f0f1; font-weight: 600; }
        .btn { background: #2271b1; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #135e96; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Sui User Wallets - Auto-Update Debug</h1>

        <?php
        // Aktuelle Version
        $current_version = defined('SUW_VERSION') ? SUW_VERSION : 'Unbekannt';
        echo '<div class="info-box">';
        echo '<strong>Installierte Version:</strong> ' . esc_html($current_version);
        echo '</div>';

        // GitHub API Abfrage
        echo '<h2>1. GitHub API Test</h2>';
        $api_url = 'https://api.github.com/repos/utakapp/sui-user-wallets/releases/latest';

        $response = wp_remote_get($api_url, array(
            'timeout' => 10,
            'headers' => array('Accept' => 'application/vnd.github.v3+json')
        ));

        if (is_wp_error($response)) {
            echo '<div class="info-box error">';
            echo '<strong>❌ GitHub API Fehler:</strong> ' . esc_html($response->get_error_message());
            echo '</div>';
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $release = json_decode($body, true);

            echo '<div class="info-box success">';
            echo '<strong>✅ GitHub API Status:</strong> ' . $status_code;
            echo '</div>';

            if (!empty($release)) {
                $latest_version = ltrim($release['tag_name'] ?? '', 'v');
                $published_at = $release['published_at'] ?? '';
                $assets = $release['assets'] ?? array();

                echo '<table>';
                echo '<tr><th>Latest Version (GitHub)</th><td>' . esc_html($latest_version) . '</td></tr>';
                echo '<tr><th>Published</th><td>' . esc_html($published_at) . '</td></tr>';
                echo '<tr><th>Assets</th><td>' . count($assets) . '</td></tr>';

                if (!empty($assets)) {
                    foreach ($assets as $asset) {
                        echo '<tr><td colspan="2">';
                        echo '📦 <strong>' . esc_html($asset['name']) . '</strong><br>';
                        echo '<code>' . esc_html($asset['browser_download_url']) . '</code>';
                        echo '</td></tr>';
                    }
                }
                echo '</table>';

                // Version Vergleich
                echo '<h2>2. Version Vergleich</h2>';
                $needs_update = version_compare($current_version, $latest_version, '<');

                if ($needs_update) {
                    echo '<div class="info-box warning">';
                    echo '<strong>⚠️ Update verfügbar!</strong><br>';
                    echo 'Installiert: <code>' . esc_html($current_version) . '</code><br>';
                    echo 'Verfügbar: <code>' . esc_html($latest_version) . '</code>';
                    echo '</div>';
                } else {
                    echo '<div class="info-box success">';
                    echo '<strong>✅ Plugin ist aktuell!</strong><br>';
                    echo 'Version: <code>' . esc_html($current_version) . '</code>';
                    echo '</div>';
                }
            }
        }

        // Cache Status
        echo '<h2>3. Cache Status</h2>';
        $cache_key = 'suw_github_update';
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            echo '<div class="info-box warning">';
            echo '<strong>⚠️ Cache gefunden</strong><br>';
            echo '<pre>' . print_r($cached, true) . '</pre>';
            echo '<p><a href="?clear_cache=1" class="btn">Cache löschen</a></p>';
            echo '</div>';
        } else {
            echo '<div class="info-box success">';
            echo '<strong>✅ Kein Cache</strong> - API wird bei nächstem Check abgefragt';
            echo '</div>';
        }

        // Cache löschen
        if (isset($_GET['clear_cache'])) {
            delete_transient($cache_key);
            delete_site_transient('update_plugins');
            echo '<div class="info-box success">';
            echo '<strong>✅ Cache gelöscht!</strong> <a href="?">Seite neu laden</a>';
            echo '</div>';
        }

        // WordPress Update Transient
        echo '<h2>4. WordPress Update Check</h2>';
        $update_plugins = get_site_transient('update_plugins');

        if (!empty($update_plugins->response)) {
            $plugin_basename = 'sui-user-wallets/sui-user-wallets.php';

            if (isset($update_plugins->response[$plugin_basename])) {
                echo '<div class="info-box warning">';
                echo '<strong>⚠️ Update in WordPress erkannt!</strong><br>';
                echo '<pre>' . print_r($update_plugins->response[$plugin_basename], true) . '</pre>';
                echo '</div>';
            } else {
                echo '<div class="info-box">';
                echo '<strong>ℹ️ Kein Update in WordPress erkannt</strong><br>';
                echo 'Plugin Basename: <code>' . $plugin_basename . '</code>';
                echo '</div>';
            }
        }

        // Aktionen
        echo '<h2>5. Aktionen</h2>';
        echo '<p><a href="' . admin_url('plugins.php') . '" class="btn">Zu Plugins</a> ';
        echo '<a href="?clear_cache=1" class="btn">Cache löschen</a></p>';
        ?>

        <h2>6. Manuelle Lösung</h2>
        <div class="info-box">
            <p><strong>Falls Auto-Update nicht funktioniert:</strong></p>
            <ol>
                <li>Download: <a href="https://github.com/utakapp/sui-user-wallets/releases/download/v1.0.11/sui-user-wallets-v1.0.11.zip" target="_blank">sui-user-wallets-v1.0.11.zip</a></li>
                <li>WordPress → Plugins → Installieren → Plugin hochladen</li>
                <li>ZIP auswählen und installieren</li>
            </ol>
        </div>
    </div>
</body>
</html>
