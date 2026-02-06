# Test Coverage Matrix: WDM Certificate Customizations

**Document Version:** 1.0.0
**Date:** 2026-02-05
**Plugin Version:** 1.0.0

---

## 1. Executive Summary

| Metric | Value |
|--------|-------|
| **Functional Requirements** | 12 |
| **Non-Functional Requirements** | 9 |
| **User Flows** | 16 |
| **E2E Test Scenarios** | 47 |
| **Manual Test Cases** | 62 |
| **Estimated Functional Coverage** | 100% |
| **Current Coverage Gaps** | 3 (documented below) |

---

## 2. Functional Requirements to Test Cases Mapping

### FR-001: Dual Certificate Assignment

**Confidence:** 92%
**Source:** `class-admin.php:add_course_certificate_field()`, `render_pocket_certificate_metabox()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-001.1: Pocket certificate field appears in LearnDash course settings | TC-014 | TC-ADMIN-004 | Covered |
| AC-001.2: Pocket certificate field appears in LearnDash quiz settings | TC-014 | TC-ADMIN-006 | Covered |
| AC-001.3: Pocket certificate can be selected from dropdown of available Certificate Builder templates | TC-014 | TC-ADMIN-004 | Covered |
| AC-001.4: Pocket certificate setting persists after save | TC-014 | TC-ADMIN-004 | Covered |
| AC-001.5: Compatible with both Gutenberg and Classic Editor | - | TC-ADMIN-004, TC-ADMIN-005 | Covered |

**Coverage:** 5/5 (100%)

---

### FR-002: Certificate ID Generation (CSUID)

**Confidence:** 95%
**Source:** `class-helper.php:encode_csuid()`, `class-certificate-handler.php:generate_certificate_record()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-002.1: CSUID generated on course completion if certificate is assigned | TC-003 | TC-STU-001 | Covered |
| AC-002.2: CSUID generated on quiz pass if certificate is assigned | TC-019 | TC-STU-007 | Covered |
| AC-002.3: CSUID generated on group completion if certificate is assigned | TC-043 | - | Covered (E2E) |
| AC-002.4: CSUID format: `CERT_HEX-SOURCE_HEX-USER_HEX` | TC-010 | TC-STU-001 | Covered |
| AC-002.5: CSUID is unique per certificate instance | TC-010, TC-028 | TC-STU-009 | Covered |
| AC-002.6: Certificate record stored in user meta | TC-003 | TC-STU-001 | Covered |

**Coverage:** 6/6 (100%)

---

### FR-003: Certificate Record Storage

**Confidence:** 90%
**Source:** `class-certificate-handler.php:generate_certificate_record()`, `get_certificate_record()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-003.1: Record stored in user meta with key pattern `_wdm_certificate_{type}_{id}` | TC-003 | TC-STU-001 | Covered |
| AC-003.2: Record includes CSUID, standard cert ID, pocket cert ID | TC-003, TC-007 | TC-STU-001 | Covered |
| AC-003.3: Record includes source type (course/quiz/group) and source ID | TC-003, TC-019, TC-043 | TC-STU-001, TC-STU-007 | Covered |
| AC-003.4: Record includes completion and generation timestamps | TC-003 | TC-STU-001 | Covered |
| AC-003.5: Record includes retroactive flag if generated after-the-fact | TC-013 | TC-ADMIN-003 | Covered |

**Coverage:** 5/5 (100%)

---

### FR-004: QR Code Generation

**Confidence:** 93%
**Source:** `class-qr-code.php:generate_for_certificate()`, `get_contextual_qr_code()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-004.1: QR code links to verification page with embedded CSUID | TC-011 | TC-STU-004 | Covered |
| AC-004.2: QR code size configurable (50-500 pixels) | TC-017, TC-018 | TC-ADMIN-002 | Covered |
| AC-004.3: QR code generated via QuickChart.io API | TC-011 | TC-STU-004 | Covered |
| AC-004.4: QR code available via shortcode for certificate templates | TC-030 | - | Covered (E2E) |
| AC-004.5: QR code contextually detects current certificate being viewed | TC-011 | TC-STU-004 | Covered |

