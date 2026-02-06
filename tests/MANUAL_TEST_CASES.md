# Manual Test Cases: WDM Certificate Customizations

**Document Version:** 1.0.0
**Date:** 2026-02-05
**Plugin Version:** 1.0.0

---

## Test Summary

| Metric | Count |
|--------|-------|
| **Total Test Cases** | 62 |
| Functional Tests | 28 |
| Security Tests | 12 |
| Accessibility Tests | 8 |
| Cross-Browser Tests | 8 |
| Mobile Responsive Tests | 6 |

---

## Test Case Categories

| Category | Prefix | Count |
|----------|--------|-------|
| Administrator Flows | TC-ADMIN | 12 |
| Student Flows | TC-STU | 10 |
| Anonymous/Verifier Flows | TC-ANON | 6 |
| Security | TC-SEC | 12 |
| Accessibility | TC-A11Y | 8 |
| Cross-Browser | TC-BROWSER | 8 |
| Mobile Responsive | TC-MOBILE | 6 |

---

## Administrator Flow Tests

### TC-ADMIN-001: Configure Plugin Settings

**User Flow Reference:** WP-ADMIN-001
**Priority:** High
**Estimated Time:** 8 minutes

#### Preconditions
- User logged in as Administrator
- LearnDash LMS active
- LearnDash Certificate Builder active
- Plugin activated

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to LearnDash > Certificate Customizations | Settings page loads successfully |
| 2 | Verify page structure | Tabs visible: General, Shortcodes |
| 3 | Select a page from "Verification Page" dropdown | Dropdown allows selection |
| 4 | Enter "200" in QR Code Size field | Value accepted |
| 5 | Check "Enable Pocket Certificates" checkbox | Checkbox becomes checked |
| 6 | Enter "CERT-" in Certificate ID Prefix field | Value accepted |
| 7 | Click "Save Settings" | Success notice appears |
| 8 | Refresh the page | All values persist correctly |

#### Pass Criteria
- [ ] Settings page loads without errors
- [ ] All form fields are functional
- [ ] Settings save successfully
- [ ] Values persist after page refresh
- [ ] No PHP errors in debug log

#### Test Data
- QR Code Size: 200
- Certificate ID Prefix: CERT-

---

### TC-ADMIN-002: Configure QR Code Size Validation

**User Flow Reference:** WP-ADMIN-001
**Priority:** Medium
**Estimated Time:** 5 minutes

#### Preconditions
- User logged in as Administrator
- Plugin settings page accessible

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to plugin settings | Settings page loads |
| 2 | Enter "30" in QR Code Size field | Value entered |
| 3 | Click "Save Settings" | Settings saved |
| 4 | Refresh page | Value shows 50 (minimum) |
| 5 | Enter "600" in QR Code Size field | Value entered |
| 6 | Click "Save Settings" | Settings saved |
| 7 | Refresh page | Value shows 500 (maximum) |
| 8 | Enter "150" in QR Code Size field | Value entered |
| 9 | Save and refresh | Value shows 150 |

#### Pass Criteria
- [ ] Values below 50 are corrected to 50
- [ ] Values above 500 are corrected to 500
- [ ] Valid values (50-500) saved correctly

---

### TC-ADMIN-003: Generate Retroactive Certificate IDs

**User Flow Reference:** WP-ADMIN-002
**Priority:** High
**Estimated Time:** 10 minutes

#### Preconditions
- User logged in as Administrator
- Historical course completions exist without certificate records
- Plugin settings page accessible

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to LearnDash > Certificate Customizations | Settings page loads |
| 2 | Scroll to "Retroactive Certificate IDs" section | Section visible |
| 3 | Note current statistics (if shown) | Statistics recorded |
| 4 | Click "Generate Retroactive IDs" button | Confirmation dialog appears |
| 5 | Confirm the action | Processing starts, button disabled |
| 6 | Wait for completion | Success message with count appears |
| 7 | Verify the count | Matches expected completions |
| 8 | Click button again | "No new records" or similar message |

#### Pass Criteria
- [ ] Confirmation dialog prevents accidental execution
- [ ] Progress indicator shown during processing
- [ ] Success message displays count of records created
- [ ] Duplicate prevention works (second run creates 0)
- [ ] Records marked as `is_retroactive: true`

#### Notes
- For large datasets, this may take several minutes
- Monitor browser console for JavaScript errors

---

### TC-ADMIN-004: Assign Pocket Certificate to Course (Gutenberg)

**User Flow Reference:** WP-ADMIN-003
**Priority:** High
**Estimated Time:** 6 minutes

#### Preconditions
- User logged in as Administrator or Course Creator
- At least one Certificate Builder template exists
- Course exists in LearnDash
- Pocket certificates enabled in plugin settings

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to LearnDash > Courses | Course list displayed |
| 2 | Click on a course to edit | Gutenberg editor loads |
| 3 | Open Settings sidebar panel | LearnDash settings visible |
| 4 | Expand "Display & Content" section | Certificate fields visible |
| 5 | Locate "Pocket Certificate" dropdown | Dropdown present with templates |
| 6 | Select a pocket certificate template | Selection captured |
| 7 | Click "Update" to save course | Success notice appears |
| 8 | Refresh the page | Selected value persists |

#### Pass Criteria
- [ ] Pocket Certificate dropdown appears in course settings
- [ ] Dropdown populated with Certificate Builder templates
- [ ] "-- None --" option available
- [ ] Selection saves correctly
- [ ] Value persists after page refresh

---

### TC-ADMIN-005: Assign Pocket Certificate to Course (Classic Editor)

**User Flow Reference:** WP-ADMIN-003
**Priority:** Medium
**Estimated Time:** 6 minutes

