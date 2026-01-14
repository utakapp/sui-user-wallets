<?php
/**
 * Transaction History Class
 *
 * Fetches and displays transaction history from Sui blockchain
 *
 * @package Sui_User_Wallets
 */

if (!defined('ABSPATH')) exit;

class SUW_Transaction_History {

    /**
     * Get transaction history for wallet
     */
    public function get_transactions($wallet_address, $limit = 10) {
        $vercel_api_url = get_option('suw_vercel_api_url');
        if (!$vercel_api_url) {
            return new WP_Error('no_api', 'Vercel API URL not configured');
        }

        $response = wp_remote_post($vercel_api_url . '/api/get-transactions', array(
            'body' => json_encode(array(
                'address' => $wallet_address,
                'limit' => $limit,
            )),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['transactions'] ?? array();
    }

    /**
     * Render transaction history table
     */
    public function render_transaction_history($wallet_address) {
        $transactions = $this->get_transactions($wallet_address);

        if (is_wp_error($transactions)) {
            echo '<p>Unable to load transaction history: ' . esc_html($transactions->get_error_message()) . '</p>';
            return;
        }

        if (empty($transactions)) {
            echo '<p>No transactions yet.</p>';
            return;
        }

        ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Tx Hash</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?php echo esc_html(date('Y-m-d H:i', strtotime($tx['timestamp']))); ?></td>
                        <td><?php echo esc_html($tx['type']); ?></td>
                        <td><?php echo esc_html($tx['amount']); ?> SUI</td>
                        <td>
                            <span class="suw-status-<?php echo esc_attr($tx['status']); ?>">
                                <?php echo esc_html($tx['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="https://suiexplorer.com/txblock/<?php echo esc_attr($tx['hash']); ?>?network=testnet" target="_blank">
                                <?php echo esc_html(substr($tx['hash'], 0, 8)); ?>...
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <style>
            .suw-status-success { color: #00a32a; font-weight: bold; }
            .suw-status-pending { color: #f0b849; font-weight: bold; }
            .suw-status-failed { color: #d63638; font-weight: bold; }
        </style>
        <?php
    }
}
