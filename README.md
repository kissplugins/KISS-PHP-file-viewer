# PHP File Viewer

**Contributors:** kissplugins  
**Tags:** php, file, scanner, code viewer, plugin  
**Requires at least:** WordPress 5.0  
**Tested up to:** WordPress 6.2  
**Stable tag:** 1.3  
**License:** GPLv2 ([GNU GPL v2 License](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html))

## Description

PHP File Scanner is a simple WordPress plugin that displays the content of a defined PHP file within a code viewing container. The file path can be configured from the admin settings page. It is particularly useful for developers and administrators who need to review code snippets or debug file contents directly from the dashboard.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/php-file-scanner` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **PHP File Scanner** under the admin menu to set the relative path of the PHP file you wish to display.
4. Use the `[file_scanner]` **shortcode** in your posts or pages to display the file contents.

## Frequently Asked Questions

### How do I set the file path?

Enter the file path relative to your WordPress installation in the plugin settings page (e.g., `wp-content/plugins/php-file-scanner/test.php`).

### Can I use this plugin to display any file?

The plugin is intended for PHP files only. Ensure that the file exists and that its path is correct relative to your WordPress root.

## Changelog

### 1.3
- Updated license to GNU GPL v2.
- Changed author information to **KISS Plugins**.
- Incremented version number.
- Added a settings link to the Plugins listings page for easier access.
- Added sanitization callback for plugin settings.

### 1.2
- Initial release.

## Upgrade Notice

### 1.3
Please update to version 1.3 to ensure you have the latest security and usability improvements, including proper licensing and an easier way to access plugin settings.

## Screenshots

1. Settings page where you can enter the PHP file path.
2. Display of the PHP file content in a code viewing container on your site.

## License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License v2 as published by the Free Software Foundation. This program is distributed in the hope that it will be useful, but **WITHOUT ANY WARRANTY**.

For more details, please see [GNU GPL v2 License](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).
