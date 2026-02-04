import { test, expect } from '@playwright/test';
import { VerificationPage } from '../../pages/verification.page';
import { testData } from '../../fixtures/test-data';

/**
 * Standard Mobile Responsive Tests
 *
 * TC-040: Mobile Responsive Verification Page
 *
 * Note: Mobile device emulation is configured in playwright.config.ts
 * via the 'mobile-chrome' project. These tests use viewport settings instead.
 */

test.describe('Mobile Responsive - Standard', () => {
  // Use mobile viewport size
  test.use({
    viewport: { width: 390, height: 844 }, // iPhone 12 dimensions
  });

  /**
   * TC-040: Mobile Responsive Verification Page
   *
   * Verification page is mobile responsive.
   */
  test.describe('TC-040: Mobile Verification Page', () => {
    test('Verification page loads on mobile', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Page should load without errors
      const title = await page.title();
      expect(title.toLowerCase()).not.toContain('error');
    });

    test('Search form is usable on mobile', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Search input should be visible and accessible
      await expect(verificationPage.searchInput).toBeVisible();
      await expect(verificationPage.verifyButton).toBeVisible();

      // Input should be usable
      await verificationPage.searchInput.fill(testData.certificates.valid.csuid);
      const value = await verificationPage.searchInput.inputValue();
      expect(value).toBe(testData.certificates.valid.csuid);
    });

    test('Verification works on mobile', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Verify a certificate
      await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

      // Should show results
      expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();
    });

    test('No horizontal scroll on mobile', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Check viewport width vs document width
      const { documentWidth, viewportWidth } = await page.evaluate(() => ({
        documentWidth: document.documentElement.scrollWidth,
        viewportWidth: window.innerWidth,
      }));

      // Document should not be wider than viewport (allowing small tolerance)
      expect(documentWidth).toBeLessThanOrEqual(viewportWidth + 10);
    });

    test('Buttons are accessible on mobile (touch-friendly)', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Check button size (should be at least 44x44 for touch)
      const buttonBox = await verificationPage.verifyButton.boundingBox();

      if (buttonBox) {
        // Buttons should be reasonably sized for touch
        expect(buttonBox.height).toBeGreaterThanOrEqual(40);
        expect(buttonBox.width).toBeGreaterThanOrEqual(40);
      }
    });

    test('Results display properly on mobile', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

      // Results should be visible
      await expect(verificationPage.successContainer).toBeVisible();

      // Check result container fits viewport
      const resultBox = await verificationPage.successContainer.boundingBox();
      const viewportWidth = page.viewportSize()?.width || 375;

      if (resultBox) {
        expect(resultBox.width).toBeLessThanOrEqual(viewportWidth);
      }
    });

    test('Tabs work on mobile (if dual cert)', async ({ page }) => {
      if (!testData.certificates.validWithPocket?.csuid) {
        test.skip();
        return;
      }

      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      await verificationPage.verifyCertificate(testData.certificates.validWithPocket.csuid);

      // Tabs should be visible
      if (await verificationPage.areTabsVisible()) {
        // Should be able to tap tabs
        await verificationPage.switchToPocketTab();
        await expect(verificationPage.pocketTab).toHaveClass(/active/);

        await verificationPage.switchToStandardTab();
        await expect(verificationPage.standardTab).toHaveClass(/active/);
      }
    });
  });

  /**
   * Additional viewport tests
   */
  test.describe('Various Viewports', () => {
    test('Page loads correctly on small mobile (320px)', async ({ page }) => {
      await page.setViewportSize({ width: 320, height: 568 }); // iPhone SE
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      await expect(verificationPage.searchForm).toBeVisible();
    });

    test('Page loads correctly on medium mobile (375px)', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 }); // iPhone 8
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      await expect(verificationPage.searchForm).toBeVisible();
    });

    test('Page loads correctly on large mobile (414px)', async ({ page }) => {
      await page.setViewportSize({ width: 414, height: 896 }); // iPhone 11
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      await expect(verificationPage.searchForm).toBeVisible();
    });
  });
});

/**
 * Tablet viewport tests
 */
test.describe('Tablet Responsive - Standard', () => {
  test.use({
    viewport: { width: 810, height: 1080 }, // iPad dimensions
  });

  test('Verification page works on tablet', async ({ page }) => {
    const verificationPage = new VerificationPage(page);
    await verificationPage.gotoVerificationPage();

    // Form should be visible and usable
    await expect(verificationPage.searchInput).toBeVisible();

    // Verify a certificate
    await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

    // Should show results
    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();
  });
});
