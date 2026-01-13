<?php
/**
 * Auto-Updater für Sui User Wallets Plugin
 *
 * Ermöglicht automatische Updates direkt von GitHub
 */

if (!defined('ABSPATH')) {
    exit;
}

class SUW_Auto_Updater {

    private $plugin_slug;
    private $plugin_basename;
    private $github_username;
    private $github_repo;
    private $version;
    private $cache_key;
    private $cache_allowed;

    public function __construct() {
        $this->plugin_slug = 'sui-user-wallets';
        $this->plugin_basename = plugin_basename(dirname(dirname(__FILE__)) . '/sui-user-wallets.php');

        // GitHub Repository Info
        $this->github_username = 'utakapp';  // Ändere auf dein GitHub Username
        $this->github_repo = 'sui-user-wallets';  // Ändere auf dein Repo Name

        $this->version = SUW_VERSION;
        $this->cache_key = 'suw_github_update';
        $this->cache_allowed = true;

        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);

        // Add "Check for Updates" link in plugin row
        add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
        add_action('admin_init', array($this, 'handle_manual_update_check'));
    }

    /**
     * Hole Informationen vom GitHub Repository
     */
    private function get_repository_info() {
        if ($this->cache_allowed) {
            $cached = get_transient($this->cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }

        $api_url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest";

        $response = wp_remote_get($api_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
            )
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $release = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($release) || !isset($release['tag_name'])) {
            return false;
        }

        // Finde ZIP Asset
        $download_url = null;
        if (!empty($release['assets'])) {
            foreach ($release['assets'] as $asset) {
                if (strpos($asset['name'], '.zip') !== false) {
                    $download_url = $asset['browser_download_url'];
                    break;
                }
            }
        }

        // Fallback: Source Code ZIP
        if (empty($download_url) && isset($release['zipball_url'])) {
            $download_url = $release['zipball_url'];
        }

        // Wenn immer noch kein Download URL, abbrechen
        if (empty($download_url)) {
            return false;
        }

        $info = array(
            'version' => ltrim($release['tag_name'], 'v'),
            'download_url' => $download_url,
            'body' => $release['body'] ?? '',
            'published_at' => $release['published_at'] ?? '',
            'html_url' => $release['html_url'] ?? ''
        );

        // Cache für 12 Stunden
        set_transient($this->cache_key, $info, 12 * HOUR_IN_SECONDS);

        return $info;
    }

    /**
     * Prüfe auf Updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Hole neueste Version von GitHub
        $remote_info = $this->get_repository_info();

        if ($remote_info === false) {
            return $transient;
        }

        // Vergleiche Versionen
        if (version_compare($this->version, $remote_info['version'], '<')) {
            $plugin_data = array(
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_basename,
                'new_version' => $remote_info['version'],
                'url' => "https://github.com/{$this->github_username}/{$this->github_repo}",
                'package' => $remote_info['download_url'],
                'tested' => '6.4',
                'requires_php' => '7.4',
                'compatibility' => new stdClass(),
            );

            $transient->response[$this->plugin_basename] = (object) $plugin_data;
        }

        return $transient;
    }

    /**
     * Plugin Info für Update-Screen
     */
    public function plugin_info($false, $action, $args) {
        if ($action !== 'plugin_information') {
            return $false;
        }

        if ($args->slug !== $this->plugin_slug) {
            return $false;
        }

        $remote_info = $this->get_repository_info();

        if ($remote_info === false) {
            return $false;
        }

        $info = new stdClass();
        $info->name = 'Sui User Wallets';
        $info->slug = $this->plugin_slug;
        $info->version = $remote_info['version'];
        $info->author = '<a href="https://github.com/' . $this->github_username . '">' . $this->github_username . '</a>';
        $info->homepage = "https://github.com/{$this->github_username}/{$this->github_repo}";
        $info->download_link = $remote_info['download_url'];
        $info->requires = '5.0';
        $info->tested = '6.4';
        $info->requires_php = '7.4';
        $info->last_updated = $remote_info['published_at'];

        // Changelog
        $info->sections = array(
            'description' => 'Automatische Sui Wallet-Verwaltung für WordPress User',
            'changelog' => $this->parse_changelog($remote_info['body']),
        );

        return $info;
    }

    /**
     * Nach Installation
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        // Prüfe ob unser Plugin
        $install_directory = plugin_dir_path(dirname(__FILE__));
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;

        // Plugin aktiviert lassen
        if ($this->is_plugin_active()) {
            activate_plugin($this->plugin_basename);
        }

        return $result;
    }

    /**
     * Parse Changelog aus Release Notes
     */
    private function parse_changelog($body) {
        if (empty($body)) {
            return 'Keine Changelog-Informationen verfügbar.';
        }

        // Konvertiere Markdown zu HTML (basic)
        $html = nl2br(esc_html($body));
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);

        return $html;
    }

    /**
     * Prüfe ob Plugin aktiv ist
     */
    private function is_plugin_active() {
        return is_plugin_active($this->plugin_basename);
    }

    /**
     * Füge "Check for Updates" Link hinzu
     */
    public function plugin_row_meta($links, $file) {
        if ($file === $this->plugin_basename) {
            $check_url = wp_nonce_url(
                add_query_arg('suw_force_update_check', '1', admin_url('plugins.php')),
                'suw_force_update_check'
            );

            $links[] = '<a href="' . $check_url . '">Check for Updates</a>';
            $links[] = '<a href="https://github.com/' . $this->github_username . '/' . $this->github_repo . '/releases" target="_blank">View Releases</a>';
        }

        return $links;
    }

    /**
     * Handle manueller Update-Check
     */
    public function handle_manual_update_check() {
        if (!isset($_GET['suw_force_update_check'])) {
            return;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'], 'suw_force_update_check')) {
            return;
        }

        if (!current_user_can('update_plugins')) {
            return;
        }

        // Lösche Cache
        delete_transient($this->cache_key);

        // Lösche WordPress Update-Cache
        delete_site_transient('update_plugins');

        // Redirect mit Erfolgs-Nachricht
        wp_redirect(add_query_arg(
            array('suw_update_check' => 'success'),
            admin_url('plugins.php')
        ));
        exit;
    }
}

// Initialisiere nur wenn nicht im Deployment-Prozess
if (!defined('WP_UNINSTALL_PLUGIN')) {
    new SUW_Auto_Updater();
}