#### Preconditions
- Classic Editor plugin active (or Gutenberg disabled)
- User logged in as Administrator
- Certificate Builder templates exist

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to LearnDash > Courses | Course list displayed |
| 2 | Click on a course to edit | Classic editor loads |
| 3 | Scroll to "WDM Pocket Certificate" meta box | Meta box visible |
| 4 | Select pocket certificate from dropdown | Selection captured |
| 5 | Click "Update" to save | Page reloads with success notice |
| 6 | Verify selection persists | Dropdown shows selected value |

#### Pass Criteria
- [ ] Meta box displays in Classic Editor
- [ ] Dropdown functions correctly
- [ ] Selection saves on course update

---

### TC-ADMIN-006: Assign Pocket Certificate to Quiz

**User Flow Reference:** WP-ADMIN-004
**Priority:** Medium
**Estimated Time:** 6 minutes

#### Preconditions
- User logged in as Administrator
- Quiz exists with standard certificate assigned
- Pocket certificates enabled

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to LearnDash > Quizzes | Quiz list displayed |
| 2 | Click on a quiz to edit | Quiz editor loads |
| 3 | Open Settings panel | LearnDash settings visible |
| 4 | Locate "Pocket Certificate" dropdown | Field present |
| 5 | Select pocket certificate template | Selection captured |
| 6 | Click "Update" to save quiz | Success notice appears |
| 7 | Verify selection persists | Dropdown shows selected value |

#### Pass Criteria
- [ ] Pocket Certificate field appears for quizzes
- [ ] Selection saves correctly
- [ ] Value persists after save

---

### TC-ADMIN-007: View Certificate Statistics

**User Flow Reference:** WP-ADMIN-005
**Priority:** Low
**Estimated Time:** 3 minutes

#### Preconditions
- User logged in as Administrator
- Some certificate records exist in the system

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to LearnDash > Certificate Customizations | Settings page loads |
| 2 | Locate statistics section | Statistics table visible |
| 3 | Review total certificate records | Count displayed |
| 4 | Review breakdown by type | Course/Quiz/Group counts shown |
| 5 | Review retroactive count | Retroactive records identified |
| 6 | Review pocket certificate count | Count shown |

#### Pass Criteria
- [ ] Statistics section visible
- [ ] Total count accurate
- [ ] Breakdown by type accurate
- [ ] No display errors

---

### TC-ADMIN-008: Disable Pocket Certificates Feature

**User Flow Reference:** WP-ADMIN-001
**Priority:** Medium
**Estimated Time:** 5 minutes

#### Preconditions
- User logged in as Administrator
- Pocket certificates currently enabled

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to plugin settings | Settings page loads |
| 2 | Uncheck "Enable Pocket Certificates" | Checkbox unchecked |
| 3 | Save settings | Success notice |
| 4 | Edit any course | Course editor opens |
| 5 | Check for Pocket Certificate field | Field NOT visible |
| 6 | Re-enable pocket certificates | Setting saved |
| 7 | Edit course again | Field IS visible |

#### Pass Criteria
- [ ] Field hidden when feature disabled
- [ ] Field visible when feature enabled
- [ ] Existing assignments not deleted when disabled

---

### TC-ADMIN-009: Settings Page Shortcode Reference

**User Flow Reference:** WP-ADMIN-001
**Priority:** Low
**Estimated Time:** 3 minutes

#### Preconditions
- User logged in as Administrator

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to plugin settings | Settings page loads |
| 2 | Click "Shortcodes" tab | Shortcode reference displays |
| 3 | Verify shortcode list | All shortcodes documented |
| 4 | Check for usage examples | Examples provided |
| 5 | Verify attributes documented | Attributes listed |

#### Pass Criteria
- [ ] `[wdm_certificate_verify]` documented
- [ ] `[wdm_certificate_qr_code]` documented
- [ ] `[wdm_certificate_id]` documented
- [ ] `[wdm_certificate_verification_url]` documented
- [ ] Attributes and examples shown

---

### TC-ADMIN-010: Plugin Settings Link on Plugins Page

**User Flow Reference:** N/A
**Priority:** Low
**Estimated Time:** 2 minutes

#### Preconditions
- Plugin activated

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to Plugins > Installed Plugins | Plugins list displays |
| 2 | Find WDM Certificate Customizations entry | Entry visible |
| 3 | Check action links below plugin name | "Settings" link present |
| 4 | Click "Settings" link | Navigates to plugin settings |

#### Pass Criteria
- [ ] Settings link visible
- [ ] Link navigates to correct page

---

### TC-ADMIN-011: Admin Notice for Missing Verification Page

**User Flow Reference:** WP-ADMIN-001
**Priority:** Medium
**Estimated Time:** 4 minutes

#### Preconditions
- Plugin activated
- No verification page configured (or page deleted)

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as Administrator | Dashboard loads |
| 2 | Clear verification page setting | Setting = 0 or empty |
| 3 | Visit WordPress Dashboard | Admin notice displayed |
| 4 | Verify notice content | Mentions verification page |
| 5 | Click settings link in notice | Navigates to settings |
| 6 | Configure verification page | Notice should disappear |

#### Pass Criteria
- [ ] Warning notice displayed when not configured
- [ ] Notice includes link to settings
- [ ] Notice disappears after configuration

---

### TC-ADMIN-012: Migration from LD Certificate Verify and Share

**User Flow Reference:** WP-ADMIN-006
**Priority:** Low
**Estimated Time:** 10 minutes

