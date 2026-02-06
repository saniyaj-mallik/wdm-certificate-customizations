# RECOVERED: WDM Certificate Customizations Specification

> **RECOVERED DOCUMENT:** Auto-generated from code analysis on 2026-02-05
> **Confidence Score:** 88%
> **Review Required:** Yes - Verify business requirements and acceptance criteria

---

## 1. Executive Summary

### 1.1 Purpose

WDM Certificate Customizations is a WordPress plugin that extends LearnDash LMS with dual certificate support (Standard + Pocket/Wallet Size) and a built-in QR code verification system. The plugin enables organizations to issue verifiable digital certificates with unique Certificate IDs (CSUIDs) that can be independently verified by third parties.

### 1.2 Key Capabilities

1. **Dual Certificate Support:** Assign both standard and pocket-size certificates to courses, quizzes, and groups
2. **Unique Certificate IDs:** Generate hexadecimal-encoded CSUIDs for each certificate issuance
3. **QR Code Generation:** Embed scannable QR codes in certificates linking to verification pages
4. **Public Verification System:** Allow anyone to verify certificate authenticity via web interface
5. **Retroactive Generation:** Generate certificate IDs for historical completions
6. **Migration Support:** Import certificate data from LD Certificate Verify and Share plugin

### 1.3 Target Users

| User Role | Primary Use Case |
|-----------|------------------|
| Site Administrators | Configure certificate settings, manage verification pages |
| Course Creators | Assign pocket certificates to courses/quizzes |
| Students | View and download certificates, share verification links |
| Third Parties | Verify certificate authenticity via public verification page |

---

## 2. Functional Requirements

### FR-001: Dual Certificate Assignment

**Confidence:** 92%
**Source:** `class-admin.php:add_course_certificate_field()`, `render_pocket_certificate_metabox()`

**Description:** System shall allow administrators to assign a secondary "pocket certificate" to courses and quizzes in addition to the standard LearnDash certificate.

**Acceptance Criteria:**
- [ ] AC-001.1: Pocket certificate field appears in LearnDash course settings
- [ ] AC-001.2: Pocket certificate field appears in LearnDash quiz settings
- [ ] AC-001.3: Pocket certificate can be selected from dropdown of available Certificate Builder templates
- [ ] AC-001.4: Pocket certificate setting persists after save
- [ ] AC-001.5: Compatible with both Gutenberg and Classic Editor

---

### FR-002: Certificate ID Generation (CSUID)

**Confidence:** 95%
**Source:** `class-helper.php:encode_csuid()`, `class-certificate-handler.php:generate_certificate_record()`

**Description:** System shall generate a unique Certificate ID (CSUID) when a user earns a certificate, encoded in hexadecimal format.

**Acceptance Criteria:**
- [ ] AC-002.1: CSUID generated on course completion if certificate is assigned
- [ ] AC-002.2: CSUID generated on quiz pass if certificate is assigned
- [ ] AC-002.3: CSUID generated on group completion if certificate is assigned
- [ ] AC-002.4: CSUID format: `CERT_HEX-SOURCE_HEX-USER_HEX` (e.g., `1A-2B-3C`)
- [ ] AC-002.5: CSUID is unique per certificate instance
- [ ] AC-002.6: Certificate record stored in user meta

**CSUID Format:**
```
Format: {certificate_id_hex}-{source_id_hex}-{user_id_hex}
Example: 1F4-A3-2BC (Certificate 500, Source 163, User 700)
```

---

### FR-003: Certificate Record Storage

**Confidence:** 90%
**Source:** `class-certificate-handler.php:generate_certificate_record()`, `get_certificate_record()`

**Description:** System shall store a complete certificate record for each earned certificate containing all metadata required for verification.

**Acceptance Criteria:**
- [ ] AC-003.1: Record stored in user meta with key pattern `_wdm_certificate_{type}_{id}`
- [ ] AC-003.2: Record includes CSUID, standard cert ID, pocket cert ID
- [ ] AC-003.3: Record includes source type (course/quiz/group) and source ID
- [ ] AC-003.4: Record includes completion and generation timestamps
- [ ] AC-003.5: Record includes retroactive flag if generated after-the-fact

