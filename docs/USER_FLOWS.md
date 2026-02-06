# User Flows: WDM Certificate Customizations

**Generated From:** specs/RECOVERED_SPECIFICATION.md, docs/ARCHITECTURE.md
**Date:** 2026-02-05
**Version:** 1.0.0
**Platform:** WordPress + LearnDash LMS

---

## Roles Identified

| Role | Capabilities | Feature Access | Flow Count |
|------|--------------|----------------|------------|
| Administrator | Full access (`manage_options`) | All settings, retroactive generation, statistics, meta boxes | 6 |
| Course Creator/Instructor | Course management (`edit_courses`) | Assign pocket certificates to courses/quizzes | 2 |
| Student (Certificate Owner) | Learning completion | View, download, share certificates | 5 |
| Anonymous User (Third Party) | None (public access) | Verify certificates, view certificate preview | 3 |

---

## WordPress Core Flows (Affected by Plugin)

### WP-CORE-001: Plugin Activation

**Base Flow:** WordPress Plugin Activation
**Plugin Modification:** Dependency check, rewrite rule flush

| Step | System Action | Result |
|------|---------------|--------|
| 1 | Admin clicks "Activate" on plugin | Activation hook triggered |
| 2 | Plugin checks for LearnDash LMS | Continues if `SFWD_LMS` class exists |
| 3 | Plugin checks for Certificate Builder | Continues if `LEARNDASH_CERTIFICATE_BUILDER_VERSION` defined |
| 4 | Rewrite rules flushed | Pretty URLs enabled for verification |
| 5 | Plugin components initialized | Admin menu, handlers, shortcodes registered |

**Related Capabilities:** `WDM_Certificate_Customizations::activate()`, `check_dependencies()`

---

## Administrator Flows

### WP-ADMIN-001: Configure Plugin Settings

**Flow ID:** WP-ADMIN-001
**Flow Name:** Configure Plugin Settings
**Actor:** Administrator
**Preconditions:**
- User logged in with `manage_options` capability
- LearnDash LMS active
- LearnDash Certificate Builder active
- Plugin activated

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to LearnDash > Certificate Customizations | Load settings page | Settings form displayed with tabs | None |
| 2 | Select verification page from dropdown | Page ID updated in form | Dropdown shows selected page | None (unsaved) |
| 3 | Enter QR code default size (50-500px) | Value validated client-side | Size input shows value | None (unsaved) |
| 4 | Toggle "Enable Pocket Certificates" checkbox | Checkbox state updated | Checkbox reflects new state | None (unsaved) |
| 5 | (Optional) Enter Certificate ID prefix | Prefix text captured | Text field shows prefix | None (unsaved) |
| 6 | (Optional) Enter custom CSS | CSS text captured | Textarea shows CSS | None (unsaved) |
| 7 | Click "Save Changes" | Settings sanitized and saved | Success notice displayed | `wdm_certificate_options` updated |
| 8 | Rewrite rules flushed automatically | Pretty URLs updated | No visible change | Rewrite rules refreshed |

#### Error Paths

| Error Scenario | Trigger | System Response | Recovery |
|----------------|---------|-----------------|----------|
| Invalid QR size | Size < 50 or > 500 | Validation error, value clamped | Correct to valid range |
| No verification page selected | Submit without page | Warning notice, settings saved | Select a page later |
| Insufficient permissions | Non-admin access attempt | Access denied / 403 | Login as administrator |

#### Edge Cases

| Case ID | Condition | Expected Behavior |
|---------|-----------|-------------------|
| EC-001 | Verification page deleted after selection | Plugin shows warning, prompts reselection |
| EC-002 | Certificate Builder deactivated | Plugin shows dependency notice, features disabled |
| EC-003 | Settings saved with empty values | Defaults applied, no error |

#### Related Capabilities

- `WDM_Cert_Admin::render_settings_page()` - Renders the settings form
- `WDM_Cert_Admin::register_settings()` - Registers WordPress settings
- `WDM_Cert_Admin::sanitize_options()` - Sanitizes input values
- `WDM_Certificate_Customizations::get_option()` - Retrieves option values

