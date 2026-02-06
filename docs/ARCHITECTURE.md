# WDM Certificate Customizations - Architecture Documentation

## Overview

WDM Certificate Customizations is a WordPress plugin that extends LearnDash LMS with dual certificate support (Standard + Pocket/Wallet Size) and a built-in QR code verification system. This document provides a comprehensive overview of the system architecture.

---

## 1. High-Level Architecture

```mermaid
graph TB
    subgraph "WordPress Core"
        WP[WordPress]
        WPDB[(wp_options<br/>wp_usermeta<br/>wp_postmeta)]
    end

    subgraph "LearnDash LMS"
        LD[LearnDash Core]
        LDC[Certificate Builder]
        LDA[(learndash_user_activity)]
    end

    subgraph "WDM Certificate Customizations"
        MAIN[WDM_Certificate_Customizations<br/>Main Plugin Class]

        subgraph "Core Components"
            ADMIN[WDM_Cert_Admin<br/>Settings & Meta Boxes]
            HANDLER[WDM_Cert_Handler<br/>Certificate Generation]
            VERIFY[WDM_Cert_Verification<br/>Public Verification]
            SHORT[WDM_Cert_Shortcodes<br/>Shortcode Registry]
        end

        subgraph "Utility Classes"
            HELPER[WDM_Cert_Helper<br/>CSUID Encoding]
            QR[WDM_Cert_QR_Code<br/>QR Generation]
            UPGRADE[WDM_Cert_Upgrade<br/>Migrations]
        end
    end

    subgraph "External Services"
        QCHART[QuickChart.io<br/>QR Code API]
    end

    subgraph "Frontend"
        BROWSER[User Browser]
        PDF[Certificate PDF]
    end

    WP --> MAIN
    LD --> MAIN
    LDC --> MAIN

    MAIN --> ADMIN
    MAIN --> HANDLER
    MAIN --> VERIFY
    MAIN --> SHORT

    ADMIN --> UPGRADE
    HANDLER --> HELPER
    VERIFY --> HELPER
    VERIFY --> HANDLER
    SHORT --> VERIFY
    SHORT --> QR
    SHORT --> HELPER
    QR --> HELPER
    UPGRADE --> HELPER

    QR --> QCHART
    VERIFY --> BROWSER
    HANDLER --> PDF

    ADMIN --> WPDB
    HANDLER --> WPDB
    VERIFY --> LDA
    UPGRADE --> LDA
```

---

## 2. Component Architecture

### 2.1 Main Plugin Class

**File:** `wdm-certificate-customizations.php`
**Class:** `WDM_Certificate_Customizations`
**Pattern:** Singleton

The main plugin class serves as the orchestrator for all plugin functionality. It:

1. Manages plugin lifecycle (activation, deactivation)
2. Checks dependencies (LearnDash, Certificate Builder)
3. Initializes all component classes
4. Registers global hooks for certificate access control
5. Enqueues frontend and admin assets

```mermaid
graph LR
    subgraph "Initialization Flow"
        A[plugins_loaded:5] --> B[check_dependencies]
        B --> C[plugins_loaded:15]
        C --> D[init_plugin]
        D --> E[load_classes]
        E --> F[Initialize Components]
    end
```

### 2.2 Admin Component

**File:** `includes/class-admin.php`
**Class:** `WDM_Cert_Admin`
**Pattern:** Singleton

Responsibilities:
- Settings page under LearnDash menu
- Course/Quiz meta boxes for pocket certificate assignment
- AJAX handler for retroactive certificate generation
- Settings sanitization and validation

### 2.3 Certificate Handler

**File:** `includes/class-certificate-handler.php`
**Class:** `WDM_Cert_Handler`
**Pattern:** Singleton

Responsibilities:
- Listen to LearnDash completion events
- Generate certificate records with CSUIDs
- Store records in user meta
- Modify certificate links to include CSUIDs

### 2.4 Verification Component

**File:** `includes/class-verification.php`
**Class:** `WDM_Cert_Verification`
**Pattern:** Singleton

Responsibilities:
- Register rewrite rules for pretty URLs
- AJAX handler for certificate verification
- Render verification results
- Handle query variables

### 2.5 Shortcodes Component

