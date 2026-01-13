<?php
/**
 * Plugin Name: Debug Helper - View Logs
 * Description: Zeigt debug.log direkt im WordPress Admin (falls kein FTP-Zugriff vorhanden)
 * Version: 1.0.0
 * Author: utakapp
 */

if (!defined('ABSPATH')) exit;

class Debug_Helper {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
    }

    public function add_menu() {
        add_management_page(
            'Debug Log',
            'Debug Log',
            'manage_options',
            'debug-log-viewer',
            array($this, 'show_log_page')
        );
    }

    public function show_log_page() {
        ?>
        <div class="wrap">
            <h1>WordPress Debug Log</h1>

            <?php
            $log_file = WP_CONTENT_DIR . '/debug.log';

            if (!file_exists($log_file)) {
                echo '<div class="notice notice-warning"><p>';
                echo '<strong>Debug-Log existiert noch nicht.</strong><br>';
                echo 'Aktivieren Sie WP_DEBUG_LOG in wp-config.php<br>';
                echo 'Log wird erstellt sobald der erste Fehler auftritt.';
                echo '</p></div>';

                $this->show_config_instructions();
                return;
            }

            // File Size
            $size = filesize($log_file);
            $size_mb = round($size / 1024 / 1024, 2);

            echo '<p><strong>Datei:</strong> ' . $log_file . '</p>';
            echo '<p><strong>Größe:</strong> ' . $size_mb . ' MB</p>';

            // Clear Button
            if (isset($_GET['clear']) && $_GET['clear'] === '1' && wp_verify_nonce($_GET['_wpnonce'], 'clear_log')) {
                file_put_contents($log_file, '');
                echo '<div class="notice notice-success"><p>Debug-Log wurde geleert!</p></div>';
                $content = '';
            } else {
                // Read last 500 lines
                $lines = $this->tail($log_file, 500);
                $content = implode("\n", $lines);
            }

            $clear_url = wp_nonce_url(
                add_query_arg('clear', '1', admin_url('tools.php?page=debug-log-viewer')),
                'clear_log'
            );

            ?>

            <p>
                <a href="<?php echo admin_url('tools.php?page=debug-log-viewer'); ?>" class="button">Aktualisieren</a>
                <a href="<?php echo $clear_url; ?>" class="button" onclick="return confirm('Log wirklich leeren?')">Log leeren</a>
            </p>

            <div style="background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 4px; max-height: 600px; overflow-y: scroll; font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.5;">
                <?php if (empty($content)): ?>
                    <em style="color: #888;">Log ist leer</em>
                <?php else: ?>
                    <?php echo $this->format_log($content); ?>
                <?php endif; ?>
            </div>

            <p style="margin-top: 20px;">
                <strong>Tipp:</strong> Diese Seite zeigt die letzten 500 Zeilen.
                Für vollständigen Log: Download via FTP von <code>/wp-content/debug.log</code>
            </p>

            <?php $this->show_wallet_debug_info(); ?>
        </div>
        <?php
    }

    private function tail($file, $lines = 100) {
        $handle = fopen($file, "r");
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = array();

        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) break;
        }
        fclose($handle);
        return array_reverse($text);
    }

    private function format_log($content) {
        // Escape HTML
        $content = esc_html($content);

        // Colorize errors
        $content = preg_replace(
            '/(PHP Fatal error|Fatal error|PHP Warning|Warning|PHP Notice|Notice)/i',
            '<span style="color: #f48771; font-weight: bold;">$1</span>',
            $content
        );

        // Colorize success
        $content = preg_replace(
            '/(Successfully|Success|✅|✓)/i',
            '<span style="color: #4ec9b0;">$1</span>',
            $content
        );

        // Colorize [SUW] prefix
        $content = preg_replace(
            '/(\[SUW[^\]]*\])/i',
            '<span style="color: #dcdcaa;">$1</span>',
            $content
        );

        // Colorize timestamps
        $content = preg_replace(
            '/(\[\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2} [A-Z]+\])/i',
            '<span style="color: #858585;">$1</span>',
            $content
        );

        return nl2br($content);
    }

    private function show_config_instructions() {
        ?>
        <div class="notice notice-info">
            <h3>🔧 Debug-Modus aktivieren</h3>
            <p><strong>Öffne wp-config.php und füge hinzu:</strong></p>
            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );</pre>

            <p><strong>Via FTP/SFTP:</strong></p>
            <ol>
                <li>Verbinde zu deinem Server</li>
                <li>Öffne <code>wp-config.php</code> (im WordPress Root)</li>
                <li>Suche: <code>define( 'WP_DEBUG', false );</code></li>
                <li>Ersetze mit dem Code oben</li>
                <li>Speichern</li>
            </ol>

            <p><strong>Dann:</strong> Lade diese Seite neu. Das Log wird dann angezeigt.</p>
        </div>
        <?php
    }

    private function show_wallet_debug_info() {
        if (!class_exists('SUW_Wallet_Manager')) {
            return;
        }
        ?>
        <hr style="margin: 40px 0;">
        <h2>🔍 Wallet Plugin Debug Info</h2>

        <table class="widefat">
            <tr>
                <th>Plugin Version</th>
                <td><?php echo defined('SUW_VERSION') ? SUW_VERSION : 'N/A'; ?></td>
            </tr>
            <tr>
                <th>Vercel API URL</th>
                <td><?php echo esc_html(get_option('suw_vercel_api_url', 'Nicht konfiguriert')); ?></td>
            </tr>
            <tr>
                <th>Auto-Create aktiv</th>
                <td><?php echo get_option('suw_auto_create_on_registration') === '1' ? '✅ Ja' : '❌ Nein'; ?></td>
            </tr>
            <tr>
                <th>Verschlüsselung aktiv</th>
                <td><?php echo get_option('suw_encryption_enabled') === '1' ? '✅ Ja' : '❌ Nein'; ?></td>
            </tr>
            <tr>
                <th>Wallets in DB</th>
                <td>
                    <?php
                    global $wpdb;
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}sui_user_wallets");
                    echo $count ? $count : '0';
                    ?>
                </td>
            </tr>
            <tr>
                <th>PHP Version</th>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <th>OpenSSL Extension</th>
                <td><?php echo extension_loaded('openssl') ? '✅ Geladen' : '❌ Nicht verfügbar'; ?></td>
            </tr>
        </table>

        <h3>Test Wallet-Erstellung</h3>
        <p>
            <a href="<?php echo admin_url('user-new.php'); ?>" class="button button-primary">
                Test-User erstellen
            </a>
            <span style="margin-left: 10px;">→ Dann diese Seite neu laden um Log zu sehen</span>
        </p>
        <?php
    }
}

new Debug_Helper();
