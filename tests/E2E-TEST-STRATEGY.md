# WDM Certificate Customizations - E2E Test Strategy

## Executive Summary

| Metric | Value |
|--------|-------|
| **Total Scenarios** | 47 |
| **P1 Critical** | 15 |
| **P2 Important** | 18 |
| **P3 Standard** | 14 |
| **Estimated Coverage** | 95% |
| **Implementation Effort** | 24-32 hours |
| **Test Framework** | Playwright |

---

## Site Analysis

### Plugin Overview
- **Name:** WDM Certificate Customizations
- **Version:** 1.0.0
- **Type:** LearnDash Extension Plugin

### Detected Features

| Category | Features |
|----------|----------|
| **Certificate System** | Dual certificate support (Standard + Pocket), Certificate ID (CSUID) generation, QR code generation |
| **Verification** | Public verification page, AJAX-based search, Tab switching for dual certificates |
| **Admin** | Settings page, Course/Quiz meta fields, Retroactive ID generation |
| **Integration** | LearnDash hooks, Certificate link modification, Completion tracking |

### Dependencies
- WordPress 6.0+
- PHP 7.4+
- LearnDash LMS 4.0+
- LearnDash Certificate Builder

### User Roles

| Role | Access Level | Key Actions |
|------|-------------|-------------|
| **Anonymous Visitor** | Public | View verification page, Search certificates, View public certificate details |
| **Student (Logged In)** | Authenticated | All anonymous actions + Download own certificates |
| **Course Instructor** | LearnDash Role | View student certificates in assigned courses |
| **Administrator** | Full | Configure settings, Manage pocket certificates, Run retroactive generation |

---

## Critical User Journeys

### J1: Certificate Verification (Public)
```
Third-party receives certificate
    → Enters Certificate ID in search form
    → System validates and decodes CSUID
    → Displays verified certificate with details
    → (If dual) Can switch between Standard/Pocket views
```
**Business Impact:** High - Primary feature for certificate authenticity
**Frequency:** Very High - Every certificate verification

### J2: Certificate Generation on Completion
```
Student completes course requirements
    → LearnDash triggers completion hook
    → Plugin generates unique CSUID
    → Stores certificate record in user meta
    → Modifies certificate link to verification page
```
**Business Impact:** Critical - Core functionality
**Frequency:** High - Every course completion

### J3: Student Downloads Certificate
```
Student completes course
    → Navigates to verification page (via link/QR)
    → Views certificate preview
    → (If dual) Switches between Standard/Pocket
    → Downloads PDF certificate
```
**Business Impact:** High - User satisfaction
**Frequency:** High - Every certificate download

### J4: Admin Configures Plugin
```
Admin accesses settings page
    → Selects/confirms verification page
    → Configures pocket certificate option
    → Sets QR code size
    → Saves settings
```
**Business Impact:** Medium - Initial setup
**Frequency:** Low - One-time setup

### J5: Admin Assigns Pocket Certificate
```
Admin edits course
    → Navigates to certificate settings
    → Selects pocket certificate from dropdown
    → Saves course
    → Future completions include pocket cert
```
**Business Impact:** Medium - Feature enablement
**Frequency:** Low - Per course setup

### J6: Admin Runs Retroactive Generation
```
Admin accesses settings page
    → Clicks retroactive generation button
    → Confirms action in dialog
    → System scans all historical completions
    → Generates CSUIDs for missing records
    → Displays completion summary
```
**Business Impact:** High - Historical data migration
**Frequency:** Very Low - One-time operation

### J7: QR Code Scanning
```
Third-party scans QR code on certificate
    → Mobile browser opens verification URL
    → Verification page loads with certificate data
    → Certificate authenticity confirmed
```
**Business Impact:** High - Mobile verification
**Frequency:** High - Common verification method

---

## Test Scenarios

### Priority 1: Critical (P1)

#### TC-001: Valid Certificate Verification
**Journey:** J1
**Priority:** P1 - Critical
**Description:** Verify a valid certificate ID returns correct certificate details

**Preconditions:**
- Verification page exists with shortcode
- User has completed a course with certificate
- Certificate record exists in database

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to verification page | Search form displayed |
| 2 | Enter valid Certificate ID | Input accepts text |
| 3 | Click "Verify Certificate" | Loading spinner shows |
| 4 | Wait for AJAX response | Success message displayed |
| 5 | Verify certificate details | Recipient name, course, date shown |
| 6 | Check certificate preview | iFrame loads certificate |

**Assertions:**
- [ ] Success indicator visible (green checkmark)
- [ ] "Certificate Verified" title displayed
- [ ] Recipient name matches database
- [ ] Course/Source title correct
- [ ] Completion date formatted correctly
- [ ] Certificate ID displayed correctly
- [ ] Status shows "Valid"

**Edge Cases:**
- Lowercase Certificate ID: Should convert to uppercase and verify
- Certificate ID with spaces: Should trim and verify

---

#### TC-002: Invalid Certificate ID Format
**Journey:** J1
**Priority:** P1 - Critical
**Description:** Invalid format Certificate IDs show appropriate error