**File:** `includes/class-shortcodes.php`
**Class:** `WDM_Cert_Shortcodes`
**Pattern:** Singleton

Responsibilities:
- Register all plugin shortcodes
- Render verification form and results
- Generate QR codes for certificates
- Display certificate IDs and verification URLs

### 2.6 Helper Class

**File:** `includes/class-helper.php`
**Class:** `WDM_Cert_Helper`
**Pattern:** Static Utility

Responsibilities:
- CSUID encoding/decoding
- Certificate record retrieval
- URL generation
- Type mapping (post type <-> source type)

### 2.7 QR Code Class

**File:** `includes/class-qr-code.php`
**Class:** `WDM_Cert_QR_Code`
**Pattern:** Static Utility

Responsibilities:
- Generate QR code URLs via QuickChart.io
- Render QR code HTML
- Context-aware QR generation for certificates

### 2.8 Upgrade Class

**File:** `includes/class-upgrade.php`
**Class:** `WDM_Cert_Upgrade`
**Pattern:** Singleton

Responsibilities:
- Retroactive certificate ID generation
- Migration from LD Certificate Verify and Share
- Certificate statistics gathering

---

## 3. Data Flow Diagrams

### 3.1 Certificate Generation Flow

```mermaid
sequenceDiagram
    participant User
    participant LD as LearnDash
    participant Handler as WDM_Cert_Handler
    participant Helper as WDM_Cert_Helper
    participant DB as WordPress DB

    User->>LD: Complete Course/Quiz
    LD->>Handler: learndash_course_completed hook
    Handler->>Helper: get_assigned_certificate()
    Helper->>DB: learndash_get_setting()
    DB-->>Helper: Certificate ID
    Helper-->>Handler: Certificate ID
    Handler->>Helper: get_pocket_certificate()
    Helper->>DB: get_post_meta()
    DB-->>Helper: Pocket Cert ID
    Helper-->>Handler: Pocket Cert ID
    Handler->>Helper: encode_csuid()
    Helper-->>Handler: CSUID (e.g., "1A-2B-3C")
    Handler->>DB: update_user_meta()
    Handler->>Handler: do_action('wdm_certificate_record_generated')
```

### 3.2 Certificate Verification Flow

```mermaid
sequenceDiagram
    participant Verifier as Third Party
    participant Browser
    participant WP as WordPress
    participant Verify as WDM_Cert_Verification
    participant Helper as WDM_Cert_Helper
    participant DB as WordPress DB

    Verifier->>Browser: Scan QR Code / Enter CSUID
    Browser->>WP: GET /verify/1A-2B-3C/
    WP->>Verify: Route via rewrite rules
    Verify->>Helper: decode_csuid("1A-2B-3C")
    Helper-->>Verify: {cert_id, source_id, user_id}
    Verify->>DB: get_post(source_id)
    DB-->>Verify: Course/Quiz post
    Verify->>DB: get_user_by(user_id)
    DB-->>Verify: User data
    Verify->>Helper: get_certificate_by_csuid()
    Helper->>DB: get_user_meta()
    DB-->>Helper: Certificate record
    Helper-->>Verify: Certificate data
    Verify->>Browser: Render verification result
    Browser-->>Verifier: Display certificate details
```

### 3.3 Public Certificate Access Flow

```mermaid
sequenceDiagram
    participant Verifier as Third Party
    participant Browser
    participant Main as WDM_Certificate_Customizations
    participant Helper as WDM_Cert_Helper
    participant LD as LearnDash
    participant PDF as PDF Generator

    Verifier->>Browser: Click PDF link from verification
    Browser->>Main: GET /certificate/?course_id=X&user=Y&cert-nonce=Z
    Main->>Main: allow_public_certificate_view()
    Main->>Main: Check: is_singular('sfwd-certificates')
    Main->>Main: Check: !is_user_logged_in()
    Main->>Main: Validate parameters
    Main->>Helper: get_assigned_certificate()
    Helper-->>Main: Standard cert ID
    Main->>Main: Verify certificate matches
    Main->>LD: learndash_course_completed()
    LD-->>Main: Completion status
    Main->>Main: wp_set_current_user(cert_user_id)
    Main->>LD: do_action('learndash_tcpdf_init')
    LD->>PDF: Generate PDF
    PDF-->>Browser: Render certificate PDF
```