**Record Structure:**
```php
array(
    'certificate_id'  => string,  // CSUID
    'standard_cert'   => int,     // Certificate template ID
    'pocket_cert'     => int,     // Pocket certificate ID (optional)
    'source_type'     => string,  // 'course', 'quiz', or 'group'
    'source_id'       => int,     // Course/Quiz/Group ID
    'user_id'         => int,     // User ID
    'completion_date' => int,     // Unix timestamp
    'generated_date'  => int,     // Unix timestamp
    'is_retroactive'  => bool,    // True if retroactively generated
)
```

---

### FR-004: QR Code Generation

**Confidence:** 93%
**Source:** `class-qr-code.php:generate_for_certificate()`, `get_contextual_qr_code()`

**Description:** System shall generate QR codes containing verification URLs that can be embedded in certificate templates.

**Acceptance Criteria:**
- [ ] AC-004.1: QR code links to verification page with embedded CSUID
- [ ] AC-004.2: QR code size configurable (50-500 pixels)
- [ ] AC-004.3: QR code generated via QuickChart.io API
- [ ] AC-004.4: QR code available via shortcode for certificate templates
- [ ] AC-004.5: QR code contextually detects current certificate being viewed

---

### FR-005: Certificate Verification System

**Confidence:** 95%
**Source:** `class-verification.php:verify_certificate()`, `ajax_verify_certificate()`

**Description:** System shall provide a public verification page where anyone can verify certificate authenticity by entering or scanning a CSUID.

**Acceptance Criteria:**
- [ ] AC-005.1: Verification page accessible without login
- [ ] AC-005.2: Verification accepts CSUID via URL parameter or form input
- [ ] AC-005.3: Valid certificates display: recipient name, course/quiz title, completion date
- [ ] AC-005.4: Valid certificates provide PDF download links
- [ ] AC-005.5: Invalid certificates display appropriate error messages
- [ ] AC-005.6: Pretty URLs supported (e.g., `/verify/1A-2B-3C/`)

**Error Codes:**
| Code | Description |
|------|-------------|
| `invalid_format` | CSUID does not match expected pattern |
| `decode_failed` | CSUID could not be decoded |
| `source_not_found` | Course/Quiz/Group no longer exists |
| `user_not_found` | Certificate recipient no longer exists |
| `certificate_not_found` | Certificate template no longer exists |
| `not_completed` | User has not completed the course/quiz |

---

### FR-006: Public Certificate Access

**Confidence:** 90%
**Source:** `wdm-certificate-customizations.php:allow_public_certificate_view()`, `allow_pocket_certificate()`

**Description:** System shall allow non-logged-in users to view certificate PDFs via verification links while maintaining security.

**Acceptance Criteria:**
- [ ] AC-006.1: Certificates viewable via verification URL without login
- [ ] AC-006.2: Download buttons hidden for non-owners
- [ ] AC-006.3: Access validated against CSUID and completion status
- [ ] AC-006.4: Pocket certificates accessible alongside standard certificates
- [ ] AC-006.5: Certificate owner retains download capability

---

### FR-007: Shortcode System

**Confidence:** 95%
**Source:** `class-shortcodes.php`

**Description:** System shall provide shortcodes for embedding certificate elements in templates and pages.

**Acceptance Criteria:**
- [ ] AC-007.1: `[wdm_certificate_verify]` displays verification form and results
- [ ] AC-007.2: `[wdm_certificate_qr_code]` displays QR code on certificates
- [ ] AC-007.3: `[wdm_certificate_id]` displays CSUID on certificates
- [ ] AC-007.4: `[wdm_certificate_verification_url]` displays verification URL
- [ ] AC-007.5: Shortcodes support customization via attributes

**Shortcode Reference:**

| Shortcode | Use Location | Key Attributes |
|-----------|--------------|----------------|
| `[wdm_certificate_verify]` | Verification page | `show_form` |
| `[wdm_certificate_qr_code]` | Certificate template | `size`, `align`, `class` |
| `[wdm_certificate_id]` | Certificate template | `prefix`, `suffix`, `class` |
| `[wdm_certificate_verification_url]` | Certificate template | `link_text`, `target` |

---

### FR-008: Admin Settings Interface

**Confidence:** 92%
**Source:** `class-admin.php:render_settings_page()`, `register_settings()`

**Description:** System shall provide an admin interface for configuring plugin settings under the LearnDash menu.