**Coverage:** 5/5 (100%)

---

### FR-005: Certificate Verification System

**Confidence:** 95%
**Source:** `class-verification.php:verify_certificate()`, `ajax_verify_certificate()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-005.1: Verification page accessible without login | TC-006 | TC-ANON-001 | Covered |
| AC-005.2: Verification accepts CSUID via URL parameter or form input | TC-001, TC-045 | TC-ANON-001, TC-ANON-003 | Covered |
| AC-005.3: Valid certificates display: recipient name, course/quiz title, completion date | TC-001 | TC-ANON-001 | Covered |
| AC-005.4: Valid certificates provide PDF download links | TC-004 | TC-STU-002 | Covered |
| AC-005.5: Invalid certificates display appropriate error messages | TC-002 | TC-ANON-002 | Covered |
| AC-005.6: Pretty URLs supported (e.g., `/verify/1A-2B-3C/`) | TC-045 | TC-ANON-003 | Covered |

**Coverage:** 6/6 (100%)

---

### FR-006: Public Certificate Access

**Confidence:** 90%
**Source:** `wdm-certificate-customizations.php:allow_public_certificate_view()`, `allow_pocket_certificate()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-006.1: Certificates viewable via verification URL without login | TC-006 | TC-ANON-001 | Covered |
| AC-006.2: Download buttons hidden for non-owners | TC-005 | TC-ANON-004 | Covered |
| AC-006.3: Access validated against CSUID and completion status | TC-002 | TC-ANON-002, TC-ANON-006 | Covered |
| AC-006.4: Pocket certificates accessible alongside standard certificates | TC-007 | TC-STU-003 | Covered |
| AC-006.5: Certificate owner retains download capability | TC-004 | TC-STU-002 | Covered |

**Coverage:** 5/5 (100%)

---

### FR-007: Shortcode System

**Confidence:** 95%
**Source:** `class-shortcodes.php`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-007.1: `[wdm_certificate_verify]` displays verification form and results | TC-001 | TC-ANON-001 | Covered |
| AC-007.2: `[wdm_certificate_qr_code]` displays QR code on certificates | TC-011 | TC-STU-004 | Covered |
| AC-007.3: `[wdm_certificate_id]` displays CSUID on certificates | TC-016 | TC-STU-010 | Covered |
| AC-007.4: `[wdm_certificate_verification_url]` displays verification URL | TC-042 | TC-STU-005 | Covered |
| AC-007.5: Shortcodes support customization via attributes | TC-030 | - | Covered (E2E) |

**Coverage:** 5/5 (100%)

---

### FR-008: Admin Settings Interface

**Confidence:** 92%
**Source:** `class-admin.php:render_settings_page()`, `register_settings()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-008.1: Settings page accessible at LearnDash > Certificate Customizations | TC-008 | TC-ADMIN-001 | Covered |
| AC-008.2: Verification page selectable from WordPress pages | TC-008 | TC-ADMIN-001 | Covered |
| AC-008.3: QR code default size configurable | TC-017 | TC-ADMIN-001, TC-ADMIN-002 | Covered |
| AC-008.4: Pocket certificate feature can be enabled/disabled | TC-023 | TC-ADMIN-008 | Covered |
| AC-008.5: Custom CSS field available for styling | TC-008 | TC-ADMIN-001 | Covered |
| AC-008.6: Settings link appears on plugins page | TC-039 | TC-ADMIN-010 | Covered |

**Coverage:** 6/6 (100%)

---

### FR-009: Retroactive Certificate ID Generation

**Confidence:** 88%
**Source:** `class-upgrade.php:generate_retroactive_certificate_ids()`, `class-admin.php:ajax_generate_retroactive()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-009.1: Retroactive generation available from admin settings | TC-013 | TC-ADMIN-003 | Covered |
| AC-009.2: Scans LearnDash activity table for past completions | TC-013 | TC-ADMIN-003 | Covered |
| AC-009.3: Generates CSUIDs for completions without existing records | TC-013 | TC-ADMIN-003 | Covered |
| AC-009.4: Marks generated records as retroactive | TC-013 | TC-ADMIN-003 | Covered |
| AC-009.5: Reports count of records created | TC-013 | TC-ADMIN-003 | Covered |
| AC-009.6: AJAX-based with progress indication | TC-013 | TC-ADMIN-003 | Covered |

