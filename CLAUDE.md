# CLAUDE.md - WDM Certificate Customizations

## Project Overview

**Plugin Name:** WDM Certificate Customizations
**Version:** 1.0.0
**Platform:** WordPress (LearnDash Extension)
**Author:** WisdmLabs

### Purpose
Adds dual certificate support (Standard + Wallet Card) with built-in QR code verification system for LearnDash LMS.

### Dependencies
- WordPress 6.0+
- PHP 7.4+
- LearnDash LMS 4.0+
- LearnDash Certificate Builder

## Architecture

### Directory Structure
```
wdm-certificate-customizations/
├── assets/
│   ├── css/           # Frontend and admin stylesheets
│   └── js/            # Frontend and admin scripts
├── includes/
│   ├── class-admin.php           # Admin settings and course meta
│   ├── class-certificate-handler.php  # Certificate ID generation
│   ├── class-helper.php          # Utility functions
│   ├── class-qr-code.php         # QR code generation
│   ├── class-shortcodes.php      # Shortcode handlers
│   ├── class-upgrade.php         # Migration utilities
│   └── class-verification.php    # Certificate verification
├── languages/         # i18n translation files
├── templates/         # PHP templates for verification UI
├── tests/             # E2E tests (Playwright)
└── wdm-certificate-customizations.php  # Main plugin file
```

### Core Components
1. **WDM_Certificate_Customizations** - Main plugin singleton
2. **WDM_Cert_Admin** - Admin settings page and course meta boxes
3. **WDM_Cert_Handler** - Certificate ID (CSUID) generation on completion
4. **WDM_Cert_Verification** - AJAX verification endpoint
5. **WDM_Cert_Shortcodes** - QR code and Certificate ID shortcodes
6. **WDM_Cert_Helper** - Static utility methods

## Coding Standards

### WordPress Standards
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use `wp_` prefixed functions for database/security operations
- Escape all output with `esc_html()`, `esc_attr()`, `esc_url()`
- Sanitize all input with `sanitize_text_field()`, `absint()`, etc.
- Use nonces for all form submissions and AJAX calls

### Naming Conventions
- **Classes:** `WDM_Cert_{ComponentName}` (e.g., `WDM_Cert_Admin`)
- **Functions:** `wdm_cert_{action}` (e.g., `wdm_cert_verify`)
- **Hooks:** `wdm_cert_{hook_name}` (e.g., `wdm_cert_before_verify`)
- **Meta Keys:** `_wdm_{key}` (e.g., `_wdm_pocket_certificate`)
- **Options:** `wdm_certificate_options` (single array option)

### Security Requirements
- Validate user capabilities before admin actions
- Verify nonces on all form submissions
- Sanitize and validate all user input
- Escape all output in templates
- Use prepared statements for custom queries

## Key Concepts

### CSUID (Certificate Secure Unique ID)
- Format: `{cert_id_hex}-{source_id_hex}-{user_id_hex}` (e.g., `3F-41-2`)
- Encodes certificate template, source (course/quiz), and user IDs
- Case-insensitive verification
- Stored in user meta: `_wdm_cert_id_{source_type}_{source_id}`

### Certificate Types
1. **Standard Certificate** - Main LearnDash certificate assigned to course/quiz
2. **Wallet Card** - Optional compact certificate (stored in `_wdm_pocket_certificate` meta)

### Verification Flow
1. User enters CSUID on verification page
2. AJAX call to `wdm_cert_verify` action
3. Decode CSUID to get certificate, source, user IDs
4. Validate completion status
5. Return certificate data with PDF URLs

## Testing

### E2E Tests (Playwright)
```bash
cd tests/e2e
npm install
npm test                    # Run all tests
npm run test:critical      # Run critical tests only
npm run test:chromium      # Run on Chromium only
```

### Test Environment Variables
See `tests/e2e/.env.example` for required configuration.

## Development Commands

### Build/Deploy
- No build step required (vanilla PHP/JS/CSS)
- Deploy via FTP or WordPress plugin upload

### Translation
```bash
wp i18n make-pot . languages/wdm-certificate-customizations.pot
```

## Common Tasks

### Adding a New Setting
1. Register field in `WDM_Cert_Admin::register_settings()`
2. Add sanitization in `WDM_Cert_Admin::sanitize_options()`
3. Create render method for the field

### Adding a New Shortcode
1. Register in `WDM_Cert_Shortcodes::register_shortcodes()`
2. Create callback method
3. Document in admin settings page

### Modifying Certificate Access
- Main access control in `allow_pocket_certificate()` and `allow_public_certificate_view()`
- Uses LearnDash's `learndash_certificate_disallowed` hook

## Troubleshooting

### Common Issues
1. **Certificate not showing** - Check completion status and certificate assignment
2. **QR code not rendering** - Verify shortcode syntax and certificate context
3. **Verification fails** - Check CSUID format and user completion records

### Debug Mode
Add to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Resources

- [LearnDash Developer Documentation](https://developers.learndash.com/)
- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [Playwright Documentation](https://playwright.dev/)