#### Preconditions
- User logged in as Administrator
- Previous plugin data exists in database (or simulate)

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to plugin settings | Settings page loads |
| 2 | Locate "Migration" section | Section visible (if applicable) |
| 3 | Click "Migrate from LD CVSS" button | Confirmation appears |
| 4 | Confirm migration | Processing starts |
| 5 | Wait for completion | Success message with counts |
| 6 | Verify migrated records | Records exist with new CSUIDs |

#### Pass Criteria
- [ ] Migration option visible when old data exists
- [ ] Records imported correctly
- [ ] New CSUIDs generated
- [ ] Completion dates preserved

---

## Student Flow Tests

### TC-STU-001: Complete Course and Receive Certificate

**User Flow Reference:** WP-STUDENT-001
**Priority:** Critical
**Estimated Time:** 15 minutes

#### Preconditions
- User logged in as enrolled Student
- Course has certificate assigned
- Course has completable content (lessons/topics)

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to enrolled course | Course page displays |
| 2 | Complete all lessons/topics | Progress updates to 100% |
| 3 | Verify course marked complete | Completion message/badge shown |
| 4 | Navigate to course page or profile | Certificate link visible |
| 5 | Click certificate link | Verification page loads |
| 6 | Verify certificate displays | Certificate details shown |
| 7 | Check Certificate ID format | CSUID in hex format displayed |

#### Pass Criteria
- [ ] Course completion triggers certificate generation
- [ ] Certificate record created with valid CSUID
- [ ] Certificate link points to verification page
- [ ] Certificate auto-displays on verification page
- [ ] All certificate details accurate

#### Database Verification
Check user meta for:
- Key pattern: `_wdm_certificate_course_{course_id}`
- Contains: `certificate_id`, `standard_cert`, `source_type`, `source_id`, `user_id`, `completion_date`, `generated_date`

---

### TC-STU-002: Download Standard Certificate PDF

**User Flow Reference:** WP-STUDENT-002
**Priority:** High
**Estimated Time:** 5 minutes

#### Preconditions
- Student logged in
- Course completed with certificate earned
- Certificate record exists

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to verification page with certificate | Certificate displays |
| 2 | Locate "Download Standard PDF" button | Button visible |
| 3 | Click download button | PDF download initiates |
| 4 | Open downloaded PDF | PDF opens successfully |
| 5 | Verify PDF content | Contains student name, course, QR code |

#### Pass Criteria
- [ ] Download button visible to owner
- [ ] PDF downloads successfully
- [ ] PDF contains correct certificate content
- [ ] QR code present on certificate
- [ ] Certificate ID present on certificate

---

### TC-STU-003: Download Pocket Certificate PDF

**User Flow Reference:** WP-STUDENT-003
**Priority:** High
**Estimated Time:** 5 minutes

#### Preconditions
- Student logged in
- Course has pocket certificate assigned
- Course completed with certificate earned

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to verification page with certificate | Certificate displays |
| 2 | Locate certificate tabs | Standard and Wallet Card tabs visible |
| 3 | Click "Wallet Card" tab | Tab becomes active |
| 4 | Verify pocket certificate preview | Different template shows |
| 5 | Locate "Download Wallet Card PDF" button | Button visible |
| 6 | Click download button | PDF download initiates |
| 7 | Open downloaded PDF | Smaller/pocket format PDF |

#### Pass Criteria
- [ ] Wallet Card tab visible when pocket cert exists
- [ ] Tab switching works without page reload
- [ ] Pocket PDF downloads successfully
- [ ] PDF is pocket-size format

---

### TC-STU-004: Share Certificate via QR Code

**User Flow Reference:** WP-STUDENT-004
**Priority:** Medium
**Estimated Time:** 8 minutes

#### Preconditions
- Certificate earned with QR code
- Mobile device with QR scanner available

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | View certificate PDF | PDF displays with QR code |
| 2 | Display QR code on screen | QR code visible |
| 3 | Scan QR code with mobile device | URL recognized |
| 4 | Open scanned URL | Verification page loads |
| 5 | Verify certificate auto-displays | Certificate details shown |

#### Pass Criteria
- [ ] QR code scannable with standard apps
- [ ] QR code contains verification URL
- [ ] URL includes certificate ID
- [ ] Certificate verifies automatically

#### Notes
- Test with iPhone camera, Android camera, or dedicated QR app

---

### TC-STU-005: View Verification URL on Certificate

**User Flow Reference:** WP-STUDENT-005
**Priority:** Medium
**Estimated Time:** 3 minutes

#### Preconditions
- Certificate template includes verification URL shortcode
- Certificate earned

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | View certificate PDF | PDF displays |
| 2 | Locate verification URL text | URL visible on certificate |
| 3 | Copy/note the URL | URL accessible |
| 4 | Open URL in browser | Verification page loads |
| 5 | Certificate displays | Details shown correctly |

#### Pass Criteria
- [ ] Verification URL displayed on certificate
- [ ] URL is clickable or copyable
- [ ] URL leads to verification page
- [ ] Certificate verifies when URL visited

---

### TC-STU-006: Certificate Tab Switching with URL Parameter

**User Flow Reference:** WP-STUDENT-003
**Priority:** Medium
**Estimated Time:** 4 minutes

#### Preconditions
- Dual certificate exists (standard + pocket)
- Verification page accessible

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Visit verification URL without view parameter | Standard tab active |
| 2 | Add `?view=pocket` to URL | Pocket tab becomes active |
| 3 | Click Standard tab | URL updates, standard shows |
| 4 | Click Wallet Card tab | URL updates, pocket shows |
| 5 | Copy URL with view=pocket | URL includes parameter |
| 6 | Open copied URL in new tab | Pocket tab active directly |

#### Pass Criteria
- [ ] URL parameter pre-selects correct tab
- [ ] Tab clicking updates URL without reload
- [ ] Bookmarking with parameter works

---

