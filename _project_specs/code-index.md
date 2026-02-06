# Code Capability Index: WDM Certificate Customizations

## Summary

| Metric | Count |
|--------|-------|
| Files Analyzed | 8 |
| Classes | 8 |
| Public Methods | 45+ |
| WordPress Actions Registered | 15 |
| WordPress Filters Registered | 7 |
| AJAX Endpoints | 2 |
| Shortcodes | 4 |
| Database Operations | User Meta, Post Meta, Options |

## Plugin Overview

**Plugin Name:** WDM Certificate Customizations
**Version:** 1.0.0
**Purpose:** Adds dual certificate support (Standard + Pocket Size) with built-in QR code verification system for LearnDash.

**Dependencies:**
- LearnDash LMS (`SFWD_LMS` class)
- LearnDash Certificate Builder (`LEARNDASH_CERTIFICATE_BUILDER_VERSION` constant)

---

## File: wdm-certificate-customizations.php

### Constants Defined

| Constant | Value/Description |
|----------|-------------------|
| `WDM_CERT_VERSION` | `'1.0.0'` |
| `WDM_CERT_PLUGIN_FILE` | Main plugin file path |
| `WDM_CERT_PLUGIN_DIR` | Plugin directory path |
| `WDM_CERT_PLUGIN_URL` | Plugin URL |
| `WDM_CERT_PLUGIN_BASENAME` | Plugin basename |

### Class: WDM_Certificate_Customizations

**Purpose:** Main plugin class implementing singleton pattern. Orchestrates all plugin components.