---

## 4. Data Model

### 4.1 WordPress Options

```mermaid
erDiagram
    WP_OPTIONS {
        string option_name PK "wdm_certificate_options"
        text option_value "Serialized array"
    }

    WDM_OPTIONS {
        int verification_page_id "WordPress page ID"
        int qr_code_size "50-500 pixels"
        bool enable_pocket_certificate "true/false"
        string certificate_id_prefix "Optional prefix"
        text custom_css "Custom styles"
    }

    WP_OPTIONS ||--|| WDM_OPTIONS : contains
```

### 4.2 User Meta Structure

```mermaid
erDiagram
    WP_USERMETA {
        bigint umeta_id PK
        bigint user_id FK
        string meta_key "_wdm_certificate_{type}_{id}"
        text meta_value "Serialized record"
    }

    CERTIFICATE_RECORD {
        string certificate_id "CSUID (e.g., 1A-2B-3C)"
        int standard_cert "Certificate template ID"
        int pocket_cert "Pocket certificate ID"
        string source_type "course|quiz|group"
        int source_id "Post ID"
        int user_id "User ID"
        int completion_date "Unix timestamp"
        int generated_date "Unix timestamp"
        bool is_retroactive "true/false"
    }

    WP_USERMETA ||--|| CERTIFICATE_RECORD : contains
```

### 4.3 Post Meta Structure

```mermaid
erDiagram
    WP_POSTMETA {
        bigint meta_id PK
        bigint post_id FK
        string meta_key
        text meta_value
    }

    COURSE_QUIZ_META {
        int _wdm_pocket_certificate "Pocket cert template ID"
    }

    WP_POSTMETA ||--o| COURSE_QUIZ_META : contains
```

---

## 5. Integration Points

### 5.1 LearnDash Integration

| Hook Type | Hook Name | Usage |
|-----------|-----------|-------|
| Action | `learndash_course_completed` | Trigger certificate generation |
| Action | `learndash_quiz_completed` | Trigger certificate generation |
| Action | `ld_added_group_access` | Check group completion |
| Action | `learndash_certificate_disallowed` | Allow pocket/public access |
| Action | `learndash_tcpdf_init` | Render certificate PDF |
| Filter | `learndash_course_certificate_link` | Modify certificate links |
| Filter | `learndash_quiz_certificate_link` | Modify quiz certificate links |
| Filter | `learndash_settings_fields` | Add pocket certificate field |

### 5.2 LearnDash Functions Used

| Function | Purpose |
|----------|---------|
| `learndash_get_setting()` | Get course/quiz/group settings |
| `learndash_course_completed()` | Check course completion status |
| `learndash_course_status()` | Get course progress status |
| `learndash_get_user_quiz_attempt()` | Get quiz attempt data |
| `learndash_get_user_group_completed_timestamp()` | Get group completion time |
| `learndash_get_course_id()` | Get course from quiz |
| `learndash_is_admin_user()` | Check admin privileges |
| `learndash_is_group_leader_user()` | Check group leader role |
| `learndash_certificate_post_shortcode()` | Render certificate PDF |

### 5.3 External API Integration

**QuickChart.io QR Code API**

```
Endpoint: https://quickchart.io/qr
Method: GET
Parameters:
  - text: URL to encode
  - size: Image size in pixels
  - margin: Margin in modules (default: 1)

Example: https://quickchart.io/qr?text=https://example.com/verify/1A-2B-3C&size=150&margin=1
```

---

## 6. Security Architecture

### 6.1 Authentication & Authorization

```mermaid
graph TB
    subgraph "Access Control Layers"
        L1[WordPress Capabilities]
        L2[LearnDash Roles]
        L3[Nonce Verification]
        L4[Completion Verification]
    end

    subgraph "Protected Actions"
        A1[Admin Settings - manage_options]
        A2[Retroactive Generation - manage_options]
        A3[Meta Box Save - edit_post]
        A4[Certificate Access - Nonce + Completion]
    end

    L1 --> A1
    L1 --> A2
    L1 --> A3
    L3 --> A4
    L4 --> A4
```