### TC-STU-007: Quiz Completion Certificate Generation

**User Flow Reference:** WP-STUDENT-001 (Quiz variant)
**Priority:** High
**Estimated Time:** 10 minutes

#### Preconditions
- Quiz with certificate and passing score
- Student enrolled in course with quiz

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to quiz | Quiz page displays |
| 2 | Start quiz | Quiz begins |
| 3 | Answer questions to achieve passing score | Quiz submitted |
| 4 | Verify pass message | Passing notification shown |
| 5 | Check for certificate link | Link appears |
| 6 | Click certificate link | Verification page loads |
| 7 | Verify source_type | Shows quiz name |

#### Pass Criteria
- [ ] Passing quiz generates certificate record
- [ ] CSUID created correctly
- [ ] `source_type` is "quiz"
- [ ] Certificate link points to verification page

---

### TC-STU-008: Quiz Failure - No Certificate

**User Flow Reference:** N/A (Negative test)
**Priority:** Medium
**Estimated Time:** 8 minutes

#### Preconditions
- Quiz with certificate and passing score requirement
- Student enrolled

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to quiz | Quiz page displays |
| 2 | Answer questions to FAIL (below passing score) | Quiz submitted |
| 3 | Verify fail message | Failure notification shown |
| 4 | Check for certificate link | NO certificate link |
| 5 | Check database for record | NO certificate record created |

#### Pass Criteria
- [ ] Failing quiz does NOT create certificate record
- [ ] No certificate link shown
- [ ] No CSUID generated

---

### TC-STU-009: Re-completion Does Not Duplicate Certificate

**User Flow Reference:** N/A (Edge case)
**Priority:** Medium
**Estimated Time:** 12 minutes

#### Preconditions
- Student has completed course with certificate
- Certificate record exists
- Course allows reset/re-enrollment

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Note current certificate CSUID | CSUID recorded |
| 2 | Reset student progress on course | Progress cleared |
| 3 | Complete course again | Completion recorded |
| 4 | Check certificate record | Only ONE record exists |
| 5 | Verify CSUID | Same CSUID as before |

#### Pass Criteria
- [ ] No duplicate certificate records created
- [ ] Original CSUID preserved
- [ ] Certificate still verifiable

---

### TC-STU-010: Certificate ID Display on Certificate

**User Flow Reference:** WP-STUDENT-005
**Priority:** Medium
**Estimated Time:** 3 minutes

#### Preconditions
- Certificate template includes `[wdm_certificate_id]` shortcode

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | View certificate PDF | PDF displays |
| 2 | Locate Certificate ID | ID visible on certificate |
| 3 | Compare with database value | Values match |
| 4 | Verify format | Hexadecimal format (e.g., 1A-2B-3C) |

#### Pass Criteria
- [ ] Certificate ID displayed correctly
- [ ] Format matches CSUID standard
- [ ] Matches value in database

---

## Anonymous/Verifier Flow Tests

### TC-ANON-001: Verify Certificate by ID (Valid)

**User Flow Reference:** WP-ANON-001
**Priority:** Critical
**Estimated Time:** 5 minutes

#### Preconditions
- Verification page exists and is public
- Valid certificate exists in system
- User NOT logged in

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to verification page (not logged in) | Search form displayed |
| 2 | Enter valid Certificate ID | ID entered in field |
| 3 | Click "Verify Certificate" button | Loading indicator shows |
| 4 | Wait for verification | Success result displays |
| 5 | Verify recipient name displayed | Name shown correctly |
| 6 | Verify course/source title | Title shown correctly |
| 7 | Verify completion date | Date shown correctly |
| 8 | Verify certificate preview | iFrame loads certificate |

#### Pass Criteria
- [ ] No login required for verification
- [ ] Success indicator visible
- [ ] Recipient name displayed
- [ ] Course/Quiz title displayed
- [ ] Completion date displayed
- [ ] Certificate preview loads

#### Test Data
Use a known valid CSUID from test data setup

---

### TC-ANON-002: Verify Certificate by ID (Invalid)

**User Flow Reference:** WP-ANON-001
**Priority:** High
**Estimated Time:** 4 minutes

#### Preconditions
- Verification page accessible
- User NOT logged in

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to verification page | Search form displayed |
| 2 | Enter "INVALID123" | Text entered |
| 3 | Click "Verify Certificate" | Verification attempted |
| 4 | Wait for response | Error message displayed |
| 5 | Verify error message | "Certificate Not Found" or similar |
| 6 | Check for "Try Again" option | Retry mechanism available |

#### Pass Criteria
- [ ] Error message displayed for invalid ID
- [ ] Error is user-friendly
- [ ] Possible reasons explained
- [ ] No system error or crash

#### Test Data (Invalid IDs)
- `INVALID123`
- `123`
- `ABC-DEF`
- `ABC-DEF-GHI-JKL-MNO`
- Empty string
- Whitespace only

---

### TC-ANON-003: Verify Certificate via QR Code URL

**User Flow Reference:** WP-ANON-002
**Priority:** High
**Estimated Time:** 5 minutes

#### Preconditions
- Valid certificate with CSUID
- Pretty permalinks enabled
- Verification page configured

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Construct URL: `/verify/{CSUID}/` | URL constructed |
| 2 | Open URL in browser (not logged in) | Page loads |
| 3 | Verify certificate auto-displays | Certificate details shown |
| 4 | No manual input required | Form pre-filled/bypassed |

#### Pass Criteria
- [ ] Pretty URL format works
- [ ] Certificate ID extracted from URL
- [ ] Verification runs automatically
- [ ] Results display without user input

---

### TC-ANON-004: View Certificate Preview (Non-Owner)