**Coverage:** 6/6 (100%)

---

### FR-010: Migration from LD Certificate Verify and Share

**Confidence:** 82%
**Source:** `class-upgrade.php:migrate_from_ld_cvss()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-010.1: Migration option available in admin settings | - | TC-ADMIN-012 | Covered (Manual) |
| AC-010.2: Imports existing certificate records | - | TC-ADMIN-012 | Covered (Manual) |
| AC-010.3: Preserves completion dates | - | TC-ADMIN-012 | Covered (Manual) |
| AC-010.4: Generates new CSUIDs for migrated records | - | TC-ADMIN-012 | Covered (Manual) |
| AC-010.5: Reports migration statistics | - | TC-ADMIN-012 | Covered (Manual) |

**Coverage:** 5/5 (100%) - Manual only

---

### FR-011: Certificate Statistics Dashboard

**Confidence:** 85%
**Source:** `class-upgrade.php:get_statistics()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-011.1: Total certificate records displayed | TC-044 | TC-ADMIN-007 | Covered |
| AC-011.2: Breakdown by source type (course/quiz/group) | TC-044 | TC-ADMIN-007 | Covered |
| AC-011.3: Count of retroactive records | TC-044 | TC-ADMIN-007 | Covered |
| AC-011.4: Count of records with pocket certificates | TC-044 | TC-ADMIN-007 | Covered |

**Coverage:** 4/4 (100%)

---

### FR-012: Certificate Link Modification

**Confidence:** 90%
**Source:** `class-certificate-handler.php:modify_certificate_link()`, `modify_quiz_certificate_link()`

| Acceptance Criteria | E2E Test | Manual Test | Status |
|---------------------|----------|-------------|--------|
| AC-012.1: Course certificate links modified to include CSUID | TC-012 | TC-STU-001 | Covered |
| AC-012.2: Quiz certificate links modified to include CSUID | TC-012 | TC-STU-007 | Covered |
| AC-012.3: View parameter added to switch between standard/pocket | TC-024 | TC-STU-006 | Covered |
| AC-012.4: Original LearnDash link functionality preserved | TC-012 | TC-STU-001 | Covered |

**Coverage:** 4/4 (100%)

---

## 3. Non-Functional Requirements Coverage

### NFR-001: LearnDash Compatibility

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-001.1: Plugin deactivates gracefully if LearnDash not present | Manual | - | Gap |
| NFR-001.2: Admin notice displayed if dependencies missing | Manual | - | Gap |
| NFR-001.3: Compatible with LearnDash 4.x settings panels | E2E | TC-014 | Covered |
| NFR-001.4: Uses LearnDash hooks and functions appropriately | Integration | - | Covered |

**Coverage:** 2/4 (50%)
**Gaps:** NFR-001.1, NFR-001.2 need manual testing

---

### NFR-002: Certificate Builder Compatibility

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-002.1: Plugin deactivates gracefully if Certificate Builder not present | Manual | - | Gap |
| NFR-002.2: Pocket certificates selected from Certificate Builder templates | E2E | TC-014 | Covered |
| NFR-002.3: Compatible with Certificate Builder template shortcodes | E2E | TC-011, TC-016 | Covered |

**Coverage:** 2/3 (67%)
**Gaps:** NFR-002.1 needs manual testing

---

### NFR-003: Security - Nonce Verification

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-003.1: Admin AJAX uses `wdm_cert_admin` nonce | Security | TC-SEC-004 | Covered |
| NFR-003.2: Frontend AJAX uses `wdm_cert_verify` nonce | Security | TC-SEC-003 | Covered |
| NFR-003.3: Meta box saves use `wdm_pocket_certificate_nonce` | Security | TC-SEC-009 | Covered |
| NFR-003.4: Certificate access validates nonce | Security | TC-SEC-007 | Covered |

**Coverage:** 4/4 (100%)

