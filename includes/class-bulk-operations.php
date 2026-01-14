<?php
/**
 * Bulk Operations Class
 *
 * Handles bulk operations for wallets
 *
 * @package Sui_User_Wallets
 */

if (!defined('ABSPATH')) exit;

class SUW_Bulk_Operations {

    private $wallet_manager;
    private $crypto;

    public function __construct() {
        $this->wallet_manager = new SUW_Wallet_Manager();
        $this->crypto = new SUW_Wallet_Crypto();
    }

    /**
     * Export multiple wallets to CSV
     */
    public function export_wallets_csv($user_ids = null) {
        if ($user_ids === null) {
            $wallets = $this->wallet_manager->get_all_wallets();
        } else {
            $wallets = array();
            foreach ($user_ids as $user_id) {
                $wallet = $this->wallet_manager->get_user_wallet($user_id);
                if ($wallet) {
                    $wallets[] = $wallet;
                }
            }
        }

        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=sui-wallets-export-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, array('User ID', 'Username', 'Email', 'Wallet Address', 'Balance', 'Created At'));

        // CSV Rows
        foreach ($wallets as $wallet) {
            $user = get_userdata($wallet->user_id);
            fputcsv($output, array(
                $wallet->user_id,
                $user ? $user->user_login : 'N/A',
                $user ? $user->user_email : 'N/A',
                $wallet->wallet_address,
                $wallet->cached_balance . ' SUI',
                $wallet->created_at,
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * Export private keys (admin only, encrypted)
     */
    public function export_private_keys($user_ids) {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=sui-private-keys-' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, array('User ID', 'Username', 'Wallet Address', 'Private Key (SENSITIVE!)'));

        // CSV Rows
        foreach ($user_ids as $user_id) {
            $wallet = $this->wallet_manager->get_user_wallet($user_id);
            if ($wallet) {
                $user = get_userdata($user_id);
                $private_key = $this->crypto->decrypt($wallet->encrypted_private_key);

                fputcsv($output, array(
                    $user_id,
                    $user ? $user->user_login : 'N/A',
                    $wallet->wallet_address,
                    $private_key,
                ));
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Bulk balance check
     */
    public function bulk_balance_check($user_ids) {
        $results = array();

        foreach ($user_ids as $user_id) {
            $wallet = $this->wallet_manager->get_user_wallet($user_id);
            if ($wallet) {
                $balance = $this->wallet_manager->get_wallet_balance($wallet->wallet_address);
                $results[$user_id] = array(
                    'wallet_address' => $wallet->wallet_address,
                    'balance' => $balance,
                    'success' => $balance !== false,
                );

                // Update cached balance
                if ($balance !== false) {
                    global $wpdb;
                    $wpdb->update(
                        $wpdb->prefix . 'sui_user_wallets',
                        array(
                            'cached_balance' => $balance,
                            'last_balance_check' => current_time('mysql'),
                        ),
                        array('user_id' => $user_id)
                    );
                }
            }
        }

        return $results;
    }

    /**
     * Render bulk operations page
     */
    public function render_bulk_operations_page() {
        ?>
        <div class="wrap">
            <h1>Bulk Operations</h1>

            <div class="suw-bulk-ops">
                <!-- Export All Wallets -->
                <div class="postbox">
                    <h2 class="hndle">Export All Wallets</h2>
                    <div class="inside">
                        <p>Export all wallets to CSV file (without private keys).</p>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                            <?php wp_nonce_field('suw_bulk_export'); ?>
                            <input type="hidden" name="action" value="suw_bulk_export_csv">
                            <button type="submit" class="button button-primary">Export to CSV</button>
                        </form>
                    </div>
                </div>

                <!-- Bulk Balance Check -->
                <div class="postbox">
                    <h2 class="hndle">Bulk Balance Check</h2>
                    <div class="inside">
                        <p>Update cached balances for all wallets.</p>
                        <form method="post" action="" id="suw-bulk-balance-form">
                            <?php wp_nonce_field('suw_bulk_balance'); ?>
                            <button type="button" class="button button-primary" id="suw-bulk-balance-btn">
                                Check All Balances
                            </button>
                            <span id="suw-bulk-balance-status"></span>
                        </form>
                    </div>
                </div>

                <!-- Export Private Keys (Admin Only) -->
                <div class="postbox" style="border-left: 4px solid #d63638;">
                    <h2 class="hndle">⚠️ Export Private Keys (Dangerous!)</h2>
                    <div class="inside">
                        <p style="color: #d63638;"><strong>Warning:</strong> This exports all private keys in plain text. Only use for backups on secure devices!</p>
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('Are you sure? This will export ALL private keys!');">
                            <?php wp_nonce_field('suw_bulk_export_keys'); ?>
                            <input type="hidden" name="action" value="suw_bulk_export_keys">
                            <button type="submit" class="button button-secondary">Export Private Keys</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#suw-bulk-balance-btn').on('click', function() {
                var $btn = $(this);
                var $status = $('#suw-bulk-balance-status');

                $btn.prop('disabled', true).text('Checking...');
                $status.html('⏳ Updating balances...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'suw_bulk_balance_check',
                        _wpnonce: '<?php echo wp_create_nonce('suw_bulk_balance'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('✅ ' + response.data.message);
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $status.html('❌ ' + response.data.message);
                            $btn.prop('disabled', false).text('Check All Balances');
                        }
                    },
                    error: function() {
                        $status.html('❌ AJAX error');
                        $btn.prop('disabled', false).text('Check All Balances');
                    }
                });
            });
        });
        </script>

        <style>
            .suw-bulk-ops .postbox {
                margin-bottom: 20px;
            }
            .suw-bulk-ops .inside {
                padding: 15px;
            }
        </style>
        <?php
    }

    /**
     * Handle bulk export CSV
     */
    public function handle_bulk_export_csv() {
        check_admin_referer('suw_bulk_export');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $this->export_wallets_csv();
    }

    /**
     * Handle bulk export private keys
     */
    public function handle_bulk_export_keys() {
        check_admin_referer('suw_bulk_export_keys');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $wallets = $this->wallet_manager->get_all_wallets();
        $user_ids = array_map(function($wallet) {
            return $wallet->user_id;
        }, $wallets);

        $this->export_private_keys($user_ids);
    }

    /**
     * AJAX: Bulk balance check
     */
    public function ajax_bulk_balance_check() {
        check_ajax_referer('suw_bulk_balance');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }

        $wallets = $this->wallet_manager->get_all_wallets();
        $user_ids = array_map(function($wallet) {
            return $wallet->user_id;
        }, $wallets);

        $results = $this->bulk_balance_check($user_ids);

        $success_count = count(array_filter($results, function($result) {
            return $result['success'];
        }));

        wp_send_json_success(array(
            'message' => sprintf('Updated %d of %d wallets', $success_count, count($results))
        ));
    }
}