**User Flow Reference:** WP-ANON-003
**Priority:** High
**Estimated Time:** 4 minutes

#### Preconditions
- Valid certificate exists
- User NOT logged in (or logged in as different user)

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Verify certificate as non-owner | Details displayed |
| 2 | Verify certificate preview visible | iFrame shows certificate |
| 3 | Check for download buttons | NO download buttons visible |
| 4 | Attempt to access PDF URL directly | Access restricted or preview only |

#### Pass Criteria
- [ ] Certificate details visible (public info)
- [ ] Certificate preview/iFrame loads
- [ ] Download buttons NOT visible to non-owner
- [ ] Cannot download PDF as non-owner

---

### TC-ANON-005: Certificate ID Case Insensitivity

**User Flow Reference:** WP-ANON-001
**Priority:** Medium
**Estimated Time:** 3 minutes

#### Preconditions
- Valid certificate with known CSUID

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter CSUID in lowercase | e.g., `abc123-def456-ghi789` |
| 2 | Click verify | Verification succeeds |
| 3 | Enter in mixed case | e.g., `AbC123-DeF456-GhI789` |
| 4 | Click verify | Verification succeeds |
| 5 | Verify result shows uppercase | Display normalizes to uppercase |

#### Pass Criteria
- [ ] Lowercase input verifies correctly
- [ ] Mixed case input verifies correctly
- [ ] Result displays normalized format

---

### TC-ANON-006: Verification with Deleted User/Course

**User Flow Reference:** WP-ANON-001
**Priority:** Medium
**Estimated Time:** 6 minutes

#### Preconditions
- Certificate record exists
- Ability to delete test user or course (carefully!)

#### Test Steps (Deleted User)

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Delete test user (or simulate) | User removed |
| 2 | Enter CSUID for that certificate | ID entered |
| 3 | Click verify | Error displayed |
| 4 | Verify error message | "Certificate recipient not found" |

#### Test Steps (Deleted Course)

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Delete test course (or simulate) | Course removed |
| 2 | Enter CSUID for that certificate | ID entered |
| 3 | Click verify | Error displayed |
| 4 | Verify error message | "Certificate source not found" |

#### Pass Criteria
- [ ] Graceful error for deleted user
- [ ] Graceful error for deleted course
- [ ] No system crash or exception
- [ ] Helpful error message displayed

---

## Security Tests

### TC-SEC-001: Non-Admin Cannot Access Settings Page

**Priority:** Critical
**Estimated Time:** 4 minutes

#### Preconditions
- User exists with Subscriber or Student role

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as Subscriber | Dashboard loads |
| 2 | Navigate directly to settings URL | Access denied |
| 3 | Verify error message | "You do not have permission" |

#### Pass Criteria
- [ ] Settings page inaccessible to non-admins
- [ ] WordPress capability check enforced
- [ ] Appropriate error message shown

---

### TC-SEC-002: Non-Admin Cannot Run Retroactive Generation

**Priority:** Critical
**Estimated Time:** 4 minutes

#### Preconditions
- User exists with Editor role (can edit posts but not manage_options)

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as Editor | Dashboard loads |
| 2 | Attempt to call AJAX endpoint directly | Construct request |
| 3 | Send AJAX request for retroactive generation | Request sent |
| 4 | Verify response | Permission denied error |

#### Pass Criteria
- [ ] AJAX endpoint checks capabilities
- [ ] Non-admin cannot trigger generation
- [ ] Error response returned

---

### TC-SEC-003: AJAX Nonce Verification - Verification

**Priority:** Critical
**Estimated Time:** 5 minutes

#### Preconditions
- Verification page accessible
- Developer tools available

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page | Page loads |
| 2 | Open browser developer tools | DevTools open |
| 3 | Intercept AJAX request | View request details |
| 4 | Modify nonce value to invalid | Nonce changed |
| 5 | Resend request | Send modified request |
| 6 | Verify response | Security error returned |

#### Pass Criteria
- [ ] Invalid nonce rejected
- [ ] Security error message returned
- [ ] No data exposed without valid nonce

---

### TC-SEC-004: AJAX Nonce Verification - Admin Actions

**Priority:** Critical
**Estimated Time:** 5 minutes

#### Preconditions
- Admin logged in
- Developer tools available

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open settings page | Page loads |
| 2 | Intercept retroactive generation request | View request |
| 3 | Modify nonce to invalid value | Nonce changed |
| 4 | Resend request | Request sent |
| 5 | Verify response | Security error returned |

#### Pass Criteria
- [ ] Admin AJAX verifies nonce
- [ ] Invalid nonce rejected
- [ ] Operation not performed

---

### TC-SEC-005: Input Sanitization - Certificate ID

**Priority:** High
**Estimated Time:** 4 minutes

#### Preconditions
- Verification page accessible

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter `<script>alert('XSS')</script>` | Script entered |
| 2 | Click verify | Request sent |
| 3 | View response | No script execution |
| 4 | Check displayed error | Sanitized text shown |

#### Pass Criteria
- [ ] Script tags not executed
- [ ] Input sanitized before processing
- [ ] No XSS vulnerability

---

### TC-SEC-006: Input Sanitization - Settings Fields

**Priority:** High
**Estimated Time:** 5 minutes

#### Preconditions
- Admin logged in on settings page

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter `<script>alert('XSS')</script>` in prefix field | Script entered |
| 2 | Save settings | Settings saved |
| 3 | Reload page | Page loads |
| 4 | Check saved value | Script tags stripped |

#### Pass Criteria
- [ ] Settings inputs sanitized
- [ ] No script execution on reload
- [ ] Malicious content removed

---

### TC-SEC-007: Certificate Access Control - Owner Only Download

**Priority:** Critical
**Estimated Time:** 5 minutes