**Acceptance Criteria:**
- [ ] AC-008.1: Settings page accessible at LearnDash > Certificate Customizations
- [ ] AC-008.2: Verification page selectable from WordPress pages
- [ ] AC-008.3: QR code default size configurable
- [ ] AC-008.4: Pocket certificate feature can be enabled/disabled
- [ ] AC-008.5: Custom CSS field available for styling
- [ ] AC-008.6: Settings link appears on plugins page

**Configuration Options:**
| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `verification_page_id` | int | - | Page containing verification shortcode |
| `qr_code_size` | int | 150 | Default QR code size (50-500px) |
| `enable_pocket_certificate` | bool | true | Enable wallet card feature |
| `certificate_id_prefix` | string | - | Prefix for certificate IDs |
| `custom_css` | text | - | Custom CSS styles |

---

### FR-009: Retroactive Certificate ID Generation

**Confidence:** 88%
**Source:** `class-upgrade.php:generate_retroactive_certificate_ids()`, `class-admin.php:ajax_generate_retroactive()`

**Description:** System shall allow administrators to generate certificate IDs for historical course/quiz completions that occurred before plugin installation.

**Acceptance Criteria:**
- [ ] AC-009.1: Retroactive generation available from admin settings
- [ ] AC-009.2: Scans LearnDash activity table for past completions
- [ ] AC-009.3: Generates CSUIDs for completions without existing records
- [ ] AC-009.4: Marks generated records as retroactive
- [ ] AC-009.5: Reports count of records created
- [ ] AC-009.6: AJAX-based with progress indication

---

### FR-010: Migration from LD Certificate Verify and Share

**Confidence:** 82%
**Source:** `class-upgrade.php:migrate_from_ld_cvss()`

**Description:** System shall support migration of certificate data from the LD Certificate Verify and Share plugin.

**Acceptance Criteria:**
- [ ] AC-010.1: Migration option available in admin settings
- [ ] AC-010.2: Imports existing certificate records
- [ ] AC-010.3: Preserves completion dates
- [ ] AC-010.4: Generates new CSUIDs for migrated records
- [ ] AC-010.5: Reports migration statistics

---

### FR-011: Certificate Statistics Dashboard

**Confidence:** 85%
**Source:** `class-upgrade.php:get_statistics()`

**Description:** System shall provide statistics about certificate issuance in the admin dashboard.

**Acceptance Criteria:**
- [ ] AC-011.1: Total certificate records displayed
- [ ] AC-011.2: Breakdown by source type (course/quiz/group)
- [ ] AC-011.3: Count of retroactive records
- [ ] AC-011.4: Count of records with pocket certificates

---

### FR-012: Certificate Link Modification

**Confidence:** 90%
**Source:** `class-certificate-handler.php:modify_certificate_link()`, `modify_quiz_certificate_link()`

**Description:** System shall modify LearnDash certificate links to include CSUID parameters for proper verification support.

**Acceptance Criteria:**
- [ ] AC-012.1: Course certificate links modified to include CSUID
- [ ] AC-012.2: Quiz certificate links modified to include CSUID
- [ ] AC-012.3: View parameter added to switch between standard/pocket
- [ ] AC-012.4: Original LearnDash link functionality preserved

---

## 3. Non-Functional Requirements

### NFR-001: LearnDash Compatibility

**Confidence:** 95%
**Source:** Dependency check in `check_dependencies()`

**Description:** System shall require and integrate with LearnDash LMS core plugin.

**Requirements:**
- [ ] NFR-001.1: Plugin deactivates gracefully if LearnDash not present
- [ ] NFR-001.2: Admin notice displayed if dependencies missing
- [ ] NFR-001.3: Compatible with LearnDash 4.x settings panels
- [ ] NFR-001.4: Uses LearnDash hooks and functions appropriately

---

### NFR-002: Certificate Builder Compatibility

**Confidence:** 95%
**Source:** Dependency check for `LEARNDASH_CERTIFICATE_BUILDER_VERSION`

**Description:** System shall require and integrate with LearnDash Certificate Builder add-on.

**Requirements:**
- [ ] NFR-002.1: Plugin deactivates gracefully if Certificate Builder not present
- [ ] NFR-002.2: Pocket certificates selected from Certificate Builder templates
- [ ] NFR-002.3: Compatible with Certificate Builder template shortcodes