**Preconditions:**
- Verification page exists

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to verification page | Search form displayed |
| 2 | Enter "INVALID123" | Input accepts text |
| 3 | Click "Verify Certificate" | AJAX request sent |
| 4 | Wait for response | Error message displayed |

**Assertions:**
- [ ] Error indicator visible (red X)
- [ ] "Certificate Not Found" title displayed
- [ ] Error message explains issue
- [ ] "Possible reasons" list shown
- [ ] "Try Another Certificate" button visible

**Test Data:**
```
Invalid IDs to test:
- "INVALID123" (wrong format)
- "123" (too short)
- "ABC-DEF" (only 2 segments)
- "ABC-DEF-GHI-JKL" (4 segments)
- "" (empty)
- "   " (whitespace only)
- "ABC123-DEF456-GHI789-EXTRA" (extra segment)
```

---

#### TC-003: Certificate Generation on Course Completion
**Journey:** J2
**Priority:** P1 - Critical
**Description:** Completing a course generates certificate record with CSUID

**Preconditions:**
- Course exists with certificate assigned
- Student enrolled in course
- Course has lessons/topics to complete

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as student | Dashboard loaded |
| 2 | Navigate to course | Course page displayed |
| 3 | Complete all lessons | Progress 100% |
| 4 | Complete course | Completion confirmed |
| 5 | Check certificate link | Link points to verification page |
| 6 | Verify user meta | Certificate record exists |

**Assertions:**
- [ ] Certificate record created in user meta
- [ ] CSUID follows correct format
- [ ] `standard_cert` matches course certificate
- [ ] `source_type` is "course"
- [ ] `source_id` matches course ID
- [ ] `completion_date` is accurate
- [ ] `is_retroactive` is false
- [ ] Certificate link includes CSUID

---

#### TC-004: Certificate Download by Owner
**Journey:** J3
**Priority:** P1 - Critical
**Description:** Certificate owner can download their PDF certificates

**Preconditions:**
- Student has completed course with certificate
- Certificate record exists
- Student is logged in

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as certificate owner | Dashboard loaded |
| 2 | Navigate to verification page with cert ID | Certificate displayed |
| 3 | Verify download buttons visible | Both download buttons shown |
| 4 | Click "Download Standard PDF" | PDF download initiates |
| 5 | Verify PDF content | Certificate contains correct data |

**Assertions:**
- [ ] Download buttons visible to owner
- [ ] "Download Standard PDF" button works
- [ ] "Download Pocket PDF" button works (if exists)
- [ ] PDF filename contains certificate info
- [ ] PDF content matches verification details

---

#### TC-005: Certificate Non-Owner Cannot Download
**Journey:** J3
**Priority:** P1 - Critical
**Description:** Non-owners cannot see download buttons

**Preconditions:**
- Certificate exists for User A
- User B is logged in (not owner)

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as User B (not owner) | Dashboard loaded |
| 2 | Navigate to User A's certificate verification | Certificate details displayed |
| 3 | Check for download buttons | No download buttons visible |

**Assertions:**
- [ ] Certificate details visible (public info)
- [ ] Download buttons NOT visible
- [ ] No direct PDF URL accessible

---

#### TC-006: Anonymous User Verification
**Journey:** J1
**Priority:** P1 - Critical
**Description:** Anonymous users can verify certificates but cannot download

**Preconditions:**
- Certificate exists with valid CSUID
- User is not logged in

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Visit site (not logged in) | Home page loads |
| 2 | Navigate to verification page | Search form displayed |
| 3 | Enter valid Certificate ID | Verified successfully |
| 4 | Check for download buttons | Not visible |

**Assertions:**
- [ ] Verification works without login
- [ ] Certificate details displayed
- [ ] Download buttons NOT visible
- [ ] No authentication prompt

---

#### TC-007: Dual Certificate Tab Switching
**Journey:** J3
**Priority:** P1 - Critical
**Description:** Tab switching between Standard and Pocket certificates works

**Preconditions:**
- Course has both standard and pocket certificates assigned
- Student has completed course
- Certificate record includes pocket_cert

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to verification page | Tabs visible |
| 2 | Verify "Standard Certificate" tab active | Standard preview shown |
| 3 | Click "Pocket Size Certificate" tab | Tab becomes active |
| 4 | Verify pocket preview shows | Different iframe content |
| 5 | Check URL parameter | ?view=pocket in URL |
| 6 | Click back to Standard | Standard tab active |

**Assertions:**
- [ ] Both tabs visible when pocket cert exists
- [ ] Tab switching updates preview
- [ ] URL updates with view parameter
- [ ] No page reload on tab switch
- [ ] Active tab styling correct

---

#### TC-008: Admin Settings Page Access
**Journey:** J4
**Priority:** P1 - Critical
**Description:** Admin can access and configure plugin settings

**Preconditions:**
- Plugin activated
- User is administrator

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as admin | Dashboard loaded |
| 2 | Navigate to LearnDash menu | Submenu visible |
| 3 | Click "Certificate Customizations" | Settings page loads |
| 4 | Verify all settings visible | All fields present |
| 5 | Change QR code size to 200 | Value updates |
| 6 | Click "Save Settings" | Settings saved |
| 7 | Refresh page | Value persists |