#### Preconditions
- Certificate exists for User A
- User B logged in

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Verify User A's certificate as User B | Details shown |
| 2 | Inspect page for download links | No download buttons |
| 3 | Attempt to construct PDF URL | URL constructed |
| 4 | Access PDF URL directly | Access denied or preview only |

#### Pass Criteria
- [ ] Non-owner cannot see download buttons
- [ ] Non-owner cannot download PDF directly
- [ ] Access control properly enforced

---

### TC-SEC-008: SQL Injection Prevention

**Priority:** Critical
**Estimated Time:** 5 minutes

#### Preconditions
- Verification page accessible

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter `' OR '1'='1` in certificate ID | Payload entered |
| 2 | Click verify | Request sent |
| 3 | View response | Normal error (invalid ID) |
| 4 | No database error or data leakage | Standard error response |

#### Pass Criteria
- [ ] SQL injection payload handled safely
- [ ] No database errors exposed
- [ ] Standard "invalid ID" response

---

### TC-SEC-009: CSRF Protection on Settings Save

**Priority:** High
**Estimated Time:** 5 minutes

#### Preconditions
- Admin logged in

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open settings page | Page loads |
| 2 | Inspect form for nonce field | Nonce present |
| 3 | Submit form normally | Settings save |
| 4 | Modify nonce and resubmit | Nonce changed |
| 5 | Verify response | Save fails, security error |

#### Pass Criteria
- [ ] Settings form includes nonce
- [ ] Invalid nonce prevents save
- [ ] CSRF attack prevented

---

### TC-SEC-010: Capability Check on Meta Box Save

**Priority:** High
**Estimated Time:** 5 minutes

#### Preconditions
- User with limited edit capabilities
- Course exists

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as user without `edit_post` capability | Dashboard loads |
| 2 | Attempt to save pocket certificate meta | Via direct request |
| 3 | Verify response | Permission denied |

#### Pass Criteria
- [ ] `edit_post` capability required
- [ ] Unauthorized save rejected
- [ ] No data modified

---

### TC-SEC-011: Certificate Record Tampering Prevention

**Priority:** High
**Estimated Time:** 5 minutes

#### Preconditions
- Valid certificate exists

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Note valid CSUID components | Cert ID, Source ID, User ID |
| 2 | Modify one component | Change user ID in CSUID |
| 3 | Attempt verification | Invalid/not found error |

#### Pass Criteria
- [ ] Modified CSUID fails verification
- [ ] Cannot claim another user's certificate
- [ ] Integrity check works

---

### TC-SEC-012: Public Certificate View Security

**Priority:** High
**Estimated Time:** 5 minutes

#### Preconditions
- Certificate viewable by public

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Access certificate as anonymous user | Preview loads |
| 2 | Verify only public info shown | Name, course, date |
| 3 | Check for sensitive data | No email, no personal data |
| 4 | Verify download restricted | No download for anonymous |

#### Pass Criteria
- [ ] Only public information displayed
- [ ] No sensitive user data exposed
- [ ] Download restricted to owner

---

## Accessibility Tests (WCAG 2.1 AA)

### TC-A11Y-001: Verification Form Keyboard Navigation

**Priority:** High
**Estimated Time:** 5 minutes

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to verification page | Page loads |
| 2 | Press Tab to move through form | Focus moves to input |
| 3 | Continue Tab | Focus moves to button |
| 4 | Press Enter on button | Form submits |
| 5 | Tab through results | All interactive elements reachable |

#### Pass Criteria
- [ ] All form elements keyboard accessible
- [ ] Focus visible at all times
- [ ] Tab order logical
- [ ] Enter submits form

---

### TC-A11Y-002: Form Labels and ARIA

**Priority:** High
**Estimated Time:** 4 minutes

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Inspect certificate ID input | Label associated |
| 2 | Check for placeholder as only label | Proper label exists |
| 3 | Inspect submit button | Accessible name present |
| 4 | Check ARIA attributes on loading state | aria-busy, aria-live |

#### Pass Criteria
- [ ] Input has associated label (for/id)
- [ ] Not relying on placeholder alone
- [ ] Button has accessible name
- [ ] Dynamic content uses ARIA live regions

---

### TC-A11Y-003: Color Contrast

**Priority:** Medium
**Estimated Time:** 5 minutes

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Use contrast checker tool | Tool ready |
| 2 | Check form input text | 4.5:1 minimum |
| 3 | Check button text | 4.5:1 minimum |
| 4 | Check success message | 4.5:1 minimum |
| 5 | Check error message | 4.5:1 minimum |

#### Pass Criteria
- [ ] Text meets 4.5:1 contrast ratio (AA)
- [ ] Large text meets 3:1 ratio
- [ ] UI components meet 3:1 ratio

---

### TC-A11Y-004: Screen Reader Compatibility

**Priority:** High
**Estimated Time:** 8 minutes

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enable screen reader (NVDA/VoiceOver) | Reader active |
| 2 | Navigate to verification page | Page announced |
| 3 | Find and complete form | Form accessible |
| 4 | Submit and hear results | Results announced |
| 5 | Navigate tabs (if dual cert) | Tabs announced |

#### Pass Criteria
- [ ] Page structure announced correctly
- [ ] Form instructions clear
- [ ] Results announced when displayed
- [ ] Tab changes announced

---

### TC-A11Y-005: Focus Management

**Priority:** Medium
**Estimated Time:** 4 minutes

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Submit verification form | Form submitted |
| 2 | Check focus after results load | Focus moved to results |
| 3 | Click tab | Focus on tab content |
| 4 | Check skip links (if any) | Skip links work |