---

### WP-ADMIN-002: Generate Retroactive Certificate IDs

**Flow ID:** WP-ADMIN-002
**Flow Name:** Generate Retroactive Certificate IDs
**Actor:** Administrator
**Preconditions:**
- User logged in with `manage_options` capability
- Historical course/quiz completions exist in LearnDash
- Plugin settings page accessible

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to LearnDash > Certificate Customizations | Load settings page | Settings page displayed | None |
| 2 | Scroll to "Retroactive Certificate IDs" section | Section visible | Statistics shown (if any) | None |
| 3 | Click "Generate Retroactive IDs" button | AJAX request initiated | Button disabled, spinner shown | None |
| 4 | (Wait) System scans LearnDash activity table | Progress indication | "Processing..." message | None |
| 5 | System generates CSUIDs for past completions | Records created | Progress updates | User meta records created |
| 6 | Process completes | Summary displayed | Success message with count | Multiple `_wdm_certificate_*` records |
| 7 | View updated statistics | Stats refresh | New totals displayed | None |

#### Error Paths

| Error Scenario | Trigger | System Response | Recovery |
|----------------|---------|-----------------|----------|
| AJAX timeout | Large dataset, slow server | Error message | Retry with smaller batches |
| Nonce verification failed | Session expired | Security error | Refresh page, retry |
| No completions found | Empty activity table | "No records to process" | None needed |

#### Edge Cases

| Case ID | Condition | Expected Behavior |
|---------|-----------|-------------------|
| EC-001 | Record already exists for completion | Skip existing, no duplicate |
| EC-002 | Course/Quiz deleted but completion exists | Record created with retroactive flag |
| EC-003 | User deleted but completion exists | Skip record, log warning |
| EC-004 | Concurrent generation requests | Queue or reject duplicate |

#### Related Capabilities

- `WDM_Cert_Admin::ajax_generate_retroactive()` - AJAX handler
- `WDM_Cert_Upgrade::generate_retroactive_certificate_ids()` - Core logic
- `WDM_Cert_Handler::generate_certificate_record()` - Record creation
- `WDM_Cert_Helper::encode_csuid()` - CSUID generation

---

### WP-ADMIN-003: Assign Wallet Card to Course

**Flow ID:** WP-ADMIN-003
**Flow Name:** Assign Wallet Card (Pocket Certificate) to Course
**Actor:** Administrator or Course Creator
**Preconditions:**
- User logged in with `edit_courses` capability
- At least one Certificate Builder template exists
- Course exists in LearnDash
- Pocket certificates enabled in plugin settings

#### Flow Steps (Gutenberg Editor)

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to LearnDash > Courses | Course list displayed | Course table visible | None |
| 2 | Click on course to edit | Course editor loads | Gutenberg editor open | None |
| 3 | Open "Settings" sidebar panel | LearnDash settings visible | Settings panel expanded | None |
| 4 | Locate "Display & Content" section | Section expanded | Certificate fields visible | None |
| 5 | Find "Pocket Certificate" dropdown | Dropdown populated with templates | Shows "-- Select --" or current | None |
| 6 | Select pocket certificate template | Selection captured | Dropdown shows selected | None (unsaved) |
| 7 | Click "Update" to save course | Course and meta saved | Success notice | `_wdm_pocket_certificate` post meta |

#### Flow Steps (Classic Editor)

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to LearnDash > Courses | Course list displayed | Course table visible | None |
| 2 | Click on course to edit | Course editor loads | Classic editor open | None |
| 3 | Scroll to "WDM Pocket Certificate" meta box | Meta box visible | Dropdown shown | None |
| 4 | Select pocket certificate from dropdown | Selection captured | Dropdown updated | None (unsaved) |
| 5 | Click "Update" to save course | Course and meta saved | Page reloads with notice | `_wdm_pocket_certificate` post meta |

#### Error Paths

| Error Scenario | Trigger | System Response | Recovery |
|----------------|---------|-----------------|----------|
| No certificate templates | Certificate Builder empty | Dropdown shows only "-- Select --" | Create certificate template first |
| Nonce verification failed | Session expired | Save fails silently | Refresh page, retry |
| Insufficient permissions | Non-editor access | Meta box not shown | Login with proper role |