**Assertions:**
- [ ] Settings page accessible
- [ ] Verification page dropdown present
- [ ] Pocket certificate checkbox present
- [ ] QR code size field present
- [ ] Settings save successfully
- [ ] Values persist after refresh

---

#### TC-009: Non-Admin Cannot Access Settings
**Journey:** J4
**Priority:** P1 - Critical
**Description:** Non-admin users cannot access plugin settings

**Preconditions:**
- User exists with subscriber/student role

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as subscriber | Dashboard loaded |
| 2 | Try direct URL to settings page | Access denied |

**Assertions:**
- [ ] Settings page not accessible
- [ ] WordPress access denied message shown
- [ ] No capability escalation possible

---

#### TC-010: CSUID Encoding Correctness
**Journey:** J2
**Priority:** P1 - Critical
**Description:** CSUID encoding produces valid, decodable IDs

**Preconditions:**
- Known certificate ID, course ID, user ID

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Encode CSUID with known values | Returns formatted string |
| 2 | Validate CSUID format | Matches regex |
| 3 | Decode CSUID | Returns original values |

**Assertions:**
- [ ] Encoded CSUID matches format `[A-F0-9]+-[A-F0-9]+-[A-F0-9]+`
- [ ] Decoded cert_id matches input
- [ ] Decoded source_id matches input
- [ ] Decoded user_id matches input
- [ ] Round-trip preserves data integrity

**Test Data:**
```javascript
// Small IDs
{ cert_id: 123, source_id: 456, user_id: 1 }
// Large IDs (>8 chars when combined)
{ cert_id: 12345678, source_id: 87654321, user_id: 99999999 }
```

---

#### TC-011: QR Code Generation
**Journey:** J7
**Priority:** P1 - Critical
**Description:** QR code shortcode generates valid QR linking to verification

**Preconditions:**
- Certificate template with QR code shortcode
- Certificate Builder active
- User viewing certificate

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | View certificate with QR shortcode | QR code image displayed |
| 2 | Inspect QR code URL | Contains verification URL |
| 3 | Scan QR code | Links to verification page |
| 4 | Verify cert_id in URL | Matches certificate |

**Assertions:**
- [ ] QR code image renders
- [ ] Image URL from quickchart.io
- [ ] QR encodes correct verification URL
- [ ] Scanning opens verification page
- [ ] Correct certificate auto-loads

---

#### TC-012: Course Certificate Link Modification
**Journey:** J2, J3
**Priority:** P1 - Critical
**Description:** Certificate link in LearnDash points to verification page

**Preconditions:**
- Course completed with certificate
- Certificate record exists
- Verification page configured

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Complete course as student | Completion page shows |
| 2 | Find certificate link | Link visible |
| 3 | Inspect link URL | Points to verification page |
| 4 | Click certificate link | Verification page loads |
| 5 | Certificate auto-displays | Details shown |

**Assertions:**
- [ ] Link does NOT point to certificate post directly
- [ ] Link includes verification page URL
- [ ] Link includes CSUID parameter
- [ ] Certificate loads automatically on page

---

#### TC-013: Retroactive Generation Functionality
**Journey:** J6
**Priority:** P1 - Critical
**Description:** Retroactive generation creates CSUIDs for historical completions

**Preconditions:**
- Historical course completions exist
- No certificate records for those completions
- Admin logged in

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as admin | Dashboard loaded |
| 2 | Navigate to settings page | Page loads |
| 3 | Click retroactive generation button | Confirm dialog appears |
| 4 | Click confirm | Processing starts |
| 5 | Wait for completion | Success message shown |
| 6 | Verify user meta created | Records exist |

**Assertions:**
- [ ] Confirmation dialog appears
- [ ] Loading state shows during processing
- [ ] Success message displays count
- [ ] Certificate records created for all completions
- [ ] `is_retroactive` flag set to true
- [ ] CSUIDs are valid format
- [ ] Previously existing records not duplicated

---

#### TC-014: Pocket Certificate Assignment
**Journey:** J5
**Priority:** P1 - Critical
**Description:** Admin can assign pocket certificate to course

**Preconditions:**
- Course exists with standard certificate
- Pocket certificate template exists
- Admin logged in

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as admin | Dashboard loaded |
| 2 | Edit course | Course editor opens |
| 3 | Find certificate settings | Settings section visible |
| 4 | Locate pocket certificate dropdown | Field present |
| 5 | Select pocket certificate | Value selected |
| 6 | Save/Update course | Saved successfully |
| 7 | Reload course editor | Value persists |

**Assertions:**
- [ ] Pocket certificate dropdown visible
- [ ] Dropdown populated with certificates
- [ ] "-- None --" option available
- [ ] Selection saves correctly
- [ ] Meta value stored correctly
- [ ] Value persists after save

---

#### TC-015: Verification Page Auto-Creation
**Journey:** J4
**Priority:** P1 - Critical
**Description:** Plugin activation creates verification page automatically

