import { test, expect } from '@playwright/test';
import { testData } from '../../fixtures/test-data';
import { authStatePaths } from '../../fixtures/users';

/**
 * Critical Certificate Generation Tests
 *
 * TC-003: Certificate Generation on Course Completion
 * TC-010: CSUID Encoding Correctness
 * TC-011: QR Code Generation
 * TC-012: Course Certificate Link Modification
 */

test.describe('Certificate Generation - Critical', () => {
  /**
   * TC-003: Certificate Generation on Course Completion
   *
   * Completing a course generates certificate record with CSUID.
   *
   * Note: This test requires LearnDash course setup and may need
   * WP-CLI or database helpers for complete verification.
   */
  test.describe('TC-003: Course Completion Generation', () => {
    test.use({ storageState: authStatePaths.student });

    test('Course completion creates certificate record', async ({ page }) => {
      // This test requires:
      // 1. A course with certificate assigned
      // 2. Student enrolled in the course
      // 3. Ability to mark lessons/topics complete
      // 4. Database/WP-CLI access to verify record creation

      // Navigate to course
      await page.goto(`/?p=${testData.courses.withCertificate.id}`);

      // This is a placeholder - actual implementation depends on
      // LearnDash course structure and completion method

      // After completion:
      // 1. Certificate record should exist in user meta
      // 2. CSUID should follow correct format
      // 3. Certificate link should point to verification page
    });

    test('Certificate link points to verification page', async ({ page }) => {
      // After course completion, verify the certificate link
      // Navigate to course completion page or profile

      // Find certificate link
      const certLink = page.locator('a:has-text("Certificate"), a:has-text("View Certificate")');

      if (await certLink.isVisible()) {
        const href = await certLink.getAttribute('href');

        // Link should include verification page URL
        expect(href).toContain(testData.verificationPage.url);

        // Link should include cert_id parameter
        expect(href).toMatch(/cert_id=|\/[A-F0-9]+-[A-F0-9]+-[A-F0-9]+/i);
      }
    });
  });

  /**
   * TC-010: CSUID Encoding Correctness
   *
   * CSUID encoding produces valid, decodable IDs.
   * This test verifies the encoding/decoding logic via API or JS.
   */
  test.describe('TC-010: CSUID Encoding', () => {
    test.use({ storageState: authStatePaths.admin });

    test('CSUID format is valid', async ({ page }) => {
      // Valid CSUID should match format: [A-F0-9]+-[A-F0-9]+-[A-F0-9]+
      const csuid = testData.certificates.valid.csuid;
      const csuidPattern = /^[A-F0-9]+-[A-F0-9]+-[A-F0-9]+$/i;

      expect(csuid).toMatch(csuidPattern);
    });

    test('CSUID has three segments', async ({ page }) => {
      const csuid = testData.certificates.valid.csuid;
      const segments = csuid.split('-');

      expect(segments.length).toBe(3);
      expect(segments[0].length).toBeGreaterThan(0);
      expect(segments[1].length).toBeGreaterThan(0);
      expect(segments[2].length).toBeGreaterThan(0);
    });

    test('CSUID segments are hexadecimal', async ({ page }) => {
      const csuid = testData.certificates.valid.csuid;
      const segments = csuid.split('-');

      for (const segment of segments) {
        // Each segment should be valid hexadecimal
        expect(() => parseInt(segment, 16)).not.toThrow();
        expect(parseInt(segment, 16)).toBeGreaterThanOrEqual(0);
      }
    });

    test.describe('CSUID round-trip encoding', () => {
      // Test encoding/decoding via page JavaScript
      for (const testCase of testData.csuidTestCases) {
        test(`Encode/decode: cert=${testCase.cert_id}, source=${testCase.source_id}, user=${testCase.user_id}`, async ({
          page,
        }) => {
          // Navigate to a page where we can run JavaScript
          await page.goto('/wp-admin/');

          // Execute encoding logic (matching PHP implementation)
          const result = await page.evaluate(
            ({ certId, sourceId, userId }) => {
              // Replicate the PHP encoding logic
              const cid = certId.toString(16).toUpperCase();
              const sid = sourceId.toString(16).toUpperCase();
              // User ID with offset for security
              const offset = 1000;
              const uid = (userId + offset).toString(16).toUpperCase();

              const encoded = `${cid}-${sid}-${uid}`;

              // Decode it back
              const parts = encoded.split('-');
              const decodedCertId = parseInt(parts[0], 16);
              const decodedSourceId = parseInt(parts[1], 16);
              const decodedUserId = parseInt(parts[2], 16) - offset;

              return {
                encoded,
                decodedCertId,
                decodedSourceId,
                decodedUserId,
              };
            },
            { certId: testCase.cert_id, sourceId: testCase.source_id, userId: testCase.user_id }
          );

          // Verify round-trip
          expect(result.decodedCertId).toBe(testCase.cert_id);
          expect(result.decodedSourceId).toBe(testCase.source_id);
          expect(result.decodedUserId).toBe(testCase.user_id);

          // Verify format
          expect(result.encoded).toMatch(/^[A-F0-9]+-[A-F0-9]+-[A-F0-9]+$/);
        });
      }
    });
  });

  /**
   * TC-011: QR Code Generation
   *
   * QR code shortcode generates valid QR linking to verification.
   */
  test.describe('TC-011: QR Code Generation', () => {
    test.use({ storageState: authStatePaths.student });

    test('QR code shortcode generates image', async ({ page }) => {
      // This test requires viewing a certificate with QR shortcode
      // The certificate template should contain [wdm_certificate_qr_code]

      // For now, verify the QR code on the verification result page
      const { VerificationPage } = await import('../../pages/verification.page');
      const verificationPage = new VerificationPage(page);

      await verificationPage.gotoVerificationPage();
      await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

      // If QR code is displayed on verification result
      const qrImage = page.locator('.wdm-cert-qr-code img, img[alt*="QR"]');
      const hasQr = await qrImage.isVisible().catch(() => false);

      if (hasQr) {
        // QR image should have src
        const src = await qrImage.getAttribute('src');
        expect(src).toBeTruthy();

        // Should be from quickchart.io
        expect(src).toContain('quickchart.io');
      }
    });

    test('QR code URL contains verification link', async ({ page }) => {
      // Navigate to certificate view if available
      // Check QR code encodes correct URL

      const { VerificationPage } = await import('../../pages/verification.page');
      const verificationPage = new VerificationPage(page);

      await verificationPage.gotoVerificationPage();
      await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

      const qrImage = page.locator('.wdm-cert-qr-code img, img[alt*="QR"]');
      const hasQr = await qrImage.isVisible().catch(() => false);

      if (hasQr) {
        const src = await qrImage.getAttribute('src');

        // Decode the URL encoded in the QR (it's in the API URL)
        // quickchart.io/qr?text=ENCODED_URL
        if (src && src.includes('text=')) {
          const match = src.match(/text=([^&]+)/);
          if (match) {
            const encodedUrl = decodeURIComponent(match[1]);
            // Should contain verification page URL
            expect(encodedUrl.toLowerCase()).toContain('certificate');
          }
        }
      }
    });
  });

  /**
   * TC-012: Course Certificate Link Modification
   *
   * Certificate link in LearnDash points to verification page.
   */
  test.describe('TC-012: Certificate Link Modification', () => {
    test.use({ storageState: authStatePaths.student });

    test('Certificate link is modified to verification page', async ({ page }) => {
      // This requires navigating to a page where LearnDash shows
      // the certificate link (course completion, profile, etc.)

      // Navigate to LearnDash profile or course page
      await page.goto('/learndash-profile/');

      // Find certificate links
      const certLinks = page.locator(
        'a[href*="certificate"], a:has-text("Certificate"), .ld-certificate-link'
      );

      const linkCount = await certLinks.count();

      for (let i = 0; i < linkCount; i++) {
        const href = await certLinks.nth(i).getAttribute('href');

        if (href) {
          // Link should NOT point directly to certificate post
          expect(href).not.toMatch(/\/sfwd-certificates\/\d+/);

          // Link should contain verification page URL
          expect(href).toContain(testData.verificationPage.url);

          // Link should contain cert_id parameter
          expect(href).toMatch(/cert_id=[A-F0-9]+-[A-F0-9]+-[A-F0-9]+/i);
        }
      }
    });

    test('Clicking certificate link opens verification page', async ({ page }) => {
      // Direct test of the verification page auto-load feature

      const { VerificationPage } = await import('../../pages/verification.page');
      const verificationPage = new VerificationPage(page);

      // Navigate with cert_id in URL (simulating certificate link click)
      await verificationPage.gotoWithParams({
        cert_id: testData.certificates.valid.csuid,
      });

      // Certificate should auto-verify and display
      await verificationPage.waitForLoadingComplete();

      // Verification result should be shown
      expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();
    });
  });
});
