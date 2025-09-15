# Contributing to Khaisa Product Exporter

Thank you for your interest in contributing to the Khaisa Product Exporter! We welcome contributions from the community.

## 🚀 Getting Started

### Prerequisites
- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+
- Basic knowledge of WordPress plugin development

### Development Setup

1. **Clone the repository**:
```bash
git clone https://github.com/khmuhtadin/khaisa-product-exporter.git
cd khaisa-product-exporter
```

2. **Set up your WordPress development environment**:
   - Use Local by Flywheel, XAMPP, or Docker
   - Install WordPress and WooCommerce
   - Activate WooCommerce and add some test orders

3. **Install the plugin**:
```bash
# Create symlink in your WordPress plugins directory
ln -s /path/to/khaisa-product-exporter /path/to/wordpress/wp-content/plugins/khaisa-product-exporter
```

4. **Activate the plugin** in WordPress admin

## 🛠️ Development Guidelines

### Code Standards
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use proper PHPDoc comments for all functions and classes
- Ensure compatibility with PHP 7.4+
- Test on multiple WordPress/WooCommerce versions

### Security
- Always sanitize and validate user inputs
- Use WordPress nonces for form submissions
- Check user capabilities before performing sensitive operations
- Follow WordPress security best practices

### Testing
- Test with both HPOS (High-Performance Order Storage) enabled and disabled
- Test with large datasets (1000+ orders)
- Verify CSV exports open correctly in Excel and Google Sheets
- Test on different PHP versions

## 📝 How to Contribute

### Reporting Bugs
1. Check if the issue already exists in [GitHub Issues](https://github.com/khmuhtadin/khaisa-product-exporter/issues)
2. Create a new issue with:
   - Clear description of the problem
   - Steps to reproduce
   - WordPress/WooCommerce/PHP versions
   - Expected vs actual behavior

### Suggesting Features
1. Open a new issue with the "enhancement" label
2. Describe the feature and its benefits
3. Explain the use case
4. Consider backward compatibility

### Submitting Code Changes

1. **Fork the repository**
2. **Create a feature branch**:
```bash
git checkout -b feature/your-feature-name
```

3. **Make your changes**:
   - Keep commits small and focused
   - Write clear commit messages
   - Add comments for complex logic

4. **Test thoroughly**:
   - Test with different order statuses
   - Test export with various filter combinations
   - Verify CSV format and encoding

5. **Submit a Pull Request**:
   - Provide a clear description of changes
   - Reference any related issues
   - Include screenshots if UI changes are involved

## 🔍 Code Review Process

1. All submissions require review before merging
2. We may suggest changes or improvements
3. Once approved, changes will be merged
4. Contributors will be credited in releases

## 🆘 Getting Help

- Check the [documentation](README.md)
- Search existing [GitHub Issues](https://github.com/khmuhtadin/khaisa-product-exporter/issues)
- Join the discussion in existing issues
- Contact [@khmuhtadin](https://github.com/khmuhtadin) for major questions

## 📊 Areas for Contribution

We especially welcome contributions in these areas:

### High Priority
- **Performance optimization** for large datasets
- **Additional export formats** (Excel, JSON)
- **Advanced filtering options** (by product category, customer groups)
- **Scheduled exports** functionality
- **Email delivery** of exports

### Medium Priority
- **Custom field support** for orders and products
- **Multi-site compatibility**
- **REST API endpoints** for programmatic access
- **Export templates** and presets
- **Internationalization** improvements

### Documentation
- **Video tutorials** for complex features
- **Developer documentation** for extending the plugin
- **FAQ** based on common support questions
- **Translation** to other languages

## 🏷️ Versioning

We use [Semantic Versioning](https://semver.org/):
- **MAJOR** version for incompatible API changes
- **MINOR** version for backward-compatible functionality additions
- **PATCH** version for backward-compatible bug fixes

## 📜 License

By contributing, you agree that your contributions will be licensed under the GPL v3.0 License.

## 🙏 Recognition

Contributors will be:
- Listed in the plugin credits
- Mentioned in release notes
- Added to the GitHub contributors list
- Acknowledged in the WordPress.org plugin page (if applicable)

---

**Thank you for helping make Khaisa Product Exporter better! 🚀**