#### Pass Criteria
- [ ] Focus moves to results after verification
- [ ] Tab switching manages focus
- [ ] No focus trap

---

### TC-A11Y-006: Error Identification

**Priority:** High
**Estimated Time:** 4 minutes

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Submit empty form | Validation error |
| 2 | Check error announcement | Error announced to SR |
| 3 | Submit invalid ID | Error result |
| 4 | Check error is not color-only | Icon or text indicator |

#### Pass Criteria
- [ ] Errors identified by more than color
- [ ] Error text describes issue
- [ ] Error associated with field (if applicable)
- [ ] Screen reader announces error

---

### TC-A11Y-007: Text Resize

**Priority:** Medium
**Estimated Time:** 4 minutes

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Set browser zoom to 200% | Page zoomed |
| 2 | Check form usability | Form still usable |
| 3 | Check results readability | Results readable |
| 4 | Check for horizontal scroll | Minimal or none |

#### Pass Criteria
- [ ] Content readable at 200% zoom
- [ ] No content cut off
- [ ] No horizontal scrolling required
- [ ] Form still functional

---

### TC-A11Y-008: Motion and Animation

**Priority:** Low
**Estimated Time:** 3 minutes

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enable "prefers-reduced-motion" | Setting enabled |
| 2 | Submit verification | Check animations |
| 3 | Loading spinner | Should be static or reduced |
| 4 | Tab transitions | No motion-based transitions |

#### Pass Criteria
- [ ] Respects prefers-reduced-motion
- [ ] No motion required for functionality
- [ ] Essential feedback still visible

---

## Cross-Browser Tests

### TC-BROWSER-001: Chrome Desktop Verification

**Priority:** High
**Estimated Time:** 5 minutes
**Browser:** Chrome (latest) on Desktop

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page | Page renders correctly |
| 2 | Verify visual appearance | Matches design |
| 3 | Complete verification flow | All steps work |
| 4 | Check console for errors | No JavaScript errors |

#### Pass Criteria
- [ ] Page renders correctly
- [ ] All functionality works
- [ ] No console errors

---

### TC-BROWSER-002: Firefox Desktop Verification

**Priority:** High
**Estimated Time:** 5 minutes
**Browser:** Firefox (latest) on Desktop

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page | Page renders correctly |
| 2 | Verify visual appearance | Matches design |
| 3 | Complete verification flow | All steps work |
| 4 | Check console for errors | No JavaScript errors |

#### Pass Criteria
- [ ] Page renders correctly
- [ ] All functionality works
- [ ] No console errors

---

### TC-BROWSER-003: Safari Desktop Verification

**Priority:** Medium
**Estimated Time:** 5 minutes
**Browser:** Safari (latest) on macOS

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page | Page renders correctly |
| 2 | Verify visual appearance | Matches design |
| 3 | Complete verification flow | All steps work |
| 4 | Check console for errors | No JavaScript errors |

#### Pass Criteria
- [ ] Page renders correctly
- [ ] All functionality works
- [ ] No Safari-specific issues

---

### TC-BROWSER-004: Edge Desktop Verification

**Priority:** Medium
**Estimated Time:** 5 minutes
**Browser:** Edge (latest) on Desktop

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page | Page renders correctly |
| 2 | Verify visual appearance | Matches design |
| 3 | Complete verification flow | All steps work |
| 4 | Check console for errors | No JavaScript errors |

#### Pass Criteria
- [ ] Page renders correctly
- [ ] All functionality works
- [ ] No Edge-specific issues

---

### TC-BROWSER-005: Chrome Admin Settings

**Priority:** High
**Estimated Time:** 5 minutes
**Browser:** Chrome (latest) on Desktop

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open admin settings page | Page renders correctly |
| 2 | All form elements functional | Inputs, dropdowns work |
| 3 | Save settings | Save completes |
| 4 | Check for JavaScript errors | No console errors |

#### Pass Criteria
- [ ] Settings page renders correctly
- [ ] All controls functional
- [ ] Settings save successfully

---

### TC-BROWSER-006: Firefox Admin Settings

**Priority:** Medium
**Estimated Time:** 5 minutes
**Browser:** Firefox (latest) on Desktop

#### Test Steps
Same as TC-BROWSER-005

#### Pass Criteria
Same as TC-BROWSER-005

---

### TC-BROWSER-007: Safari Mobile (iOS)

**Priority:** High
**Estimated Time:** 5 minutes
**Browser:** Safari on iOS

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page | Page loads correctly |
| 2 | Verify mobile layout | Responsive design works |
| 3 | Complete verification | Touch interactions work |
| 4 | Check tab functionality | Tabs work on touch |

#### Pass Criteria
- [ ] Page responsive on iOS
- [ ] Touch interactions work
- [ ] No horizontal scroll
- [ ] Form usable on mobile keyboard

---

### TC-BROWSER-008: Chrome Mobile (Android)

**Priority:** High
**Estimated Time:** 5 minutes
**Browser:** Chrome on Android

#### Test Steps
Same as TC-BROWSER-007

#### Pass Criteria
Same as TC-BROWSER-007

---

## Mobile Responsive Tests

### TC-MOBILE-001: Verification Page Mobile Layout

**Priority:** High
**Estimated Time:** 5 minutes
**Viewport:** 375x667 (iPhone SE)

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page on mobile | Page loads |
| 2 | Check search form layout | Form fits viewport |
| 3 | Check input and button sizing | Touch-friendly (44px min) |
| 4 | Verify no horizontal scroll | Content fits |
| 5 | Submit verification | Results display properly |
| 6 | Check results layout | Readable on mobile |

#### Pass Criteria
- [ ] Search form fits viewport
- [ ] Touch targets adequate size
- [ ] No horizontal scrolling
- [ ] Results readable on mobile