**Preconditions:**
- Plugin not yet activated
- No verification page exists

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Activate plugin | Activation complete |
| 2 | Check pages | Verification page exists |
| 3 | View page content | Contains shortcode |
| 4 | Check plugin settings | Page ID saved |

**Assertions:**
- [ ] Page created with title "Certificate Verification"
- [ ] Page contains `[wdm_certificate_verify]` shortcode
- [ ] Page status is "published"
- [ ] Page ID saved in plugin options
- [ ] Page accessible on frontend

---

### Priority 2: Important (P2)

#### TC-016: Certificate ID Shortcode Output
**Journey:** J2
**Priority:** P2 - Important
**Description:** Certificate ID shortcode displays correct CSUID on certificate

**Preconditions:**
- Certificate template contains `[wdm_certificate_id]`
- User viewing their certificate

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | View certificate with ID shortcode | ID displayed |
| 2 | Compare with database | Values match |

**Assertions:**
- [ ] CSUID displayed in correct format
- [ ] Matches value in user meta
- [ ] Styling applied (if class specified)

---

#### TC-017: QR Code Size Configuration
**Journey:** J4
**Priority:** P2 - Important
**Description:** QR code size setting affects generated QR codes

**Preconditions:**
- Admin access to settings
- Certificate with QR shortcode

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Set QR size to 100px in settings | Setting saved |
| 2 | View certificate QR code | Size is 100px |
| 3 | Set QR size to 200px | Setting saved |
| 4 | View certificate QR code | Size is 200px |

**Assertions:**
- [ ] QR code respects global size setting
- [ ] Size attribute in shortcode overrides global
- [ ] Size validates within 50-500 range

---

#### TC-018: QR Code Size Validation
**Journey:** J4
**Priority:** P2 - Important
**Description:** QR code size validates within allowed range

**Preconditions:**
- Admin on settings page

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter 30 (below minimum) | Value saved as 50 |
| 2 | Enter 600 (above maximum) | Value saved as 500 |
| 3 | Enter 150 (valid) | Value saved as 150 |

**Assertions:**
- [ ] Values below 50 are corrected to 50
- [ ] Values above 500 are corrected to 500
- [ ] Valid values saved correctly

---

#### TC-019: Quiz Certificate Generation
**Journey:** J2
**Priority:** P2 - Important
**Description:** Quiz completion with pass generates certificate record

**Preconditions:**
- Quiz with certificate assigned
- Quiz has passing score requirement
- Student enrolled

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Complete quiz with passing score | Pass confirmed |
| 2 | Check certificate record | Record created |
| 3 | Verify source_type | Is "quiz" |

**Assertions:**
- [ ] Certificate record created
- [ ] CSUID valid format
- [ ] source_type is "quiz"
- [ ] Certificate link modified

---

#### TC-020: Quiz Failure No Certificate
**Journey:** J2
**Priority:** P2 - Important
**Description:** Quiz failure does not generate certificate record

**Preconditions:**
- Quiz with certificate and passing score
- Student enrolled

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Complete quiz below passing score | Fail confirmed |
| 2 | Check for certificate record | No record exists |

**Assertions:**
- [ ] No certificate record created
- [ ] No CSUID generated
- [ ] No certificate link shown

---

#### TC-021: Certificate with Deleted User
**Journey:** J1
**Priority:** P2 - Important
**Description:** Certificate for deleted user shows appropriate error

**Preconditions:**
- Certificate record exists
- User account deleted

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter CSUID for deleted user | Error displayed |
| 2 | Check error message | "Certificate recipient not found" |

**Assertions:**
- [ ] Error message displayed
- [ ] Appropriate error code returned
- [ ] No crash or exception

---

#### TC-022: Certificate with Deleted Course
**Journey:** J1
**Priority:** P2 - Important
**Description:** Certificate for deleted course shows appropriate error

**Preconditions:**
- Certificate record exists
- Course deleted

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter CSUID for deleted course | Error displayed |
| 2 | Check error message | "Certificate source not found" |

**Assertions:**
- [ ] Error message displayed
- [ ] Appropriate error code returned
- [ ] Graceful error handling

---

#### TC-023: Pocket Certificate Toggle
**Journey:** J4
**Priority:** P2 - Important
**Description:** Disabling pocket certificates hides field from course settings

**Preconditions:**
- Admin access
- Pocket certificates currently enabled

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Disable pocket certificates in settings | Setting saved |
| 2 | Edit a course | Pocket field NOT visible |
| 3 | Enable pocket certificates | Setting saved |
| 4 | Edit course again | Pocket field visible |

**Assertions:**
- [ ] Pocket field hidden when disabled
- [ ] Pocket field visible when enabled
- [ ] Existing pocket assignments preserved

---

#### TC-024: Certificate URL Parameter View
**Journey:** J3
**Priority:** P2 - Important
**Description:** URL view parameter pre-selects certificate tab

**Preconditions:**
- Certificate with pocket cert
- Both tabs available

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Visit verification URL with ?view=pocket | Pocket tab active |
| 2 | Visit without view param | Standard tab active |
| 3 | Visit with ?view=standard | Standard tab active |

**Assertions:**
- [ ] view=pocket selects pocket tab
- [ ] No param defaults to standard
- [ ] view=standard selects standard tab

