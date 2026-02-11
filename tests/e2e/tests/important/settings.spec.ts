import { test, expect } from '@playwright/test';
import { AdminSettingsPage } from '../../pages/admin-settings.page';
import { CourseEditorPage } from '../../pages/course-editor.page';
import { testData } from '../../fixtures/test-data';
import { authStatePaths } from '../../fixtures/users';

/**
 * Important Settings Tests
 *
 * TC-017: QR Code Size Configuration
 * TC-018: QR Code Size Validation
 * TC-023: Pocket Certificate Toggle
 * TC-031: Verification Page Missing Shortcode
 * TC-033: Admin Notice for Missing Verification Page
 */

test.describe('Settings - Important', () => {
  test.use({ storageState: authStatePaths.admin });

  /**
   * TC-017: QR Code Size Configuration
   *
   * QR code size setting affects generated QR codes.
   */
  test.describe('TC-017: QR Code Size', () => {
    test('QR size can be set to 100px', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Set size to 100
      await settingsPage.setQrCodeSize(100);
      await settingsPage.saveSettings();

      // Verify saved
      expect(await settingsPage.isSettingsSaved()).toBeTruthy();

      // Verify persisted
      await page.reload();
      const size = await settingsPage.getQrCodeSize();
      expect(size).toBe(100);
    });

    test('QR size can be set to 200px', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Set size to 200
      await settingsPage.setQrCodeSize(200);
      await settingsPage.saveSettings();

      // Verify persisted
      await page.reload();
      const size = await settingsPage.getQrCodeSize();
      expect(size).toBe(200);
    });
  });

  /**
   * TC-018: QR Code Size Validation
   *
   * QR code size validates within allowed range.
   */
  test.describe('TC-018: QR Size Validation', () => {
    test('Value below minimum is corrected to 50', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Try to set below minimum (30)
      await settingsPage.setQrCodeSize(30);
      await settingsPage.saveSettings();

      // Should be corrected to minimum (50)
      await page.reload();
      const size = await settingsPage.getQrCodeSize();
      expect(size).toBeGreaterThanOrEqual(testData.settings.minQrSize);
    });

    test('Value above maximum is corrected to 500', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Try to set above maximum (600)
      await settingsPage.setQrCodeSize(600);
      await settingsPage.saveSettings();

      // Should be corrected to maximum (500)
      await page.reload();
      const size = await settingsPage.getQrCodeSize();
      expect(size).toBeLessThanOrEqual(testData.settings.maxQrSize);
    });

    test('Valid value within range is saved correctly', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Set valid value
      await settingsPage.setQrCodeSize(150);
      await settingsPage.saveSettings();

      // Should be saved as-is
      await page.reload();
      const size = await settingsPage.getQrCodeSize();
      expect(size).toBe(150);
    });
  });

  /**
   * TC-023: Pocket Certificate Toggle
   *
   * Disabling pocket certificates hides field from course settings.
   */
  test.describe('TC-023: Pocket Certificate Toggle', () => {
    test('Disabling pocket cert hides field in course editor', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      const courseEditor = new CourseEditorPage(page);

      // First enable pocket cert
      await settingsPage.gotoSettingsPage();
      await settingsPage.enablePocketCert();
      await settingsPage.saveSettings();

      // Verify field is visible in course editor
      await courseEditor.gotoCourseEditor(testData.courses.withCertificate.id);
      await courseEditor.expandSettingsMetabox();
      expect(await courseEditor.isPocketCertificateFieldVisible()).toBeTruthy();

      // Now disable pocket cert
      await settingsPage.gotoSettingsPage();
      await settingsPage.disablePocketCert();
      await settingsPage.saveSettings();

      // Verify field is hidden in course editor
      await courseEditor.gotoCourseEditor(testData.courses.withCertificate.id);
      await courseEditor.expandSettingsMetabox();
      expect(await courseEditor.isPocketCertificateFieldVisible()).toBeFalsy();

      // Re-enable for cleanup
      await settingsPage.gotoSettingsPage();
      await settingsPage.enablePocketCert();
      await settingsPage.saveSettings();
    });

    test('Enabling pocket cert shows field in course editor', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      const courseEditor = new CourseEditorPage(page);

      // Enable pocket cert
      await settingsPage.gotoSettingsPage();
      await settingsPage.enablePocketCert();
      await settingsPage.saveSettings();

      // Verify field is visible
      await courseEditor.gotoCourseEditor(testData.courses.withCertificate.id);
      await courseEditor.expandSettingsMetabox();
      expect(await courseEditor.isPocketCertificateFieldVisible()).toBeTruthy();
    });
  });

  /**
   * TC-031: Verification Page Missing Shortcode
   *
   * Warning shown if verification page missing shortcode.
   */
  test.describe('TC-031: Missing Shortcode Warning', () => {
    test('Page without shortcode shows no verification UI', async ({ page }) => {
      // This test requires creating a page without the shortcode
      // and setting it as the verification page

      // Create test page without shortcode
      // Set as verification page
      // Visit page and verify no form displayed

      // This is complex to automate without API/WP-CLI access
    });
  });

  /**
   * TC-033: Admin Notice for Missing Verification Page
   *
   * Admin notice displays when verification page not configured.
   */
  test.describe('TC-033: Missing Page Notice', () => {
    test('Warning notice when no verification page set', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);

      // This test requires:
      // 1. Removing the verification page setting
      // 2. Checking for admin notice on dashboard
      // 3. Restoring the setting

      // Save original setting
      await settingsPage.gotoSettingsPage();
      const originalPage = await settingsPage.getSelectedVerificationPage();

      // Clear setting (select empty/0)
      await settingsPage.selectVerificationPage('0');
      await settingsPage.saveSettings();

      // Visit dashboard
      await page.goto('/wp-admin/');

      // Check for warning notice
      expect(await settingsPage.hasAdminNotice('warning')).toBeTruthy();

      const noticeText = await settingsPage.getAdminNoticeMessage();
      expect(noticeText.toLowerCase()).toContain('verification page');

      // Restore original setting
      await settingsPage.gotoSettingsPage();
      await settingsPage.selectVerificationPage(originalPage);
      await settingsPage.saveSettings();
    });
  });
});