#### Edge Cases

| Case ID | Condition | Expected Behavior |
|---------|-----------|-------------------|
| EC-001 | Pocket certificates disabled in settings | Field hidden or disabled |
| EC-002 | Selected template deleted | Fallback to no selection, warning |
| EC-003 | Course has no standard certificate | Pocket still assignable |

#### Related Capabilities

- `WDM_Cert_Admin::add_course_certificate_field()` - Adds field to Gutenberg
- `WDM_Cert_Admin::add_meta_boxes()` - Registers classic meta box
- `WDM_Cert_Admin::render_pocket_certificate_metabox()` - Renders meta box
- `WDM_Cert_Admin::save_pocket_certificate_learndash()` - Saves via LearnDash
- `WDM_Cert_Admin::save_course_meta()` - Saves via classic editor

---

### WP-ADMIN-004: Assign Wallet Card to Quiz

**Flow ID:** WP-ADMIN-004
**Flow Name:** Assign Wallet Card (Pocket Certificate) to Quiz
**Actor:** Administrator or Course Creator
**Preconditions:**
- User logged in with `edit_quizzes` capability
- At least one Certificate Builder template exists
- Quiz exists in LearnDash with certificate enabled
- Pocket certificates enabled in plugin settings

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to LearnDash > Quizzes | Quiz list displayed | Quiz table visible | None |
| 2 | Click on quiz to edit | Quiz editor loads | Editor open | None |
| 3 | Open "Settings" panel | LearnDash settings visible | Settings expanded | None |
| 4 | Locate "Display & Content" section | Section expanded | Certificate fields visible | None |
| 5 | Find "Pocket Certificate" dropdown | Dropdown populated | Shows current or default | None |
| 6 | Select pocket certificate template | Selection captured | Dropdown updated | None (unsaved) |
| 7 | Click "Update" to save quiz | Quiz and meta saved | Success notice | `_wdm_pocket_certificate` post meta |

#### Related Capabilities

- `WDM_Cert_Admin::add_course_certificate_field()` - Adds field (works for quizzes)
- `WDM_Cert_Admin::save_quiz_meta()` - Saves quiz meta
- `WDM_Cert_Helper::get_pocket_certificate()` - Retrieves pocket cert ID

---

### WP-ADMIN-005: View Certificate Statistics

**Flow ID:** WP-ADMIN-005
**Flow Name:** View Certificate Statistics
**Actor:** Administrator
**Preconditions:**
- User logged in with `manage_options` capability
- Plugin settings page accessible

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to LearnDash > Certificate Customizations | Load settings page | Settings page displayed | None |
| 2 | View statistics section | Statistics calculated | Stats table displayed | None |
| 3 | Review total certificate records | Count displayed | Number visible | None |
| 4 | Review breakdown by source type | Course/Quiz/Group counts | Breakdown table visible | None |
| 5 | Review retroactive records count | Retroactive count shown | Number visible | None |
| 6 | Review pocket certificate count | Count shown | Number visible | None |

#### Statistics Displayed

| Statistic | Description | Source |
|-----------|-------------|--------|
| Total Records | All certificate records in system | User meta count |
| Course Records | Certificates from course completions | Type filter |
| Quiz Records | Certificates from quiz passes | Type filter |
| Group Records | Certificates from group completions | Type filter |
| Retroactive | Records generated after-the-fact | `is_retroactive` flag |
| With Pocket Cert | Records with pocket certificate assigned | Non-zero `pocket_cert` |

#### Related Capabilities

- `WDM_Cert_Upgrade::get_statistics()` - Gathers statistics
- `WDM_Cert_Admin::render_settings_page()` - Displays statistics

---

### WP-ADMIN-006: Migrate from LD Certificate Verify and Share