**Pattern:** Singleton

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$instance` | `WDM_Certificate_Customizations` | Static singleton instance |
| `$admin` | `WDM_Cert_Admin` | Admin component |
| `$handler` | `WDM_Cert_Handler` | Certificate handler component |
| `$verification` | `WDM_Cert_Verification` | Verification component |
| `$shortcodes` | `WDM_Cert_Shortcodes` | Shortcodes component |

#### Public Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `get_instance()` | none | `WDM_Certificate_Customizations` | Get singleton instance |
| `check_dependencies()` | none | `bool` | Check if LearnDash & Certificate Builder are active |
| `init_plugin()` | none | `void` | Initialize plugin components after dependency check |
| `load_textdomain()` | none | `void` | Load plugin translations |
| `enqueue_frontend_assets()` | none | `void` | Enqueue CSS/JS on verification page |
| `enqueue_admin_assets()` | `string $hook` | `void` | Enqueue admin CSS/JS |
| `activate()` | none | `void` | Plugin activation handler |
| `deactivate()` | none | `void` | Plugin deactivation handler |
| `allow_pocket_certificate()` | none | `void` | Allow access to pocket certificates via LearnDash |
| `allow_public_certificate_view()` | none | `void` | Allow non-logged-in users to view certificates for verification |
| `get_option()` | `string $key`, `mixed $default` | `mixed` | Get plugin option value |

#### WordPress Hooks Registered

**Actions:**
| Hook | Callback | Priority |
|------|----------|----------|
| `plugins_loaded` | `check_dependencies` | 5 |
| `plugins_loaded` | `init_plugin` | 15 |
| `init` | `load_textdomain` | 10 |
| `learndash_certificate_disallowed` | `allow_pocket_certificate` | 5 |
| `learndash_certificate_disallowed` | `allow_public_certificate_view` | 10 |
| `wp_enqueue_scripts` | `enqueue_frontend_assets` | 10 |
| `admin_enqueue_scripts` | `enqueue_admin_assets` | 10 |

**Activation/Deactivation:**
| Hook | Callback |
|------|----------|
| `register_activation_hook` | `activate` |
| `register_deactivation_hook` | `deactivate` |

#### Database Operations

**Options:**
| Option Key | Type | Description |
|------------|------|-------------|
| `wdm_certificate_options` | `array` | Plugin settings |

**Option Fields:**
- `verification_page_id` - Page ID for verification
- `qr_code_size` - QR code default size (50-500px)
- `enable_pocket_certificate` - Enable wallet cards feature
- `certificate_id_prefix` - Prefix for certificate IDs
- `custom_css` - Custom CSS

### Global Function

| Function | Return | Description |
|----------|--------|-------------|
| `wdm_certificate_customizations()` | `WDM_Certificate_Customizations` | Get plugin instance |

---

## File: includes/class-admin.php

### Class: WDM_Cert_Admin

**Purpose:** Admin settings page, course/quiz meta boxes, and AJAX handlers.

**Pattern:** Singleton

#### Public Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `get_instance()` | none | `WDM_Cert_Admin` | Get singleton instance |
| `add_admin_menu()` | none | `void` | Add submenu under LearnDash |
| `register_settings()` | none | `void` | Register WordPress settings |
| `sanitize_options()` | `array $input` | `array` | Sanitize settings input |
| `render_settings_page()` | none | `void` | Render admin settings page |
| `render_general_section()` | none | `void` | Render general settings section |
| `render_qr_section()` | none | `void` | Render QR code settings section |
| `render_retroactive_section()` | none | `void` | Render retroactive generation section |
| `render_verification_page_field()` | none | `void` | Render verification page dropdown |
| `render_enable_pocket_field()` | none | `void` | Render enable wallet cards checkbox |
| `render_qr_size_field()` | none | `void` | Render QR size input |
| `add_course_certificate_field()` | `array $fields`, `string $metabox_key` | `array` | Add pocket certificate field to course settings |
| `add_meta_boxes()` | none | `void` | Add classic editor meta boxes |
| `render_pocket_certificate_metabox()` | `WP_Post $post` | `void` | Render pocket certificate metabox |
| `save_pocket_certificate_learndash()` | `array $settings`, `string $key`, `string $screen_id` | `array` | Save via LearnDash mechanism |
| `save_course_meta()` | `int $post_id`, `WP_Post $post` | `void` | Save course meta (classic editor) |
| `save_quiz_meta()` | `int $post_id`, `WP_Post $post` | `void` | Save quiz meta (classic editor) |
| `ajax_generate_retroactive()` | none | `void` | AJAX handler for retroactive generation |
| `admin_notices()` | none | `void` | Display admin notices |
| `plugin_action_links()` | `array $links` | `array` | Add settings link to plugins page |

#### WordPress Hooks Registered

**Actions:**
| Hook | Callback | Priority |
|------|----------|----------|
| `admin_menu` | `add_admin_menu` | 100 |
| `admin_init` | `register_settings` | 10 |
| `save_post_sfwd-courses` | `save_course_meta` | 10 |
| `save_post_sfwd-quiz` | `save_quiz_meta` | 10 |
| `add_meta_boxes` | `add_meta_boxes` | 10 |
| `wp_ajax_wdm_cert_generate_retroactive` | `ajax_generate_retroactive` | 10 |
| `admin_notices` | `admin_notices` | 10 |

**Filters:**
| Hook | Callback | Priority |
|------|----------|----------|
| `learndash_settings_fields` | `add_course_certificate_field` | 10 |
| `learndash_metabox_save_fields_learndash-course-display-content-settings` | `save_pocket_certificate_learndash` | 30 |
| `learndash_metabox_save_fields_learndash-quiz-display-content-settings` | `save_pocket_certificate_learndash` | 30 |
| `plugin_action_links_{basename}` | `plugin_action_links` | 10 |

#### AJAX Endpoints

| Action | Callback | Auth Required | Nonce |
|--------|----------|---------------|-------|
| `wdm_cert_generate_retroactive` | `ajax_generate_retroactive` | Yes (`manage_options`) | `wdm_cert_admin` |

#### Database Operations

**Post Meta:**
| Meta Key | Post Type | Description |
|----------|-----------|-------------|
| `_wdm_pocket_certificate` | `sfwd-courses`, `sfwd-quiz` | Pocket certificate ID |

**Settings API:**
| Setting Group | Option Name |
|---------------|-------------|
| `wdm_certificate_options_group` | `wdm_certificate_options` |

---

## File: includes/class-certificate-handler.php

### Class: WDM_Cert_Handler

**Purpose:** Handles certificate generation, storage, and link modification.

**Pattern:** Singleton

#### Public Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `get_instance()` | none | `WDM_Cert_Handler` | Get singleton instance |
| `on_course_completed()` | `array $data` | `void` | Handle course completion event |
| `on_quiz_completed()` | `array $quiz_data`, `WP_User $user` | `void` | Handle quiz completion event |
| `check_group_completion()` | `int $user_id`, `int $group_id` | `void` | Check and handle group completion |
| `generate_certificate_record()` | `int $cert_id`, `int $source_id`, `int $user_id`, `string $source_type` | `bool` | Generate and store certificate record |
| `get_certificate_record()` | `int $source_id`, `int $user_id`, `string $source_type` | `array\|false` | Get certificate record |
| `modify_certificate_link()` | `string $link`, `int $course_id`, `int $user_id` | `string` | Modify course certificate link |
| `modify_quiz_certificate_link()` | `string $link`, `WP_User\|int $user` | `string` | Modify quiz certificate link |
| `get_user_certificates()` | `int $user_id` | `array` | Get all certificates for a user |
| `delete_certificate_record()` | `int $source_id`, `int $user_id`, `string $source_type` | `bool` | Delete a certificate record |

#### WordPress Hooks Registered

**Actions:**
| Hook | Callback | Priority |
|------|----------|----------|
| `learndash_course_completed` | `on_course_completed` | 10 |
| `learndash_quiz_completed` | `on_quiz_completed` | 10 |
| `ld_added_group_access` | `check_group_completion` | 10 |

**Filters:**
| Hook | Callback | Priority |
|------|----------|----------|
| `learndash_course_certificate_link` | `modify_certificate_link` | 20 |
| `learndash_quiz_certificate_link` | `modify_quiz_certificate_link` | 20 |

#### Custom Actions Fired

| Action | Parameters | Description |
|--------|------------|-------------|
| `wdm_certificate_record_generated` | `array $record`, `string $csuid` | Fired after certificate record is generated |

#### Database Operations

**User Meta:**
| Meta Key Pattern | Description |
|------------------|-------------|
| `_wdm_certificate_{source_type}_{source_id}` | Certificate record for user |

**Record Structure:**
```php
array(
    'certificate_id'  => string,  // CSUID
    'standard_cert'   => int,     // Certificate template ID
    'pocket_cert'     => int,     // Pocket certificate ID
    'source_type'     => string,  // 'course', 'quiz', 'group'
    'source_id'       => int,     // Source post ID
    'user_id'         => int,     // User ID
    'completion_date' => int,     // Timestamp
    'generated_date'  => int,     // Timestamp
    'is_retroactive'  => bool,    // Was generated retroactively
)
```

---

## File: includes/class-helper.php

### Class: WDM_Cert_Helper

**Purpose:** Static helper methods for CSUID encoding/decoding, URL generation, and data retrieval.

**Pattern:** Static utility class

#### Static Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `encode_csuid()` | `int $cert_id`, `int $source_id`, `int $user_id` | `string` | Encode IDs to Certificate ID (hex format) |
| `decode_csuid()` | `string $csuid` | `array` | Decode Certificate ID to IDs |
| `is_csuid_valid()` | `string $csuid` | `bool` | Validate Certificate ID format |
| `get_certificate_by_csuid()` | `string $csuid` | `array\|false` | Get certificate record by CSUID |
| `get_source_type()` | `string $post_type` | `string\|false` | Convert post type to source type |
| `get_post_type()` | `string $source_type` | `string\|false` | Convert source type to post type |
| `get_assigned_certificate()` | `int $source_id`, `string $source_type` | `int` | Get assigned certificate for source |
| `get_pocket_certificate()` | `int $source_id`, `string $source_type` | `int` | Get pocket certificate for source |
| `get_standard_cert_for_csuid()` | `int $current_cert_id`, `int $source_id` | `int` | Get standard cert ID for CSUID generation |
| `get_completion_date()` | `int $source_id`, `int $user_id`, `string $source_type` | `int` | Get completion timestamp |
| `get_verification_url()` | `string $csuid = ''` | `string` | Get verification page URL |
| `get_pdf_url()` | `int $cert_id`, `int $source_id`, `int $user_id`, `string $source_type` | `string` | Get certificate PDF URL |

#### CSUID Format

**Encoding:** `CERT_HEX-SOURCE_HEX-USER_HEX`
- Example: `1A-2B-3C` (Certificate 26, Source 43, User 60)
- Uppercase hexadecimal values separated by hyphens

**Validation Regex:**
- New format: `/^[A-F0-9]+(?:_[A-F0-9]+)?-[A-F0-9]+(?:_[A-F0-9]+)?-[A-F0-9]+(?:_[A-F0-9]+)?$/`
- Old format: `/^[A-F0-9]+-[A-F0-9]+-[A-F0-9]+$/`

#### Type Mappings

| Post Type | Source Type |
|-----------|-------------|
| `sfwd-courses` | `course` |
| `sfwd-quiz` | `quiz` |
| `groups` | `group` |

#### Database Operations

**Post Meta Read:**
| Meta Key | Post Type | Description |
|----------|-----------|-------------|
| `_wdm_pocket_certificate` | Any source | Pocket certificate ID |

**User Meta Read:**
| Meta Key Pattern | Description |
|------------------|-------------|
| `_wdm_certificate_{type}_{id}` | Certificate record |

**LearnDash Settings:**
- Uses `learndash_get_setting()` to get assigned certificates

---

## File: includes/class-qr-code.php

### Class: WDM_Cert_QR_Code

**Purpose:** QR code generation using external API.

**Pattern:** Static utility class

#### Static Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `generate_url()` | `string $data`, `int $size = 150` | `string` | Generate QR code image URL |
| `generate_html()` | `string $url`, `array $atts = []` | `string` | Generate QR code HTML img tag |
| `generate_for_certificate()` | `string $csuid`, `array $atts = []` | `string` | Generate QR for certificate |
| `get_contextual_qr_code()` | `array $atts = []` | `string` | Get QR code for current certificate context |

#### External API

| Service | URL Pattern |
|---------|-------------|
| QuickChart.io | `https://quickchart.io/qr?text={data}&size={size}&margin=1` |