### 6.2 Nonce Implementation

| Context | Nonce Action | Used In |
|---------|--------------|---------|
| Admin AJAX | `wdm_cert_admin` | Retroactive generation |
| Frontend AJAX | `wdm_cert_verify` | Certificate verification |
| Meta Box | `wdm_pocket_certificate_nonce` | Save pocket certificate |
| Certificate Access | `{source_id}{cert_user_id}{view_user_id}` | PDF generation |

### 6.3 Input Sanitization

All user inputs are sanitized using WordPress functions:

| Function | Usage |
|----------|-------|
| `absint()` | Integer IDs |
| `sanitize_text_field()` | Text inputs |
| `wp_kses_post()` | HTML content |
| `esc_url()` | URL output |
| `esc_attr()` | Attribute output |
| `esc_html()` | HTML output |

### 6.4 Public Access Security

For public certificate viewing (non-logged-in users):

1. **Certificate Assignment Check:** Verify certificate is assigned to the source
2. **Completion Verification:** User must have completed the course/quiz/group
3. **Parameter Validation:** All URL parameters sanitized
4. **Temporary User Context:** `wp_set_current_user()` for PDF generation only

---

## 7. URL Routing

### 7.1 Rewrite Rules

```mermaid
graph LR
    subgraph "Pretty URLs"
        A["/verify/1A-2B-3C/"] --> B["index.php?pagename=verify&cert_id=1A-2B-3C"]
    end

    subgraph "Query Parameters"
        C["cert_id"] --> D["Certificate CSUID"]
        E["view"] --> F["standard | pocket"]
    end
```

### 7.2 Certificate PDF URLs

| Source Type | URL Pattern |
|-------------|-------------|
| Course | `/certificate/?course_id={id}&user={uid}&cert-nonce={nonce}` |
| Quiz | `/certificate/?quiz={id}&user={uid}&cert-nonce={nonce}` |
| Group | `/certificate/?group_id={id}&user={uid}&cert-nonce={nonce}` |

---

## 8. Asset Management

### 8.1 Frontend Assets

| Asset | Dependencies | Loaded On |
|-------|--------------|-----------|
| `assets/css/frontend.css` | None | Verification page |
| `assets/js/frontend.js` | jQuery | Verification page |

**Localized Data (`wdmCertVars`):**
- `ajaxUrl`: Admin AJAX endpoint
- `nonce`: Verification nonce
- `verificationUrl`: Base verification URL
- `strings`: Localized UI strings

### 8.2 Admin Assets

| Asset | Dependencies | Loaded On |
|-------|--------------|-----------|
| `assets/css/admin.css` | None | Settings page, Course/Quiz edit |
| `assets/js/admin.js` | jQuery | Settings page, Course/Quiz edit |

**Localized Data (`wdmCertAdmin`):**
- `ajaxUrl`: Admin AJAX endpoint
- `nonce`: Admin nonce
- `strings`: Localized UI strings

---

## 9. Error Handling

### 9.1 Verification Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `invalid_format` | 400 | CSUID format invalid |
| `decode_failed` | 400 | CSUID decode error |
| `source_not_found` | 404 | Course/Quiz not found |
| `invalid_source_type` | 400 | Invalid post type |
| `user_not_found` | 404 | User not found |
| `certificate_not_found` | 404 | Certificate template missing |
| `certificate_mismatch` | 403 | Certificate not assigned |
| `not_completed` | 403 | User hasn't completed |

### 9.2 Dependency Errors

Missing dependencies trigger:
1. Admin notice on dashboard
2. Plugin components not loaded
3. Settings page inaccessible

---

## 10. Caching Considerations

### 10.1 QR Code Caching

QR codes are generated via external API. Consider:
- Browser caching of QR images
- CDN caching for static QR code URLs
- No server-side caching implemented (stateless)

### 10.2 Certificate Record Caching

Certificate records are stored in user meta:
- WordPress object cache applies
- No additional caching layer
- Records generated on-demand for retroactive support

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0.0 | 2026-02-05 | Documentation Generator | Initial documentation |

---

*Generated from code analysis of WDM Certificate Customizations v1.0.0*