**Flow ID:** WP-ADMIN-006
**Flow Name:** Migrate from LD Certificate Verify and Share Plugin
**Actor:** Administrator
**Preconditions:**
- User logged in with `manage_options` capability
- Previous plugin data exists in database
- Plugin settings page accessible

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to LearnDash > Certificate Customizations | Load settings page | Settings page displayed | None |
| 2 | Locate "Migration" section | Section visible | Migration button shown | None |
| 3 | Click "Migrate from LD CVSS" button | AJAX request initiated | Button disabled, spinner | None |
| 4 | (Wait) System scans old plugin data | Progress indication | "Migrating..." message | None |
| 5 | System imports existing records | Records created | Progress updates | User meta records created |
| 6 | New CSUIDs generated for migrated data | IDs encoded | Completion status | Certificate IDs assigned |
| 7 | Process completes | Summary displayed | Success with counts | Migration complete |

#### Related Capabilities

- `WDM_Cert_Upgrade::migrate_from_ld_cvss()` - Migration logic
- `WDM_Cert_Helper::encode_csuid()` - New CSUID generation

---

## Course Creator/Instructor Flows

### WP-INST-001: Review Course Certificate Setup

**Flow ID:** WP-INST-001
**Flow Name:** Review Course Certificate Setup
**Actor:** Course Creator/Instructor
**Preconditions:**
- User logged in with course edit capability
- Course exists and is accessible to user

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to course edit screen | Course editor loads | Editor displayed | None |
| 2 | Open Display & Content settings | Settings panel expands | Certificate fields visible | None |
| 3 | Verify standard certificate assigned | Current selection shown | Dropdown displays value | None |
| 4 | Verify pocket certificate assigned | Current selection shown | Dropdown displays value | None |
| 5 | Preview certificate if needed | Open certificate template | Template preview | None |

---

### WP-INST-002: View Student Certificate Records

**Flow ID:** WP-INST-002
**Flow Name:** View Student Certificate Records
**Actor:** Course Creator/Instructor (with Group Leader access)
**Preconditions:**
- User is Group Leader for student's group
- Student has completed course/quiz with certificate

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to LearnDash > Groups | Group list displayed | Groups visible | None |
| 2 | Select group with students | Group details load | Student list shown | None |
| 3 | Click on student name | Student profile loads | Profile displayed | None |
| 4 | View course progress | Completion status shown | Progress visible | None |
| 5 | Note certificate availability | Certificate links shown | Links visible if earned | None |

---

## Student (Certificate Owner) Flows

### WP-STUDENT-001: Complete Course and Receive Certificate

**Flow ID:** WP-STUDENT-001
**Flow Name:** Complete Course and Receive Certificate
**Actor:** Student (Certificate Owner)
**Preconditions:**
- User logged in as enrolled student
- Course has certificate assigned
- All course requirements completed

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Complete final lesson/topic | Progress updated to 100% | Completion message shown | LearnDash progress updated |
| 2 | Course marked as complete | `learndash_course_completed` hook fires | Success indication | Activity record created |
| 3 | (Automatic) Plugin generates certificate record | CSUID created and stored | No visible UI change | `_wdm_certificate_course_{id}` created |
| 4 | View course page or profile | Certificate link visible | "View Certificate" button | None |
| 5 | Click certificate link | Certificate PDF loads | PDF viewer displayed | None |

#### Data Generated

| Data | Format | Location |
|------|--------|----------|
| Certificate ID (CSUID) | `CERT_HEX-SOURCE_HEX-USER_HEX` | User meta |
| Standard Certificate ID | Integer | User meta record |
| Pocket Certificate ID | Integer (if assigned) | User meta record |
| Completion Date | Unix timestamp | User meta record |
| Generated Date | Unix timestamp | User meta record |

#### Related Capabilities

- `WDM_Cert_Handler::on_course_completed()` - Handles completion event
- `WDM_Cert_Handler::generate_certificate_record()` - Creates record
- `WDM_Cert_Helper::encode_csuid()` - Generates certificate ID
- `WDM_Cert_Handler::modify_certificate_link()` - Updates link with CSUID

---

### WP-STUDENT-002: Download Standard Certificate PDF

