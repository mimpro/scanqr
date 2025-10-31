# Changelog

All notable changes to the Scan QR module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial public release
- QR Scanner Field Type for content entities
- Webform QR Scanner Element integration
- Real-time camera-based QR code scanning
- Mobile-optimized interface and controls
- QR code generation using chillerlan/php-qrcode
- Multi-format QR code support
- Configurable scanner dimensions
- Manual input fallback option
- Timestamp recording for scans
- Multiple display formatters
- HTTPS requirement handling
- Cross-browser compatibility (Chrome, Firefox, Safari, Mobile)

### Features
- **Field Integration**: Custom field type for any content entity
- **Webform Support**: Native webform element for form building
- **Mobile First**: Optimized for smartphone and tablet cameras
- **Real-time Scanning**: Instant QR code detection and processing
- **Flexible Display**: Multiple formatter options for different use cases
- **QR Generation**: Create QR codes from scanned content
- **Developer Friendly**: Clean API and extensible architecture

### Technical
- Drupal 11 compatibility
- PHP 8.1+ support
- jsQR library integration for detection
- chillerlan/php-qrcode for generation
- Responsive CSS design
- Modern JavaScript with fallbacks
- Field API integration
- Webform API integration

### Browser Support
- Chrome 67+ (Desktop & Mobile)
- Firefox 68+ (Desktop & Mobile)
- Safari 11+ (Desktop & iOS)
- Edge 79+
- Mobile browsers with camera API support

## [1.0.0] - 2025-10-31

### Added
- Initial release of Scan QR module
- Core QR scanning functionality
- Basic field and webform integration
- Mobile camera support
- QR code generation capabilities

---

## Release Notes

### Version 1.0.0

This is the initial release of the Scan QR module for Drupal 11. The module provides comprehensive QR code scanning and generation functionality with the following highlights:

**🎯 Core Features:**
- Real-time QR scanning using device cameras
- Mobile-optimized interface
- Field API integration for content types
- Webform element for dynamic forms
- QR code generation and display

**📱 Mobile Support:**
- Works on iOS Safari 11+
- Android Chrome 67+
- Touch-friendly controls
- Responsive design
- Camera permission handling

**🔧 Developer Features:**
- Clean, extensible API
- Proper Drupal coding standards
- Comprehensive documentation
- Example implementations
- Easy customization options

**🚀 Getting Started:**
1. Install via Composer: `composer require chillerlan/php-qrcode`
2. Enable module: `drush en scanqr -y`
3. Add QR Scanner fields to content types
4. Create webforms with QR scanner elements
5. Test on mobile devices with camera access

For detailed installation and usage instructions, see the [README.md](README.md) file.

---

**Note**: This changelog will be updated with each release. For the latest development updates, see the [commit history](https://github.com/mimpro/scanqr/commits/main).