---

#### TC-025: AJAX Loading State
**Journey:** J1
**Priority:** P2 - Important
**Description:** Loading state displays during AJAX verification

**Preconditions:**
- Verification page accessible

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter certificate ID | ID entered |
| 2 | Click verify button | Button shows loading |
| 3 | During request | Spinner visible |
| 4 | After response | Loading state clears |

**Assertions:**
- [ ] Button text changes to "Verifying..."
- [ ] Spinner animation displays
- [ ] Button disabled during request
- [ ] State clears after response

---

#### TC-026: Empty Certificate ID Submission
**Journey:** J1
**Priority:** P2 - Important
**Description:** Empty form submission shows validation error

**Preconditions:**
- Verification page with form

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Leave input empty | Field empty |
| 2 | Click verify button | Validation prevents submit |

**Assertions:**
- [ ] HTML5 required validation triggers
- [ ] Form does not submit
- [ ] No AJAX request sent

---

#### TC-027: URL History Update
**Journey:** J1
**Priority:** P2 - Important
**Description:** Successful verification updates browser URL

**Preconditions:**
- Valid certificate exists
- Browser supports history API

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter valid certificate ID | ID entered |
| 2 | Click verify | Verification succeeds |
| 3 | Check browser URL | URL updated with cert ID |
| 4 | Click browser back | Returns to search |

**Assertions:**
- [ ] URL updates to include certificate ID
- [ ] No page reload occurs
- [ ] Back button works correctly

---

#### TC-028: Multiple Completions Same Course
**Journey:** J2
**Priority:** P2 - Important
**Description:** Re-completing course doesn't duplicate certificate record

**Preconditions:**
- Student completed course
- Certificate record exists
- LearnDash allows re-enrollment

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Reset student progress | Progress cleared |
| 2 | Complete course again | Second completion |
| 3 | Check certificate records | Only one record |

**Assertions:**
- [ ] No duplicate certificate records
- [ ] Original CSUID preserved
- [ ] Completion date may update (depends on implementation)

---

#### TC-029: Certificate ID Case Insensitivity
**Journey:** J1
**Priority:** P2 - Important
**Description:** Certificate ID verification is case-insensitive

**Preconditions:**
- Valid certificate with CSUID

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Enter CSUID in lowercase | e.g., "abc123-def456-ghi789" |
| 2 | Click verify | Verification succeeds |
| 3 | Enter in mixed case | e.g., "AbC123-DeF456-GhI789" |
| 4 | Click verify | Verification succeeds |

**Assertions:**
- [ ] Lowercase input verifies correctly
- [ ] Mixed case input verifies correctly
- [ ] Result shows uppercase CSUID

---

#### TC-030: Shortcode Attributes
**Journey:** J2
**Priority:** P2 - Important
**Description:** Shortcode attributes are respected

**Preconditions:**
- Certificate template with shortcodes

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Use `[wdm_certificate_qr_code size="200"]` | QR is 200px |
| 2 | Use `[wdm_certificate_qr_code align="left"]` | QR aligned left |
| 3 | Use `[wdm_certificate_id prefix="ID: "]` | Output prefixed |

**Assertions:**
- [ ] Size attribute respected
- [ ] Align attribute respected
- [ ] Prefix/suffix attributes work
- [ ] Class attributes applied

---

#### TC-031: Verification Page Missing Shortcode
**Journey:** J4
**Priority:** P2 - Important
**Description:** Warning shown if verification page missing shortcode

**Preconditions:**
- Page set as verification page
- Shortcode removed from page content

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Create page without shortcode | Page created |
| 2 | Set as verification page | Setting saved |
| 3 | Visit verification page | No form displayed |

**Assertions:**
- [ ] Page loads but no verification UI
- [ ] Admin notice may warn about issue

---

#### TC-032: Network Error Handling
**Journey:** J1
**Priority:** P2 - Important
**Description:** Network errors during AJAX show appropriate message

**Preconditions:**
- Verification page accessible
- Network can be throttled/blocked

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Block network or AJAX endpoint | Network error condition |
| 2 | Enter certificate ID | ID entered |
| 3 | Click verify | Request fails |
| 4 | Check error message | Generic error shown |

**Assertions:**
- [ ] Error message displayed
- [ ] Button re-enabled
- [ ] No JavaScript console errors crash page

---

#### TC-033: Admin Notice for Missing Verification Page
**Journey:** J4
**Priority:** P2 - Important
**Description:** Admin notice displays when verification page not configured

**Preconditions:**
- Plugin active
- No verification page set (option = 0)

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Login as admin | Dashboard |
| 2 | Visit any admin page | Notice displayed |
| 3 | Notice includes settings link | Link works |
| 4 | Configure verification page | Notice disappears |

**Assertions:**
- [ ] Warning notice visible
- [ ] Link to settings page works
- [ ] Notice hidden on settings page itself
- [ ] Notice disappears after configuration

---

### Priority 3: Standard (P3)

#### TC-034: Plugin Activation Hook
**Journey:** J4
**Priority:** P3 - Standard
**Description:** Plugin activation runs all setup tasks