---

### NFR-003: Security - Nonce Verification

**Confidence:** 95%
**Source:** Multiple AJAX handlers and form processing

**Description:** System shall verify nonces for all AJAX requests and form submissions.

**Requirements:**
- [ ] NFR-003.1: Admin AJAX uses `wdm_cert_admin` nonce
- [ ] NFR-003.2: Frontend AJAX uses `wdm_cert_verify` nonce
- [ ] NFR-003.3: Meta box saves use `wdm_pocket_certificate_nonce`
- [ ] NFR-003.4: Certificate access validates `{source_id}{cert_user_id}{view_user_id}` nonce

---

### NFR-004: Security - Capability Checks

**Confidence:** 93%
**Source:** Admin methods with capability checks

**Description:** System shall enforce capability checks for privileged operations.

**Requirements:**
- [ ] NFR-004.1: Settings page requires `manage_options` capability
- [ ] NFR-004.2: Retroactive generation requires `manage_options` capability
- [ ] NFR-004.3: Meta box saves require `edit_post` capability
- [ ] NFR-004.4: Admin menu respects LearnDash admin checks

---

### NFR-005: Security - Input Sanitization

**Confidence:** 92%
**Source:** Input handling throughout codebase

**Description:** System shall sanitize all user inputs and escape all outputs.

**Requirements:**
- [ ] NFR-005.1: Integer IDs sanitized with `absint()`
- [ ] NFR-005.2: Text inputs sanitized with `sanitize_text_field()`
- [ ] NFR-005.3: HTML output sanitized with `wp_kses_post()`
- [ ] NFR-005.4: URLs escaped with `esc_url()`
- [ ] NFR-005.5: Attributes escaped with `esc_attr()`
- [ ] NFR-005.6: HTML escaped with `esc_html()`

---

### NFR-006: Performance - External API

**Confidence:** 88%
**Source:** `class-qr-code.php:generate_url()`

**Description:** System shall use external QR code API efficiently.

**Requirements:**
- [ ] NFR-006.1: QR codes generated via QuickChart.io API
- [ ] NFR-006.2: QR code URLs cached when possible
- [ ] NFR-006.3: Graceful handling of API unavailability

---

### NFR-007: Internationalization

**Confidence:** 90%
**Source:** `load_textdomain()` in main plugin file

**Description:** System shall support translation via WordPress i18n system.

**Requirements:**
- [ ] NFR-007.1: Text domain `wdm-certificate-customizations` loaded
- [ ] NFR-007.2: All user-facing strings wrapped in translation functions
- [ ] NFR-007.3: POT file available for translation

---

### NFR-008: WordPress Compatibility

**Confidence:** 85%
**Source:** WordPress API usage patterns

**Description:** System shall be compatible with WordPress coding standards and best practices.

**Requirements:**
- [ ] NFR-008.1: Compatible with WordPress 5.0+ (Gutenberg support)
- [ ] NFR-008.2: Compatible with Classic Editor
- [ ] NFR-008.3: Uses WordPress Settings API
- [ ] NFR-008.4: Uses WordPress AJAX API
- [ ] NFR-008.5: Uses WordPress Rewrite API

---

### NFR-009: PHP Compatibility

**Confidence:** 80%
**Source:** Code syntax and patterns

**Description:** System shall be compatible with supported PHP versions.

**Requirements:**
- [ ] NFR-009.1: PHP 7.4 minimum required
- [ ] NFR-009.2: PHP 8.0+ compatible
- [ ] NFR-009.3: No deprecated function usage

---

## 4. User Stories

### 4.1 Administrator User Stories

#### US-ADMIN-001: Configure Verification Page
**As an** administrator
**I want to** select which WordPress page serves as the verification page
**So that** I can control where certificate verification occurs

**Acceptance Criteria:**
- Dropdown shows all published WordPress pages
- Selected page ID saved to plugin options
- Rewrite rules generated for pretty URLs

---

#### US-ADMIN-002: Assign Pocket Certificate to Course
**As an** administrator
**I want to** assign a pocket-size certificate to a course
**So that** students receive both standard and wallet-size certificates

**Acceptance Criteria:**
- Pocket certificate field visible in course settings
- Can select from Certificate Builder templates
- Setting saved with course meta

---

