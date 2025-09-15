# Changelog

All notable changes to the Khaisa Product Exporter will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Excel export format support
- Scheduled export functionality
- Additional filter options (product categories, customer groups)
- REST API endpoints
- Custom field support

## [1.0.0] - 2024-09-15

### Added
- 🎯 Complete order data export functionality
- 📅 Advanced date range filtering
- 🎯 Order status selection filters
- 👤 Customer and product filtering options
- 🖥️ Modern, responsive admin interface
- 🔍 Preview functionality before export
- 🏗️ HPOS (High-Performance Order Storage) compatibility
- 🔒 Security implementation with nonces and capability checks
- 📱 Mobile-responsive design
- 🌐 Internationalization support
- ⚡ Performance optimization for large datasets
- 🎨 Quick export templates (This Month, Last Month, etc.)
- 📊 Comprehensive CSV export with UTF-8 BOM support
- 🛡️ Automatic file cleanup after download
- 💾 Temporary file management for security

### Export Features
- Order information (ID, status, dates, totals, payment methods)
- Complete customer billing information
- Shipping addresses and details
- Individual order items with product details
- SKU, quantities, prices, and revenue data
- Tax and shipping breakdowns
- Configurable export formats (Detailed, Summary, Items Only)

### Technical Features
- WordPress 5.0+ compatibility
- WooCommerce 3.0+ compatibility
- PHP 7.4+ support
- Legacy and HPOS database query support
- Proper WordPress coding standards
- Security-first implementation
- Efficient database queries
- Memory optimization for large exports

### Documentation
- Comprehensive README with installation and usage instructions
- Inline code documentation
- Security and performance guidelines
- Contributing guidelines

[Unreleased]: https://github.com/khmuhtadin/khaisa-product-exporter/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/khmuhtadin/khaisa-product-exporter/releases/tag/v1.0.0