**Preconditions:**
- Plugin deactivated
- No existing options or pages

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Activate plugin | Activation hook fires |
| 2 | Check options | Default options set |
| 3 | Check pages | Verification page created |
| 4 | Check rewrite rules | Rules flushed |

**Assertions:**
- [ ] `wdm_certificate_options` created
- [ ] Default values set correctly
- [ ] Verification page exists
- [ ] Pretty URLs work

---

#### TC-035: Plugin Deactivation Hook
**Journey:** J4
**Priority:** P3 - Standard
**Description:** Plugin deactivation cleans up rewrite rules

**Preconditions:**
- Plugin active

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Deactivate plugin | Deactivation hook fires |
| 2 | Check rewrite rules | Rules flushed |

**Assertions:**
- [ ] No lingering rewrite rules
- [ ] Options preserved (not deleted)
- [ ] User meta preserved

---

#### TC-036: Shortcode in Non-Certificate Context
**Journey:** J2
**Priority:** P3 - Standard
**Description:** Shortcodes gracefully handle missing context

**Preconditions:**
- Shortcode used outside certificate template
- No course_id in URL

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Add `[wdm_certificate_qr_code]` to regular page | Page saved |
| 2 | View page as logged in user | No crash |
| 3 | Check output | Empty or comment |

**Assertions:**
- [ ] No PHP errors
- [ ] Graceful empty output
- [ ] HTML comment may indicate missing context

---

#### TC-037: Retroactive Generation with No Completions
**Journey:** J6
**Priority:** P3 - Standard
**Description:** Retroactive generation handles empty completion list

**Preconditions:**
- No historical completions exist
- Admin on settings page

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Click retroactive generation | Processing starts |
| 2 | Wait for completion | Success message |
| 3 | Check count | "Generated Certificate IDs for 0 completions" |

**Assertions:**
- [ ] No errors thrown
- [ ] Count shows 0
- [ ] Success message displayed

---

#### TC-038: Settings Page Shortcode Reference
**Journey:** J4
**Priority:** P3 - Standard
**Description:** Shortcode reference table displays correctly

**Preconditions:**
- Admin on settings page

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Scroll to shortcode reference | Table visible |
| 2 | Verify all shortcodes listed | 3+ shortcodes |
| 3 | Check descriptions | Accurate info |

**Assertions:**
- [ ] `[wdm_certificate_verify]` documented
- [ ] `[wdm_certificate_qr_code]` documented
- [ ] `[wdm_certificate_id]` documented
- [ ] Usage examples shown

---

#### TC-039: Plugin Action Links
**Journey:** J4
**Priority:** P3 - Standard
**Description:** Plugin list shows Settings link

**Preconditions:**
- Plugin active
- Admin on plugins page

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Navigate to Plugins page | List displayed |
| 2 | Find plugin entry | Entry visible |
| 3 | Check action links | "Settings" link present |
| 4 | Click Settings | Goes to settings page |

**Assertions:**
- [ ] "Settings" link visible
- [ ] Link URL correct
- [ ] Navigates to plugin settings

---

#### TC-040: Mobile Responsive Verification Page
**Journey:** J1, J7
**Priority:** P3 - Standard
**Description:** Verification page is mobile responsive

**Preconditions:**
- Verification page exists
- Mobile viewport

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | View verification page on mobile | Page loads |
| 2 | Enter certificate ID | Input usable |
| 3 | Verify certificate | Results display |
| 4 | Check layout | No horizontal scroll |

**Assertions:**
- [ ] Search form fits viewport
- [ ] Tabs stack or fit on mobile
- [ ] Details grid responsive
- [ ] Buttons accessible

---

#### TC-041: Certificate Preview iFrame Loading
**Journey:** J3
**Priority:** P3 - Standard
**Description:** Certificate preview iFrame loads correctly

**Preconditions:**
- Valid certificate verified
- LearnDash certificate accessible

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Verify valid certificate | Results displayed |
| 2 | Check iFrame | iFrame present |
| 3 | Verify iFrame src | Points to certificate URL |
| 4 | Wait for load | Content visible |

**Assertions:**
- [ ] iFrame has correct src URL
- [ ] Certificate renders in iFrame
- [ ] No cross-origin errors (if applicable)

---

#### TC-042: Verification URL Shortcode
**Journey:** J2
**Priority:** P3 - Standard
**Description:** Verification URL shortcode outputs correct URL

**Preconditions:**
- Certificate template
- Shortcode `[wdm_certificate_verification_url]`

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | View certificate with URL shortcode | URL displayed |
| 2 | Verify URL format | Includes verification page |
| 3 | With link_text attribute | Link rendered |

**Assertions:**
- [ ] URL includes verification page path
- [ ] URL includes CSUID
- [ ] link_text creates anchor element

---

#### TC-043: Group Certificate Support
**Journey:** J2
**Priority:** P3 - Standard
**Description:** Group completion generates certificate record

**Preconditions:**
- LearnDash group with certificate
- User in group
- All group courses completed

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Complete all group courses | Group completes |
| 2 | Check certificate record | Record created |
| 3 | Verify source_type | Is "group" |

