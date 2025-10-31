# Contributing to Scan QR Module

Thank you for your interest in contributing to the Scan QR module! This document provides guidelines for contributing to this project.

## 🚀 Getting Started

### Prerequisites
- Drupal 11.x development environment
- PHP 8.1 or higher
- Composer
- Git
- Modern browser for testing

### Development Setup

1. **Fork and clone the repository**:
   ```bash
   git clone https://github.com/your-username/scanqr.git
   cd scanqr
   ```

2. **Install dependencies**:
   ```bash
   composer require chillerlan/php-qrcode
   ```

3. **Set up development environment**:
   ```bash
   # Using DDEV (recommended)
   ddev start
   ddev composer install
   ddev drush en scanqr -y
   ```

## 📝 How to Contribute

### Reporting Bugs

Before creating bug reports, please check existing issues. When creating a bug report, include:

- **Use a clear, descriptive title**
- **Describe the exact steps** to reproduce the problem
- **Provide specific examples** and sample code if possible
- **Describe the expected vs actual behavior**
- **Include environment details** (Drupal version, PHP version, browser)

### Suggesting Enhancements

Enhancement suggestions are welcome! Please include:

- **Use a clear, descriptive title**
- **Provide a detailed description** of the suggested enhancement
- **Explain why this enhancement would be useful**
- **List any alternatives** you've considered

### Pull Requests

1. **Fork the repository** and create your branch from `main`
2. **Follow coding standards** (Drupal coding standards)
3. **Add tests** for new functionality
4. **Update documentation** as needed
5. **Ensure CI passes** all checks
6. **Create a pull request** with a clear title and description

#### Pull Request Process

1. Update the README.md with details of changes if applicable
2. Update the CHANGELOG.md with your changes
3. The PR will be merged once reviewed and approved

## 🎯 Development Guidelines

### Code Style

This project follows [Drupal coding standards](https://www.drupal.org/docs/develop/standards):

- Use 2-space indentation
- Follow PSR-4 autoloading standards
- Use meaningful variable and function names
- Add docblocks for all classes and methods

### Testing

- Test on multiple browsers (Chrome, Firefox, Safari, Mobile Safari)
- Test QR scanning with various QR code types and sizes
- Verify mobile responsiveness
- Test with and without camera permissions

### JavaScript Guidelines

- Use ES5 syntax for broad browser compatibility
- Follow Drupal JavaScript coding standards
- Test camera functionality across devices
- Handle errors gracefully (no camera, permissions denied)

## 🏷️ Commit Messages

Use clear, descriptive commit messages:

- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters
- Reference issues and pull requests when applicable

Example:
```
Add mobile camera optimization for QR scanning

- Improve camera constraints for mobile devices
- Add touch-friendly scanner controls
- Fix iOS Safari camera permission handling

Fixes #123
```

## 🔄 Versioning

We use [Semantic Versioning](http://semver.org/). For the versions available, see the [tags on this repository](https://github.com/mimpro/scanqr/tags).

## 📞 Contact

- **Issues**: [GitHub Issues](https://github.com/mimpro/scanqr/issues)
- **Discussions**: [GitHub Discussions](https://github.com/mimpro/scanqr/discussions)

## 📜 Code of Conduct

This project follows the [Drupal Code of Conduct](https://www.drupal.org/dcoc). By participating, you are expected to uphold this code.

## 🙏 Recognition

Contributors will be recognized in:
- README.md contributors section
- CHANGELOG.md for significant contributions
- GitHub contributors page

Thank you for contributing to make QR scanning better for the Drupal community! 🎉