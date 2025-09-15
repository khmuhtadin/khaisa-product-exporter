<?php
/**
 * Plugin Name: Khaisa Product Exporter
 * Plugin URI: https://github.com/khmuhtadin/khaisa-product-exporter
 * Description: Export WooCommerce orders with detailed customer information, shipping addresses, and order items in CSV format with advanced filtering options. Built by Khairul Muhtadin.
 * Version: 1.0.1
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
define('KPE_VERSION', '1.0.1');
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
        
        // Add submenu under WooCommerce (primary location)
        add_submenu_page(
            'woocommerce',
            __('Khaisa Order Exporter', 'khaisa-product-exporter'),
            __('Order Exporter', 'khaisa-product-exporter'),
            'manage_woocommerce',
            'khaisa-order-exporter',
            array($this, 'admin_page')
        );
        
        // Also add as a top-level menu for better accessibility
        add_menu_page(
            __('Khaisa Product Exporter', 'khaisa-product-exporter'),
            __('Order Exporter', 'khaisa-product-exporter'),
            'manage_woocommerce',
            'khaisa-product-exporter',
            array($this, 'admin_page'),
            'dashicons-download',
            30
        );
    }

    /**
     * Admin page callback
     */
    public function admin_page() {
        $admin = new KPE_Admin();
        $admin->render_admin_page();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Check if we're on either the WooCommerce submenu or the main menu page
        if ('woocommerce_page_khaisa-order-exporter' !== $hook && 
            'toplevel_page_khaisa-product-exporter' !== $hook) {
            return;
        }

        wp_enqueue_script(
            'kpe-admin-js',
            KPE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-datepicker'),
            KPE_VERSION,
            true
        );

        wp_enqueue_style(
            'kpe-admin-css',
            KPE_PLUGIN_URL . 'assets/css/admin.css',
            array('jquery-ui-theme'),
            KPE_VERSION
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