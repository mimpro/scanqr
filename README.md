# Scan QR - Drupal QR Code Scanner Module

[![Drupal 11](https://img.shields.io/badge/Drupal-11-blue.svg)](https://www.drupal.org/project/drupal)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://github.com/mimpro/scanqr/graphs/commit-activity)

A comprehensive Drupal module that provides real-time QR code scanning functionality using device cameras. Perfect for mobile-first applications, event management, inventory systems, and any use case requiring seamless QR code integration.

![QR Scanner Demo](https://via.placeholder.com/600x300/007cba/ffffff?text=QR+Scanner+Demo)

## 🚀 Key Features

## Features

### 🎯 **QR Scanner Field Type**
- Custom field type for content entities
- Real-time camera-based QR code scanning
- Automatic timestamp recording
- Configurable scanner dimensions
- Optional manual input fallback

### 📱 **Advanced Scanning**
- Uses jsQR library for accurate QR detection
- Support for various QR code formats
- Camera permission handling
- Mobile-responsive design

### 🔧 **QR Code Generation**
- Integrated chillerlan/php-qrcode library
- Generate QR codes from scanned content
- Multiple output formats (SVG, PNG, etc.)
- Configurable QR code sizes

### 🎨 **Flexible Display Options**
- Multiple formatter options
- Content-only display
- Content with generated QR code
- QR code-only display
- Timestamp display options

## Installation

1. **Install via Composer** (recommended):
   ```bash
   composer require chillerlan/php-qrcode
   ```

2. **Enable the module**:
   ```bash
   drush en scanqr -y
   ```

3. **Clear cache**:
   ```bash
   drush cr
   ```

## Usage

### Adding QR Scanner Fields to Content Types

1. **Navigate to Content Types**:
   - Go to `Administration > Structure > Content types`
   - Select "Manage fields" for your desired content type

2. **Add QR Scanner Field**:
   - Click "Add field"
   - Select "QR Scanner Field" from the field type dropdown
   - Configure field settings:
     - Scanner width/height
     - Enable manual input option
     - Field cardinality

3. **Configure Display**:
   - **Form Display**: Configure widget settings
     - Scanner dimensions
     - Manual input enable/disable
   - **View Display**: Configure formatter settings
     - Display mode (content only, with QR, QR only)
     - Show timestamp
     - Generate QR code
     - QR code size

### Using the Scanner

1. **Creating Content**:
   - Navigate to content creation form
   - Find your QR Scanner field
   - Click "Start Scanner" button
   - Allow camera permissions when prompted
   - Point camera at QR code
   - Content will be automatically filled when detected

2. **Field Features**:
   - **Auto-detection**: Automatically fills field when QR code is scanned
   - **Manual Input**: Optional fallback for manual entry
   - **Timestamp**: Automatic recording of scan time
   - **Visual Feedback**: Real-time scanning status

## Standalone Scanner

Access the standalone scanner at `/scanqr` for demonstration purposes.

## Technical Details

### Dependencies
- **chillerlan/php-qrcode**: For QR code generation
- **jsQR**: JavaScript library for QR code detection
- **Drupal Field API**: For field integration

### Browser Requirements
- Modern browser with camera API support
- HTTPS required for camera access (automatically handled by DDEV)
- JavaScript enabled

### Mobile Device Support
- **iOS**: Safari 11+ (iPhone, iPad)
- **Android**: Chrome 67+, Firefox Mobile 68+
- **Responsive Design**: Automatically adapts to mobile screen sizes
- **Touch Optimized**: Large touch targets and mobile-friendly controls
- **Camera Optimization**: Prefers rear camera for better QR scanning
- **Performance**: Mobile-optimized scanning algorithms

### Field Storage
- **qr_content**: Text field for scanned content
- **scanned_at**: Timestamp field for scan time

## Configuration

### Module Settings
Access module configuration at `/admin/config/system/scanqr`:
- Default scanner dimensions
- Enable/disable sound feedback
- Other global settings

### Field Widget Settings
- Scanner width/height
- Enable manual input
- Custom styling options

### Field Formatter Settings
- Display mode selection
- Timestamp display
- QR code generation
- Generated QR code size

## Permissions

The module provides the following permissions:
- **Access QR Code Scanner**: Allow users to use the scanner
- **Administer Scan QR**: Configure module settings

Configure at `/admin/people/permissions`.

## Testing

A test content type "QR Test" has been created with a configured QR Scanner field:
- Create content at `/node/add/qr_test`
- Test the scanner functionality
- View formatted output

## API Integration

### Programmatic Usage
```php
// Create QR scanner field programmatically
$field_storage = FieldStorageConfig::create([
  'field_name' => 'field_my_qr',
  'entity_type' => 'node',
  'type' => 'scanqr_field',
  'cardinality' => 1,
]);
$field_storage->save();
```

### Hooks Available
- `hook_scanqr_scan()`: React to successful QR scans
- `hook_scanqr_scan_alter()`: Modify scanned content

## Troubleshooting

### Camera Not Working
- Ensure HTTPS connection (required for camera access)
- Check browser permissions for camera access
- Verify camera is not in use by another application

### Module Not Appearing
- Clear Drupal cache: `drush cr`
- Verify module is enabled: `drush pml | grep scanqr`
- Check for PHP errors in logs

### QR Code Not Detected
- Ensure good lighting conditions
- Hold camera steady
- Try different angles/distances
- Verify QR code is valid and not damaged

## Support

For issues and feature requests, please refer to the module documentation or create issues in your project repository.

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/mimpro/scanqr.git
   cd scanqr
   ```

2. **Install in Drupal**:
   ```bash
   # Place in modules/custom/scanqr
   composer require chillerlan/php-qrcode
   drush en scanqr -y
   ```

3. **Development dependencies**:
   - Drupal 11.x
   - PHP 8.1+
   - Modern browser with camera API support

### Reporting Issues

Please report bugs and feature requests on our [GitHub Issues](https://github.com/mimpro/scanqr/issues) page.

## 📋 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

## 📄 License

This project is licensed under the GNU General Public License v2.0 or later - see the [LICENSE](LICENSE) file for details.

## 🙏 Credits

- **QR Detection**: [jsQR](https://github.com/cozmo/jsQR) library
- **QR Generation**: [chillerlan/php-qrcode](https://github.com/chillerlan/php-qrcode)
- **Drupal Integration**: Built for Drupal 11+ with Field API and Webform support

## 🔗 Related Projects

- [Webform](https://www.drupal.org/project/webform) - Form builder integration
- [Field API](https://api.drupal.org/api/drupal/core!modules!field!field.api.php) - Drupal field system

---

**Made with ❤️ for the Drupal community**