# Troubleshooting Guide - Khaisa Product Exporter

## 🔍 Plugin Menu Tidak Muncul (Menu Not Appearing)

### Solusi Umum (Common Solutions):

#### 1. **Periksa WooCommerce Status**
Plugin ini memerlukan WooCommerce untuk berfungsi. Menu akan muncul di:

**Lokasi Menu:**
- **Primary**: `WooCommerce → Order Exporter`
- **Alternative**: `Order Exporter` (menu utama di sidebar)

#### 2. **Langkah Troubleshooting:**

```bash
# 1. Pastikan WooCommerce aktif
WordPress Admin → Plugins → Cari "WooCommerce" → Activate

# 2. Periksa permission user
User harus memiliki capability 'manage_woocommerce'

# 3. Clear cache jika menggunakan caching plugin
```

#### 3. **Status Check Page**
Jika WooCommerce tidak aktif, akan muncul menu `KPE Status` yang menampilkan:
- Plugin version
- WordPress version  
- WooCommerce status
- PHP compatibility
- Langkah-langkah perbaikan

### 🛠️ Debugging Steps:

#### Step 1: Cek Plugin Active
```php
// Pastikan plugin active di WordPress Admin → Plugins
// Look for "Khaisa Product Exporter" dengan status "Active"
```

#### Step 2: Cek WooCommerce 
```php
// WordPress Admin → Plugins
// Pastikan "WooCommerce" active
// Atau install dari: Add New → Search "WooCommerce"
```

#### Step 3: Cek User Permission
```php
// User yang login harus memiliki role:
// - Administrator (recommended)
// - Shop Manager
// - Atau role dengan capability 'manage_woocommerce'
```

#### Step 4: Cek PHP Version
```php
// Plugin memerlukan PHP 7.4+
// Cek di: WordPress Admin → Tools → Site Health
```

## 🔧 Manual Fix

Jika masih bermasalah, tambahkan code ini ke `functions.php` theme:

```php
// Temporary debug function - REMOVE setelah testing
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
1. **KPE Status** (main menu) - Shows troubleshooting info

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

**Pro Tip**: Setelah WooCommerce diaktifkan, refresh halaman admin WordPress untuk melihat menu muncul.