**Flow ID:** WP-STUDENT-002
**Flow Name:** Download Standard Certificate PDF
**Actor:** Student (Certificate Owner)
**Preconditions:**
- User logged in
- Course/Quiz completed with certificate earned
- Certificate record exists

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to course/profile page | Page loads with certificate link | Link visible | None |
| 2 | Click "View Certificate" link | Certificate page loads | PDF viewer shown | None |
| 3 | Click "Download" button (browser) | PDF download initiated | Download dialog | None |
| 4 | Save PDF to device | File saved locally | File saved | None |

#### Certificate URL Format

```
/certificate/?course_id={id}&user={uid}&cert-nonce={nonce}
```

| Parameter | Description |
|-----------|-------------|
| `course_id` or `quiz` | Source ID (course or quiz) |
| `user` | User ID of certificate owner |
| `cert-nonce` | Security nonce for validation |

#### Related Capabilities

- `WDM_Cert_Handler::modify_certificate_link()` - Generates link
- `WDM_Certificate_Customizations::allow_public_certificate_view()` - Validates access
- LearnDash PDF generation via `learndash_certificate_post_shortcode()`

---

### WP-STUDENT-003: Download Wallet Card (Pocket Certificate) PDF

**Flow ID:** WP-STUDENT-003
**Flow Name:** Download Wallet Card PDF
**Actor:** Student (Certificate Owner)
**Preconditions:**
- User logged in
- Course/Quiz completed with certificate earned
- Pocket certificate assigned to course/quiz
- Pocket certificates enabled in plugin settings

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to verification page with CSUID | Page loads with certificate details | Verification result shown | None |
| 2 | Locate "Download Wallet Card" button | Button visible (owner only) | Download button displayed | None |
| 3 | Click "Download Wallet Card" | Pocket certificate PDF loads | PDF viewer shown | None |
| 4 | Click browser download button | PDF download initiated | Download dialog | None |
| 5 | Save pocket-sized PDF | File saved locally | File saved | None |

#### Certificate URL Format (Pocket)

```
/certificate/?course_id={id}&user={uid}&cert-nonce={nonce}&view=pocket
```

| Parameter | Description |
|-----------|-------------|
| `view` | Set to `pocket` for wallet card |

#### Related Capabilities

- `WDM_Certificate_Customizations::allow_pocket_certificate()` - Enables pocket access
- `WDM_Cert_Helper::get_pocket_certificate()` - Retrieves pocket cert ID
- `WDM_Cert_Helper::get_pdf_url()` - Generates PDF URL

---

### WP-STUDENT-004: Share Certificate via QR Code

**Flow ID:** WP-STUDENT-004
**Flow Name:** Share Certificate via QR Code
**Actor:** Student (Certificate Owner)
**Preconditions:**
- Certificate earned and record exists
- QR code visible on certificate PDF

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | View certificate PDF | PDF displays with QR code | QR code visible | None |
| 2 | Show QR code to verifier | (Physical action) | QR code displayed | None |
| 3 | Verifier scans with phone | Phone camera captures QR | Scanning interface | None |
| 4 | Verification URL opens | Verification page loads | Result displayed | None |

#### QR Code Contents

| Component | Value |
|-----------|-------|
| URL Base | Site verification page URL |
| Certificate ID | CSUID appended to URL |
| Example | `https://example.com/verify/1A-2B-3C/` |

#### Related Capabilities

- `WDM_Cert_QR_Code::generate_for_certificate()` - Creates QR code
- `WDM_Cert_QR_Code::get_contextual_qr_code()` - Context-aware generation
- `[wdm_certificate_qr_code]` shortcode in certificate template

---

### WP-STUDENT-005: View and Copy Verification URL

**Flow ID:** WP-STUDENT-005
**Flow Name:** View and Copy Verification URL
**Actor:** Student (Certificate Owner)
**Preconditions:**
- Certificate earned and record exists
- Verification URL displayed on certificate

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | View certificate PDF | PDF displays with verification URL | URL visible | None |
| 2 | Select/copy verification URL | URL copied to clipboard | Selection highlighted | None |
| 3 | Share URL via email/message | (External action) | URL shared | None |
| 4 | Recipient opens URL | Verification page loads | Certificate verified | None |

