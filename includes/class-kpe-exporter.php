<?php
/**
 * Export functionality for WooCommerce Advanced Order Exporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class KPE_Exporter {

    /**
     * Export orders to CSV
     */
    public function export_orders($filters) {
        global $wpdb;
        
        // Create upload directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/khaisa-order-exports';
        
        if (!wp_mkdir_p($export_dir)) {
            throw new Exception(__('Could not create export directory.', 'khaisa-product-exporter'));
        }
        
        // Generate filename
        $filename = 'wc-orders-export-' . date('Y-m-d-H-i-s') . '.csv';
        $filepath = $export_dir . '/' . $filename;
        
        // Get orders data
        $orders_data = $this->get_orders_data($filters);
        
        if (empty($orders_data)) {
            throw new Exception(__('No orders found matching the criteria.', 'khaisa-product-exporter'));
        }
        
        // Create CSV file
        $fp = fopen($filepath, 'w');
        if (!$fp) {
            throw new Exception(__('Could not create export file.', 'khaisa-product-exporter'));
        }
        
        // Add BOM for UTF-8
        fwrite($fp, "\xEF\xBB\xBF");
        
        // Write headers
        if (!empty($orders_data)) {
            fputcsv($fp, array_keys($orders_data[0]));
            
            // Write data
            foreach ($orders_data as $row) {
                fputcsv($fp, $row);
            }
        }
        
        fclose($fp);
        
        // Return download information
        return array(
            'filename' => $filename,
            'download_url' => admin_url('admin.php?kpe_download=1&file=' . urlencode($filename) . '&nonce=' . wp_create_nonce('kpe_download')),
            'count' => count($orders_data),
            'filesize' => size_format(filesize($filepath))
        );
    }

    /**
     * Get order preview data
     */
    public function get_order_preview($filters, $limit = 10) {
        $filters['limit'] = $limit;
        $orders_data = $this->get_orders_data($filters);
        
        return array(
            'data' => $orders_data,
            'total_count' => $this->count_orders($filters),
            'columns' => !empty($orders_data) ? array_keys($orders_data[0]) : array()
        );
    }

    /**
     * Count orders matching filters
     */
    private function count_orders($filters) {
        global $wpdb;
        
        // Determine if HPOS is enabled
        $hpos_enabled = $this->is_hpos_enabled();
        
        if ($hpos_enabled) {
            return $this->count_orders_hpos($filters);
        } else {
            return $this->count_orders_legacy($filters);
        }
    }

    /**
     * Count orders using HPOS
     */
    private function count_orders_hpos($filters) {
        global $wpdb;
        
        $where_conditions = array();
        $where_values = array();
        
        // Date filters
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "o.date_created_gmt >= %s";
            $where_values[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "o.date_created_gmt <= %s";
            $where_values[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Status filter
        if (!empty($filters['statuses'])) {
            $status_placeholders = implode(',', array_fill(0, count($filters['statuses']), '%s'));
            $where_conditions[] = "o.status IN ($status_placeholders)";
            $where_values = array_merge($where_values, $filters['statuses']);
        }
        
        // Customer filter
        if (!empty($filters['customer_id'])) {
            $where_conditions[] = "o.customer_id = %d";
            $where_values[] = $filters['customer_id'];
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $sql = "SELECT COUNT(DISTINCT o.id) FROM {$wpdb->prefix}wc_orders o $where_clause";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return (int) $wpdb->get_var($sql);
    }

    /**
     * Count orders using legacy posts table
     */
    private function count_orders_legacy($filters) {
        global $wpdb;
        
        $where_conditions = array("p.post_type = 'shop_order'");
        $where_values = array();
        
        // Date filters
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "p.post_date_gmt >= %s";
            $where_values[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "p.post_date_gmt <= %s";
            $where_values[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Status filter
        if (!empty($filters['statuses'])) {
            $status_placeholders = implode(',', array_fill(0, count($filters['statuses']), '%s'));
            $where_conditions[] = "p.post_status IN ($status_placeholders)";
            $where_values = array_merge($where_values, $filters['statuses']);
        }
        
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        
        $sql = "SELECT COUNT(p.ID) FROM {$wpdb->posts} p $where_clause";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return (int) $wpdb->get_var($sql);
    }

    /**
     * Get orders data based on filters
     */
    private function get_orders_data($filters) {
        // Determine if HPOS is enabled
        $hpos_enabled = $this->is_hpos_enabled();
        
        if ($hpos_enabled) {
            return $this->get_orders_data_hpos($filters);
        } else {
            return $this->get_orders_data_legacy($filters);
        }
    }

    /**
     * Get orders data using HPOS
     */
    private function get_orders_data_hpos($filters) {
        global $wpdb;
        
        $select_fields = array();
        $joins = array();
        $where_conditions = array();
        $where_values = array();
        
        // Base order fields
        $select_fields[] = "o.id as Order_ID";
        $select_fields[] = "o.status as Status";
        $select_fields[] = "o.date_created_gmt as Order_Date";
        $select_fields[] = "o.date_updated_gmt as Last_Updated";
        $select_fields[] = "o.total_amount as Order_Total";
        $select_fields[] = "o.tax_amount as Order_Tax";
        $select_fields[] = "o.currency as Currency";
        $select_fields[] = "o.payment_method as Payment_Method";
        $select_fields[] = "o.payment_method_title as Payment_Method_Title";
        $select_fields[] = "o.customer_id as Customer_ID";
        
        // Include billing information
        if ($filters['include_billing']) {
            $joins[] = "LEFT JOIN {$wpdb->prefix}wc_order_addresses ba ON o.id = ba.order_id AND ba.address_type = 'billing'";
            $select_fields[] = "COALESCE(ba.first_name, '') as Billing_First_Name";
            $select_fields[] = "COALESCE(ba.last_name, '') as Billing_Last_Name";
            $select_fields[] = "COALESCE(ba.email, o.billing_email) as Billing_Email";
            $select_fields[] = "COALESCE(ba.phone, '') as Billing_Phone";
            $select_fields[] = "COALESCE(ba.company, '') as Billing_Company";
            $select_fields[] = "COALESCE(ba.address_1, '') as Billing_Address_1";
            $select_fields[] = "COALESCE(ba.address_2, '') as Billing_Address_2";
            $select_fields[] = "COALESCE(ba.city, '') as Billing_City";
            $select_fields[] = "COALESCE(ba.state, '') as Billing_State";
            $select_fields[] = "COALESCE(ba.postcode, '') as Billing_Postcode";
            $select_fields[] = "COALESCE(ba.country, '') as Billing_Country";
        }
        
        // Include shipping information
        if ($filters['include_shipping']) {
            $joins[] = "LEFT JOIN {$wpdb->prefix}wc_order_addresses sa ON o.id = sa.order_id AND sa.address_type = 'shipping'";
            $select_fields[] = "COALESCE(sa.first_name, '') as Shipping_First_Name";
            $select_fields[] = "COALESCE(sa.last_name, '') as Shipping_Last_Name";
            $select_fields[] = "COALESCE(sa.address_1, '') as Shipping_Address_1";
            $select_fields[] = "COALESCE(sa.address_2, '') as Shipping_Address_2";
            $select_fields[] = "COALESCE(sa.city, '') as Shipping_City";
            $select_fields[] = "COALESCE(sa.state, '') as Shipping_State";
            $select_fields[] = "COALESCE(sa.postcode, '') as Shipping_Postcode";
            $select_fields[] = "COALESCE(sa.country, '') as Shipping_Country";
        }
        
        // Include order items
        if ($filters['include_items']) {
            $joins[] = "LEFT JOIN {$wpdb->prefix}wc_order_product_lookup opl ON o.id = opl.order_id";
            $joins[] = "LEFT JOIN {$wpdb->posts} p ON opl.product_id = p.ID";
            $joins[] = "LEFT JOIN {$wpdb->posts} pv ON opl.variation_id = pv.ID";
            $joins[] = "LEFT JOIN {$wpdb->postmeta} pm ON (CASE WHEN opl.variation_id > 0 THEN opl.variation_id ELSE opl.product_id END) = pm.post_id AND pm.meta_key = '_sku'";
            
            $select_fields[] = "opl.order_item_id as Order_Item_ID";
            $select_fields[] = "opl.product_id as Product_ID";
            $select_fields[] = "opl.variation_id as Variation_ID";
            $select_fields[] = "p.post_title as Product_Name";
            $select_fields[] = "CASE WHEN opl.variation_id > 0 THEN CONCAT(p.post_title, ' - ', pv.post_title) ELSE p.post_title END as Full_Product_Name";
            $select_fields[] = "pm.meta_value as SKU";
            $select_fields[] = "opl.product_qty as Quantity";
            $select_fields[] = "opl.product_gross_revenue as Item_Gross_Revenue";
            $select_fields[] = "opl.product_net_revenue as Item_Net_Revenue";
            $select_fields[] = "opl.coupon_amount as Item_Coupon_Amount";
            $select_fields[] = "opl.tax_amount as Item_Tax_Amount";
            $select_fields[] = "opl.shipping_amount as Item_Shipping_Amount";
            $select_fields[] = "opl.shipping_tax_amount as Item_Shipping_Tax_Amount";
        }
        
        // Date filters
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "o.date_created_gmt >= %s";
            $where_values[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "o.date_created_gmt <= %s";
            $where_values[] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Status filter
        if (!empty($filters['statuses'])) {
            $status_placeholders = implode(',', array_fill(0, count($filters['statuses']), '%s'));
            $where_conditions[] = "o.status IN ($status_placeholders)";
            $where_values = array_merge($where_values, $filters['statuses']);
        }
        
        // Customer filter
        if (!empty($filters['customer_id'])) {
            $where_conditions[] = "o.customer_id = %d";
            $where_values[] = $filters['customer_id'];
        }
        
        // Product filter
        if (!empty($filters['product_id']) && $filters['include_items']) {
            $where_conditions[] = "opl.product_id = %d";
            $where_values[] = $filters['product_id'];
        }
        
        // Build query
        $select_clause = "SELECT " . implode(', ', $select_fields);
        $from_clause = "FROM {$wpdb->prefix}wc_orders o";
        $join_clause = !empty($joins) ? implode(' ', $joins) : '';
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        $order_clause = "ORDER BY o.date_created_gmt DESC, o.id DESC";
        
        if ($filters['include_items']) {
            $order_clause .= ", opl.order_item_id ASC";
        }
        
        $limit_clause = '';
        if (!empty($filters['limit'])) {
            $limit_clause = "LIMIT " . intval($filters['limit']);
        }
        
        $sql = "$select_clause $from_clause $join_clause $where_clause $order_clause $limit_clause";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        return $results ? $results : array();
    }

    /**
     * Get orders data using legacy posts table
     */
    private function get_orders_data_legacy($filters) {
        // For legacy support, we'll use WC_Order objects
        // This is simpler but might be slower for large datasets
        
        $args = array(
            'type' => 'shop_order',
            'limit' => $filters['limit'] > 0 ? $filters['limit'] : -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'objects'
        );
        
        // Date filters
        if (!empty($filters['date_from'])) {
            $args['date_created'] = '>=' . $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $date_query = isset($args['date_created']) ? $args['date_created'] . '...' : '...';
            $args['date_created'] = $date_query . $filters['date_to'];
        }
        
        // Status filter
        if (!empty($filters['statuses'])) {
            $args['status'] = $filters['statuses'];
        }
        
        // Customer filter
        if (!empty($filters['customer_id'])) {
            $args['customer_id'] = $filters['customer_id'];
        }
        
        $orders = wc_get_orders($args);
        $results = array();
        
        foreach ($orders as $order) {
            if (!$filters['include_items']) {
                // Single row per order
                $row = $this->get_order_row_data($order, $filters);
                $results[] = $row;
            } else {
                // One row per order item
                $items = $order->get_items();
                if (empty($items)) {
                    // Order with no items, include one row
                    $row = $this->get_order_row_data($order, $filters);
                    $results[] = $row;
                } else {
                    foreach ($items as $item_id => $item) {
                        $row = $this->get_order_row_data($order, $filters, $item_id, $item);
                        $results[] = $row;
                    }
                }
            }
        }
        
        return $results;
    }

    /**
     * Get row data for a single order
     */
    private function get_order_row_data($order, $filters, $item_id = null, $item = null) {
        $row = array();
        
        // Basic order data
        $row['Order_ID'] = $order->get_id();
        $row['Status'] = $order->get_status();
        $row['Order_Date'] = $order->get_date_created()->format('Y-m-d H:i:s');
        $row['Last_Updated'] = $order->get_date_modified()->format('Y-m-d H:i:s');
        $row['Order_Total'] = $order->get_total();
        $row['Order_Tax'] = $order->get_total_tax();
        $row['Currency'] = $order->get_currency();
        $row['Payment_Method'] = $order->get_payment_method();
        $row['Payment_Method_Title'] = $order->get_payment_method_title();
        $row['Customer_ID'] = $order->get_customer_id();
        
        // Billing information
        if ($filters['include_billing']) {
            $row['Billing_First_Name'] = $order->get_billing_first_name();
            $row['Billing_Last_Name'] = $order->get_billing_last_name();
            $row['Billing_Email'] = $order->get_billing_email();
            $row['Billing_Phone'] = $order->get_billing_phone();
            $row['Billing_Company'] = $order->get_billing_company();
            $row['Billing_Address_1'] = $order->get_billing_address_1();
            $row['Billing_Address_2'] = $order->get_billing_address_2();
            $row['Billing_City'] = $order->get_billing_city();
            $row['Billing_State'] = $order->get_billing_state();
            $row['Billing_Postcode'] = $order->get_billing_postcode();
            $row['Billing_Country'] = $order->get_billing_country();
        }
        
        // Shipping information
        if ($filters['include_shipping']) {
            $row['Shipping_First_Name'] = $order->get_shipping_first_name();
            $row['Shipping_Last_Name'] = $order->get_shipping_last_name();
            $row['Shipping_Address_1'] = $order->get_shipping_address_1();
            $row['Shipping_Address_2'] = $order->get_shipping_address_2();
            $row['Shipping_City'] = $order->get_shipping_city();
            $row['Shipping_State'] = $order->get_shipping_state();
            $row['Shipping_Postcode'] = $order->get_shipping_postcode();
            $row['Shipping_Country'] = $order->get_shipping_country();
        }
        
        // Item information
        if ($filters['include_items'] && $item) {
            $product = $item->get_product();
            
            $row['Order_Item_ID'] = $item_id;
            $row['Product_ID'] = $item->get_product_id();
            $row['Variation_ID'] = $item->get_variation_id();
            $row['Product_Name'] = $item->get_name();
            $row['Full_Product_Name'] = $item->get_name();
            $row['SKU'] = $product ? $product->get_sku() : '';
            $row['Quantity'] = $item->get_quantity();
            $row['Item_Gross_Revenue'] = $item->get_total() + $item->get_total_tax();
            $row['Item_Net_Revenue'] = $item->get_total();
            $row['Item_Coupon_Amount'] = 0; // Would need additional calculation
            $row['Item_Tax_Amount'] = $item->get_total_tax();
            $row['Item_Shipping_Amount'] = 0; // Would need additional calculation
            $row['Item_Shipping_Tax_Amount'] = 0; // Would need additional calculation
        } elseif ($filters['include_items']) {
            // Empty item data
            $row['Order_Item_ID'] = '';
            $row['Product_ID'] = '';
            $row['Variation_ID'] = '';
            $row['Product_Name'] = '';
            $row['Full_Product_Name'] = '';
            $row['SKU'] = '';
            $row['Quantity'] = '';
            $row['Item_Gross_Revenue'] = '';
            $row['Item_Net_Revenue'] = '';
            $row['Item_Coupon_Amount'] = '';
            $row['Item_Tax_Amount'] = '';
            $row['Item_Shipping_Amount'] = '';
            $row['Item_Shipping_Tax_Amount'] = '';
        }
        
        return $row;
    }

    /**
     * Check if WooCommerce HPOS is enabled
     */
    private function is_hpos_enabled() {
        return class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && 
               method_exists('Automattic\WooCommerce\Utilities\OrderUtil', 'custom_orders_table_usage_is_enabled') &&
               \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }
}