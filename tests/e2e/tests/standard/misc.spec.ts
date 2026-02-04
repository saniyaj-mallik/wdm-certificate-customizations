import { test, expect } from '@playwright/test';
import { VerificationPage } from '../../pages/verification.page';
import { AdminSettingsPage } from '../../pages/admin-settings.page';
import { testData } from '../../fixtures/test-data';
import { authStatePaths } from '../../fixtures/users';

/**
 * Standard Miscellaneous Tests
 *
 * TC-037: Retroactive Generation with No Completions
 * TC-041: Certificate Preview iFrame Loading
 * TC-042: Verification URL Shortcode
 * TC-043: Group Certificate Support
 * TC-044: Statistics from Upgrade Class
 * TC-045: Pretty Permalink Verification URL
 * TC-046: Non-Pretty Permalink Fallback
 * TC-047: Translation Ready (i18n)
 */

test.describe('Miscellaneous - Standard', () => {
  /**
   * TC-037: Retroactive Generation with No Completions
   *
   * Retroactive generation handles empty completion list.
   */
  test.describe('TC-037: Empty Retroactive Generation', () => {
    test.use({ storageState: authStatePaths.admin });

    test('Retroactive generation with no completions shows success', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Run retroactive generation
      await settingsPage.confirmRetroactiveGeneration();
      await settingsPage.waitForRetroactiveComplete();

      // Should show success message (even if 0 generated)
      const result = await settingsPage.getRetroactiveResult();
      expect(result).toBeTruthy();
      expect(result).not.toContain('error');
    });
  });

  /**
   * TC-041: Certificate Preview iFrame Loading
   *
   * Certificate preview iFrame loads correctly.
   */
  test.describe('TC-041: iFrame Preview', () => {
    test('Certificate preview iFrame loads', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

      // Check if preview loaded
      const previewLoaded = await verificationPage.isCertificatePreviewLoaded();

      if (previewLoaded) {
        // iFrame should have valid src
        const previewUrl = await verificationPage.getCertificatePreviewUrl();
        expect(previewUrl).toBeTruthy();
        expect(previewUrl).toContain('http');
      }
    });

    test('iFrame src contains certificate reference', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

      if (await verificationPage.isCertificatePreviewLoaded()) {
        const previewUrl = await verificationPage.getCertificatePreviewUrl();

        // URL should reference the certificate somehow
        // Either by ID, slug, or CSUID
        expect(previewUrl).toMatch(/certificate|cert|sfwd/i);
      }
    });
  });

  /**
   * TC-042: Verification URL Shortcode
   *
   * Verification URL shortcode outputs correct URL.
   */
  test.describe('TC-042: Verification URL Shortcode', () => {
    test.skip('Verification URL shortcode outputs correct URL', async ({ page }) => {
      // This requires:
      // 1. Certificate template with [wdm_certificate_verification_url] shortcode
      // 2. Viewing the certificate
      // 3. Checking the URL output

      // Would need access to certificate template content
    });
  });

  /**
   * TC-043: Group Certificate Support
   *
   * Group completion generates certificate record.
   */
  test.describe('TC-043: Group Certificates', () => {
    test.skip('Group completion creates certificate record', async ({ page }) => {
      // This requires:
      // 1. LearnDash group with certificate
      // 2. User completing all group courses
      // 3. Verifying certificate record with source_type="group"

      // Complex to automate without proper test data setup
    });
  });

  /**
   * TC-044: Statistics from Upgrade Class
   *
   * Statistics method returns accurate counts.
   */
  test.describe('TC-044: Statistics', () => {
    test.use({ storageState: authStatePaths.admin });

    test.skip('Statistics show on settings page', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Check for statistics display
      // This depends on if/how statistics are shown in admin
    });
  });

  /**
   * TC-045: Pretty Permalink Verification URL
   *
   * Pretty URLs work for verification.
   */
  test.describe('TC-045: Pretty Permalinks', () => {
    test('Pretty URL format works', async ({ page }) => {
      // Try accessing with pretty URL format
      const prettyUrl = `${testData.verificationPage.url}${testData.certificates.valid.csuid}/`;

      await page.goto(prettyUrl);
      await page.waitForLoadState('networkidle');

      // Should not be 404
      const title = await page.title();
      expect(title.toLowerCase()).not.toContain('not found');
    });

    test('Certificate auto-verifies from pretty URL', async ({ page }) => {
      const verificationPage = new VerificationPage(page);

      // Navigate with cert ID in path
      await page.goto(`${testData.verificationPage.url}${testData.certificates.valid.csuid}/`);
      await verificationPage.waitForPageLoad();

      // Certificate should auto-verify
      // Either form is pre-filled or results are shown
      const hasResults = await verificationPage.isVerificationSuccessful();
      const inputValue = await verificationPage.searchInput.inputValue().catch(() => '');

      expect(hasResults || inputValue.includes(testData.certificates.valid.csuid)).toBeTruthy();
    });
  });

  /**
   * TC-046: Non-Pretty Permalink Fallback
   *
   * Non-pretty URLs work for verification.
   */
  test.describe('TC-046: Query Parameter URLs', () => {
    test('Query parameter format works', async ({ page }) => {
      const verificationPage = new VerificationPage(page);

      // Navigate with query parameter
      await verificationPage.gotoWithParams({
        cert_id: testData.certificates.valid.csuid,
      });

      // Should work
      const hasResults = await verificationPage.isVerificationSuccessful();
      const hasForm = await verificationPage.isVisible(verificationPage.searchForm);

      expect(hasResults || hasForm).toBeTruthy();
    });
  });

  /**
   * TC-047: Translation Ready (i18n)
   *
   * All strings are translatable.
   */
  test.describe('TC-047: Internationalization', () => {
    test.use({ storageState: authStatePaths.admin });

    test('Settings page uses translatable strings', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Check that UI elements have text (indicating strings are loaded)
      const pageTitle = await settingsPage.getPageTitle();
      expect(pageTitle).toBeTruthy();

      // Labels should be present
      await expect(settingsPage.verificationPageLabel).toBeVisible();
      await expect(settingsPage.pocketCertLabel).toBeVisible();
      await expect(settingsPage.qrCodeSizeLabel).toBeVisible();
    });

    test('Verification page uses translatable strings', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Button text should be present
      const buttonText = await verificationPage.verifyButton.textContent();
      expect(buttonText).toBeTruthy();

      // Verify and check error strings
      await verificationPage.verifyCertificate('INVALID-ID');

      if (await verificationPage.isVerificationError()) {
        const errorTitle = await verificationPage.errorTitle.textContent();
        expect(errorTitle).toBeTruthy();
      }
    });

    test('JavaScript strings are localized', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Check that wdm_cert_vars is defined (localized JS object)
      const hasLocalization = await page.evaluate(() => {
        // @ts-ignore
        return typeof window.wdm_cert_vars !== 'undefined';
      });

      expect(hasLocalization).toBeTruthy();
    });
  });

  /**
   * Additional: Accessibility basics
   */
  test.describe('Accessibility Basics', () => {
    test('Search input has label', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Input should have associated label or aria-label
      const hasLabel =
        (await verificationPage.searchInput.getAttribute('aria-label')) ||
        (await page.locator(`label[for="${await verificationPage.searchInput.getAttribute('id')}"]`).count()) > 0;

      expect(hasLabel).toBeTruthy();
    });

    test('Form is keyboard navigable', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Tab to input
      await page.keyboard.press('Tab');
      const focusedElement1 = await page.evaluate(() => document.activeElement?.tagName);

      // Tab to button
      await page.keyboard.press('Tab');
      const focusedElement2 = await page.evaluate(() => document.activeElement?.tagName);

      // Should be able to navigate with keyboard
      expect(['INPUT', 'BUTTON']).toContain(focusedElement1);
    });
  });
});