#### Attributes Supported

| Attribute | Default | Description |
|-----------|---------|-------------|
| `size` | 150 | QR code size in pixels (50-500) |
| `align` | '' | Alignment (left, center, right) |
| `alt` | 'Certificate QR Code' | Alt text |
| `class` | 'wdm-cert-qr-code' | CSS class |

---

## File: includes/class-shortcodes.php

### Class: WDM_Cert_Shortcodes

**Purpose:** Register and handle all plugin shortcodes.

**Pattern:** Singleton

#### Shortcodes Registered

| Shortcode | Callback | Description |
|-----------|----------|-------------|
| `[wdm_certificate_verify]` | `shortcode_verify` | Display verification form and results |
| `[wdm_certificate_qr_code]` | `shortcode_qr_code` | Display QR code on certificates |
| `[wdm_certificate_id]` | `shortcode_certificate_id` | Display Certificate ID |
| `[wdm_certificate_verification_url]` | `shortcode_verification_url` | Display verification URL |

#### Shortcode Details

**[wdm_certificate_verify]**
| Attribute | Default | Description |
|-----------|---------|-------------|
| `show_form` | 'yes' | Show search form |

**[wdm_certificate_qr_code]**
| Attribute | Default | Description |
|-----------|---------|-------------|
| `size` | From settings | QR code size |
| `align` | 'center' | Alignment |
| `class` | 'wdm-cert-qr-code' | CSS class |

