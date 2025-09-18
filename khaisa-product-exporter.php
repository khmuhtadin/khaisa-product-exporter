<?php
/**
 * Plugin Name: Khaisa Product Exporter
 * Plugin URI: https://github.com/khmuhtadin/khaisa-product-exporter
 * Description: Export WooCommerce orders with detailed customer information, shipping addresses, and order items in CSV format with advanced filtering options. Built by Khairul Muhtadin.
 * Version: 1.0.3
 * Author: Khairul Muhtadin
 * Author URI: https://khmuhtadin.com
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: khaisa-product-exporter
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * Network: false
 * 
 * @package KhaisaProductExporter
 * @author Khairul Muhtadin
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('KPE_VERSION', '1.0.3');
define('KPE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KPE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KPE_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Khaisa Product Exporter Class
 * 
 * @class KhaisaProductExporter
 * @version 1.0.0
 */
final class KhaisaProductExporter {

    /**
     * Plugin instance
     * @var KhaisaProductExporter
     */
    private static $instance = null;

    /**
     * Get plugin instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize plugin
     */
    private function init() {
        // Declare HPOS compatibility early
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));

        // Load plugin text domain first
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Always add fallback menu first
        add_action('admin_menu', array($this, 'admin_menu_fallback'));

        // Check for WooCommerce after all plugins are loaded
        add_action('plugins_loaded', array($this, 'init_after_plugins_loaded'), 20);

        // Add plugin action links
        add_filter('plugin_action_links_' . KPE_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
    }

    /**
     * Initialize after all plugins are loaded
     */
    public function init_after_plugins_loaded() {
        // Check if WooCommerce is active after all plugins loaded
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        // Include required files
        $this->includes();

        // Initialize admin
        if (is_admin()) {
            $this->init_admin();
        }

        // Remove the error notice if it was added
        remove_action('admin_notices', array($this, 'woocommerce_missing_notice'));
    }

    /**
     * Declare WooCommerce HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }

    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        // Check multiple ways to ensure WooCommerce is properly detected
        return (
            class_exists('WooCommerce') ||
            class_exists('woocommerce') ||
            function_exists('WC') ||
            in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
        );
    }

    /**
     * Admin notice if WooCommerce is not active
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php _e('Khaisa Product Exporter', 'khaisa-product-exporter'); ?></strong>
                <?php _e('requires WooCommerce to be installed and active.', 'khaisa-product-exporter'); ?>
                <a href="<?php echo admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce'); ?>" target="_blank">
                    <?php _e('Install WooCommerce', 'khaisa-product-exporter'); ?>
                </a>
            </p>
            <p>
                <strong><?php _e('Plugin Status:', 'khaisa-product-exporter'); ?></strong>
                <?php _e('Menu will appear under WooCommerce → Order Exporter once WooCommerce is activated.', 'khaisa-product-exporter'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Fallback admin menu when WooCommerce is not active
     */
    public function admin_menu_fallback() {
        // Only show fallback menu if WooCommerce is not active
        // This runs after admin_menu hook, so WooCommerce should be detected
        if (!$this->is_woocommerce_active()) {
            add_menu_page(
                __('Khaisa Product Exporter', 'khaisa-product-exporter'),
                __('KPE Status', 'khaisa-product-exporter'),
                'manage_options',
                'khaisa-product-exporter-status',
                array($this, 'admin_page_fallback'),
                'dashicons-download',
                30
            );
        }
    }

    /**
     * Fallback admin page when WooCommerce is not active
     */
    public function admin_page_fallback() {
        ?>
        <div class="wrap">
            <h1><?php _e('Khaisa Product Exporter - Status', 'khaisa-product-exporter'); ?></h1>
            
            <div class="notice notice-warning">
                <h2><?php _e('WooCommerce Required', 'khaisa-product-exporter'); ?></h2>
                <p><?php _e('This plugin requires WooCommerce to function properly.', 'khaisa-product-exporter'); ?></p>
            </div>
            
            <div class="card">
                <h2><?php _e('Plugin Status Check', 'khaisa-product-exporter'); ?></h2>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td><strong><?php _e('Plugin Version:', 'khaisa-product-exporter'); ?></strong></td>
                            <td>
                                <?php echo KPE_VERSION; ?>
                                <br><small><em>File Version: <?php echo get_plugin_data(__FILE__)['Version']; ?></em></small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('WordPress Version:', 'khaisa-product-exporter'); ?></strong></td>
                            <td><?php echo get_bloginfo('version'); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('WooCommerce Status:', 'khaisa-product-exporter'); ?></strong></td>
                            <td>
                                <?php if (class_exists('WooCommerce')) : ?>
                                    <span style="color: green;">✓ <?php _e('Active', 'khaisa-product-exporter'); ?></span>
                                    <br><small><em>
                                        Class: <?php echo class_exists('WooCommerce') ? 'YES' : 'NO'; ?> | 
                                        Function: <?php echo function_exists('WC') ? 'YES' : 'NO'; ?> | 
                                        Plugin: <?php echo in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ? 'YES' : 'NO'; ?>
                                    </em></small>
                                    <?php if ($this->is_woocommerce_active()): ?>
                                        <br><em><?php _e('Detection: PASSED - Menus should be available', 'khaisa-product-exporter'); ?></em>
                                    <?php else: ?>
                                        <br><em style="color: red;"><?php _e('Detection: FAILED - This is the issue!', 'khaisa-product-exporter'); ?></em>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span style="color: red;">✗ <?php _e('Not Active', 'khaisa-product-exporter'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('Menu Status:', 'khaisa-product-exporter'); ?></strong></td>
                            <td>
                                <?php 
                                global $menu, $submenu;
                                $wc_menu_found = false;
                                $kpe_menu_found = false;
                                
                                // Check if WooCommerce menu exists
                                if (isset($submenu['woocommerce'])) {
                                    foreach ($submenu['woocommerce'] as $item) {
                                        if (strpos($item[2], 'khaisa-order-exporter') !== false) {
                                            $wc_menu_found = true;
                                            break;
                                        }
                                    }
                                }
                                
                                // Check if main KPE menu exists
                                if (isset($menu)) {
                                    foreach ($menu as $item) {
                                        if (isset($item[2]) && $item[2] === 'khaisa-product-exporter') {
                                            $kpe_menu_found = true;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                WooCommerce Submenu: <?php echo $wc_menu_found ? '<span style="color: green;">✓ Found</span>' : '<span style="color: red;">✗ Missing</span>'; ?><br>
                                Main Menu: <?php echo $kpe_menu_found ? '<span style="color: green;">✓ Found</span>' : '<span style="color: red;">✗ Missing</span>'; ?><br>
                                <small><em>Current Hook: <?php echo current_filter(); ?></em></small>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php _e('PHP Version:', 'khaisa-product-exporter'); ?></strong></td>
                            <td>
                                <?php echo PHP_VERSION; ?>
                                <?php if (version_compare(PHP_VERSION, '7.4', '>=')) : ?>
                                    <span style="color: green;">✓ <?php _e('Compatible', 'khaisa-product-exporter'); ?></span>
                                <?php else : ?>
                                    <span style="color: red;">✗ <?php _e('Requires PHP 7.4+', 'khaisa-product-exporter'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h2><?php _e('Next Steps', 'khaisa-product-exporter'); ?></h2>
                <ol>
                    <li>
                        <?php if (!class_exists('WooCommerce')) : ?>
                            <a href="<?php echo admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce'); ?>" class="button button-primary">
                                <?php _e('Install WooCommerce', 'khaisa-product-exporter'); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php echo admin_url('plugins.php'); ?>" class="button button-primary">
                                <?php _e('Activate WooCommerce', 'khaisa-product-exporter'); ?>
                            </a>
                        <?php endif; ?>
                    </li>
                    <li><?php _e('Once WooCommerce is active, the Order Exporter will appear under:', 'khaisa-product-exporter'); ?> <strong><?php _e('WooCommerce → Order Exporter', 'khaisa-product-exporter'); ?></strong></li>
                    <li><?php _e('Visit the exporter to export your WooCommerce orders with advanced filtering options.', 'khaisa-product-exporter'); ?></li>
                </ol>
            </div>
            
            <div class="card">
                <h2><?php _e('Plugin Information', 'khaisa-product-exporter'); ?></h2>
                <p><?php _e('Khaisa Product Exporter allows you to export WooCommerce orders with detailed customer information, shipping addresses, and order items in CSV format.', 'khaisa-product-exporter'); ?></p>
                <p>
                    <a href="https://github.com/khmuhtadin/khaisa-product-exporter" target="_blank" class="button">
                        <?php _e('Documentation', 'khaisa-product-exporter'); ?>
                    </a>
                    <a href="https://github.com/khmuhtadin/khaisa-product-exporter/issues" target="_blank" class="button">
                        <?php _e('Support', 'khaisa-product-exporter'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('khaisa-product-exporter', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once KPE_PLUGIN_DIR . 'includes/class-kpe-admin.php';
        require_once KPE_PLUGIN_DIR . 'includes/class-kpe-exporter.php';
    }

    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        new KPE_Admin();
        
        // Add admin menu
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    /**
     * Add admin menu
     */
    public function admin_menu() {
        // Only add menus if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            return;
        }

        // Add main menu page
        add_menu_page(
            __('Khaisa Product Exporter', 'khaisa-product-exporter'),
            __('Order Exporter', 'khaisa-product-exporter'),
            'manage_woocommerce',
            'khaisa-product-exporter',
            array($this, 'admin_page_compatibility'),
            'dashicons-download',
            30
        );

        // Add submenu pages
        add_submenu_page(
            'khaisa-product-exporter',
            __('Plugin Status & Compatibility', 'khaisa-product-exporter'),
            __('Plugin Status', 'khaisa-product-exporter'),
            'manage_woocommerce',
            'khaisa-product-exporter',
            array($this, 'admin_page_compatibility')
        );

        add_submenu_page(
            'khaisa-product-exporter',
            __('Order Export Tool', 'khaisa-product-exporter'),
            __('Export Orders', 'khaisa-product-exporter'),
            'manage_woocommerce',
            'khaisa-order-exporter',
            array($this, 'admin_page')
        );

        // Also add submenu under WooCommerce for easy access
        add_submenu_page(
            'woocommerce',
            __('Khaisa Order Exporter', 'khaisa-product-exporter'),
            __('Order Exporter', 'khaisa-product-exporter'),
            'manage_woocommerce',
            'khaisa-order-exporter-wc',
            array($this, 'admin_page')
        );
    }

    /**
     * Admin page callback for export tool
     */
    public function admin_page() {
        $admin = new KPE_Admin();
        $admin->render_admin_page();
    }

    /**
     * Admin page callback for compatibility status
     */
    public function admin_page_compatibility() {
        $this->render_compatibility_page();
    }

    /**
     * Render compatibility status page
     */
    private function render_compatibility_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Khaisa WooExporter', 'khaisa-product-exporter'); ?></h1>

            <div class="kpe-nav-tabs">
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=khaisa-product-exporter'); ?>" class="current"><?php _e('Plugin Status', 'khaisa-product-exporter'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=khaisa-order-exporter'); ?>"><?php _e('Export Orders', 'khaisa-product-exporter'); ?></a></li>
                </ul>
            </div>

            <div class="kpe-container">
                <div class="kpe-main-content">
                    <div class="kpe-section">
                        <h3><?php _e('System Status', 'khaisa-product-exporter'); ?></h3>

                        <?php
                        $all_good = true;
                        $wc_active = $this->is_woocommerce_active();
                        $php_compatible = version_compare(PHP_VERSION, '7.4', '>=');
                        $wp_compatible = version_compare(get_bloginfo('version'), '5.0', '>=');

                        if ($wc_active && $php_compatible && $wp_compatible) {
                            echo '<div class="notice notice-success inline"><p><strong>' . __('All systems are working correctly!', 'khaisa-product-exporter') . '</strong></p></div>';
                        } else {
                            $all_good = false;
                            echo '<div class="notice notice-error inline"><p><strong>' . __('Some issues detected. Please check the details below.', 'khaisa-product-exporter') . '</strong></p></div>';
                        }
                        ?>

                        <table class="widefat kpe-status-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Component', 'khaisa-product-exporter'); ?></th>
                                    <th><?php _e('Status', 'khaisa-product-exporter'); ?></th>
                                    <th><?php _e('Details', 'khaisa-product-exporter'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong><?php _e('Plugin Version', 'khaisa-product-exporter'); ?></strong></td>
                                    <td><span class="kpe-status-badge completed"><?php echo KPE_VERSION; ?></span></td>
                                    <td><?php printf(__('Current version: %s', 'khaisa-product-exporter'), KPE_VERSION); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('WordPress Version', 'khaisa-product-exporter'); ?></strong></td>
                                    <td>
                                        <?php if ($wp_compatible): ?>
                                            <span class="kpe-status-badge completed">✓ <?php echo get_bloginfo('version'); ?></span>
                                        <?php else: ?>
                                            <span class="kpe-status-badge failed">✗ <?php echo get_bloginfo('version'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($wp_compatible): ?>
                                            <?php _e('WordPress version is compatible', 'khaisa-product-exporter'); ?>
                                        <?php else: ?>
                                            <?php _e('WordPress 5.0 or higher required', 'khaisa-product-exporter'); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('PHP Version', 'khaisa-product-exporter'); ?></strong></td>
                                    <td>
                                        <?php if ($php_compatible): ?>
                                            <span class="kpe-status-badge completed">✓ <?php echo PHP_VERSION; ?></span>
                                        <?php else: ?>
                                            <span class="kpe-status-badge failed">✗ <?php echo PHP_VERSION; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($php_compatible): ?>
                                            <?php _e('PHP version is compatible', 'khaisa-product-exporter'); ?>
                                        <?php else: ?>
                                            <?php _e('PHP 7.4 or higher required', 'khaisa-product-exporter'); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('WooCommerce Status', 'khaisa-product-exporter'); ?></strong></td>
                                    <td>
                                        <?php if ($wc_active): ?>
                                            <span class="kpe-status-badge completed">✓ <?php _e('Active', 'khaisa-product-exporter'); ?></span>
                                        <?php else: ?>
                                            <span class="kpe-status-badge failed">✗ <?php _e('Not Active', 'khaisa-product-exporter'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($wc_active): ?>
                                            <?php
                                            if (defined('WC_VERSION')) {
                                                printf(__('WooCommerce %s is active and working', 'khaisa-product-exporter'), WC_VERSION);
                                            } else {
                                                _e('WooCommerce is active', 'khaisa-product-exporter');
                                            }
                                            ?>
                                        <?php else: ?>
                                            <?php _e('WooCommerce plugin is required', 'khaisa-product-exporter'); ?>
                                            <a href="<?php echo admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce'); ?>" class="button button-small"><?php _e('Install', 'khaisa-product-exporter'); ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('HPOS Compatibility', 'khaisa-product-exporter'); ?></strong></td>
                                    <td>
                                        <?php if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')): ?>
                                            <span class="kpe-status-badge completed">✓ <?php _e('Supported', 'khaisa-product-exporter'); ?></span>
                                        <?php else: ?>
                                            <span class="kpe-status-badge processing">~ <?php _e('Legacy Mode', 'khaisa-product-exporter'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') &&
                                            method_exists('\Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled') &&
                                            \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
                                            _e('High-Performance Order Storage (HPOS) is enabled and supported', 'khaisa-product-exporter');
                                        } elseif (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
                                            _e('HPOS is available but not enabled. Plugin supports both modes.', 'khaisa-product-exporter');
                                        } else {
                                            _e('Using legacy WordPress posts table (compatible)', 'khaisa-product-exporter');
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('Database Access', 'khaisa-product-exporter'); ?></strong></td>
                                    <td>
                                        <?php
                                        global $wpdb;
                                        $db_test = $wpdb->get_var("SELECT 1");
                                        if ($db_test == 1): ?>
                                            <span class="kpe-status-badge completed">✓ <?php _e('Working', 'khaisa-product-exporter'); ?></span>
                                        <?php else: ?>
                                            <span class="kpe-status-badge failed">✗ <?php _e('Error', 'khaisa-product-exporter'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($db_test == 1): ?>
                                            <?php _e('Database connection is working properly', 'khaisa-product-exporter'); ?>
                                        <?php else: ?>
                                            <?php _e('Database connection issue detected', 'khaisa-product-exporter'); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e('File Permissions', 'khaisa-product-exporter'); ?></strong></td>
                                    <td>
                                        <?php
                                        $upload_dir = wp_upload_dir();
                                        $writable = wp_is_writable($upload_dir['basedir']);
                                        if ($writable): ?>
                                            <span class="kpe-status-badge completed">✓ <?php _e('Writable', 'khaisa-product-exporter'); ?></span>
                                        <?php else: ?>
                                            <span class="kpe-status-badge failed">✗ <?php _e('Not Writable', 'khaisa-product-exporter'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($writable): ?>
                                            <?php printf(__('Upload directory is writable: %s', 'khaisa-product-exporter'), $upload_dir['basedir']); ?>
                                        <?php else: ?>
                                            <?php printf(__('Upload directory is not writable: %s', 'khaisa-product-exporter'), $upload_dir['basedir']); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!$all_good): ?>
                    <div class="kpe-section">
                        <h3><?php _e('Recommended Actions', 'khaisa-product-exporter'); ?></h3>
                        <ul>
                            <?php if (!$wc_active): ?>
                            <li><?php _e('Install and activate WooCommerce plugin', 'khaisa-product-exporter'); ?></li>
                            <?php endif; ?>
                            <?php if (!$php_compatible): ?>
                            <li><?php _e('Upgrade PHP to version 7.4 or higher', 'khaisa-product-exporter'); ?></li>
                            <?php endif; ?>
                            <?php if (!$wp_compatible): ?>
                            <li><?php _e('Update WordPress to version 5.0 or higher', 'khaisa-product-exporter'); ?></li>
                            <?php endif; ?>
                            <?php if (!wp_is_writable($upload_dir['basedir'])): ?>
                            <li><?php _e('Fix file permissions for the uploads directory', 'khaisa-product-exporter'); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if ($all_good): ?>
                    <div class="kpe-section">
                        <h3><?php _e('Ready to Export', 'khaisa-product-exporter'); ?></h3>
                        <p><?php _e('Your system is properly configured and ready to use the order exporter.', 'khaisa-product-exporter'); ?></p>
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=khaisa-order-exporter'); ?>" class="button button-primary">
                                <?php _e('Start Exporting Orders', 'khaisa-product-exporter'); ?>
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="kpe-sidebar">
                    <div class="kpe-widget status">
                        <h3><?php _e('Quick Links', 'khaisa-product-exporter'); ?></h3>
                        <ul>
                            <li><a href="<?php echo admin_url('admin.php?page=khaisa-order-exporter'); ?>"><?php _e('Export Orders Tool', 'khaisa-product-exporter'); ?></a></li>
                            <li><a href="<?php echo admin_url('admin.php?page=woocommerce'); ?>"><?php _e('WooCommerce Settings', 'khaisa-product-exporter'); ?></a></li>
                            <li><a href="<?php echo admin_url('plugins.php'); ?>"><?php _e('Plugins Management', 'khaisa-product-exporter'); ?></a></li>
                        </ul>
                    </div>

                    <div class="kpe-widget support">
                        <h3><?php _e('Support Information', 'khaisa-product-exporter'); ?></h3>
                        <p><?php _e('If you encounter any issues, please check:', 'khaisa-product-exporter'); ?></p>
                        <ul>
                            <li><a href="https://github.com/khmuhtadin/khaisa-product-exporter" target="_blank"><?php _e('Plugin Documentation', 'khaisa-product-exporter'); ?></a></li>
                            <li><a href="https://github.com/khmuhtadin/khaisa-product-exporter/issues" target="_blank"><?php _e('Report Issues', 'khaisa-product-exporter'); ?></a></li>
                        </ul>
                    </div>

                    <div class="kpe-widget info">
                        <h3><?php _e('Plugin Info', 'khaisa-product-exporter'); ?></h3>
                        <ul>
                            <li><strong><?php _e('Author:', 'khaisa-product-exporter'); ?></strong> Khairul Muhtadin</li>
                            <li><strong><?php _e('Version:', 'khaisa-product-exporter'); ?></strong> <?php echo KPE_VERSION; ?></li>
                            <li><strong><?php _e('License:', 'khaisa-product-exporter'); ?></strong> GPL v3+</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // List of pages where we need our assets
        $kpe_pages = array(
            'woocommerce_page_khaisa-order-exporter-wc', // WooCommerce submenu
            'toplevel_page_khaisa-product-exporter', // Main compatibility page
            'order-exporter_page_khaisa-order-exporter', // Export tool submenu
            'admin_page_khaisa-order-exporter' // Legacy support
        );

        if (!in_array($hook, $kpe_pages)) {
            return;
        }

        // Always enqueue CSS for all our pages
        wp_enqueue_style(
            'kpe-admin-css',
            KPE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            KPE_VERSION
        );

        // Only enqueue JS and datepicker for export tool pages
        if (strpos($hook, 'khaisa-order-exporter') !== false && strpos($hook, 'khaisa-product-exporter') === false) {
            wp_enqueue_script(
                'kpe-admin-js',
                KPE_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-datepicker'),
                KPE_VERSION,
                true
            );

            wp_enqueue_style('jquery-ui-theme');

            // Localize script for AJAX
            wp_localize_script('kpe-admin-js', 'kpe_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('kpe_nonce'),
                'strings' => array(
                    'exporting' => __('Exporting orders...', 'khaisa-product-exporter'),
                    'export_complete' => __('Export completed successfully!', 'khaisa-product-exporter'),
                    'export_error' => __('An error occurred during export.', 'khaisa-product-exporter'),
                    'no_orders_found' => __('No orders found for the selected criteria.', 'khaisa-product-exporter'),
                    'plugin_credit' => __('Powered by Khaisa Product Exporter', 'khaisa-product-exporter'),
                )
            ));
        }
    }

    /**
     * Add plugin action links
     */
    public function plugin_action_links($links) {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=khaisa-product-exporter') . '">' . __('Export Orders', 'khaisa-product-exporter') . '</a>',
            'github' => '<a href="https://github.com/khmuhtadin/khaisa-product-exporter" target="_blank">' . __('GitHub', 'khaisa-product-exporter') . '</a>',
        );

        return array_merge($action_links, $links);
    }
}

/**
 * Returns the main instance of KhaisaProductExporter
 */
function KPE() {
    return KhaisaProductExporter::instance();
}

// Initialize the plugin
KPE();

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'kpe_activation');

function kpe_activation() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('This plugin requires WooCommerce to be installed and active.', 'khaisa-product-exporter'),
            __('Plugin Activation Error', 'khaisa-product-exporter'),
            array('back_link' => true)
        );
    }
    
    // Set activation flag
    add_option('kpe_plugin_activated', true);
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'kpe_deactivation');

function kpe_deactivation() {
    // Clean up temporary files
    $upload_dir = wp_upload_dir();
    $export_dir = $upload_dir['basedir'] . '/khaisa-order-exports';
    
    if (is_dir($export_dir)) {
        $files = glob($export_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    // Remove activation flag
    delete_option('kpe_plugin_activated');
}