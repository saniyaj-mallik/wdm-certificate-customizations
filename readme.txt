=== WDM Certificate Customizations ===
Contributors: wisdmlabs
Tags: learndash, certificate, verification, qr code, pocket certificate
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds dual certificate support (Standard + Pocket Size) with built-in QR code verification system for LearnDash.

== Description ==

WDM Certificate Customizations extends LearnDash LMS with powerful certificate features:

**Key Features:**

* **Dual Certificate System** - Assign both standard and pocket size certificates to courses
* **Built-in Verification** - QR code and Certificate ID verification system
* **Verification Page** - Public page for third-party certificate verification
* **Shortcodes** - Easy integration with certificate templates
* **Retroactive Support** - Generate Certificate IDs for historical completions

**Requirements:**

* WordPress 6.0 or higher
* PHP 7.4 or higher
* LearnDash LMS 4.0 or higher
* LearnDash Certificate Builder

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/wdm-certificate-customizations/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to LearnDash > Certificate Customizations to configure settings
4. A verification page will be automatically created on activation

== Frequently Asked Questions ==

= What shortcodes are available? =

* `[wdm_certificate_verify]` - Display verification form and results
* `[wdm_certificate_qr_code]` - Display QR code on certificates
* `[wdm_certificate_id]` - Display Certificate ID on certificates

= How do I add a QR code to my certificate? =

In LearnDash Certificate Builder, add the shortcode `[wdm_certificate_qr_code size="150" align="center"]`

= How does the Certificate ID work? =

Certificate IDs are automatically generated when a student completes a course. The ID encodes the certificate, course, and user information.

= Can I generate Certificate IDs for past completions? =

Yes! Go to LearnDash > Certificate Customizations and click "Generate Certificate IDs for Historical Completions"

== Screenshots ==

1. Certificate verification page
2. Admin settings page
3. Course settings with pocket certificate option
4. QR code on certificate

== Changelog ==

= 1.0.0 =
* Initial release
* Dual certificate system (Standard + Pocket Size)
* QR code generation shortcode
* Certificate ID shortcode
* Verification page with search form
* Retroactive Certificate ID generation
* Admin settings page
* LearnDash course settings integration

== Upgrade Notice ==

= 1.0.0 =
Initial release of WDM Certificate Customizations.
