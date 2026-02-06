# WDM Certificate Customizations - Developer Guide

## Table of Contents

1. [Development Environment Setup](#1-development-environment-setup)
2. [Plugin Architecture Overview](#2-plugin-architecture-overview)
3. [How to Extend the Plugin](#3-how-to-extend-the-plugin)
4. [Hook Reference](#4-hook-reference)
5. [API Reference](#5-api-reference)
6. [Debugging Tips](#6-debugging-tips)
7. [Coding Standards](#7-coding-standards)
8. [Testing](#8-testing)

---

## 1. Development Environment Setup

### 1.1 Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 7.4+ (8.0+ recommended) |
| WordPress | 6.0+ |
| LearnDash LMS | 4.0+ |
| LearnDash Certificate Builder | Any version |
| MySQL/MariaDB | 5.7+ / 10.3+ |

### 1.2 Local Development Setup

1. **Install WordPress locally** using Local by Flywheel, XAMPP, or Docker.

2. **Install required plugins:**
   - LearnDash LMS
   - LearnDash Certificate Builder

3. **Clone or install the plugin:**
   ```bash
   cd wp-content/plugins/
   git clone <repository-url> wdm-certificate-customizations
   ```

4. **Activate the plugin** via WordPress admin.

### 1.3 Development Mode

Enable WordPress debug mode in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('SAVEQUERIES', true);
```

### 1.4 File Structure

```
wdm-certificate-customizations/
|-- assets/
|   |-- css/
|   |   |-- admin.css           # Admin styles
|   |   |-- frontend.css        # Frontend styles
|   |   |-- index.php
|   |-- js/
|   |   |-- admin.js            # Admin scripts
|   |   |-- frontend.js         # Frontend scripts
|   |   |-- index.php
|   |-- index.php
|
|-- includes/
|   |-- class-admin.php         # Admin functionality
|   |-- class-certificate-handler.php  # Certificate generation
|   |-- class-helper.php        # Utility functions
|   |-- class-qr-code.php       # QR code generation
|   |-- class-shortcodes.php    # Shortcode handlers
|   |-- class-upgrade.php       # Migrations
|   |-- class-verification.php  # Verification system
|   |-- index.php
|
|-- languages/
|   |-- index.php
|
|-- templates/
|   |-- search-form.php         # Verification search form
|   |-- verification-error.php  # Error display
|   |-- verification-result.php # Success display
|   |-- index.php
|
|-- tests/
|   |-- e2e/                    # End-to-end tests
|   |-- index.php
|
|-- docs/
|   |-- ARCHITECTURE.md         # Architecture documentation
|   |-- CLASS_DIAGRAM.md        # Class diagrams
|   |-- DEVELOPER.md            # This file
|
|-- wdm-certificate-customizations.php  # Main plugin file
|-- readme.txt                  # WordPress.org readme
|-- CLAUDE.md                   # AI assistant context
|-- index.php
```

---

## 2. Plugin Architecture Overview

### 2.1 Design Patterns

**Singleton Pattern:** All main classes use the Singleton pattern to ensure single instances.

```php
class WDM_Cert_Example {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialization code
    }
}
```

**Static Utility Classes:** Helper and QR Code classes use static methods for stateless operations.

### 2.2 Component Initialization Flow

```
plugins_loaded (priority 5)
    |-- check_dependencies()
    |
plugins_loaded (priority 15)
    |-- init_plugin()
        |-- load_classes()
        |-- WDM_Cert_Admin::get_instance()
        |-- WDM_Cert_Handler::get_instance()
        |-- WDM_Cert_Verification::get_instance()
        |-- WDM_Cert_Shortcodes::get_instance()
        |-- Register hooks
```

### 2.3 Key Classes

| Class | File | Purpose |
|-------|------|---------|
| `WDM_Certificate_Customizations` | Main file | Plugin orchestrator |
| `WDM_Cert_Admin` | `class-admin.php` | Admin UI and settings |
| `WDM_Cert_Handler` | `class-certificate-handler.php` | Certificate generation |
| `WDM_Cert_Verification` | `class-verification.php` | Public verification |
| `WDM_Cert_Shortcodes` | `class-shortcodes.php` | Shortcode registry |
| `WDM_Cert_Helper` | `class-helper.php` | Utility functions |
| `WDM_Cert_QR_Code` | `class-qr-code.php` | QR generation |
| `WDM_Cert_Upgrade` | `class-upgrade.php` | Migrations |

---

## 3. How to Extend the Plugin

### 3.1 Adding Custom Certificate Data

Hook into certificate record generation to add custom data:

```php
add_action('wdm_certificate_record_generated', function($record, $csuid) {
    // Add custom data to the record
    $record['custom_field'] = 'custom_value';

    // Store the updated record
    $meta_key = '_wdm_certificate_' . $record['source_type'] . '_' . $record['source_id'];
    update_user_meta($record['user_id'], $meta_key, $record);
}, 10, 2);
```

### 3.2 Modifying Verification Results

Filter the verification result before display:

```php
add_filter('wdm_cert_verification_result', function($result, $csuid) {
    if ($result['valid']) {
        // Add custom data to successful verification
        $result['certificate']['custom_info'] = 'Additional information';
    }
    return $result;
}, 10, 2);
```

### 3.3 Custom Shortcode

Create a custom shortcode that uses plugin functionality:

```php
add_shortcode('custom_cert_display', function($atts) {
    $atts = shortcode_atts(array(
        'user_id' => get_current_user_id(),
    ), $atts);

    $user_id = absint($atts['user_id']);
    if (!$user_id) {
        return '';
    }

    // Get user certificates using the handler
    $handler = WDM_Cert_Handler::get_instance();
    $certificates = $handler->get_user_certificates($user_id);

    if (empty($certificates)) {
        return '<p>No certificates found.</p>';
    }

    $output = '<ul class="custom-cert-list">';
    foreach ($certificates as $cert) {
        $verify_url = WDM_Cert_Helper::get_verification_url($cert['certificate_id']);
        $output .= sprintf(
            '<li><a href="%s">%s</a> - %s</li>',
            esc_url($verify_url),
            esc_html($cert['certificate_id']),
            esc_html(date('Y-m-d', $cert['completion_date']))
        );
    }
    $output .= '</ul>';

    return $output;
});
```

### 3.4 Custom Verification Template

Override the verification result template:

```php
add_filter('wdm_cert_verification_template', function($template_path) {
    // Use custom template from theme
    $custom_template = get_stylesheet_directory() . '/wdm-cert/verification-result.php';
    if (file_exists($custom_template)) {
        return $custom_template;
    }
    return $template_path;
});
```

### 3.5 Modifying QR Code Generation

Filter QR code URL or attributes:

```php
// Modify QR code size for specific contexts
add_filter('wdm_cert_qr_code_size', function($size, $context) {
    if ($context === 'pocket') {
        return 100; // Smaller for pocket certificates
    }
    return $size;
}, 10, 2);

// Add custom class to QR code
add_filter('wdm_cert_qr_code_atts', function($atts) {
    $atts['class'] .= ' custom-qr-class';
    return $atts;
});
```

### 3.6 Adding Custom Source Types

Extend the plugin to support custom post types:

```php
// Add custom post type to source type mapping
add_filter('wdm_cert_source_type_map', function($map) {
    $map['custom-course'] = 'custom_course';
    return $map;
});

// Handle completion for custom source type
add_action('custom_course_completed', function($user_id, $course_id) {
    $handler = WDM_Cert_Handler::get_instance();

    $cert_id = get_post_meta($course_id, '_certificate', true);
    if ($cert_id) {
        $handler->generate_certificate_record(
            $cert_id,
            $course_id,
            $user_id,
            'custom_course'
        );
    }
}, 10, 2);
```

---

## 4. Hook Reference

### 4.1 Actions

#### Plugin Actions

| Action | Parameters | Description |
|--------|------------|-------------|
| `wdm_certificate_record_generated` | `$record`, `$csuid` | Fired after certificate record creation |

**Example:**
```php
add_action('wdm_certificate_record_generated', function($record, $csuid) {
    // Send notification email
    wp_mail(
        get_userdata($record['user_id'])->user_email,
        'Certificate Generated',
        sprintf('Your certificate ID is: %s', $csuid)
    );
}, 10, 2);
```

#### LearnDash Actions Used

| Action | Callback | Description |
|--------|----------|-------------|
| `learndash_course_completed` | `on_course_completed` | Course completion |
| `learndash_quiz_completed` | `on_quiz_completed` | Quiz completion |
| `ld_added_group_access` | `check_group_completion` | Group access |
| `learndash_certificate_disallowed` | `allow_pocket_certificate` | Certificate access |
| `learndash_tcpdf_init` | Used for PDF rendering | PDF generation |

### 4.2 Filters

#### Plugin Filters

| Filter | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `wdm_cert_verification_result` | `$result`, `$csuid` | `$result` | Modify verification result |
| `wdm_cert_qr_code_size` | `$size`, `$context` | `$size` | Modify QR code size |
| `wdm_cert_qr_code_atts` | `$atts` | `$atts` | Modify QR code attributes |
| `wdm_cert_verification_template` | `$template_path` | `$template_path` | Override template |
| `wdm_cert_source_type_map` | `$map` | `$map` | Extend source types |

**Example:**
```php
add_filter('wdm_cert_verification_result', function($result, $csuid) {
    if ($result['valid']) {
        // Add organization branding
        $result['certificate']['organization'] = get_bloginfo('name');
    }
    return $result;
}, 10, 2);
```

#### LearnDash Filters Used

| Filter | Callback | Description |
|--------|----------|-------------|
| `learndash_course_certificate_link` | `modify_certificate_link` | Add CSUID to links |
| `learndash_quiz_certificate_link` | `modify_quiz_certificate_link` | Add CSUID to quiz links |
| `learndash_settings_fields` | `add_course_certificate_field` | Add pocket cert field |
| `learndash_metabox_save_fields_*` | `save_pocket_certificate_learndash` | Save pocket cert |

---

## 5. API Reference

### 5.1 CSUID Encoding/Decoding

```php
// Encode IDs to CSUID
$csuid = WDM_Cert_Helper::encode_csuid(
    $certificate_id,  // int: Certificate template ID
    $source_id,       // int: Course/Quiz/Group ID
    $user_id          // int: User ID
);
// Returns: "1A-2B-3C" (hex format)

// Decode CSUID to IDs
$data = WDM_Cert_Helper::decode_csuid($csuid);
// Returns: array('cert_id' => 26, 'source_id' => 43, 'user_id' => 60)

// Validate CSUID format
$valid = WDM_Cert_Helper::is_csuid_valid($csuid);
// Returns: bool
```

### 5.2 Certificate Record Operations

```php
// Get certificate record by CSUID
$record = WDM_Cert_Helper::get_certificate_by_csuid($csuid);
// Returns: array|false

// Get user's certificate record for a specific source
$record = WDM_Cert_Handler::get_instance()->get_certificate_record(
    $source_id,    // int: Course/Quiz/Group ID
    $user_id,      // int: User ID
    $source_type   // string: 'course', 'quiz', or 'group'
);
// Returns: array|false

// Get all certificates for a user
$certificates = WDM_Cert_Handler::get_instance()->get_user_certificates($user_id);
// Returns: array

// Delete a certificate record
$deleted = WDM_Cert_Handler::get_instance()->delete_certificate_record(
    $source_id,
    $user_id,
    $source_type
);
// Returns: bool
```

**Certificate Record Structure:**
```php
array(
    'certificate_id'  => 'CSUID string',
    'standard_cert'   => int,  // Certificate template ID
    'pocket_cert'     => int,  // Pocket certificate ID (0 if none)
    'source_type'     => 'course|quiz|group',
    'source_id'       => int,
    'user_id'         => int,
    'completion_date' => int,  // Unix timestamp
    'generated_date'  => int,  // Unix timestamp
    'is_retroactive'  => bool,
)
```

### 5.3 URL Generation

```php
// Get verification page URL
$url = WDM_Cert_Helper::get_verification_url();
// Returns: "https://example.com/verify/"

// Get verification URL with CSUID
$url = WDM_Cert_Helper::get_verification_url('1A-2B-3C');
// Returns: "https://example.com/verify/1A-2B-3C/"

// Get certificate PDF URL
$pdf_url = WDM_Cert_Helper::get_pdf_url(
    $certificate_id,
    $source_id,
    $user_id,
    $source_type  // 'course', 'quiz', or 'group'
);
```

### 5.4 Certificate Assignment

```php
// Get standard certificate assigned to source
$cert_id = WDM_Cert_Helper::get_assigned_certificate(
    $source_id,
    $source_type
);
// Returns: int (0 if none)

// Get pocket certificate assigned to source
$pocket_id = WDM_Cert_Helper::get_pocket_certificate(
    $source_id,
    $source_type
);
// Returns: int (0 if none)
```

### 5.5 QR Code Generation

```php
// Generate QR code URL
$qr_url = WDM_Cert_QR_Code::generate_url(
    'https://example.com/verify/1A-2B-3C/',
    150  // size in pixels
);
// Returns: QuickChart.io URL

// Generate QR code HTML
$html = WDM_Cert_QR_Code::generate_html($qr_url, array(
    'align' => 'center',
    'alt'   => 'Certificate QR Code',
    'class' => 'wdm-cert-qr-code',
));
// Returns: <img> tag

// Generate QR for certificate
$html = WDM_Cert_QR_Code::generate_for_certificate('1A-2B-3C', array(
    'size' => 200,
));

// Get contextual QR code (auto-detects current certificate)
$html = WDM_Cert_QR_Code::get_contextual_qr_code(array(
    'size' => 150,
));
```

### 5.6 Verification

```php
// Verify certificate programmatically
$result = WDM_Cert_Verification::get_instance()->verify_certificate('1A-2B-3C');

// Success response:
array(
    'valid' => true,
    'certificate' => array(
        'csuid'            => '1A-2B-3C',
        'recipient'        => array(
            'id'           => 123,
            'name'         => 'John Doe',
            'email'        => 'john@example.com',
            'avatar_url'   => 'https://...',
        ),
        'source'           => array(
            'id'           => 456,
            'type'         => 'course',
            'title'        => 'Course Name',
            'url'          => 'https://...',
        ),
        'standard_certificate' => array(
            'id'           => 789,
            'title'        => 'Certificate Name',
            'pdf_url'      => 'https://...',
        ),
        'pocket_certificate' => array(...) | null,
        'completion_date'  => 1612345678,
        'completion_date_formatted' => 'February 3, 2026',
        'is_owner'         => true,
    ),
)

// Error response:
array(
    'valid'   => false,
    'error'   => 'invalid_format',  // Error code
    'message' => 'Invalid Certificate ID format',
)
```

### 5.7 Type Mapping

```php
// Post type to source type
$source_type = WDM_Cert_Helper::get_source_type('sfwd-courses');
// Returns: 'course'

// Source type to post type
$post_type = WDM_Cert_Helper::get_post_type('course');
// Returns: 'sfwd-courses'

// Mappings:
// sfwd-courses <-> course
// sfwd-quiz    <-> quiz
// groups       <-> group
```

### 5.8 Plugin Options

```php
// Get plugin option
$value = wdm_certificate_customizations()->get_option('qr_code_size', 150);

// Get all options
$options = get_option('wdm_certificate_options', array());

// Option keys:
// - verification_page_id (int)
// - qr_code_size (int: 50-500)
// - enable_pocket_certificate (bool)
// - certificate_id_prefix (string)
// - custom_css (string)
```

---

## 6. Debugging Tips

### 6.1 Enable Debug Logging

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Plugin logs to: wp-content/debug.log
```

### 6.2 Check Certificate Records

```php
// Get all certificate records for a user
global $wpdb;
$user_id = 123;
$records = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->usermeta}
     WHERE user_id = %d AND meta_key LIKE '_wdm_certificate_%%'",
    $user_id
));
foreach ($records as $record) {
    error_log(print_r(maybe_unserialize($record->meta_value), true));
}
```

### 6.3 Verify CSUID Manually

```php
// Debug CSUID encoding/decoding
$cert_id = 123;
$source_id = 456;
$user_id = 789;

$csuid = WDM_Cert_Helper::encode_csuid($cert_id, $source_id, $user_id);
error_log("Encoded CSUID: " . $csuid);
// Expected: 7B-1C8-315

$decoded = WDM_Cert_Helper::decode_csuid($csuid);
error_log(print_r($decoded, true));
// Expected: array('cert_id' => 123, 'source_id' => 456, 'user_id' => 789)
```

### 6.4 Debug Verification Issues

```php
// Add debugging to verification
add_filter('wdm_cert_verification_result', function($result, $csuid) {
    error_log("Verification for CSUID: " . $csuid);
    error_log("Result: " . print_r($result, true));
    return $result;
}, 10, 2);
```

### 6.5 Debug LearnDash Hooks

```php
// Debug course completion
add_action('learndash_course_completed', function($data) {
    error_log("Course completed: " . print_r($data, true));
}, 1);

// Debug certificate link modification
add_filter('learndash_course_certificate_link', function($link, $course_id, $user_id) {
    error_log("Certificate link: $link, Course: $course_id, User: $user_id");
    return $link;
}, 1, 3);
```

### 6.6 Common Issues

**Issue: Certificate not generating**
- Check if LearnDash certificate is assigned to course/quiz
- Verify user has completed the course/quiz
- Check `learndash_course_completed` hook is firing

**Issue: QR code not displaying**
- Verify QuickChart.io is accessible
- Check for HTTPS mixed content issues
- Verify CSUID is being generated correctly

**Issue: Verification fails**
- Validate CSUID format (should be hex with hyphens)
- Check if source post still exists
- Verify user exists and completed the course

**Issue: Public certificate access denied**
- Check `cert-nonce` parameter is present
- Verify certificate is assigned to the source
- Confirm user has completed the course/quiz

---

## 7. Coding Standards

### 7.1 WordPress Coding Standards

This plugin follows WordPress Coding Standards:

```php
// Class naming
class WDM_Cert_Example {}

// Method naming
public function get_certificate_record() {}

// Variable naming
$certificate_record = array();

// Array syntax
$array = array(
    'key' => 'value',
);

// Hooks
add_action('hook_name', array($this, 'callback'), 10, 2);
```

### 7.2 Security Practices

```php
// Always sanitize input
$user_id = absint($_GET['user_id']);
$text = sanitize_text_field($_POST['text']);

// Always escape output
echo esc_html($text);
echo esc_url($url);
echo esc_attr($attribute);

// Verify nonces
if (!wp_verify_nonce($_POST['nonce'], 'action_name')) {
    wp_die('Security check failed');
}

// Check capabilities
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

// Use prepared statements
$wpdb->prepare("SELECT * FROM table WHERE id = %d", $id);
```

### 7.3 Documentation

```php
/**
 * Generate certificate record
 *
 * @param int    $cert_id     Certificate template ID.
 * @param int    $source_id   Course/Quiz/Group ID.
 * @param int    $user_id     User ID.
 * @param string $source_type Source type (course, quiz, group).
 * @return bool True on success, false on failure.
 */
public function generate_certificate_record($cert_id, $source_id, $user_id, $source_type) {
    // Implementation
}
```

---

## 8. Testing

### 8.1 Test Data Setup

The plugin includes test setup utilities:

```php
// Setup test data (admin only)
// Access: /wp-admin/admin.php?page=wdm-cert-test-setup

// Or programmatically:
if (class_exists('WDM_Cert_Test_Setup')) {
    $setup = WDM_Cert_Test_Setup::get_instance();
    $result = $setup->create_test_data();
}
```

### 8.2 E2E Test Structure

```
tests/e2e/
|-- fixtures/           # Test data fixtures
|-- helpers/            # Test helper functions
|-- pages/              # Page object models
|-- tests/
|   |-- critical/       # Critical path tests
|   |-- important/      # Important feature tests
|   |-- standard/       # Standard tests
|-- setup-test-data.php # Test data setup
```

### 8.3 Manual Testing Checklist

**Certificate Generation:**
- [ ] Complete a course and verify certificate record is created
- [ ] Complete a quiz and verify certificate record is created
- [ ] Verify CSUID format is correct
- [ ] Verify pocket certificate is recorded if assigned

**Verification:**
- [ ] Access verification page
- [ ] Enter valid CSUID and verify result
- [ ] Enter invalid CSUID and verify error
- [ ] Scan QR code and verify redirect

**PDF Access:**
- [ ] Access standard certificate as owner
- [ ] Access pocket certificate as owner
- [ ] Access certificate as non-logged-in user via verification
- [ ] Verify download buttons hidden for non-owners

**Admin:**
- [ ] Configure verification page
- [ ] Assign pocket certificate to course
- [ ] Assign pocket certificate to quiz
- [ ] Run retroactive generation

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0 | 2026-02-05 | Documentation Generator | Initial documentation |

---

*Generated from code analysis of WDM Certificate Customizations v1.0.0*
