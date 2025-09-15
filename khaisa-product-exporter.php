<?php
/**
 * Plugin Name: Khaisa Product Exporter
 * Plugin URI: https://github.com/khmuhtadin/khaisa-product-exporter
 * Description: Export WooCommerce orders with detailed customer information, shipping addresses, and order items in CSV format with advanced filtering options. Built by Khairul Muhtadin.
 * Version: 1.0.0
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
define('KPE_VERSION', '1.0.0');
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
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        // Load plugin text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Include required files
        $this->includes();
        
        // Initialize admin
        if (is_admin()) {
            $this->init_admin();
        }

        // Add plugin action links
        add_filter('plugin_action_links_' . KPE_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
    }

    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
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
        add_submenu_page(
            'woocommerce',
            __('Khaisa Order Exporter', 'khaisa-product-exporter'),
            __('Order Exporter', 'khaisa-product-exporter'),
            'manage_woocommerce',
            'khaisa-order-exporter',
            array($this, 'admin_page')
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
        if ('woocommerce_page_khaisa-order-exporter' !== $hook) {
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
            'settings' => '<a href="' . admin_url('admin.php?page=khaisa-order-exporter') . '">' . __('Export Orders', 'khaisa-product-exporter') . '</a>',
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