#### US-ADMIN-003: Generate Retroactive Certificate IDs
**As an** administrator
**I want to** generate certificate IDs for past completions
**So that** historical completions can also be verified

**Acceptance Criteria:**
- Button available in admin settings
- Progress indication during generation
- Summary of records created displayed

---

#### US-ADMIN-004: View Certificate Statistics
**As an** administrator
**I want to** see statistics about issued certificates
**So that** I can monitor certificate usage

**Acceptance Criteria:**
- Total count displayed
- Breakdown by course/quiz/group shown
- Retroactive count identified

---

#### US-ADMIN-005: Customize QR Code Settings
**As an** administrator
**I want to** configure default QR code size
**So that** QR codes fit my certificate design

**Acceptance Criteria:**
- Size input accepts 50-500 pixels
- Default applied to all QR shortcodes
- Individual shortcodes can override

---

### 4.2 Student User Stories

#### US-STUDENT-001: View Certificate with QR Code
**As a** student
**I want to** view my certificate with an embedded QR code
**So that** others can easily verify my achievement

**Acceptance Criteria:**
- QR code visible on certificate PDF
- QR code scans to verification page
- QR code includes my certificate ID

---

#### US-STUDENT-002: Download Standard and Pocket Certificates
**As a** student
**I want to** download both my standard and pocket-size certificates
**So that** I can print them at different sizes

**Acceptance Criteria:**
- Both download buttons visible (if pocket assigned)
- Each downloads correct certificate template
- Downloads work from profile and verification page

---

#### US-STUDENT-003: Share Verification Link
**As a** student
**I want to** copy a verification link for my certificate
**So that** I can share it with employers or others

**Acceptance Criteria:**
- Verification URL displayed on certificate
- URL works without login required
- URL shows my certificate details

---

#### US-STUDENT-004: View Certificate ID
**As a** student
**I want to** see my unique certificate ID on my certificate
**So that** I can reference it when needed

**Acceptance Criteria:**
- Certificate ID displayed on certificate PDF
- ID matches format shown in verification system
- ID is unique to my specific certificate

---

### 4.3 Anonymous/Third-Party User Stories

#### US-ANON-001: Verify Certificate by ID
**As a** third party
**I want to** verify a certificate by entering its ID
**So that** I can confirm someone's credentials

**Acceptance Criteria:**
- Verification form accepts certificate ID
- Valid certificates show recipient and course details
- Invalid certificates show clear error message

---

#### US-ANON-002: Verify Certificate by QR Scan
**As a** third party
**I want to** scan a QR code to verify a certificate
**So that** I can quickly confirm credentials

**Acceptance Criteria:**
- QR code leads to verification page
- Certificate ID auto-populated from URL
- Verification result displayed immediately

---

#### US-ANON-003: View Certificate Details Without Login
**As a** third party
**I want to** view certificate details without creating an account
**So that** verification is quick and easy

**Acceptance Criteria:**
- No login required for verification
- Recipient name and course title visible
- Completion date displayed
- PDF preview available (download restricted)

---

## 5. System Constraints

### 5.1 Required Dependencies

| Dependency | Type | Version | Purpose |
|------------|------|---------|---------|
| WordPress | Core | 5.0+ | CMS platform |
| LearnDash LMS | Plugin | 4.0+ | LMS functionality |
| LearnDash Certificate Builder | Add-on | Any | Certificate templates |
| PHP | Runtime | 7.4+ | Server-side processing |

### 5.2 Conditional Dependencies

| Dependency | Type | When Required |
|------------|------|---------------|
| MySQL/MariaDB | Database | Always (via WordPress) |
| cURL or file_get_contents | PHP Extension | QR code generation |
| Internet connectivity | Network | QR code API calls |

### 5.3 Dependency Detection

The plugin checks for dependencies at load time:
- `SFWD_LMS` class existence for LearnDash
- `LEARNDASH_CERTIFICATE_BUILDER_VERSION` constant for Certificate Builder

If dependencies are missing:
- Plugin remains inactive (components not loaded)
- Admin notice displayed on dashboard
- Settings page inaccessible

---

## 6. Data Requirements

### 6.1 Post Meta Fields

| Meta Key | Post Type | Type | Description |
|----------|-----------|------|-------------|
| `_wdm_pocket_certificate` | `sfwd-courses` | int | Pocket certificate template ID |
| `_wdm_pocket_certificate` | `sfwd-quiz` | int | Pocket certificate template ID |