---

### TC-MOBILE-002: Verification Page Tablet Layout

**Priority:** Medium
**Estimated Time:** 5 minutes
**Viewport:** 768x1024 (iPad)

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page on tablet | Page loads |
| 2 | Check layout | Appropriate for tablet |
| 3 | Complete verification | All functionality works |
| 4 | Check certificate preview | Preview visible |

#### Pass Criteria
- [ ] Layout appropriate for tablet
- [ ] All functionality works
- [ ] Good use of screen space

---

### TC-MOBILE-003: Certificate Tabs on Mobile

**Priority:** High
**Estimated Time:** 4 minutes
**Viewport:** 375x667

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verified certificate with dual certs | Tabs visible |
| 2 | Check tab layout | Tabs fit or scroll |
| 3 | Tap Standard tab | Tab switches |
| 4 | Tap Wallet Card tab | Tab switches |
| 5 | Check preview area | Fits viewport |

#### Pass Criteria
- [ ] Tabs accessible on mobile
- [ ] Tab switching works on touch
- [ ] Content updates correctly
- [ ] Preview visible and scrollable

---

### TC-MOBILE-004: Admin Settings on Tablet

**Priority:** Low
**Estimated Time:** 5 minutes
**Viewport:** 768x1024

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open settings page on tablet | Page loads |
| 2 | Check form layout | Forms usable |
| 3 | Fill and save settings | All inputs work |
| 4 | Verify save success | Settings saved |

#### Pass Criteria
- [ ] Settings page usable on tablet
- [ ] All form elements accessible
- [ ] Save functionality works

---

### TC-MOBILE-005: Touch Target Sizes

**Priority:** Medium
**Estimated Time:** 4 minutes
**Viewport:** 375x667

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Measure verify button | Min 44x44px |
| 2 | Measure input field | Adequate height |
| 3 | Measure tab targets | Min 44px height |
| 4 | Measure download buttons | Adequate size |

#### Pass Criteria
- [ ] All touch targets minimum 44x44px
- [ ] Adequate spacing between targets
- [ ] Easy to tap without misclicks

---

### TC-MOBILE-006: Landscape Orientation

**Priority:** Low
**Estimated Time:** 4 minutes
**Viewport:** 667x375 (Landscape)

#### Test Steps

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Open verification page in landscape | Page loads |
| 2 | Check layout | Content adapts |
| 3 | Complete verification | Works in landscape |
| 4 | Check results | Readable and usable |

#### Pass Criteria
- [ ] Layout works in landscape
- [ ] No content cut off
- [ ] All functionality works

---

## Exploratory Testing Checklist

Use this checklist for exploratory testing sessions:

### Verification Flow Exploration

- [ ] Try verifying same certificate multiple times rapidly
- [ ] Try verifying while page is still loading previous result
- [ ] Try navigating away mid-verification
- [ ] Try browser back/forward during verification
- [ ] Try with very slow network (DevTools throttle)
- [ ] Try with intermittent network (toggle offline)
- [ ] Try with extremely long certificate ID
- [ ] Try with special characters in ID
- [ ] Try with unicode characters in ID

### Admin Settings Exploration

- [ ] Try saving settings with tab/page closed
- [ ] Try rapid clicking on save button
- [ ] Try concurrent admin sessions
- [ ] Try very long values in text fields
- [ ] Try HTML in text fields
- [ ] Try saving with required fields empty

### Certificate Generation Exploration

- [ ] Try completing course very quickly
- [ ] Try completing course with multiple tabs
- [ ] Try completing while logged into multiple devices
- [ ] Try completing course twice simultaneously
- [ ] Try completing course just after another user

### Edge Cases to Explore

- [ ] What happens if QuickChart.io is down?
- [ ] What happens if verification page is trashed?
- [ ] What happens if certificate template is trashed?
- [ ] What happens with empty certificate templates?
- [ ] What happens with very large certificate templates?

---

## Test Execution Tracking

### Test Run Summary Template

```markdown
## Test Run: [Date] - [Version]

**Tester:** [Name]
**Environment:** [Local/Staging/Production]
**Browser:** [Browser and version]

### Results Summary

| Category | Total | Pass | Fail | Skip |
|----------|-------|------|------|------|
| Admin Flows | 12 | | | |
| Student Flows | 10 | | | |
| Anonymous Flows | 6 | | | |
| Security | 12 | | | |
| Accessibility | 8 | | | |
| Cross-Browser | 8 | | | |
| Mobile | 6 | | | |
| **TOTAL** | **62** | | | |

### Failed Tests

| Test ID | Description | Actual Result | Defect ID |
|---------|-------------|---------------|-----------|
| | | | |

### Notes

[Any observations, concerns, or recommendations]
```

---

## Appendix: Test Data Setup

### Required Test Users

| Username | Role | Purpose |
|----------|------|---------|
| admin_test | Administrator | Admin flow testing |
| student_test_1 | Subscriber + Enrolled | Student flow testing |
| student_test_2 | Subscriber + Enrolled | Non-owner testing |
| editor_test | Editor | Permission testing |

### Required Test Content

| Content | Requirements |
|---------|--------------|
| Course 1 | Standard certificate only |
| Course 2 | Standard + Pocket certificate |
| Course 3 | No certificate |
| Quiz 1 | Certificate with passing score |
| Certificate Template 1 | Standard size |
| Certificate Template 2 | Pocket size |

### Test Data Reset Script

Before each test run, ensure:
1. Test users exist with correct roles
2. Test courses exist with correct certificate assignments
3. Clear any existing certificate records for test users
4. Reset course progress for test students

---

*Document maintained as part of WDM Certificate Customizations QA documentation.*