**Assertions:**
- [ ] Certificate record created
- [ ] source_type is "group"
- [ ] Group certificate link modified

---

#### TC-044: Statistics from Upgrade Class
**Journey:** J4
**Priority:** P3 - Standard
**Description:** Statistics method returns accurate counts

**Preconditions:**
- Various certificate records exist

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Call get_statistics() | Stats returned |
| 2 | Verify counts | Match database |

**Assertions:**
- [ ] total_records accurate
- [ ] course_records accurate
- [ ] quiz_records accurate
- [ ] group_records accurate
- [ ] retroactive count accurate
- [ ] with_pocket_cert count accurate

---

#### TC-045: Pretty Permalink Verification URL
**Journey:** J1
**Priority:** P3 - Standard
**Description:** Pretty URLs work for verification

**Preconditions:**
- WordPress pretty permalinks enabled
- Verification page slug known

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Visit /certificate-verify/CERT-ID/ | Page loads |
| 2 | Certificate auto-verifies | Details shown |

**Assertions:**
- [ ] Pretty URL format works
- [ ] Certificate ID extracted from URL
- [ ] Verification runs automatically

---

#### TC-046: Non-Pretty Permalink Fallback
**Journey:** J1
**Priority:** P3 - Standard
**Description:** Non-pretty URLs work for verification

**Preconditions:**
- WordPress plain permalinks (?p=X)
- Verification page ID known

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Visit /?page_id=X&cert_id=CERT-ID | Page loads |
| 2 | Certificate auto-verifies | Details shown |

**Assertions:**
- [ ] Query parameter format works
- [ ] Certificate ID from query var
- [ ] Verification successful

---

#### TC-047: Translation Ready (i18n)
**Journey:** J4
**Priority:** P3 - Standard
**Description:** All strings are translatable

**Preconditions:**
- Plugin active
- Translation file available (or POT file)

**Steps:**
| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Review POT file | Strings present |
| 2 | Check key UI strings | All wrapped |

**Assertions:**
- [ ] Settings page strings translatable
- [ ] Verification page strings translatable
- [ ] Error messages translatable
- [ ] JavaScript strings localized

---

## Infrastructure Recommendations

### Directory Structure

```
tests/e2e/
├── playwright.config.ts          # Playwright configuration
├── .env.example                  # Environment variables template
├── fixtures/
│   ├── test-data.ts              # Test data constants
│   ├── users.ts                  # Test user credentials
│   └── certificates.ts           # Pre-generated certificate data
├── pages/
│   ├── base.page.ts              # Base page object
│   ├── login.page.ts             # WordPress login page
│   ├── admin-settings.page.ts    # Plugin settings page
│   ├── course-editor.page.ts     # Course edit page
│   ├── verification.page.ts      # Certificate verification page
│   └── certificate.page.ts       # Certificate template view
├── helpers/
│   ├── wp-cli.ts                 # WP-CLI helper functions
│   ├── database.ts               # Direct database helpers
│   └── api.ts                    # REST API helpers
├── tests/
│   ├── critical/
│   │   ├── verification.spec.ts  # TC-001 to TC-007
│   │   ├── generation.spec.ts    # TC-003, TC-010, TC-012
│   │   ├── admin.spec.ts         # TC-008, TC-009, TC-013, TC-014, TC-015
│   │   └── download.spec.ts      # TC-004, TC-005, TC-006
│   ├── important/
│   │   ├── shortcodes.spec.ts    # TC-016, TC-030
│   │   ├── settings.spec.ts      # TC-017, TC-018, TC-023
│   │   ├── quiz.spec.ts          # TC-019, TC-020
│   │   ├── edge-cases.spec.ts    # TC-021, TC-022, TC-028, TC-029
│   │   └── ui.spec.ts            # TC-024, TC-025, TC-026, TC-027
│   └── standard/
│       ├── activation.spec.ts    # TC-034, TC-035
│       ├── misc.spec.ts          # TC-036 to TC-047
│       └── mobile.spec.ts        # TC-040
└── global-setup.ts               # Test environment setup
```

### Authentication Setup

```typescript
// fixtures/users.ts
export const users = {
  admin: {
    username: process.env.WP_ADMIN_USER || 'admin',
    password: process.env.WP_ADMIN_PASS || 'password',
    role: 'administrator'
  },
  student: {
    username: 'test_student',
    password: 'test_password',
    role: 'subscriber'
  },
  student2: {
    username: 'test_student_2',
    password: 'test_password',
    role: 'subscriber'
  }
};

// Auth state storage
export const authStatePaths = {
  admin: 'playwright/.auth/admin.json',
  student: 'playwright/.auth/student.json',
  student2: 'playwright/.auth/student2.json'
};
```

### Data Strategy

```typescript
// fixtures/test-data.ts
export const testData = {
  courses: {
    withCertificate: {
      id: 123, // Set via setup or env
      title: 'Test Course with Certificate',
      certificateId: 456
    },
    withDualCertificate: {
      id: 124,
      title: 'Test Course with Dual Certificates',
      standardCertId: 456,
      pocketCertId: 789
    }
  },
  certificates: {
    valid: {
      csuid: 'ABC123-DEF456-GHI789',
      userId: 1,
      courseId: 123,
      certificateId: 456
    },
    invalid: [
      'INVALID123',
      '123',
      'ABC-DEF',
      '',
      'ABC123-DEF456-GHI789-EXTRA'
    ]
  }
};
```