### 6.2 User Meta Fields

| Meta Key Pattern | Type | Description |
|------------------|------|-------------|
| `_wdm_certificate_course_{id}` | array | Certificate record for course completion |
| `_wdm_certificate_quiz_{id}` | array | Certificate record for quiz completion |
| `_wdm_certificate_group_{id}` | array | Certificate record for group completion |

**Record Structure:**
```php
array(
    'certificate_id'  => string,  // CSUID (e.g., "1A-2B-3C")
    'standard_cert'   => int,     // Certificate template post ID
    'pocket_cert'     => int,     // Pocket certificate post ID (0 if none)
    'source_type'     => string,  // 'course', 'quiz', or 'group'
    'source_id'       => int,     // Course/Quiz/Group post ID
    'user_id'         => int,     // WordPress user ID
    'completion_date' => int,     // Unix timestamp of completion
    'generated_date'  => int,     // Unix timestamp of record creation
    'is_retroactive'  => bool,    // True if generated after completion
)
```

### 6.3 WordPress Options

| Option Key | Type | Structure |
|------------|------|-----------|
| `wdm_certificate_options` | array | Plugin settings |

**Options Structure:**
```php
array(
    'verification_page_id'     => int,     // WordPress page ID
    'qr_code_size'             => int,     // Default 150 (50-500)
    'enable_pocket_certificate' => bool,   // Default true
    'certificate_id_prefix'    => string,  // Optional prefix
    'custom_css'               => string,  // Custom CSS code
)
```

### 6.4 LearnDash Data Dependencies

The plugin reads from these LearnDash data sources:

| Source | Table/Meta | Purpose |
|--------|------------|---------|
| Activity Table | `{prefix}learndash_user_activity` | Historical completions |
| Course Settings | `learndash_get_setting()` | Assigned certificate ID |
| Quiz Settings | `learndash_get_setting()` | Assigned certificate ID |
| User Quiz Data | `_sfwd-quizzes` user meta | Quiz completion status |
| Group Completion | `learndash_group_completed_*` | Group completion timestamps |

### 6.5 Rewrite Rules

The plugin registers custom rewrite rules:

| Pattern | Query String |
|---------|--------------|
| `^{verification_slug}/([A-Fa-f0-9_-]+)/?$` | `index.php?pagename={slug}&cert_id=$1` |

**Query Variables:**
- `cert_id`: Certificate CSUID
- `view`: Certificate view preference (`standard` or `pocket`)

### 6.6 AJAX Endpoints

| Action | Scope | Purpose |
|--------|-------|---------|
| `wdm_cert_verify` | Public | Verify certificate |
| `wdm_cert_generate_retroactive` | Admin | Generate retroactive IDs |

---

## 7. Appendices

### Appendix A: CSUID Format Specification

**Format:** `{CERT_HEX}-{SOURCE_HEX}-{USER_HEX}`

| Component | Encoding | Example |
|-----------|----------|---------|
| Certificate ID | Uppercase hex | `1F4` (decimal 500) |
| Source ID | Uppercase hex | `A3` (decimal 163) |
| User ID | Uppercase hex | `2BC` (decimal 700) |

**Complete Example:** `1F4-A3-2BC`

**Validation Regex (new format):**
```regex
^[A-F0-9]+(?:_[A-F0-9]+)?-[A-F0-9]+(?:_[A-F0-9]+)?-[A-F0-9]+(?:_[A-F0-9]+)?$
```

### Appendix B: API Integration

**QR Code API:**
- Service: QuickChart.io
- Endpoint: `https://quickchart.io/qr`
- Parameters: `text`, `size`, `margin`
- Rate Limits: Subject to QuickChart.io terms

### Appendix C: Hook Reference

**Actions Fired:**
| Hook | Parameters |
|------|------------|
| `wdm_certificate_record_generated` | `$record`, `$csuid` |

**Filters Available:**
| Filter | Parameters | Return |
|--------|------------|--------|
| `wdm_cert_verification_result` | `$result`, `$csuid` | `$result` |

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0-RECOVERED | 2026-02-05 | Spec Recovery Agent | Initial recovery from code analysis |

---

*This document was auto-generated from code analysis. Review and validation recommended.*