#### Shortcodes for Certificate Templates

| Shortcode | Output | Example |
|-----------|--------|---------|
| `[wdm_certificate_id]` | Certificate ID text | `Certificate ID: 1A-2B-3C` |
| `[wdm_certificate_verification_url]` | Full verification URL | `https://example.com/verify/1A-2B-3C/` |
| `[wdm_certificate_verification_url link_text="Verify"]` | Clickable link | `<a href="...">Verify</a>` |

#### Related Capabilities

- `WDM_Cert_Shortcodes::shortcode_certificate_id()` - ID shortcode
- `WDM_Cert_Shortcodes::shortcode_verification_url()` - URL shortcode
- `WDM_Cert_Helper::get_verification_url()` - URL generation

---

## Anonymous User (Third Party Verifier) Flows

### WP-ANON-001: Verify Certificate by ID

**Flow ID:** WP-ANON-001
**Flow Name:** Verify Certificate by ID
**Actor:** Anonymous User (Third Party)
**Preconditions:**
- Verification page exists and is public
- Certificate ID (CSUID) obtained from certificate holder

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Navigate to verification page | Page loads with search form | Form displayed | None |
| 2 | Enter Certificate ID in form | ID captured in input field | Input shows ID | None |
| 3 | Click "Verify" button | AJAX request to verify | Loading indicator | None |
| 4 | (Wait) System validates CSUID | CSUID decoded and validated | Processing | None |
| 5 | System checks completion status | User completion verified | Processing | None |
| 6 | Verification result displayed | Certificate details shown | Result visible | None |

#### Verification Result (Valid Certificate)

| Field | Description | Example |
|-------|-------------|---------|
| Recipient Name | Certificate holder's display name | "John Smith" |
| Recipient Avatar | Profile picture (if available) | Image |
| Course/Quiz Title | Source of certificate | "Advanced WordPress Development" |
| Completion Date | When certificate was earned | "January 15, 2026" |
| Certificate Preview | Link to view PDF | "View Certificate" button |
| Download Options | PDF download links (hidden for non-owner) | Hidden or restricted |

#### Error Messages

| Error Code | User Message |
|------------|--------------|
| `invalid_format` | "Invalid Certificate ID format. Please check and try again." |
| `decode_failed` | "Could not decode Certificate ID. Please verify the ID is correct." |
| `source_not_found` | "The course or quiz for this certificate could not be found." |
| `user_not_found` | "Certificate recipient could not be found." |
| `certificate_not_found` | "Certificate template not found." |
| `not_completed` | "This certificate has not been earned yet." |

#### Related Capabilities

- `WDM_Cert_Shortcodes::shortcode_verify()` - Renders verification form
- `WDM_Cert_Verification::ajax_verify_certificate()` - AJAX handler
- `WDM_Cert_Verification::verify_certificate()` - Core verification logic
- `WDM_Cert_Helper::decode_csuid()` - CSUID decoding
- `WDM_Cert_Verification::render_verification_result()` - Result display

---

### WP-ANON-002: Verify Certificate via QR Code Scan

**Flow ID:** WP-ANON-002
**Flow Name:** Verify Certificate via QR Code Scan
**Actor:** Anonymous User (Third Party)
**Preconditions:**
- Physical or digital certificate with QR code available
- Mobile device with camera/QR scanner

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Open QR code scanner on device | Scanner activates | Camera view | None |
| 2 | Point camera at QR code | QR code detected | Scan indicator | None |
| 3 | QR code scanned successfully | URL extracted | Link preview | None |
| 4 | Tap/click to open URL | Browser navigates to verification | Loading page | None |
| 5 | Verification page loads with CSUID | CSUID auto-populated | Pre-filled form | None |
| 6 | Verification runs automatically | Certificate validated | Result displayed | None |

#### URL Structure (Pretty URL)

```
https://example.com/verify/1A-2B-3C/
```

| Component | Description |
|-----------|-------------|
| `/verify/` | Verification page slug |
| `1A-2B-3C` | Certificate ID (CSUID) |
| Trailing slash | Optional |

#### URL Structure (Query Parameter)

