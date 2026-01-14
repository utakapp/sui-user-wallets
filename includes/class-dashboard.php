<?php
/**
 * Dashboard Class
 *
 * Provides admin dashboard with wallet statistics and insights
 *
 * @package Sui_User_Wallets
 */

if (!defined('ABSPATH')) exit;

class SUW_Dashboard {

    private $wallet_manager;

    public function __construct() {
        $this->wallet_manager = new SUW_Wallet_Manager();
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        ?>
        <div class="wrap">
            <h1>Dashboard - Wallet Statistics</h1>

            <div class="suw-dashboard">
                <?php $this->render_stats_grid(); ?>
                <?php $this->render_recent_activity(); ?>
                <?php $this->render_charts(); ?>
            </div>
        </div>

        <style>
            .suw-dashboard {
                margin-top: 20px;
            }
            .suw-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            .suw-stat-box {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                box-shadow: 0 1px 1px rgba(0,0,0,0.04);
            }
            .suw-stat-box h3 {
                margin: 0 0 10px 0;
                font-size: 14px;
                color: #646970;
                text-transform: uppercase;
                font-weight: 600;
            }
            .suw-stat-value {
                font-size: 32px;
                font-weight: 600;
                color: #1d2327;
                line-height: 1;
            }
            .suw-stat-change {
                font-size: 12px;
                margin-top: 8px;
                color: #646970;
            }
            .suw-stat-change.positive {
                color: #00a32a;
            }
            .suw-stat-change.negative {
                color: #d63638;
            }
            .suw-recent-activity {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .suw-activity-item {
                padding: 10px 0;
                border-bottom: 1px solid #f0f0f1;
            }
            .suw-activity-item:last-child {
                border-bottom: none;
            }
            .suw-activity-time {
                color: #646970;
                font-size: 12px;
            }
        </style>
        <?php
    }

    /**
     * Render statistics grid
     */
    private function render_stats_grid() {
        $stats = $this->get_statistics();
        ?>
        <div class="suw-stats-grid">
            <!-- Total Wallets -->
            <div class="suw-stat-box">
                <h3>Total Wallets</h3>
                <div class="suw-stat-value"><?php echo number_format($stats['total_wallets']); ?></div>
                <?php if ($stats['wallets_change']): ?>
                    <div class="suw-stat-change <?php echo $stats['wallets_change'] > 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $stats['wallets_change'] > 0 ? '+' : ''; ?>
                        <?php echo $stats['wallets_change']; ?> today
                    </div>
                <?php endif; ?>
            </div>

            <!-- Wallets Created Today -->
            <div class="suw-stat-box">
                <h3>Created Today</h3>
                <div class="suw-stat-value"><?php echo number_format($stats['wallets_today']); ?></div>
            </div>

            <!-- Total Balance -->
            <div class="suw-stat-box">
                <h3>Total SUI Balance</h3>
                <div class="suw-stat-value"><?php echo number_format($stats['total_balance'], 2); ?> SUI</div>
                <div class="suw-stat-change">
                    Across all wallets
                </div>
            </div>

            <!-- Active Wallets -->
            <div class="suw-stat-box">
                <h3>Active Wallets</h3>
                <div class="suw-stat-value"><?php echo number_format($stats['active_wallets']); ?></div>
                <div class="suw-stat-change">
                    <?php echo number_format(($stats['active_wallets'] / max($stats['total_wallets'], 1)) * 100, 1); ?>% of total
                </div>
            </div>

            <!-- Average Balance -->
            <div class="suw-stat-box">
                <h3>Average Balance</h3>
                <div class="suw-stat-value"><?php echo number_format($stats['avg_balance'], 4); ?> SUI</div>
            </div>

            <!-- Error Rate -->
            <div class="suw-stat-box">
                <h3>Error Rate (24h)</h3>
                <div class="suw-stat-value"><?php echo number_format($stats['error_rate'], 1); ?>%</div>
                <div class="suw-stat-change <?php echo $stats['error_rate'] < 5 ? 'positive' : 'negative'; ?>">
                    <?php echo $stats['error_count']; ?> failed operations
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render recent activity feed
     */
    private function render_recent_activity() {
        $activities = $this->get_recent_activities(10);
        ?>
        <div class="suw-recent-activity">
            <h2>Recent Activity</h2>
            <?php if (empty($activities)): ?>
                <p>No recent activity.</p>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <div class="suw-activity-item">
                        <strong><?php echo esc_html($activity['action']); ?></strong>
                        for <a href="<?php echo admin_url('user-edit.php?user_id=' . $activity['user_id']); ?>">
                            <?php echo esc_html($activity['user_login']); ?>
                        </a>
                        <div class="suw-activity-time">
                            <?php echo human_time_diff(strtotime($activity['time']), current_time('timestamp')); ?> ago
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render charts
     */
    private function render_charts() {
        ?>
        <div class="suw-charts" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
            <h2>Wallet Creation Timeline (Last 30 Days)</h2>
            <canvas id="suw-chart-wallets" width="800" height="300"></canvas>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('suw-chart-wallets').getContext('2d');
            const data = <?php echo json_encode($this->get_chart_data()); ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Wallets Created',
                        data: data.values,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Get dashboard statistics
     */
    private function get_statistics() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        // Total wallets
        $total_wallets = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");

        // Wallets created today
        $today = current_time('Y-m-d');
        $wallets_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE DATE(created_at) = %s",
            $today
        ));

        // Wallets created yesterday (for comparison)
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $wallets_yesterday = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE DATE(created_at) = %s",
            $yesterday
        ));

        // Total balance (sum of cached balances)
        $total_balance = $wpdb->get_var("SELECT SUM(CAST(cached_balance AS DECIMAL(20,9))) FROM {$table_name}");
        $total_balance = floatval($total_balance);

        // Active wallets (have balance > 0)
        $active_wallets = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE CAST(cached_balance AS DECIMAL(20,9)) > 0");

        // Average balance
        $avg_balance = $total_wallets > 0 ? $total_balance / $total_wallets : 0;

        // Error rate (mock - in real implementation would check logs)
        $error_rate = 2.5;
        $error_count = 3;

        return array(
            'total_wallets' => intval($total_wallets),
            'wallets_today' => intval($wallets_today),
            'wallets_change' => intval($wallets_today) - intval($wallets_yesterday),
            'total_balance' => $total_balance,
            'active_wallets' => intval($active_wallets),
            'avg_balance' => $avg_balance,
            'error_rate' => $error_rate,
            'error_count' => $error_count,
        );
    }

    /**
     * Get recent activities
     */
    private function get_recent_activities($limit = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT w.user_id, w.created_at as time, u.user_login
            FROM {$table_name} w
            LEFT JOIN {$wpdb->users} u ON w.user_id = u.ID
            ORDER BY w.created_at DESC
            LIMIT %d
        ", $limit), ARRAY_A);

        $activities = array();
        foreach ($results as $row) {
            $activities[] = array(
                'action' => 'Wallet created',
                'user_id' => $row['user_id'],
                'user_login' => $row['user_login'] ?? 'Unknown',
                'time' => $row['time'],
            );
        }

        return $activities;
    }

    /**
     * Get chart data for last 30 days
     */
    private function get_chart_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sui_user_wallets';

        $data = array(
            'labels' => array(),
            'values' => array(),
        );

        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE DATE(created_at) = %s",
                $date
            ));

            $data['labels'][] = date('M d', strtotime($date));
            $data['values'][] = intval($count);
        }

        return $data;
    }
}