**[wdm_certificate_id]**
| Attribute | Default | Description |
|-----------|---------|-------------|
| `prefix` | '' | Text before ID |
| `suffix` | '' | Text after ID |
| `class` | 'wdm-cert-id' | CSS class |

**[wdm_certificate_verification_url]**
| Attribute | Default | Description |
|-----------|---------|-------------|
| `link_text` | '' | If set, renders as link |
| `class` | '' | CSS class |
| `target` | '_blank' | Link target |

---

## File: includes/class-upgrade.php

### Class: WDM_Cert_Upgrade

**Purpose:** Handle migrations and retroactive certificate ID generation.

**Pattern:** Singleton

#### Public Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `get_instance()` | none | `WDM_Cert_Upgrade` | Get singleton instance |
| `generate_retroactive_certificate_ids()` | none | `array` | Generate IDs for historical completions |
| `migrate_from_ld_cvss()` | none | `array` | Migrate from LD Certificate Verify and Share |
| `get_statistics()` | none | `array` | Get certificate record statistics |

#### Database Operations

**LearnDash Activity Table:**
- Table: `{prefix}learndash_user_activity`
- Query: Course completions with `activity_type = 'course'`

**User Meta Queries:**
| Meta Key | Description |
|----------|-------------|
| `_sfwd-quizzes` | Quiz completion data |
| `learndash_group_completed_%` | Group completion timestamps |
| `_wdm_certificate_%` | Certificate records |

#### Statistics Structure

```php
array(
    'total_records'    => int,
    'course_records'   => int,
    'quiz_records'     => int,
    'group_records'    => int,
    'retroactive'      => int,
    'with_pocket_cert' => int,
)
```

---

## File: includes/class-verification.php

### Class: WDM_Cert_Verification

**Purpose:** Certificate verification system with URL routing and AJAX support.

**Pattern:** Singleton

#### Public Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `get_instance()` | none | `WDM_Cert_Verification` | Get singleton instance |
| `add_rewrite_rules()` | none | `void` | Add pretty URL rewrite rules |
| `add_query_vars()` | `array $vars` | `array` | Add query vars (cert_id, view) |
| `verify_certificate()` | `string $csuid` | `array` | Verify certificate and return result |
| `ajax_verify_certificate()` | none | `void` | AJAX handler for verification |
| `render_verification_result()` | `array $certificate` | `void` | Render verification result HTML |
| `get_verification_page_content()` | none | `string` | Get full verification page content |

