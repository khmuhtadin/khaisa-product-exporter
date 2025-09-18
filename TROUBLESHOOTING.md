# Troubleshooting Guide - Khaisa Product Exporter

## 🔍 Plugin Menu Not Appearing

### Common Solutions:

#### 1. **Check WooCommerce Status**
This plugin requires WooCommerce to function. The menu will appear at:

**Menu Locations:**
- **Primary**: `WooCommerce → Order Exporter`
- **Alternative**: `Order Exporter` (main menu in sidebar)

#### 2. **Troubleshooting Steps:**

```bash
# 1. Ensure WooCommerce is active
WordPress Admin → Plugins → Search "WooCommerce" → Activate

# 2. Check user permissions
User must have 'manage_woocommerce' capability

# 3. Clear cache if using caching plugins
```

#### 3. **Status Check Page**
If WooCommerce is not active, a `KPE Status` menu will appear showing:
- Plugin version
- WordPress version  
- WooCommerce status
- PHP compatibility
- Troubleshooting steps

### 🛠️ Debugging Steps:

#### Step 1: Check Plugin Active
```php
// Ensure plugin is active in WordPress Admin → Plugins
// Look for "Khaisa Product Exporter" with "Active" status
```

#### Step 2: Check WooCommerce 
```php
// WordPress Admin → Plugins
// Ensure "WooCommerce" is active
// Or install from: Add New → Search "WooCommerce"
```

#### Step 3: Check User Permissions
```php
// Logged in user must have role:
// - Administrator (recommended)
// - Shop Manager
// - Or role with 'manage_woocommerce' capability
```

#### Step 4: Check PHP Version
```php
// Plugin requires PHP 7.4+
// Check at: WordPress Admin → Tools → Site Health
```

## 🔧 Manual Fix

If still experiencing issues, add this code to your theme's `functions.php`:

```php
// Temporary debug function - REMOVE after testing
add_action('admin_notices', function() {
    if (class_exists('WooCommerce')) {
        echo '<div class="notice notice-success"><p>WooCommerce ACTIVE ✓</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>WooCommerce NOT ACTIVE ✗</p></div>';
    }
    
    if (class_exists('KhaisaProductExporter')) {
        echo '<div class="notice notice-success"><p>Khaisa Product Exporter LOADED ✓</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Khaisa Product Exporter NOT LOADED ✗</p></div>';
    }
});
```

## 📍 Expected Menu Locations

### ✅ When WooCommerce is Active:
1. **WooCommerce → Order Exporter** (submenu)
2. **Order Exporter** (main menu with download icon)

### ⚠️ When WooCommerce is NOT Active:
1. **KPE Status** (main menu) - Shows troubleshooting information

## 🆘 Still Having Issues?

### Contact Support:
- **GitHub Issues**: https://github.com/khmuhtadin/khaisa-product-exporter/issues
- **Documentation**: Check README.md in plugin folder
- **Requirements**: WordPress 5.0+, WooCommerce 3.0+, PHP 7.4+

### System Requirements Check:
```
✅ WordPress 5.0+
✅ WooCommerce 3.0+ (ACTIVE)
✅ PHP 7.4+
✅ User with 'manage_woocommerce' capability
✅ Plugin activated in WordPress Admin → Plugins
```

---

**Pro Tip**: After activating WooCommerce, refresh the WordPress admin page to see the menu appear.