### Environment Variables

```bash
# .env.example
WP_BASE_URL=http://localhost:10003
WP_ADMIN_USER=admin
WP_ADMIN_PASS=admin_password
WP_TEST_STUDENT_USER=test_student
WP_TEST_STUDENT_PASS=test_student_password
WP_TEST_STUDENT_ID=2
WP_TEST_COURSE_ID=123
WP_TEST_CERTIFICATE_ID=456
WP_VERIFICATION_PAGE_ID=789
```

### Page Object Example

```typescript
// pages/verification.page.ts
import { Page, Locator } from '@playwright/test';

export class VerificationPage {
  readonly page: Page;
  readonly searchInput: Locator;
  readonly verifyButton: Locator;
  readonly resultContainer: Locator;
  readonly successHeader: Locator;
  readonly errorContainer: Locator;
  readonly standardTab: Locator;
  readonly pocketTab: Locator;
  readonly downloadStandardBtn: Locator;
  readonly downloadPocketBtn: Locator;

  constructor(page: Page) {
    this.page = page;
    this.searchInput = page.locator('#wdm-cert-id-input');
    this.verifyButton = page.locator('.wdm-cert-verify-btn');
    this.resultContainer = page.locator('#wdm-cert-result');
    this.successHeader = page.locator('.wdm-cert-verified-header');
    this.errorContainer = page.locator('.wdm-cert-error-container');
    this.standardTab = page.locator('.wdm-cert-tab[data-view="standard"]');
    this.pocketTab = page.locator('.wdm-cert-tab[data-view="pocket"]');
    this.downloadStandardBtn = page.locator('.wdm-cert-download-standard');
    this.downloadPocketBtn = page.locator('.wdm-cert-download-pocket');
  }

  async goto(certId?: string) {
    let url = process.env.WP_VERIFICATION_URL || '/certificate-verify/';
    if (certId) {
      url += `${certId}/`;
    }
    await this.page.goto(url);
  }

  async verify(certId: string) {
    await this.searchInput.fill(certId);
    await this.verifyButton.click();
    await this.page.waitForResponse(resp =>
      resp.url().includes('admin-ajax.php') &&
      resp.request().postData()?.includes('wdm_cert_verify')
    );
  }

  async switchToTab(tab: 'standard' | 'pocket') {
    const tabLocator = tab === 'standard' ? this.standardTab : this.pocketTab;
    await tabLocator.click();
  }

  async isVerified(): Promise<boolean> {
    return this.successHeader.isVisible();
  }

  async isError(): Promise<boolean> {
    return this.errorContainer.isVisible();
  }

  async getRecipientName(): Promise<string> {
    return this.page.locator('.wdm-cert-detail-value').nth(1).textContent() || '';
  }
}
```

---

## Implementation Roadmap

| Phase | Focus | Scenarios | Estimated Hours | Dependencies |
|-------|-------|-----------|-----------------|--------------|
| **1** | Critical Path | TC-001 to TC-015 (15 scenarios) | 12-16 hours | Test env setup |
| **2** | Important Features | TC-016 to TC-033 (18 scenarios) | 8-10 hours | Phase 1 complete |
| **3** | Standard Coverage | TC-034 to TC-047 (14 scenarios) | 4-6 hours | Phase 2 complete |

### Phase 1 Details (Critical)
- Set up Playwright project structure
- Create authentication flows
- Implement core page objects (Verification, Admin Settings)
- Write critical verification tests
- Write certificate generation tests
- Write admin access tests

### Phase 2 Details (Important)
- Extend page objects (Course Editor, Certificate Template)
- Add shortcode testing
- Add edge case handling tests
- Add UI interaction tests

### Phase 3 Details (Standard)
- Add activation/deactivation tests
- Add mobile responsive tests
- Add remaining edge cases
- Add i18n verification

---

## Test Execution Commands

```bash
# Run all tests
npx playwright test

# Run critical tests only
npx playwright test tests/critical/

# Run specific test file
npx playwright test tests/critical/verification.spec.ts

# Run with UI mode
npx playwright test --ui

# Run in headed mode
npx playwright test --headed

# Generate report
npx playwright show-report
```

---

## Success Criteria

| Metric | Target |
|--------|--------|
| P1 Pass Rate | 100% |
| P2 Pass Rate | 95%+ |
| P3 Pass Rate | 90%+ |
| Test Execution Time | < 10 minutes |
| Flaky Test Rate | < 2% |

---

## Maintenance Guidelines

1. **Test Data Isolation:** Each test should clean up after itself
2. **Page Object Updates:** Update page objects when UI changes
3. **Selector Stability:** Use data attributes over CSS classes where possible
4. **Version Compatibility:** Test against supported WordPress/LearnDash versions
5. **CI Integration:** Run tests on PR and before releases