#### WordPress Hooks Registered

**Actions:**
| Hook | Callback | Priority |
|------|----------|----------|
| `init` | `add_rewrite_rules` | 10 |
| `wp_ajax_wdm_cert_verify` | `ajax_verify_certificate` | 10 |
| `wp_ajax_nopriv_wdm_cert_verify` | `ajax_verify_certificate` | 10 |

**Filters:**
| Hook | Callback | Priority |
|------|----------|----------|
| `query_vars` | `add_query_vars` | 10 |

#### AJAX Endpoints

| Action | Callback | Auth Required | Nonce |
|--------|----------|---------------|-------|
| `wdm_cert_verify` | `ajax_verify_certificate` | No | `wdm_cert_verify` |

#### URL Rewrite Rules

| Pattern | Rewrite |
|---------|---------|
| `^{page_slug}/([A-Fa-f0-9_-]+)/?$` | `index.php?pagename={page_slug}&cert_id=$matches[1]` |

#### Query Variables

| Variable | Description |
|----------|-------------|
| `cert_id` | Certificate ID (CSUID) |
| `view` | View preference ('standard' or 'pocket') |

#### Verification Result Structure

**Success:**
```php
array(
    'valid'       => true,
    'certificate' => array(
        'csuid'            => string,
        'recipient'        => array(
            'id'           => int,
            'name'         => string,
            'email'        => string,
            'avatar_url'   => string,
        ),
        'source'           => array(
            'id'           => int,
            'type'         => string,
            'title'        => string,
            'url'          => string,
        ),
        'standard_certificate' => array(
            'id'           => int,
            'title'        => string,
            'pdf_url'      => string,
        ),
        'pocket_certificate' => array|null,
        'completion_date'  => int,
        'completion_date_formatted' => string,
        'is_owner'         => bool,
        'course'           => array|null,  // Only for quizzes
    ),
)
```

**Error:**
```php
array(
    'valid'   => false,
    'error'   => string,  // Error code
    'message' => string,  // User-friendly message
)
```

#### Error Codes

| Code | Message |
|------|---------|
| `invalid_format` | Invalid Certificate ID format |
| `decode_failed` | Could not decode Certificate ID |
| `source_not_found` | Certificate source not found |
| `invalid_source_type` | Invalid certificate source type |
| `user_not_found` | Certificate recipient not found |
| `certificate_not_found` | Certificate template not found |
| `certificate_mismatch` | Certificate not assigned to source |
| `not_completed` | User has not completed course/quiz |

---

## Dependencies Graph

```
WDM_Certificate_Customizations (Main)
├── WDM_Cert_Admin
│   └── WDM_Cert_Upgrade
├── WDM_Cert_Handler
│   └── WDM_Cert_Helper
├── WDM_Cert_Verification
│   ├── WDM_Cert_Helper
│   └── WDM_Cert_Handler
└── WDM_Cert_Shortcodes
    ├── WDM_Cert_Verification
    ├── WDM_Cert_QR_Code
    └── WDM_Cert_Helper
```

### Class Dependencies

| Class | Depends On |
|-------|------------|
| `WDM_Certificate_Customizations` | All classes (instantiation) |
| `WDM_Cert_Admin` | `WDM_Cert_Upgrade` |
| `WDM_Cert_Handler` | `WDM_Cert_Helper` |
| `WDM_Cert_Verification` | `WDM_Cert_Helper`, `WDM_Cert_Handler` |
| `WDM_Cert_Shortcodes` | `WDM_Cert_Verification`, `WDM_Cert_QR_Code`, `WDM_Cert_Helper` |
| `WDM_Cert_QR_Code` | `WDM_Cert_Helper` |
| `WDM_Cert_Helper` | None (utility class) |
| `WDM_Cert_Upgrade` | `WDM_Cert_Helper` |

---

## Assets

### Frontend Assets

| File | Type | Dependencies | Loaded On |
|------|------|--------------|-----------|
| `assets/css/frontend.css` | CSS | None | Verification page |
| `assets/js/frontend.js` | JS | jQuery | Verification page |

**Localized Script Data (`wdmCertVars`):**
```php
array(
    'ajaxUrl'         => string,
    'nonce'           => string,
    'verificationUrl' => string,
    'strings'         => array(
        'loading' => string,
        'error'   => string,
    ),
)
```