```
https://example.com/verify/?cert_id=1A-2B-3C
```

#### Related Capabilities

- `WDM_Cert_Verification::add_rewrite_rules()` - Pretty URL routing
- `WDM_Cert_Verification::add_query_vars()` - Query variable registration
- `WDM_Cert_QR_Code::generate_url()` - QR code URL generation

---

### WP-ANON-003: View Certificate Preview (Non-Owner)

**Flow ID:** WP-ANON-003
**Flow Name:** View Certificate Preview (Non-Owner)
**Actor:** Anonymous User (Third Party)
**Preconditions:**
- Valid CSUID obtained
- Certificate verification successful

#### Flow Steps

| Step | User Action | System Response | UI State | Data Changed |
|------|-------------|-----------------|----------|--------------|
| 1 | Complete verification (WP-ANON-001 or WP-ANON-002) | Certificate details displayed | Result visible | None |
| 2 | Click "View Certificate" link | Certificate PDF loads | PDF viewer opens | None |
| 3 | View certificate content | PDF displayed read-only | Certificate visible | None |
| 4 | Note: Download buttons hidden | No download option for non-owner | Buttons not displayed | None |
| 5 | Close certificate view | Return to verification page | Verification visible | None |

#### Access Control for Non-Owners

| Feature | Owner | Non-Owner |
|---------|-------|-----------|
| View Standard Certificate | Yes | Yes (read-only) |
| Download Standard Certificate | Yes | No (hidden) |
| View Pocket Certificate | Yes | Yes (read-only) |
| Download Pocket Certificate | Yes | No (hidden) |
| View Recipient Name | Yes | Yes |
| View Completion Date | Yes | Yes |
| View Course/Quiz Title | Yes | Yes |

#### Security Validation (Non-Logged-In Access)

| Check | Method | Failure Action |
|-------|--------|----------------|
| Certificate assignment | Compare cert ID with source | Deny access |
| User completion | Check LearnDash completion | Deny access |
| CSUID validity | Decode and validate format | Show error |
| Source existence | Check post exists | Show error |

#### Related Capabilities

- `WDM_Certificate_Customizations::allow_public_certificate_view()` - Public access handler
- `WDM_Cert_Verification::verify_certificate()` - Ownership check (`is_owner` field)
- Security nonce: `{source_id}{cert_user_id}{view_user_id}`

---

## Cross-Role Interactions

### CRI-001: Certificate Issuance Workflow

**Roles Involved:** Administrator (configure), Student (earn), Anonymous (verify)

| Step | Role | Action | System Response |
|------|------|--------|-----------------|
| 1 | Administrator | Configure plugin settings | Settings saved |
| 2 | Administrator | Assign certificates to course | Meta saved |
| 3 | Student | Complete course | Completion recorded |
| 4 | System | Generate certificate record | CSUID created |
| 5 | Student | View/download certificate | PDF generated |
| 6 | Student | Share QR code or URL | Verification link shared |
| 7 | Anonymous | Scan/enter certificate ID | Verification initiated |
| 8 | System | Validate and display result | Certificate verified |

---

### CRI-002: Retroactive Certificate Recovery

**Roles Involved:** Administrator (generate), Student (receive)

| Step | Role | Action | System Response |
|------|------|--------|-----------------|
| 1 | Administrator | Navigate to settings | Settings page loads |
| 2 | Administrator | Click "Generate Retroactive IDs" | Process starts |
| 3 | System | Scan historical completions | Records identified |
| 4 | System | Generate CSUIDs for each | Records created |
| 5 | Student | Access course/profile | Certificate link now visible |
| 6 | Student | View certificate with new ID | Certificate displays with CSUID |

---

## Flow-to-Spec Traceability

