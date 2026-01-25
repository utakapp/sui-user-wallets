<?php
/**
 * Plugin Name: Sui User Wallets
 * Plugin URI: https://github.com/utakapp/sui-user-wallets
 * Description: Automatische Sui Wallet-Verwaltung für WordPress User - Custodial Wallets
 * Version: 1.0.13
 * Author: utakapp
 * Author URI: https://github.com/utakapp
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sui-user-wallets
 * Domain Path: /languages
 *
 * GitHub Plugin URI: utakapp/sui-user-wallets
 * GitHub Branch: main
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Verhindere direkten Zugriff
if (!defined('ABSPATH')) {
    exit;
}

// Plugin Konstanten (mit Guards gegen Doppel-Definition)
if (!defined('SUW_VERSION')) {
    define('SUW_VERSION', '1.0.13');
}
if (!defined('SUW_PLUGIN_DIR')) {
    define('SUW_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('SUW_PLUGIN_URL')) {
    define('SUW_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Haupt-Plugin-Klasse (mit Guard gegen Doppel-Definition)
if (!class_exists('Sui_User_Wallets')) {

class Sui_User_Wallets {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Aktivierung & Deaktivierung
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Lade Klassen
        $this->load_classes();

        // Hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'check_database_table'));
        add_action('show_user_profile', array($this, 'show_user_wallet_fields'));
        add_action('edit_user_profile', array($this, 'show_user_wallet_fields'));
        add_action('user_register', array($this, 'auto_create_wallet_on_registration'));
        add_action('delete_user', array($this, 'delete_user_wallet'));

        // AJAX
        add_action('wp_ajax_suw_create_wallet', array($this, 'ajax_create_wallet'));
        add_action('wp_ajax_suw_export_private_key', array($this, 'ajax_export_private_key'));
        add_action('wp_ajax_suw_get_wallet_balance', array($this, 'ajax_get_wallet_balance'));
        add_action('wp_ajax_suw_fix_database_table', array($this, 'ajax_fix_database_table'));
        add_action('wp_ajax_suw_dismiss_v108_notice', array($this, 'ajax_dismiss_v108_notice'));
        add_action('wp_ajax_suw_bulk_balance_check', array($this, 'ajax_bulk_balance_check'));

        // Admin Post Handlers
        add_action('admin_post_suw_bulk_export_csv', array($this, 'handle_bulk_export_csv'));
        add_action('admin_post_suw_bulk_export_keys', array($this, 'handle_bulk_export_keys'));
        add_action('admin_post_suw_download_backup', array($this, 'handle_download_backup'));

        // Shortcodes
        add_shortcode('sui_user_wallet', array($this, 'wallet_shortcode'));
    }

    // Klassen laden
    private function load_classes() {
        require_once SUW_PLUGIN_DIR . 'includes/class-wallet-manager.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-wallet-crypto.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-loyalty-integration.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-auto-updater.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-dashboard.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-bulk-operations.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-email-notifications.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-transaction-history.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-multi-wallet.php';
        require_once SUW_PLUGIN_DIR . 'includes/class-backup-restore.php';
    }

    // Plugin Aktivierung
    public function activate() {
        $this->create_tables();
        $this->set_default_options();
    }

    // Plugin Deaktivierung
    public function deactivate() {
        // Cleanup falls nötig
    }

    // Erstelle DB Tabellen
    private function create_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
        dbDelta($sql);
    }

    // Prüfe ob Datenbanktabelle existiert
    private function table_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';
        $result = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        return ($result === $table_name);
    }

    // Admin Notice wenn Tabelle fehlt
    public function check_database_table() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Success Notice für Auto-Update
        $dismissed = get_option('suw_v108_notice_dismissed', false);
        if (!$dismissed && version_compare(SUW_VERSION, '1.0.8', '>=')) {
            ?>
            <div class="notice notice-success is-dismissible" data-dismissible="suw-v108-notice">
                <p>
                    <strong>🎉 Sui User Wallets v1.0.8:</strong>
                    Große Erweiterung mit 10 neuen Features! Dashboard, Bulk Operations, Backup/Restore, Email Notifications, Transaction History, Multi-Wallet Support, PHPUnit Tests und mehr.
                    <a href="https://github.com/utakapp/sui-user-wallets/releases/tag/v1.0.8" target="_blank">Release Notes</a>
                </p>
            </div>
            <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '[data-dismissible="suw-v108-notice"] .notice-dismiss', function() {
                    $.post(ajaxurl, {
                        action: 'suw_dismiss_v108_notice'
                    });
                });
            });
            </script>
            <?php
        }

        if ($this->table_exists()) {
            return;
        }

        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong>Sui User Wallets:</strong> Datenbanktabelle fehlt!
                Das Plugin kann keine Wallets speichern.
            </p>
            <p>
                <button type="button" class="button button-primary" id="suw-fix-database">
                    Jetzt reparieren
                </button>
                <span id="suw-fix-status" style="margin-left: 10px;"></span>
            </p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#suw-fix-database').on('click', function() {
                var $btn = $(this);
                var $status = $('#suw-fix-status');

                $btn.prop('disabled', true).text('Repariere...');
                $status.html('<span style="color: #999;">⏳ Erstelle Tabelle...</span>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'suw_fix_database_table',
                        _wpnonce: '<?php echo wp_create_nonce('suw_fix_database'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('<span style="color: green;">✅ ' + response.data.message + '</span>');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $status.html('<span style="color: red;">❌ ' + response.data.message + '</span>');
                            $btn.prop('disabled', false).text('Erneut versuchen');
                        }
                    },
                    error: function() {
                        $status.html('<span style="color: red;">❌ AJAX Fehler</span>');
                        $btn.prop('disabled', false).text('Erneut versuchen');
                    }
                });
            });
        });
        </script>
        <?php
    }

    // AJAX: Datenbanktabelle erstellen
    public function ajax_fix_database_table() {
        check_ajax_referer('suw_fix_database');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Keine Berechtigung'));
        }

        // Prüfe ob Tabelle bereits existiert
        if ($this->table_exists()) {
            wp_send_json_success(array('message' => 'Tabelle existiert bereits!'));
        }

        // Erstelle Tabelle
        $this->create_tables();

        // Prüfe erneut
        if ($this->table_exists()) {
            wp_send_json_success(array('message' => 'Tabelle erfolgreich erstellt!'));
        } else {
            wp_send_json_error(array('message' => 'Fehler beim Erstellen. Prüfe Datenbank-Berechtigungen.'));
        }
    }

    // AJAX: v1.0.8 Notice dismissal
    public function ajax_dismiss_v108_notice() {
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        update_option('suw_v108_notice_dismissed', true);
        wp_die();
    }

    // Setze Default-Optionen
    private function set_default_options() {
        add_option('suw_auto_create_on_registration', '1');
        add_option('suw_allow_private_key_export', '1');
        add_option('suw_encryption_enabled', '1');
        add_option('suw_network', 'testnet');
    }

    // Admin Menü
    public function add_admin_menu() {
        add_menu_page(
            'Sui User Wallets',
            'User Wallets',
            'manage_options',
            'sui-user-wallets',
            array($this, 'settings_page'),
            'dashicons-vault',
            31
        );

        add_submenu_page(
            'sui-user-wallets',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'sui-dashboard',
            array($this, 'dashboard_page')
        );

        add_submenu_page(
            'sui-user-wallets',
            'All Wallets',
            'All Wallets',
            'manage_options',
            'sui-all-wallets',
            array($this, 'all_wallets_page')
        );

        add_submenu_page(
            'sui-user-wallets',
            'Bulk Operations',
            'Bulk Operations',
            'manage_options',
            'sui-bulk-operations',
            array($this, 'bulk_operations_page')
        );

        add_submenu_page(
            'sui-user-wallets',
            'Backup & Restore',
            'Backup & Restore',
            'manage_options',
            'sui-backup-restore',
            array($this, 'backup_restore_page')
        );
    }

    // Einstellungen registrieren
    public function register_settings() {
        register_setting('suw_settings', 'suw_auto_create_on_registration');
        register_setting('suw_settings', 'suw_allow_private_key_export');
        register_setting('suw_settings', 'suw_encryption_enabled');
        register_setting('suw_settings', 'suw_network');
        register_setting('suw_settings', 'suw_vercel_api_url');
        register_setting('suw_settings', 'suw_vercel_api_key');
    }

    // Einstellungsseite
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Sui User Wallets - Einstellungen</h1>

            <form method="post" action="options.php">
                <?php settings_fields('suw_settings'); ?>

                <h2>Wallet-Erstellung</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Automatische Wallet-Erstellung</th>
                        <td>
                            <label>
                                <input type="checkbox" name="suw_auto_create_on_registration" value="1"
                                       <?php checked(get_option('suw_auto_create_on_registration'), '1'); ?> />
                                Automatisch Wallet für neue User erstellen
                            </label>
                            <p class="description">Bei User-Registration wird sofort eine Sui Wallet erstellt</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Netzwerk</th>
                        <td>
                            <select name="suw_network">
                                <option value="testnet" <?php selected(get_option('suw_network'), 'testnet'); ?>>Testnet</option>
                                <option value="mainnet" <?php selected(get_option('suw_network'), 'mainnet'); ?>>Mainnet</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>Sicherheit</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Private Key Verschlüsselung</th>
                        <td>
                            <label>
                                <input type="checkbox" name="suw_encryption_enabled" value="1"
                                       <?php checked(get_option('suw_encryption_enabled'), '1'); ?> />
                                Private Keys verschlüsselt speichern (Empfohlen)
                            </label>
                            <p class="description">Private Keys werden mit WordPress Auth-Key verschlüsselt</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Private Key Export</th>
                        <td>
                            <label>
                                <input type="checkbox" name="suw_allow_private_key_export" value="1"
                                       <?php checked(get_option('suw_allow_private_key_export'), '1'); ?> />
                                User dürfen Private Keys exportieren
                            </label>
                            <p class="description">User können ihre Private Keys sehen und exportieren</p>
                        </td>
                    </tr>
                </table>

                <h2>API Integration (Optional)</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Vercel API URL</th>
                        <td>
                            <input type="url" name="suw_vercel_api_url"
                                   value="<?php echo esc_attr(get_option('suw_vercel_api_url', '')); ?>"
                                   class="regular-text" placeholder="https://your-project.vercel.app" />
                            <p class="description">Für Balance-Checks und Transaktionen</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">API Secret Key</th>
                        <td>
                            <input type="password" name="suw_vercel_api_key"
                                   value="<?php echo esc_attr(get_option('suw_vercel_api_key', '')); ?>"
                                   class="regular-text" />
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>

            <h2>Statistiken</h2>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'sui_user_wallets';
            $total_wallets = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
            $wallets_without_user = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users} u LEFT JOIN $table_name w ON u.ID = w.user_id WHERE w.id IS NULL");
            ?>
            <table class="widefat">
                <tr>
                    <th>Gesamt Users</th>
                    <td><?php echo $total_users; ?></td>
                </tr>
                <tr>
                    <th>Users mit Wallet</th>
                    <td><?php echo $total_wallets; ?></td>
                </tr>
                <tr>
                    <th>Users ohne Wallet</th>
                    <td><?php echo $wallets_without_user; ?></td>
                </tr>
            </table>

            <?php if ($wallets_without_user > 0): ?>
                <p>
                    <button type="button" class="button button-primary" id="suw-create-missing-wallets">
                        Wallets für alle existierenden Users erstellen
                    </button>
                </p>
                <script>
                jQuery(document).ready(function($) {
                    $('#suw-create-missing-wallets').on('click', function() {
                        if (!confirm('Möchten Sie wirklich für alle <?php echo $wallets_without_user; ?> Users Wallets erstellen?')) {
                            return;
                        }
                        // TODO: Bulk wallet creation via AJAX
                        alert('Bulk-Erstellung wird implementiert...');
                    });
                });
                </script>
            <?php endif; ?>
        </div>
        <?php
    }

    // Alle Wallets Seite
    public function all_wallets_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        $wallets = $wpdb->get_results("
            SELECT w.*, u.user_login, u.user_email
            FROM $table_name w
            LEFT JOIN {$wpdb->users} u ON w.user_id = u.ID
            ORDER BY w.created_at DESC
        ");
        ?>
        <div class="wrap">
            <h1>Alle User Wallets</h1>

            <table class="widefat">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Wallet Address</th>
                        <th>Balance</th>
                        <th>Erstellt</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wallets as $wallet): ?>
                    <tr>
                        <td><?php echo esc_html($wallet->user_login); ?></td>
                        <td><?php echo esc_html($wallet->user_email); ?></td>
                        <td><code><?php echo esc_html($wallet->wallet_address); ?></code></td>
                        <td><?php echo esc_html($wallet->cached_balance); ?> SUI</td>
                        <td><?php echo esc_html($wallet->created_at); ?></td>
                        <td>
                            <a href="<?php echo admin_url('user-edit.php?user_id=' . $wallet->user_id); ?>">Edit User</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Zeige Wallet-Felder im User-Profil
    public function show_user_wallet_fields($user) {
        $wallet_manager = new SUW_Wallet_Manager();
        $wallet = $wallet_manager->get_user_wallet($user->ID);
        ?>
        <h2>Sui Wallet</h2>
        <table class="form-table">
            <?php if ($wallet): ?>
                <tr>
                    <th>Wallet Address</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($wallet['address']); ?>"
                               id="suw-wallet-address-<?php echo $user->ID; ?>" class="regular-text" readonly />
                        <button type="button" class="button suw-copy-address" data-address="<?php echo esc_attr($wallet['address']); ?>" data-user-id="<?php echo $user->ID; ?>">
                            Copy
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Balance</th>
                    <td>
                        <span id="suw-balance-<?php echo $user->ID; ?>">
                            <?php echo esc_html($wallet['cached_balance']); ?> SUI
                        </span>
                        <button type="button" class="button" id="suw-refresh-balance-<?php echo $user->ID; ?>">
                            Refresh
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Erstellt am</th>
                    <td><?php echo esc_html($wallet['created_at']); ?></td>
                </tr>
                <?php if (get_option('suw_allow_private_key_export', '1') === '1'): ?>
                <tr>
                    <th>Private Key</th>
                    <td>
                        <button type="button" class="button button-secondary" id="suw-export-key-<?php echo $user->ID; ?>">
                            Private Key anzeigen
                        </button>
                        <div id="suw-private-key-<?php echo $user->ID; ?>" style="display:none; margin-top:10px;">
                            <textarea readonly class="regular-text" rows="3" style="width:100%;font-family:monospace;"></textarea>
                            <p class="description" style="color:red;">
                                ⚠️ NIEMALS Private Key teilen oder öffentlich zeigen!
                            </p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            <?php else: ?>
                <tr>
                    <th>Wallet Status</th>
                    <td>
                        <p>Keine Wallet vorhanden</p>
                        <button type="button" class="button button-primary" id="suw-create-wallet-<?php echo $user->ID; ?>">
                            Wallet erstellen
                        </button>
                    </td>
                </tr>
            <?php endif; ?>
        </table>

        <script>
        jQuery(document).ready(function($) {
            // Create Wallet
            $('#suw-create-wallet-<?php echo $user->ID; ?>').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).text('Erstelle Wallet...');

                $.post(ajaxurl, {
                    action: 'suw_create_wallet',
                    user_id: <?php echo $user->ID; ?>,
                    nonce: '<?php echo wp_create_nonce('suw_admin_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Fehler: ' + response.data);
                        btn.prop('disabled', false).text('Wallet erstellen');
                    }
                });
            });

            // Export Private Key
            $('#suw-export-key-<?php echo $user->ID; ?>').on('click', function() {
                if (!confirm('Private Key wirklich anzeigen? Dies ist sensibel!')) {
                    return;
                }

                var container = $('#suw-private-key-<?php echo $user->ID; ?>');
                container.show();

                $.post(ajaxurl, {
                    action: 'suw_export_private_key',
                    user_id: <?php echo $user->ID; ?>,
                    nonce: '<?php echo wp_create_nonce('suw_user_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        container.find('textarea').val(response.data.private_key);
                    } else {
                        alert('Fehler: ' + response.data);
                    }
                });
            });

            // Refresh Balance
            $('#suw-refresh-balance-<?php echo $user->ID; ?>').on('click', function() {
                var btn = $(this);
                var span = $('#suw-balance-<?php echo $user->ID; ?>');
                btn.prop('disabled', true).text('Loading...');

                $.post(ajaxurl, {
                    action: 'suw_get_wallet_balance',
                    user_id: <?php echo $user->ID; ?>,
                    nonce: '<?php echo wp_create_nonce('suw_admin_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        span.text(response.data.balance + ' SUI');
                    }
                    btn.prop('disabled', false).text('Refresh');
                });
            });

            // Copy Wallet Address
            $('.suw-copy-address').on('click', function() {
                var btn = $(this);
                var address = btn.data('address');
                var originalText = btn.text();

                // Try modern clipboard API
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(address).then(function() {
                        // Success feedback
                        btn.text('✓ Copied!').css('color', 'green');
                        setTimeout(function() {
                            btn.text(originalText).css('color', '');
                        }, 2000);
                    }).catch(function(err) {
                        // Fallback for errors
                        copyToClipboardFallback(address, btn, originalText);
                    });
                } else {
                    // Fallback for older browsers
                    copyToClipboardFallback(address, btn, originalText);
                }
            });

            // Fallback copy method
            function copyToClipboardFallback(text, btn, originalText) {
                var userId = btn.data('user-id');
                var input = $('#suw-wallet-address-' + userId);
                input.select();
                try {
                    var successful = document.execCommand('copy');
                    if (successful) {
                        btn.text('✓ Copied!').css('color', 'green');
                        setTimeout(function() {
                            btn.text(originalText).css('color', '');
                        }, 2000);
                    } else {
                        btn.text('✗ Failed').css('color', 'red');
                        setTimeout(function() {
                            btn.text(originalText).css('color', '');
                        }, 2000);
                    }
                } catch (err) {
                    btn.text('✗ Failed').css('color', 'red');
                    setTimeout(function() {
                        btn.text(originalText).css('color', '');
                    }, 2000);
                }
            }
        });
        </script>
        <?php
    }

    // Automatische Wallet-Erstellung bei Registration
    public function auto_create_wallet_on_registration($user_id) {
        if (get_option('suw_auto_create_on_registration', '1') !== '1') {
            return;
        }

        $wallet_manager = new SUW_Wallet_Manager();
        $result = $wallet_manager->create_wallet_for_user($user_id);

        if ($result['success']) {
            error_log('[Sui User Wallets] Auto-created wallet for user ' . $user_id . ': ' . $result['address']);

            // Send email notification
            $email_notifications = new SUW_Email_Notifications();
            $email_notifications->send_wallet_created_email($user_id, $result['address']);
        } else {
            error_log('[Sui User Wallets] Failed to auto-create wallet for user ' . $user_id . ': ' . $result['error']);
        }
    }

    // Automatische Wallet-Löschung bei User-Deletion
    public function delete_user_wallet($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        // Hole Wallet-Info für Logging
        $wallet = $wpdb->get_row($wpdb->prepare(
            "SELECT wallet_address FROM $table_name WHERE user_id = %d",
            $user_id
        ));

        if ($wallet) {
            // Lösche Wallet aus Datenbank
            $deleted = $wpdb->delete(
                $table_name,
                array('user_id' => $user_id),
                array('%d')
            );

            if ($deleted !== false) {
                error_log('[Sui User Wallets] Deleted wallet for user ' . $user_id . ': ' . $wallet->wallet_address);

                // Optional: Trigger action for other plugins to clean up
                do_action('suw_wallet_deleted', $user_id, $wallet->wallet_address);
            } else {
                error_log('[Sui User Wallets] Failed to delete wallet for user ' . $user_id);
            }
        } else {
            error_log('[Sui User Wallets] No wallet found for deleted user ' . $user_id);
        }
    }

    // AJAX: Erstelle Wallet
    public function ajax_create_wallet() {
        check_ajax_referer('suw_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $user_id = intval($_POST['user_id']);

        $wallet_manager = new SUW_Wallet_Manager();
        $result = $wallet_manager->create_wallet_for_user($user_id);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['error']);
        }
    }

    // AJAX: Exportiere Private Key
    public function ajax_export_private_key() {
        check_ajax_referer('suw_user_nonce', 'nonce');

        $user_id = intval($_POST['user_id']);
        $current_user_id = get_current_user_id();

        // Benutzer darf nur seinen eigenen Private Key sehen, oder Admin darf alle sehen
        if ($current_user_id !== $user_id && !current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung - Sie können nur Ihren eigenen Private Key sehen');
        }

        if (get_option('suw_allow_private_key_export', '1') !== '1') {
            wp_send_json_error('Private Key Export ist deaktiviert');
        }

        $wallet_manager = new SUW_Wallet_Manager();
        $result = $wallet_manager->export_private_key($user_id);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['error']);
        }
    }

    // AJAX: Get Wallet Balance
    public function ajax_get_wallet_balance() {
        check_ajax_referer('suw_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }

        $user_id = intval($_POST['user_id']);

        $wallet_manager = new SUW_Wallet_Manager();
        $result = $wallet_manager->get_wallet_balance($user_id);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['error']);
        }
    }

    // Shortcode: Zeige User Wallet
    public function wallet_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Bitte einloggen um Wallet zu sehen.</p>';
        }

        $user_id = get_current_user_id();
        $wallet_manager = new SUW_Wallet_Manager();
        $wallet = $wallet_manager->get_user_wallet($user_id);

        if (!$wallet) {
            return '<p>Keine Wallet vorhanden. Bitte kontaktieren Sie einen Administrator.</p>';
        }

        ob_start();
        ?>
        <div class="sui-user-wallet">
            <h3>Meine Sui Wallet</h3>
            <p><strong>Adresse:</strong> <code id="suw-frontend-address-<?php echo $user_id; ?>"><?php echo esc_html($wallet['address']); ?></code></p>
            <p><strong>Balance:</strong> <?php echo esc_html($wallet['cached_balance']); ?> SUI</p>
            <button type="button" class="suw-copy-btn" id="suw-copy-frontend-<?php echo $user_id; ?>" data-address="<?php echo esc_attr($wallet['address']); ?>">
                Adresse kopieren
            </button>
        </div>
        <script>
        (function() {
            var btn = document.getElementById('suw-copy-frontend-<?php echo $user_id; ?>');
            var address = btn.getAttribute('data-address');

            btn.addEventListener('click', function() {
                var originalText = btn.textContent;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(address).then(function() {
                        btn.textContent = '✓ Kopiert!';
                        btn.style.color = 'green';
                        setTimeout(function() {
                            btn.textContent = originalText;
                            btn.style.color = '';
                        }, 2000);
                    }).catch(function() {
                        fallbackCopy();
                    });
                } else {
                    fallbackCopy();
                }

                function fallbackCopy() {
                    var input = document.createElement('input');
                    input.value = address;
                    document.body.appendChild(input);
                    input.select();
                    try {
                        var successful = document.execCommand('copy');
                        if (successful) {
                            btn.textContent = '✓ Kopiert!';
                            btn.style.color = 'green';
                        } else {
                            btn.textContent = '✗ Fehler';
                            btn.style.color = 'red';
                        }
                    } catch (err) {
                        btn.textContent = '✗ Fehler';
                        btn.style.color = 'red';
                    }
                    document.body.removeChild(input);
                    setTimeout(function() {
                        btn.textContent = originalText;
                        btn.style.color = '';
                    }, 2000);
                }
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    // Dashboard Page
    public function dashboard_page() {
        $dashboard = new SUW_Dashboard();
        $dashboard->render_dashboard();
    }

    // Bulk Operations Page
    public function bulk_operations_page() {
        $bulk_ops = new SUW_Bulk_Operations();
        $bulk_ops->render_bulk_operations_page();
    }

    // Backup & Restore Page
    public function backup_restore_page() {
        $backup = new SUW_Backup_Restore();
        $backup->render_backup_page();
    }

    // AJAX: Bulk Balance Check
    public function ajax_bulk_balance_check() {
        $bulk_ops = new SUW_Bulk_Operations();
        $bulk_ops->ajax_bulk_balance_check();
    }

    // Admin Post: Bulk Export CSV
    public function handle_bulk_export_csv() {
        $bulk_ops = new SUW_Bulk_Operations();
        $bulk_ops->handle_bulk_export_csv();
    }

    // Admin Post: Bulk Export Keys
    public function handle_bulk_export_keys() {
        $bulk_ops = new SUW_Bulk_Operations();
        $bulk_ops->handle_bulk_export_keys();
    }

    // Admin Post: Download Backup
    public function handle_download_backup() {
        check_admin_referer('suw_download_backup');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $file = sanitize_file_name($_GET['file']);
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/sui-wallet-backups';
        $backup_file = $backup_dir . '/' . $file;

        if (!file_exists($backup_file) || strpos(realpath($backup_file), realpath($backup_dir)) !== 0) {
            wp_die('Invalid backup file');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $file);
        header('Content-Length: ' . filesize($backup_file));
        readfile($backup_file);
        exit;
    }
}

} // Ende class_exists('Sui_User_Wallets')

// Initialisieren
if (class_exists('Sui_User_Wallets')) {
    add_action('plugins_loaded', array('Sui_User_Wallets', 'get_instance'));
}
