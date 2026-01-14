<?php
/**
 * Backup & Restore Class
 *
 * Handles wallet backups and restoration
 *
 * @package Sui_User_Wallets
 */

if (!defined('ABSPATH')) exit;

class SUW_Backup_Restore {

    /**
     * Create encrypted backup of all wallets
     */
    public function create_backup() {
        if (!current_user_can('manage_options')) {
            return new WP_Error('unauthorized', 'Insufficient permissions');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        // Get all wallets
        $wallets = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);

        // Create backup data
        $backup_data = array(
            'version' => SUW_VERSION,
            'created_at' => current_time('mysql'),
            'wordpress_version' => get_bloginfo('version'),
            'wallets_count' => count($wallets),
            'wallets' => $wallets,
        );

        // Encrypt backup
        $backup_json = json_encode($backup_data);
        $crypto = new SUW_Wallet_Crypto();
        $encrypted_backup = $crypto->encrypt($backup_json);

        // Save to file
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/sui-wallet-backups';

        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }

        $backup_file = $backup_dir . '/backup-' . date('Y-m-d-His') . '.enc';
        file_put_contents($backup_file, $encrypted_backup);

        return $backup_file;
    }

    /**
     * Restore wallets from backup
     */
    public function restore_backup($backup_file) {
        if (!current_user_can('manage_options')) {
            return new WP_Error('unauthorized', 'Insufficient permissions');
        }

        if (!file_exists($backup_file)) {
            return new WP_Error('file_not_found', 'Backup file not found');
        }

        // Read and decrypt backup
        $encrypted_backup = file_get_contents($backup_file);
        $crypto = new SUW_Wallet_Crypto();
        $backup_json = $crypto->decrypt($encrypted_backup);

        if (!$backup_json) {
            return new WP_Error('decrypt_failed', 'Failed to decrypt backup');
        }

        $backup_data = json_decode($backup_json, true);

        if (!$backup_data || !isset($backup_data['wallets'])) {
            return new WP_Error('invalid_backup', 'Invalid backup format');
        }

        // Restore wallets
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        $restored_count = 0;
        foreach ($backup_data['wallets'] as $wallet) {
            // Check if wallet already exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE user_id = %d",
                $wallet['user_id']
            ));

            if (!$existing) {
                $wpdb->insert($table_name, $wallet);
                $restored_count++;
            }
        }

        return array(
            'success' => true,
            'restored_count' => $restored_count,
            'total_wallets' => count($backup_data['wallets']),
            'backup_version' => $backup_data['version'],
        );
    }

    /**
     * List available backups
     */
    public function list_backups() {
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/sui-wallet-backups';

        if (!file_exists($backup_dir)) {
            return array();
        }

        $backups = glob($backup_dir . '/backup-*.enc');
        return array_map(function($file) {
            return array(
                'file' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
            );
        }, $backups);
    }

    /**
     * Render backup page
     */
    public function render_backup_page() {
        ?>
        <div class="wrap">
            <h1>Backup & Restore</h1>

            <!-- Create Backup -->
            <div class="postbox">
                <h2 class="hndle">Create Backup</h2>
                <div class="inside">
                    <p>Create an encrypted backup of all wallets.</p>
                    <form method="post" action="">
                        <?php wp_nonce_field('suw_create_backup'); ?>
                        <button type="submit" name="create_backup" class="button button-primary">
                            Create Backup Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Available Backups -->
            <div class="postbox">
                <h2 class="hndle">Available Backups</h2>
                <div class="inside">
                    <?php
                    $backups = $this->list_backups();
                    if (empty($backups)): ?>
                        <p>No backups available.</p>
                    <?php else: ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>File</th>
                                    <th>Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td><?php echo esc_html($backup['date']); ?></td>
                                        <td><?php echo esc_html($backup['file']); ?></td>
                                        <td><?php echo esc_html(size_format($backup['size'])); ?></td>
                                        <td>
                                            <form method="post" style="display: inline;" onsubmit="return confirm('Restore this backup? Existing wallets will not be overwritten.');">
                                                <?php wp_nonce_field('suw_restore_backup'); ?>
                                                <input type="hidden" name="backup_file" value="<?php echo esc_attr($backup['path']); ?>">
                                                <button type="submit" name="restore_backup" class="button button-secondary">Restore</button>
                                            </form>
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=suw_download_backup&file=' . urlencode($backup['file'])), 'suw_download_backup')); ?>" class="button">Download</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php
        // Handle form submissions
        if (isset($_POST['create_backup'])) {
            check_admin_referer('suw_create_backup');
            $result = $this->create_backup();
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>Backup created: ' . esc_html(basename($result)) . '</p></div>';
            }
        }

        if (isset($_POST['restore_backup'])) {
            check_admin_referer('suw_restore_backup');
            $result = $this->restore_backup($_POST['backup_file']);
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>Restored ' . esc_html($result['restored_count']) . ' wallets.</p></div>';
            }
        }
    }
}
