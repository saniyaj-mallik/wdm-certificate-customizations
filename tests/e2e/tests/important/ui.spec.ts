import { test, expect } from '@playwright/test';
import { VerificationPage } from '../../pages/verification.page';
import { testData } from '../../fixtures/test-data';

/**
 * Important UI Tests
 *
 * TC-024: Certificate URL Parameter View
 * TC-025: AJAX Loading State
 * TC-026: Empty Certificate ID Submission
 * TC-027: URL History Update
 * TC-029: Certificate ID Case Insensitivity
 */

test.describe('UI Interactions - Important', () => {
  let verificationPage: VerificationPage;

  test.beforeEach(async ({ page }) => {
    verificationPage = new VerificationPage(page);
  });

  /**
   * TC-024: Certificate URL Parameter View
   *
   * URL view parameter pre-selects certificate tab.
   */
  test.describe('TC-024: URL View Parameter', () => {
    test('No view param defaults to standard tab', async ({ page }) => {
      expect(testData.certificates.validWithPocket?.csuid).toBeTruthy();

      await verificationPage.gotoWithParams({
        cert_id: testData.certificates.validWithPocket.csuid,
      });

      await verificationPage.waitForLoadingComplete();

      // Standard tab should be active
      await expect(verificationPage.standardTab).toHaveClass(/active/);
    });

    test('view=pocket selects pocket tab', async ({ page }) => {
      expect(testData.certificates.validWithPocket?.csuid).toBeTruthy();

      await verificationPage.gotoWithParams({
        cert_id: testData.certificates.validWithPocket.csuid,
        view: 'pocket',
      });

      await verificationPage.waitForLoadingComplete();

      // Pocket tab should be active
      await expect(verificationPage.pocketTab).toHaveClass(/active/);
    });

    test('view=standard selects standard tab', async ({ page }) => {
      expect(testData.certificates.validWithPocket?.csuid).toBeTruthy();

      await verificationPage.gotoWithParams({
        cert_id: testData.certificates.validWithPocket.csuid,
        view: 'standard',
      });

      await verificationPage.waitForLoadingComplete();

      // Standard tab should be active
      await expect(verificationPage.standardTab).toHaveClass(/active/);
    });
  });

  /**
   * TC-025: AJAX Loading State
   *
   * Loading state displays during AJAX verification.
   */
  test('TC-025: Loading state during verification', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Enter certificate ID
    await verificationPage.searchInput.fill(testData.certificates.valid.csuid);

    // Set up to intercept AJAX and delay response
    await page.route('**/admin-ajax.php', async (route) => {
      // Add artificial delay to see loading state
      await new Promise((resolve) => setTimeout(resolve, 500));
      await route.continue();
    });

    // Click verify button
    const verifyPromise = verificationPage.verifyButton.click();

    // Check loading state appears
    // Button should show "Verifying..." or be disabled
    await expect(verificationPage.verifyButton).toBeDisabled();

    // Wait for verification to complete
    await verifyPromise;
    await verificationPage.waitForLoadingComplete();

    // Loading state should be cleared
    await expect(verificationPage.verifyButton).toBeEnabled();
  });

  /**
   * TC-026: Empty Certificate ID Submission
   *
   * Empty form submission shows validation error.
   */
  test('TC-026: Empty submission prevented by validation', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Leave input empty
    await verificationPage.searchInput.clear();

    // Try to submit
    await verificationPage.verifyButton.click();

    // HTML5 required validation should prevent submission
    // Check that the input is invalid
    const isInvalid = await verificationPage.searchInput.evaluate((el: HTMLInputElement) => {
      return !el.validity.valid;
    });

    expect(isInvalid).toBeTruthy();

    // No AJAX request should have been sent
    // Result container should not show success/error
    const hasResult =
      (await verificationPage.isVerificationSuccessful()) ||
      (await verificationPage.isVerificationError());

    expect(hasResult).toBeFalsy();
  });

  /**
   * TC-027: URL History Update
   *
   * Successful verification updates browser URL.
   */
  test('TC-027: URL updates after successful verification', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Get initial URL
    const initialUrl = page.url();

    // Verify certificate
    await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

    // Wait for verification success
    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();

    // URL should have changed to include cert_id
    const newUrl = page.url();
    expect(newUrl).not.toBe(initialUrl);
    expect(newUrl).toContain(testData.certificates.valid.csuid);
  });

  test('TC-027: Browser back button works after verification', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Verify certificate
    await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();

    // Go back
    await page.goBack();

    // Should return to search form state
    // Either URL doesn't have cert_id or form is shown
    const currentUrl = page.url();
    const hasSearch = await verificationPage.isVisible(verificationPage.searchForm);

    expect(!currentUrl.includes('cert_id') || hasSearch).toBeTruthy();
  });

  /**
   * TC-029: Certificate ID Case Insensitivity
   *
   * Certificate ID verification is case-insensitive.
   */
  test('TC-029: Lowercase input verifies correctly', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Enter in lowercase
    const lowercaseId = testData.certificates.valid.csuid.toLowerCase();
    await verificationPage.verifyCertificate(lowercaseId);

    // Should verify successfully
    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();
  });

  test('TC-029: Mixed case input verifies correctly', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Create mixed case version
    const mixedCaseId = testData.certificates.valid.csuid
      .split('')
      .map((c, i) => (i % 2 === 0 ? c.toLowerCase() : c.toUpperCase()))
      .join('');

    await verificationPage.verifyCertificate(mixedCaseId);

    // Should verify successfully
    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();
  });

  test('TC-029: Result shows uppercase CSUID', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Enter in lowercase
    const lowercaseId = testData.certificates.valid.csuid.toLowerCase();
    await verificationPage.verifyCertificate(lowercaseId);

    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();

    // Displayed ID should be uppercase
    const displayedId = await verificationPage.getDisplayedCertificateId();
    expect(displayedId).toBe(displayedId.toUpperCase());
  });
});