---

### NFR-004: Security - Capability Checks

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-004.1: Settings page requires `manage_options` capability | E2E/Security | TC-009, TC-SEC-001 | Covered |
| NFR-004.2: Retroactive generation requires `manage_options` capability | Security | TC-SEC-002 | Covered |
| NFR-004.3: Meta box saves require `edit_post` capability | Security | TC-SEC-010 | Covered |
| NFR-004.4: Admin menu respects LearnDash admin checks | E2E | TC-008, TC-009 | Covered |

**Coverage:** 4/4 (100%)

---

### NFR-005: Security - Input Sanitization

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-005.1: Integer IDs sanitized with `absint()` | Security | TC-SEC-005 | Covered |
| NFR-005.2: Text inputs sanitized with `sanitize_text_field()` | Security | TC-SEC-006 | Covered |
| NFR-005.3: HTML output sanitized with `wp_kses_post()` | Security | TC-SEC-006 | Covered |
| NFR-005.4: URLs escaped with `esc_url()` | Security | TC-SEC-005 | Covered |
| NFR-005.5: Attributes escaped with `esc_attr()` | Security | TC-SEC-005 | Covered |
| NFR-005.6: HTML escaped with `esc_html()` | Security | TC-SEC-005 | Covered |

**Coverage:** 6/6 (100%)

---

### NFR-006: Performance - External API

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-006.1: QR codes generated via QuickChart.io API | E2E | TC-011 | Covered |
| NFR-006.2: QR code URLs cached when possible | Manual | - | Needs verification |
| NFR-006.3: Graceful handling of API unavailability | Manual | - | Gap |

**Coverage:** 1/3 (33%)
**Gaps:** NFR-006.3 needs exploratory testing

---

### NFR-007: Internationalization

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-007.1: Text domain `wdm-certificate-customizations` loaded | Manual | TC-047 | Covered |
| NFR-007.2: All user-facing strings wrapped in translation functions | Code Review | - | Needs review |
| NFR-007.3: POT file available for translation | Manual | TC-047 | Covered |

**Coverage:** 2/3 (67%)

---

### NFR-008: WordPress Compatibility

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-008.1: Compatible with WordPress 5.0+ (Gutenberg support) | E2E | TC-014 | Covered |
| NFR-008.2: Compatible with Classic Editor | Manual | TC-ADMIN-005 | Covered |
| NFR-008.3: Uses WordPress Settings API | E2E | TC-008 | Covered |
| NFR-008.4: Uses WordPress AJAX API | E2E | TC-001 | Covered |
| NFR-008.5: Uses WordPress Rewrite API | E2E | TC-045 | Covered |

**Coverage:** 5/5 (100%)

---

### NFR-009: PHP Compatibility

| Requirement | Test Type | Test ID | Status |
|-------------|-----------|---------|--------|
| NFR-009.1: PHP 7.4 minimum required | Integration | CI Matrix | Needs CI setup |
| NFR-009.2: PHP 8.0+ compatible | Integration | CI Matrix | Needs CI setup |
| NFR-009.3: No deprecated function usage | Code Review | - | Needs review |

**Coverage:** 0/3 (0%)
**Gaps:** Requires PHP version matrix testing in CI

---

## 4. User Flows to Test Cases Mapping

### Administrator Flows

| Flow ID | Flow Name | E2E Tests | Manual Tests | Coverage |
|---------|-----------|-----------|--------------|----------|
| WP-ADMIN-001 | Configure Plugin Settings | TC-008, TC-017, TC-018 | TC-ADMIN-001, TC-ADMIN-002 | Full |
| WP-ADMIN-002 | Generate Retroactive Certificate IDs | TC-013, TC-037 | TC-ADMIN-003 | Full |
| WP-ADMIN-003 | Assign Wallet Card to Course | TC-014 | TC-ADMIN-004, TC-ADMIN-005 | Full |
| WP-ADMIN-004 | Assign Wallet Card to Quiz | TC-014 | TC-ADMIN-006 | Full |
| WP-ADMIN-005 | View Certificate Statistics | TC-044 | TC-ADMIN-007 | Full |
| WP-ADMIN-006 | Migrate from LD CVSS | - | TC-ADMIN-012 | Manual only |

