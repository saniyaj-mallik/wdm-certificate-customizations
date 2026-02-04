import { test, expect } from '@playwright/test';
import { AdminSettingsPage } from '../../pages/admin-settings.page';
import { CourseEditorPage } from '../../pages/course-editor.page';
import { testData } from '../../fixtures/test-data';
import { authStatePaths } from '../../fixtures/users';

/**
 * Critical Admin Tests
 *
 * TC-008: Admin Settings Page Access
 * TC-009: Non-Admin Cannot Access Settings
 * TC-013: Retroactive Generation Functionality
 * TC-014: Pocket Certificate Assignment
 * TC-015: Verification Page Auto-Creation
 */

test.describe('Admin Settings - Critical', () => {
  /**
   * TC-008: Admin Settings Page Access
   *
   * Admin can access and configure plugin settings.
   */
  test.describe('TC-008: Admin Settings Access', () => {
    test.use({ storageState: authStatePaths.admin });

    test('Admin can access settings page', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Verify page loaded
      expect(await settingsPage.isSettingsPageLoaded()).toBeTruthy();

      // Check page title
      const title = await settingsPage.getPageTitle();
      expect(title).toContain('Certificate');
    });

    test('Admin can navigate to settings via menu', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);

      // Start from dashboard
      await settingsPage.goto('/wp-admin/');
      await settingsPage.waitForPageLoad();

      // Navigate via menu
      await settingsPage.navigateViaMenu();

      // Verify page loaded
      expect(await settingsPage.isSettingsPageLoaded()).toBeTruthy();
    });

    test('All settings fields are visible', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Verification page dropdown
      await expect(settingsPage.verificationPageSelect).toBeVisible();

      // Pocket certificate checkbox
      await expect(settingsPage.pocketCertCheckbox).toBeVisible();

      // QR code size field
      await expect(settingsPage.qrCodeSizeInput).toBeVisible();
    });

    test('Settings can be saved and persist', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Get current QR size
      const originalSize = await settingsPage.getQrCodeSize();

      // Change QR code size
      const newSize = originalSize === 200 ? 150 : 200;
      await settingsPage.setQrCodeSize(newSize);

      // Save settings
      await settingsPage.saveSettings();

      // Verify save success
      expect(await settingsPage.isSettingsSaved()).toBeTruthy();

      // Refresh page
      await page.reload();
      await settingsPage.waitForPageLoad();

      // Verify value persisted
      const persistedSize = await settingsPage.getQrCodeSize();
      expect(persistedSize).toBe(newSize);

      // Restore original value
      await settingsPage.setQrCodeSize(originalSize);
      await settingsPage.saveSettings();
    });
  });

  /**
   * TC-009: Non-Admin Cannot Access Settings
   *
   * Non-admin users cannot access plugin settings.
   */
  test.describe('TC-009: Non-Admin Access Denied', () => {
    test.use({ storageState: authStatePaths.student });

    test('Student cannot access settings page directly', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);

      // Try direct URL access
      await settingsPage.gotoSettingsPage();

      // Should be redirected or show access denied
      const currentUrl = page.url();

      // Either redirected away from settings page
      // or WordPress access denied message shown
      const isOnSettingsPage = currentUrl.includes('wdm-certificate-customizations');

      if (isOnSettingsPage) {
        // If still on page, should show WordPress capability error
        const pageContent = await page.textContent('body');
        expect(pageContent).toContain('not have sufficient permissions');
      } else {
        // Successfully redirected away
        expect(isOnSettingsPage).toBeFalsy();
      }
    });

    test('Student cannot see LearnDash admin menu', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);

      // Go to dashboard
      await settingsPage.goto('/wp-admin/');

      // LearnDash menu should not be visible to students
      // or Certificate Customizations submenu should not be visible
      const certificateMenuItem = page.locator(
        '#adminmenu a:has-text("Certificate Customizations")'
      );
      await expect(certificateMenuItem).not.toBeVisible();
    });
  });

  /**
   * TC-013: Retroactive Generation Functionality
   *
   * Retroactive generation creates CSUIDs for historical completions.
   */
  test.describe('TC-013: Retroactive Generation', () => {
    test.use({ storageState: authStatePaths.admin });

    test('Retroactive generation button is visible', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      await expect(settingsPage.retroactiveButton).toBeVisible();
    });

    test('Retroactive generation shows confirmation dialog', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Set up dialog handler
      let dialogAppeared = false;
      page.on('dialog', async (dialog) => {
        dialogAppeared = true;
        expect(dialog.type()).toBe('confirm');
        await dialog.dismiss(); // Cancel for this test
      });

      await settingsPage.clickRetroactiveGeneration();

      // Wait a bit for dialog
      await page.waitForTimeout(500);

      expect(dialogAppeared).toBeTruthy();
    });

    test('Retroactive generation processes and shows result', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Confirm the dialog
      await settingsPage.confirmRetroactiveGeneration();

      // Wait for completion
      await settingsPage.waitForRetroactiveComplete();

      // Check result message
      const result = await settingsPage.getRetroactiveResult();
      expect(result).toBeTruthy();
      expect(result).toMatch(/generated|complete|success/i);
    });
  });

  /**
   * TC-014: Pocket Certificate Assignment
   *
   * Admin can assign pocket certificate to course.
   */
  test.describe('TC-014: Pocket Certificate Assignment', () => {
    test.use({ storageState: authStatePaths.admin });

    test('Pocket certificate field visible in course editor', async ({ page }) => {
      const courseEditor = new CourseEditorPage(page);
      await courseEditor.gotoCourseEditor(testData.courses.withCertificate.id);

      // Expand settings if needed
      await courseEditor.expandSettingsMetabox();

      // Pocket certificate field should be visible
      expect(await courseEditor.isPocketCertificateFieldVisible()).toBeTruthy();
    });

    test('Pocket certificate dropdown has options', async ({ page }) => {
      const courseEditor = new CourseEditorPage(page);
      await courseEditor.gotoCourseEditor(testData.courses.withCertificate.id);

      await courseEditor.expandSettingsMetabox();

      // Get dropdown options
      const options = await courseEditor.getPocketCertificateOptions();

      // Should have "-- None --" option at minimum
      expect(options.length).toBeGreaterThan(0);
      expect(options.some((o) => o.includes('None'))).toBeTruthy();
    });

    test('Pocket certificate selection saves correctly', async ({ page }) => {
      const courseEditor = new CourseEditorPage(page);
      await courseEditor.gotoCourseEditor(testData.courses.withCertificate.id);

      await courseEditor.expandSettingsMetabox();
      await courseEditor.scrollToPocketCertificateSettings();

      // Get current selection
      const originalSelection = await courseEditor.getSelectedPocketCertificate();

      // Get options and select a different one
      const options = await courseEditor.getPocketCertificateOptions();
      const optionsWithValues = options.filter((o) => !o.includes('None'));

      if (optionsWithValues.length > 0) {
        // Select first certificate option
        await courseEditor.selectPocketCertificate(
          testData.courses.withDualCertificate.pocketCertId.toString()
        );

        // Save course
        await courseEditor.saveCourse();

        // Verify saved
        expect(await courseEditor.isCourseSaved()).toBeTruthy();

        // Reload and verify persisted
        await page.reload();
        await courseEditor.expandSettingsMetabox();

        const newSelection = await courseEditor.getSelectedPocketCertificate();
        expect(newSelection).toBe(testData.courses.withDualCertificate.pocketCertId.toString());

        // Restore original
        if (originalSelection) {
          await courseEditor.selectPocketCertificate(originalSelection);
        } else {
          await courseEditor.clearPocketCertificate();
        }
        await courseEditor.saveCourse();
      }
    });
  });

  /**
   * TC-015: Verification Page Auto-Creation
   *
   * Plugin activation creates verification page automatically.
   */
  test.describe('TC-015: Verification Page Auto-Creation', () => {
    test.use({ storageState: authStatePaths.admin });

    test('Verification page exists after activation', async ({ page }) => {
      const settingsPage = new AdminSettingsPage(page);
      await settingsPage.gotoSettingsPage();

      // Verification page should be selected
      const selectedPage = await settingsPage.getSelectedVerificationPage();
      expect(selectedPage).toBeTruthy();
      expect(selectedPage).not.toBe('0');
      expect(selectedPage).not.toBe('');
    });

    test('Verification page is accessible on frontend', async ({ page }) => {
      // Navigate to verification page
      await page.goto(testData.verificationPage.url);

      // Page should load
      await page.waitForLoadState('networkidle');

      // Should not be a 404
      const title = await page.title();
      expect(title.toLowerCase()).not.toContain('not found');
      expect(title.toLowerCase()).not.toContain('404');

      // Page should contain the shortcode output (search form)
      const searchForm = page.locator('.wdm-cert-search-form, #wdm-cert-search-form');
      // or search input
      const searchInput = page.locator('#wdm-cert-id-input, input[name="cert_id"]');

      const hasForm = await searchForm.isVisible().catch(() => false);
      const hasInput = await searchInput.isVisible().catch(() => false);

      expect(hasForm || hasInput).toBeTruthy();
    });
  });
});