| Flow ID | Spec Reference | Acceptance Criteria | Related Classes |
|---------|----------------|---------------------|-----------------|
| WP-ADMIN-001 | FR-008 | AC-008.1 to AC-008.6 | `WDM_Cert_Admin` |
| WP-ADMIN-002 | FR-009 | AC-009.1 to AC-009.6 | `WDM_Cert_Upgrade` |
| WP-ADMIN-003 | FR-001 | AC-001.1 to AC-001.5 | `WDM_Cert_Admin` |
| WP-ADMIN-004 | FR-001 | AC-001.2 | `WDM_Cert_Admin` |
| WP-ADMIN-005 | FR-011 | AC-011.1 to AC-011.4 | `WDM_Cert_Upgrade` |
| WP-ADMIN-006 | FR-010 | AC-010.1 to AC-010.5 | `WDM_Cert_Upgrade` |
| WP-STUDENT-001 | FR-002, FR-003 | AC-002.1 to AC-002.6, AC-003.1 to AC-003.5 | `WDM_Cert_Handler` |
| WP-STUDENT-002 | FR-006, FR-012 | AC-006.5, AC-012.1 to AC-012.4 | `WDM_Cert_Handler`, Main class |
| WP-STUDENT-003 | FR-001, FR-006 | AC-001.3, AC-006.4 | Main class |
| WP-STUDENT-004 | FR-004 | AC-004.1 to AC-004.5 | `WDM_Cert_QR_Code` |
| WP-STUDENT-005 | FR-007 | AC-007.3, AC-007.4 | `WDM_Cert_Shortcodes` |
| WP-ANON-001 | FR-005 | AC-005.1 to AC-005.6 | `WDM_Cert_Verification` |
| WP-ANON-002 | FR-004, FR-005 | AC-004.1, AC-005.2 | `WDM_Cert_QR_Code`, `WDM_Cert_Verification` |
| WP-ANON-003 | FR-006 | AC-006.1 to AC-006.4 | Main class |

---

## Summary Statistics

| Metric | Count |
|--------|-------|
| Total Roles | 4 |
| Administrator Flows | 6 |
| Instructor Flows | 2 |
| Student Flows | 5 |
| Anonymous Flows | 3 |
| Total Flows | 16 |
| Cross-Role Interactions | 2 |
| Edge Case Scenarios | 15 |
| Error Paths Documented | 12 |

---

## LearnDash Platform Flows (Modified by Plugin)

### LD-STU-009-MODIFIED: Course Completion with Certificate

**Base Flow:** LearnDash Course Completion
**Plugin Modification:** Certificate record generation with CSUID

| Original Step | Plugin Addition |
|---------------|-----------------|
| Course marked complete | + Plugin hook `learndash_course_completed` fires |
| | + Certificate record generated with CSUID |
| | + User meta `_wdm_certificate_course_{id}` created |
| | + `wdm_certificate_record_generated` action fired |
| Certificate link generated | + CSUID appended to certificate URL |
| | + View parameter added for pocket/standard switch |

---

### LD-STU-005-MODIFIED: Quiz Completion with Certificate

**Base Flow:** LearnDash Quiz Completion (Pass)
**Plugin Modification:** Certificate record generation for passed quizzes

| Original Step | Plugin Addition |
|---------------|-----------------|
| Quiz submitted and passed | + Plugin hook `learndash_quiz_completed` fires |
| | + Certificate record generated with CSUID |
| | + User meta `_wdm_certificate_quiz_{id}` created |
| Quiz certificate link shown | + CSUID appended to certificate URL |

---

## WordPress Core Hooks Used

| Hook | Type | Plugin Usage |
|------|------|--------------|
| `plugins_loaded` | Action | Dependency check, initialization |
| `init` | Action | Text domain, rewrite rules |
| `admin_menu` | Action | Settings page registration |
| `admin_init` | Action | Settings API registration |
| `wp_enqueue_scripts` | Action | Frontend assets |
| `admin_enqueue_scripts` | Action | Admin assets |
| `wp_ajax_{action}` | Action | AJAX handlers |
| `wp_ajax_nopriv_{action}` | Action | Public AJAX handlers |
| `save_post_{type}` | Action | Meta box saves |
| `query_vars` | Filter | Custom query variables |
| `plugin_action_links` | Filter | Settings link on plugins page |

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0 | 2026-02-05 | User Flow Generator | Initial documentation |

---

*Generated from code analysis of WDM Certificate Customizations v1.0.0*