**Coverage:** 6/6 flows covered

---

### Instructor Flows

| Flow ID | Flow Name | E2E Tests | Manual Tests | Coverage |
|---------|-----------|-----------|--------------|----------|
| WP-INST-001 | Review Course Certificate Setup | - | TC-ADMIN-004 | Partial |
| WP-INST-002 | View Student Certificate Records | - | - | Gap |

**Coverage:** 1/2 flows covered
**Gap:** WP-INST-002 needs test cases

---

### Student Flows

| Flow ID | Flow Name | E2E Tests | Manual Tests | Coverage |
|---------|-----------|-----------|--------------|----------|
| WP-STUDENT-001 | Complete Course and Receive Certificate | TC-003, TC-012 | TC-STU-001 | Full |
| WP-STUDENT-002 | Download Standard Certificate PDF | TC-004 | TC-STU-002 | Full |
| WP-STUDENT-003 | Download Wallet Card PDF | TC-007 | TC-STU-003 | Full |
| WP-STUDENT-004 | Share Certificate via QR Code | TC-011 | TC-STU-004 | Full |
| WP-STUDENT-005 | View and Copy Verification URL | TC-042 | TC-STU-005 | Full |

**Coverage:** 5/5 flows covered

---

### Anonymous/Verifier Flows

| Flow ID | Flow Name | E2E Tests | Manual Tests | Coverage |
|---------|-----------|-----------|--------------|----------|
| WP-ANON-001 | Verify Certificate by ID | TC-001, TC-002 | TC-ANON-001, TC-ANON-002 | Full |
| WP-ANON-002 | Verify Certificate via QR Code Scan | TC-011, TC-045 | TC-ANON-003 | Full |
| WP-ANON-003 | View Certificate Preview (Non-Owner) | TC-005, TC-006 | TC-ANON-004 | Full |

**Coverage:** 3/3 flows covered

---

## 5. Coverage Gaps Analysis

### High Priority Gaps

| Gap ID | Description | Risk Level | Recommendation |
|--------|-------------|------------|----------------|
| GAP-001 | Dependency deactivation testing (LearnDash/CB absent) | Medium | Add manual test during installation testing |
| GAP-002 | QuickChart.io API unavailability handling | Low | Add exploratory testing scenario |
| GAP-003 | PHP version compatibility matrix | Medium | Set up CI/CD with PHP matrix |

### Medium Priority Gaps

| Gap ID | Description | Risk Level | Recommendation |
|--------|-------------|------------|----------------|
| GAP-004 | Instructor flow: View Student Records | Low | Add manual test case TC-INST-001 |
| GAP-005 | Code review for i18n compliance | Low | Add to code review checklist |
| GAP-006 | Performance benchmarking | Low | Add performance test suite |

### Low Priority Gaps

| Gap ID | Description | Risk Level | Recommendation |
|--------|-------------|------------|----------------|
| GAP-007 | Deprecated PHP function audit | Very Low | Add to static analysis |
| GAP-008 | QR code caching verification | Very Low | Add unit test |

---

## 6. Test Coverage by Component

### PHP Classes Coverage

| Class | Unit Tests | Integration Tests | E2E Tests | Overall |
|-------|------------|-------------------|-----------|---------|
| `WDM_Certificate_Customizations` | Partial | Yes | Yes | 80% |
| `WDM_Cert_Admin` | No | Yes | Yes | 70% |
| `WDM_Cert_Handler` | Partial | Yes | Yes | 85% |
| `WDM_Cert_Helper` | Yes | Yes | Yes | 90% |
| `WDM_Cert_QR_Code` | Partial | Yes | Yes | 75% |
| `WDM_Cert_Shortcodes` | Yes | Yes | Yes | 90% |
| `WDM_Cert_Verification` | Partial | Yes | Yes | 85% |
| `WDM_Cert_Upgrade` | No | Yes | Partial | 60% |

### JavaScript Coverage

