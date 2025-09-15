# Khaisa Product Exporter

![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.0+-blue.svg)
![WooCommerce Compatibility](https://img.shields.io/badge/WooCommerce-3.0+-green.svg)
![PHP Version](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![License](https://img.shields.io/badge/license-GPL--3.0+-red.svg)

**A powerful WordPress plugin for exporting WooCommerce orders with detailed customer information and order items.**

Built with ❤️ by **[Khairul Muhtadin](https://khmuhtadin.com)** 


## ✨ Features

### 🎯 **Complete Order Data Export**
- ✅ Order information (ID, status, dates, totals, payment methods)
- ✅ Complete customer billing information  
- ✅ Shipping addresses and details
- ✅ Individual order items with product details
- ✅ SKU, quantities, prices, and revenue data
- ✅ Tax and shipping breakdowns

### 🔍 **Advanced Filtering Options**
- 📅 **Date Range Filtering** - Export orders from specific time periods
- 🎯 **Order Status Selection** - Choose which statuses to include
- 👤 **Customer Filtering** - Export orders from specific customers  
- 📦 **Product Filtering** - Export orders containing specific products
- 🔢 **Result Limiting** - Set maximum number of orders to export

### 🖥️ **User-Friendly Interface**
- **WordPress Admin Integration** - Seamlessly integrates with WooCommerce menu
- **Preview Before Export** - See a sample of your data before full export
- **Quick Export Templates** - Pre-configured options for common scenarios
- **Responsive Design** - Works perfectly on desktop, tablet, and mobile
- **Real-time Validation** - Form validation with helpful error messages

### 🏗️ **Technical Excellence**  
- **HPOS Compatible** - Supports both legacy and High-Performance Order Storage
- **Secure Downloads** - Temporary file generation with automatic cleanup
- **UTF-8 Support** - Proper encoding for international characters
- **Performance Optimized** - Efficient database queries for large datasets
- **Security First** - Proper nonce verification and capability checks

## 🚀 Installation

### Method 1: WordPress Admin Upload

1. Download the latest release from [GitHub Releases](https://github.com/khmuhtadin/khaisa-product-exporter/releases)
2. Go to **WordPress Admin → Plugins → Add New**
3. Click **"Upload Plugin"**
4. Choose the downloaded ZIP file
5. Click **"Install Now"** 
6. Click **"Activate Plugin"**

### Method 2: Manual Installation

1. Download and extract the plugin files
2. Upload the `khaisa-product-exporter` folder to `/wp-content/plugins/`
3. Go to **WordPress Admin → Plugins**
4. Find **"Khaisa Product Exporter"** and click **"Activate"**

## 🎯 How to Use

### 1. Access the Exporter
Go to **WooCommerce → Order Exporter** in your WordPress admin

### 2. Configure Export Settings
- **Date Range**: Set start and end dates (or leave empty for all-time)
- **Order Status**: Select which order statuses to include
- **Filters**: Add customer ID, product ID, or result limits
- **Export Options**: Choose what data to include (billing, shipping, items)

### 3. Preview or Export
- **Preview Results**: See a sample of your data first
- **Export to CSV**: Generate and download the full CSV file

### 4. Quick Templates
Use pre-configured templates:
- This Month Orders
- Last Month Orders  
- Completed Orders Only
- Last 30 Days

## 📊 Export Data Fields

The plugin exports comprehensive data including:

**Order Information**: Order ID, Status, Dates, Totals, Currency, Payment Method  
**Customer Billing**: Name, Email, Phone, Company, Full Address  
**Customer Shipping**: Recipient Name, Full Delivery Address  
**Order Items**: Product Details, SKU, Quantities, Prices, Revenue, Tax

See the full [field reference](https://github.com/khmuhtadin/khaisa-product-exporter#export-fields) for complete details.

## 🔧 Requirements

- **WordPress**: 5.0 or higher
- **WooCommerce**: 3.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher

## 🛡️ Security Features

- ✅ **Capability Checks** - Only users with `manage_woocommerce` permission
- ✅ **Nonce Verification** - All AJAX requests properly secured
- ✅ **Temporary Files** - Export files automatically deleted after download
- ✅ **Data Validation** - All input properly sanitized and validated

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Clone the repository:
```bash
git clone https://github.com/khmuhtadin/khaisa-product-exporter.git
```

2. Navigate to your WordPress plugins directory and create a symlink:
```bash
ln -s /path/to/cloned/repo /path/to/wordpress/wp-content/plugins/khaisa-product-exporter
```

3. Activate the plugin in WordPress admin

### Coding Standards

- Follow WordPress Coding Standards
- Use proper PHPDoc comments
- Test on multiple PHP versions (7.4+)
- Ensure WooCommerce HPOS compatibility

## 📝 Changelog

### Version 1.0.0
- ✨ Initial release
- 🎯 Complete order export functionality  
- 🖥️ Modern admin interface
- 🏗️ HPOS compatibility
- 🔒 Security implementation
- 📱 Responsive design

## 🐛 Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/khmuhtadin/khaisa-product-exporter/issues)
- **Documentation**: Check this README and inline code comments
- **WordPress Support**: Compatible with WordPress.org plugin guidelines

## 📄 License

This plugin is licensed under the **GPL v3.0 or later**.

```
Khaisa Product Exporter
Copyright (C) 2024 Khairul Muhtadin

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👨‍💻 About the Author

**Khairul Muhtadin**  
n8n Verified Creator & Performance Marketer

- 🌐 Website: [khmuhtadin.com](https://khmuhtadin.com)
- 🐙 GitHub: [@khmuhtadin](https://github.com/khmuhtadin)
- 💼 LinkedIn: [Khairul Muhtadin](https://linkedin.com/in/khmuhtadin)


---

**Made with ❤️ for the WooCommerce community**
⭐ **Star this repository if you find it helpful!**