### Admin Assets

| File | Type | Dependencies | Loaded On |
|------|------|--------------|-----------|
| `assets/css/admin.css` | CSS | None | Settings page, course/quiz edit |
| `assets/js/admin.js` | JS | jQuery | Settings page, course/quiz edit |

**Localized Script Data (`wdmCertAdmin`):**
```php
array(
    'ajaxUrl' => string,
    'nonce'   => string,
    'strings' => array(
        'generating' => string,
        'complete'   => string,
        'error'      => string,
    ),
)
```

---

## Templates

| Template | Location | Purpose |
|----------|----------|---------|
| `verification-result.php` | `templates/` | Display verified certificate info |
| `verification-error.php` | `templates/` | Display verification error |
| `search-form.php` | `templates/` | Certificate search form |

---

## Potential Duplicates / Consolidation Opportunities

### High Similarity: Certificate Context Detection

The following methods contain similar logic for detecting certificate context from URL parameters:

1. `WDM_Cert_QR_Code::get_contextual_qr_code()` (lines 123-184)
2. `WDM_Cert_Shortcodes::shortcode_certificate_id()` (lines 95-164)
3. `WDM_Cert_Shortcodes::shortcode_verification_url()` (lines 172-244)

**Recommendation:** Extract common context detection logic into `WDM_Cert_Helper::get_current_certificate_context()`.

### Medium Similarity: Completion Verification

The following methods verify user completion status:

1. `WDM_Certificate_Customizations::allow_pocket_certificate()` (lines 282-406)
2. `WDM_Certificate_Customizations::allow_public_certificate_view()` (lines 419-547)
3. `WDM_Cert_Verification::verify_completion()` (lines 262-291)
4. `WDM_Cert_Helper::generate_certificate_record()` (lines 194-237)

**Recommendation:** Consolidate completion verification into a single `WDM_Cert_Helper::verify_user_completion()` method.

---

## Security Considerations

### Nonce Verification

| Context | Nonce Action | Verified In |
|---------|--------------|-------------|
| Admin AJAX | `wdm_cert_admin` | `ajax_generate_retroactive()` |
| Frontend AJAX | `wdm_cert_verify` | `ajax_verify_certificate()` |
| Meta box save | `wdm_pocket_certificate_nonce` | `save_pocket_certificate_meta()` |
| Certificate access | `{source_id}{cert_user_id}{view_user_id}` | `allow_pocket_certificate()` |

### Capability Checks

| Capability | Method |
|------------|--------|
| `manage_options` | `ajax_generate_retroactive()`, `render_settings_page()` |
| `edit_post` | `save_pocket_certificate_meta()` |

### Input Sanitization

All user inputs are sanitized using:
- `absint()` for integer IDs
- `sanitize_text_field()` for text inputs
- `wp_kses_post()` for HTML output
- `esc_url()`, `esc_attr()`, `esc_html()` for output escaping

---

## External Integrations

### LearnDash Functions Used

| Function | Purpose |
|----------|---------|
| `learndash_get_setting()` | Get course/quiz settings |
| `learndash_course_completed()` | Check course completion |
| `learndash_course_status()` | Get course status |
| `learndash_get_user_quiz_attempt()` | Get quiz attempts |
| `learndash_get_user_group_completed_timestamp()` | Get group completion |
| `learndash_get_course_id()` | Get course ID from quiz |
| `learndash_is_admin_user()` | Check if admin |
| `learndash_is_group_leader_user()` | Check if group leader |
| `learndash_certificate_post_shortcode()` | Render certificate PDF |

### LearnDash Hooks Used

| Hook | Type | Purpose |
|------|------|---------|
| `learndash_course_completed` | Action | Generate certificate on completion |
| `learndash_quiz_completed` | Action | Generate certificate on quiz pass |
| `ld_added_group_access` | Action | Check group completion |
| `learndash_certificate_disallowed` | Action | Allow pocket/public certificate access |
| `learndash_course_certificate_link` | Filter | Modify certificate link |
| `learndash_quiz_certificate_link` | Filter | Modify quiz certificate link |
| `learndash_settings_fields` | Filter | Add pocket certificate field |
| `learndash_metabox_save_fields_*` | Filter | Save pocket certificate setting |
| `learndash_tcpdf_init` | Action | Render certificate PDF |

---

## Version History

| Version | Changes |
|---------|---------|
| 1.0.0 | Initial release |

---

*Generated: 2026-02-05*
