<?php
/**
 * Admin functionality for Khaisa Product Exporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KPE_Admin {

    public function __construct() {
        add_action('wp_ajax_kpe_export_orders', array($this, 'ajax_export_orders'));
        add_action('wp_ajax_kpe_preview_orders', array($this, 'ajax_preview_orders'));
        add_action('admin_init', array($this, 'handle_download'));
    }

    /**
     * Handle file download
     */
    public function handle_download() {
        if (isset($_GET['kpe_download']) && isset($_GET['file']) && isset($_GET['nonce'])) {
            if (!wp_verify_nonce($_GET['nonce'], 'kpe_download')) {
                wp_die(__('Security check failed.', 'khaisa-product-exporter'));
            }

            $file = sanitize_file_name($_GET['file']);
            $file_path = wp_upload_dir()['basedir'] . '/khaisa-order-exports/' . $file;

            if (!file_exists($file_path)) {
                wp_die(__('File not found.', 'khaisa-product-exporter'));
            }

            // Set headers for download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $file);
            header('Pragma: no-cache');
            header('Expires: 0');

            // Output file
            readfile($file_path);

            // Clean up - delete file after download
            unlink($file_path);
            exit;
        }
    }

    /**
     * AJAX handler for order preview
     */
    public function ajax_preview_orders() {
        check_ajax_referer('kpe_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this resource.', 'khaisa-product-exporter'));
        }

        $filters = $this->sanitize_filters($_POST);
        $exporter = new KPE_Exporter();
        
        try {
            $preview_data = $exporter->get_order_preview($filters);
            wp_send_json_success($preview_data);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * AJAX handler for order export
     */
    public function ajax_export_orders() {
        check_ajax_referer('kpe_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have permission to access this resource.', 'khaisa-product-exporter'));
        }

        $filters = $this->sanitize_filters($_POST);
        $exporter = new KPE_Exporter();
        
        try {
            $result = $exporter->export_orders($filters);
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Sanitize filter inputs
     */
    private function sanitize_filters($input) {
        $filters = array();
        
        // Date range
        $filters['date_from'] = isset($input['date_from']) ? sanitize_text_field($input['date_from']) : '';
        $filters['date_to'] = isset($input['date_to']) ? sanitize_text_field($input['date_to']) : '';
        
        // Order statuses
        $filters['statuses'] = isset($input['statuses']) ? array_map('sanitize_text_field', (array)$input['statuses']) : array();
        
        // Customer ID
        $filters['customer_id'] = isset($input['customer_id']) ? intval($input['customer_id']) : 0;
        
        // Product ID
        $filters['product_id'] = isset($input['product_id']) ? intval($input['product_id']) : 0;
        
        // Export format
        $filters['format'] = isset($input['format']) ? sanitize_text_field($input['format']) : 'detailed';
        
        // Include fields
        $filters['include_billing'] = isset($input['include_billing']) ? (bool)$input['include_billing'] : true;
        $filters['include_shipping'] = isset($input['include_shipping']) ? (bool)$input['include_shipping'] : true;
        $filters['include_items'] = isset($input['include_items']) ? (bool)$input['include_items'] : true;
        $filters['include_notes'] = isset($input['include_notes']) ? (bool)$input['include_notes'] : false;
        
        // Limit
        $filters['limit'] = isset($input['limit']) ? intval($input['limit']) : 0;
        
        return $filters;
    }

    /**
     * Render the admin page
     */
    public function render_admin_page() {
        // Get available order statuses
        $order_statuses = wc_get_order_statuses();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Khaisa WooExporter', 'khaisa-product-exporter'); ?></h1>

            <div class="kpe-nav-tabs">
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=khaisa-product-exporter'); ?>"><?php _e('Plugin Status', 'khaisa-product-exporter'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=khaisa-order-exporter'); ?>" class="current"><?php _e('Export Orders', 'khaisa-product-exporter'); ?></a></li>
                </ul>
            </div>

            <div class="kpe-container">
                <div class="kpe-main-content">
                    <form id="kpe-export-form">
                        <?php wp_nonce_field('kpe_nonce', 'nonce'); ?>
                        
                        <!-- Date Range Section -->
                        <div class="kpe-section">
                            <h3><?php _e('Date Range', 'khaisa-product-exporter'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="date_from"><?php _e('From Date', 'khaisa-product-exporter'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="date_from" name="date_from" class="datepicker" placeholder="<?php _e('Select start date', 'khaisa-product-exporter'); ?>" />
                                        <p class="description"><?php _e('Leave empty to export from the beginning', 'khaisa-product-exporter'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="date_to"><?php _e('To Date', 'khaisa-product-exporter'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="date_to" name="date_to" class="datepicker" placeholder="<?php _e('Select end date', 'khaisa-product-exporter'); ?>" />
                                        <p class="description"><?php _e('Leave empty to export until now', 'khaisa-product-exporter'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Order Status Section -->
                        <div class="kpe-section">
                            <h3><?php _e('Order Status', 'khaisa-product-exporter'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Select Statuses', 'khaisa-product-exporter'); ?></th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text"><?php _e('Order Statuses', 'khaisa-product-exporter'); ?></legend>
                                            <?php foreach ($order_statuses as $status_key => $status_name) : ?>
                                            <label>
                                                <input type="checkbox" name="statuses[]" value="<?php echo esc_attr($status_key); ?>" <?php checked(in_array($status_key, array('wc-completed', 'wc-processing'))); ?> />
                                                <?php echo esc_html($status_name); ?>
                                            </label><br>
                                            <?php endforeach; ?>
                                        </fieldset>
                                        <p class="description"><?php _e('Select which order statuses to include in the export', 'khaisa-product-exporter'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Additional Filters Section -->
                        <div class="kpe-section">
                            <h3><?php _e('Additional Filters', 'khaisa-product-exporter'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="customer_id"><?php _e('Customer ID', 'khaisa-product-exporter'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="customer_id" name="customer_id" min="0" />
                                        <p class="description"><?php _e('Export orders from specific customer (leave empty for all)', 'khaisa-product-exporter'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="product_id"><?php _e('Product ID', 'khaisa-product-exporter'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="product_id" name="product_id" min="0" />
                                        <p class="description"><?php _e('Export orders containing specific product (leave empty for all)', 'khaisa-product-exporter'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="limit"><?php _e('Limit Results', 'khaisa-product-exporter'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="limit" name="limit" min="0" max="10000" />
                                        <p class="description"><?php _e('Maximum number of orders to export (0 = no limit, max 10,000)', 'khaisa-product-exporter'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Export Options Section -->
                        <div class="kpe-section">
                            <h3><?php _e('Export Options', 'khaisa-product-exporter'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Include Data', 'khaisa-product-exporter'); ?></th>
                                    <td>
                                        <fieldset>
                                            <legend class="screen-reader-text"><?php _e('Include Data', 'khaisa-product-exporter'); ?></legend>
                                            <label>
                                                <input type="checkbox" name="include_billing" value="1" checked />
                                                <?php _e('Billing Information', 'khaisa-product-exporter'); ?>
                                            </label><br>
                                            <label>
                                                <input type="checkbox" name="include_shipping" value="1" checked />
                                                <?php _e('Shipping Information', 'khaisa-product-exporter'); ?>
                                            </label><br>
                                            <label>
                                                <input type="checkbox" name="include_items" value="1" checked />
                                                <?php _e('Order Items', 'khaisa-product-exporter'); ?>
                                            </label><br>
                                            <label>
                                                <input type="checkbox" name="include_notes" value="1" />
                                                <?php _e('Order Notes', 'khaisa-product-exporter'); ?>
                                            </label><br>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="format"><?php _e('Export Format', 'khaisa-product-exporter'); ?></label>
                                    </th>
                                    <td>
                                        <select id="format" name="format">
                                            <option value="detailed"><?php _e('Detailed (All Information)', 'khaisa-product-exporter'); ?></option>
                                            <option value="summary"><?php _e('Summary (Order Overview)', 'khaisa-product-exporter'); ?></option>
                                            <option value="items_only"><?php _e('Items Only', 'khaisa-product-exporter'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Actions -->
                        <div class="kpe-actions">
                            <button type="button" id="kpe-preview" class="button button-secondary">
                                <?php _e('Preview Results', 'khaisa-product-exporter'); ?>
                            </button>
                            <button type="submit" id="kpe-export" class="button button-primary">
                                <?php _e('Export to CSV', 'khaisa-product-exporter'); ?>
                            </button>
                        </div>
                    </form>

                    <!-- Results Section -->
                    <div id="kpe-results" class="kpe-section" style="display: none;">
                        <h3><?php _e('Export Results', 'khaisa-product-exporter'); ?></h3>
                        <div id="kpe-results-content"></div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="kpe-sidebar">
                    <div class="kpe-widget">
                        <h3><?php _e('Quick Export Templates', 'khaisa-product-exporter'); ?></h3>
                        <p><?php _e('Common export scenarios:', 'khaisa-product-exporter'); ?></p>
                        <ul>
                            <li><a href="#" data-template="this_month"><?php _e('This Month Orders', 'khaisa-product-exporter'); ?></a></li>
                            <li><a href="#" data-template="last_month"><?php _e('Last Month Orders', 'khaisa-product-exporter'); ?></a></li>
                            <li><a href="#" data-template="completed_only"><?php _e('Completed Orders Only', 'khaisa-product-exporter'); ?></a></li>
                            <li><a href="#" data-template="last_30_days"><?php _e('Last 30 Days', 'khaisa-product-exporter'); ?></a></li>
                        </ul>
                    </div>

                    <div class="kpe-widget">
                        <h3><?php _e('Export Tips', 'khaisa-product-exporter'); ?></h3>
                        <ul>
                            <li><?php _e('Use date ranges for better performance', 'khaisa-product-exporter'); ?></li>
                            <li><?php _e('Preview results before large exports', 'khaisa-product-exporter'); ?></li>
                            <li><?php _e('Limit results for testing purposes', 'khaisa-product-exporter'); ?></li>
                            <li><?php _e('Include only necessary data to reduce file size', 'khaisa-product-exporter'); ?></li>
                        </ul>
                    </div>

                    <div class="kpe-widget">
                        <h3><?php _e('Help & Support', 'khaisa-product-exporter'); ?></h3>
                        <p><?php _e('Need help? Check our documentation or contact support.', 'khaisa-product-exporter'); ?></p>
                        <p>
                            <a href="#" class="button button-secondary"><?php _e('Documentation', 'khaisa-product-exporter'); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}