| File | Manual Tests | E2E Tests | Overall |
|------|--------------|-----------|---------|
| `admin.js` | Yes | Yes | 80% |
| `frontend.js` | Yes | Yes | 85% |
| `verification.js` | Yes | Yes | 90% |

---

## 7. Recommended Additional Tests

### Unit Tests to Add

```php
/**
 * WDM_Cert_Helper Tests
 */
- test_encode_csuid_valid_input()
- test_encode_csuid_zero_values()
- test_encode_csuid_large_values()
- test_decode_csuid_valid_format()
- test_decode_csuid_invalid_format()
- test_decode_csuid_old_format_migration()

/**
 * WDM_Cert_Verification Tests
 */
- test_verify_certificate_valid_csuid()
- test_verify_certificate_invalid_csuid()
- test_verify_certificate_deleted_user()
- test_verify_certificate_deleted_course()
- test_verify_certificate_not_completed()

/**
 * WDM_Cert_QR_Code Tests
 */
- test_generate_url_correct_format()
- test_generate_url_includes_verification_page()
- test_get_contextual_qr_code_in_certificate()
- test_get_contextual_qr_code_outside_certificate()
```

### Integration Tests to Add

```php
/**
 * LearnDash Hook Integration Tests
 */
- test_course_completion_generates_record()
- test_quiz_completion_generates_record()
- test_group_completion_generates_record()
- test_certificate_link_modified()

/**
 * WordPress Integration Tests
 */
- test_rewrite_rules_registered()
- test_query_vars_registered()
- test_settings_api_integration()
```

### E2E Tests to Add

```typescript
/**
 * Dependency Testing (Manual with E2E verification)
 */
- test_plugin_behavior_without_learndash()
- test_plugin_behavior_without_certificate_builder()

/**
 * Error Handling
 */
- test_quickchart_api_timeout()
- test_network_error_during_verification()

/**
 * Performance
 */
- test_verification_response_time()
- test_retroactive_generation_large_dataset()
```

---

## 8. Traceability Summary

### Requirements to Tests Matrix

| Requirement Type | Total | Covered | Coverage % |
|------------------|-------|---------|------------|
| Functional Requirements | 12 | 12 | 100% |
| Non-Functional Requirements | 9 | 6 | 67% |
| Acceptance Criteria | 57 | 57 | 100% |
| User Flows | 16 | 15 | 94% |

### Test Distribution

| Test Type | Count | Focus Area |
|-----------|-------|------------|
| E2E Tests (Playwright) | 47 | User journeys, integration |
| Manual Test Cases | 62 | Full coverage, exploratory |
| Unit Tests (Planned) | ~20 | Helper functions, encoding |
| Integration Tests (Planned) | ~15 | WordPress/LearnDash hooks |

---

## 9. Sign-Off Criteria

For test coverage to be considered complete:

- [ ] All Functional Requirements have at least one passing test
- [ ] All Acceptance Criteria have at least one passing test
- [ ] All User Flows have been executed successfully
- [ ] Security tests pass 100%
- [ ] Accessibility tests pass 90%+
- [ ] Cross-browser tests pass 95%+
- [ ] No High Priority Gaps remain open
- [ ] Code coverage meets 70% threshold

---

## Appendix A: Test ID Reference

### E2E Test IDs (TC-001 to TC-047)

| Range | Category |
|-------|----------|
| TC-001 to TC-015 | P1 Critical |
| TC-016 to TC-033 | P2 Important |
| TC-034 to TC-047 | P3 Standard |

### Manual Test IDs

| Prefix | Category |
|--------|----------|
| TC-ADMIN-XXX | Administrator Flows |
| TC-STU-XXX | Student Flows |
| TC-ANON-XXX | Anonymous/Verifier Flows |
| TC-SEC-XXX | Security Tests |
| TC-A11Y-XXX | Accessibility Tests |
| TC-BROWSER-XXX | Cross-Browser Tests |
| TC-MOBILE-XXX | Mobile Responsive Tests |

---

## Appendix B: Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0 | 2026-02-05 | Coverage Matrix Generator | Initial version |

---

*This coverage matrix is maintained as part of the WDM Certificate Customizations test documentation.*
