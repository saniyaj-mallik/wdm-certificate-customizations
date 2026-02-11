import { test, expect } from '@playwright/test';
import { VerificationPage } from '../../pages/verification.page';
import { testData } from '../../fixtures/test-data';
import { invalidCsuidCases } from '../../fixtures/certificates';

/**
 * Critical Verification Tests
 *
 * TC-001: Valid Certificate Verification
 * TC-002: Invalid Certificate ID Format
 * TC-006: Anonymous User Verification
 * TC-007: Dual Certificate Tab Switching
 */

test.describe('Certificate Verification - Critical', () => {
  let verificationPage: VerificationPage;

  test.beforeEach(async ({ page }) => {
    verificationPage = new VerificationPage(page);
  });

  /**
   * TC-001: Valid Certificate Verification
   *
   * Verify a valid certificate ID returns correct certificate details.
   */
  test('TC-001: Valid certificate ID displays correct details', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Enter valid certificate ID and verify
    await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

    // Verify success state
    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();

    // Check success indicators
    await expect(verificationPage.successIcon).toBeVisible();
    await expect(verificationPage.verifiedTitle).toBeVisible();

    // Verify certificate details are displayed
    const recipientName = await verificationPage.getRecipientName();
    expect(recipientName).toBeTruthy();

    const sourceTitle = await verificationPage.getSourceTitle();
    expect(sourceTitle).toBeTruthy();

    const completionDate = await verificationPage.getCompletionDate();
    expect(completionDate).toBeTruthy();

    // Verify displayed certificate ID matches input
    const displayedId = await verificationPage.getDisplayedCertificateId();
    expect(displayedId.toUpperCase()).toContain(testData.certificates.valid.csuid.toUpperCase());

    // Verify status shows "Valid"
    const status = await verificationPage.getStatusBadgeText();
    expect(status.toLowerCase()).toContain('valid');
  });

  /**
   * TC-001 Edge Case: Lowercase Certificate ID
   */
  test('TC-001-edge: Lowercase certificate ID verifies correctly', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Enter certificate ID in lowercase
    const lowercaseId = testData.certificates.valid.csuid.toLowerCase();
    await verificationPage.verifyCertificate(lowercaseId);

    // Should still verify successfully
    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();
  });

  /**
   * TC-001 Edge Case: Certificate ID with spaces
   */
  test('TC-001-edge: Certificate ID with spaces trims and verifies', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Enter certificate ID with leading/trailing spaces
    const spacedId = `  ${testData.certificates.valid.csuid}  `;
    await verificationPage.verifyCertificate(spacedId);

    // Should still verify successfully
    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();
  });

  /**
   * TC-002: Invalid Certificate ID Format
   *
   * Invalid format Certificate IDs show appropriate error.
   */
  test('TC-002: Invalid certificate ID format shows error', async ({ page }) => {
    await verificationPage.gotoVerificationPage();

    // Test with "INVALID123" - wrong format
    await verificationPage.verifyCertificate('INVALID123');

    // Verify error state
    expect(await verificationPage.isVerificationError()).toBeTruthy();

    // Check error indicators
    await expect(verificationPage.errorIcon).toBeVisible();
    await expect(verificationPage.errorTitle).toBeVisible();

    // Check error message
    const errorMessage = await verificationPage.getErrorMessage();
    expect(errorMessage).toBeTruthy();

    // Check "Possible reasons" list
    const reasons = await verificationPage.getErrorReasons();
    expect(reasons.length).toBeGreaterThan(0);

    // Verify "Try Another Certificate" button
    await expect(verificationPage.tryAgainButton).toBeVisible();
  });

  /**
   * TC-002: Test various invalid ID formats
   */
  test.describe('TC-002: Invalid ID format variations', () => {
    for (const testCase of invalidCsuidCases.slice(0, 5)) {
      test(`Invalid ID: ${testCase.description}`, async ({ page }) => {
        const verificationPage = new VerificationPage(page);
        await verificationPage.gotoVerificationPage();

        if (testCase.input.trim() === '') {
          // Empty input - form should not submit
          await verificationPage.searchInput.fill(testCase.input);
          await verificationPage.verifyButton.click();

          // HTML5 validation should prevent submission
          // Result container should not be visible or should stay empty
          const resultVisible = await verificationPage.isVisible(verificationPage.resultContainer);
          if (resultVisible) {
            // If AJAX was somehow triggered, it should show error
            expect(await verificationPage.isVerificationError()).toBeTruthy();
          }
        } else {
          await verificationPage.verifyCertificate(testCase.input);
          expect(await verificationPage.isVerificationError()).toBeTruthy();
        }
      });
    }
  });

  /**
   * TC-006: Anonymous User Verification
   *
   * Anonymous users can verify certificates but cannot download.
   */
  test.describe('TC-006: Anonymous User Verification', () => {
    test.use({ storageState: { cookies: [], origins: [] } }); // No auth

    test('Anonymous user can verify certificate', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Verify certificate
      await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

      // Verification should succeed
      expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();

      // Certificate details should be visible
      const recipientName = await verificationPage.getRecipientName();
      expect(recipientName).toBeTruthy();
    });

    test('Anonymous user cannot see download buttons', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Verify certificate
      await verificationPage.verifyCertificate(testData.certificates.valid.csuid);

      // Verification should succeed
      expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();

      // Download buttons should NOT be visible
      expect(await verificationPage.areDownloadButtonsVisible()).toBeFalsy();
    });
  });

  /**
   * TC-007: Dual Certificate Tab Switching
   *
   * Tab switching between Standard and Pocket certificates works.
   */
  test('TC-007: Tab switching works for dual certificates', async ({ page }) => {
    expect(testData.certificates.validWithPocket?.csuid).toBeTruthy();

    await verificationPage.gotoVerificationPage();

    // Verify certificate with pocket cert
    await verificationPage.verifyCertificate(testData.certificates.validWithPocket.csuid);

    // Verification should succeed
    expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();

    // Tabs should be visible
    expect(await verificationPage.areTabsVisible()).toBeTruthy();

    // Standard tab should be active by default
    await expect(verificationPage.standardTab).toHaveClass(/active/);

    // Click pocket tab
    await verificationPage.switchToPocketTab();

    // Pocket tab should now be active
    await expect(verificationPage.pocketTab).toHaveClass(/active/);

    // URL should update with view parameter
    expect(verificationPage.getViewFromUrl()).toBe('pocket');

    // Switch back to standard
    await verificationPage.switchToStandardTab();

    // Standard tab should be active again
    await expect(verificationPage.standardTab).toHaveClass(/active/);

    // No page reload should have occurred (SPA-style switching)
  });

  /**
   * TC-007 Edge Case: URL view parameter pre-selects tab
   */
  test('TC-007-edge: URL view=pocket pre-selects pocket tab', async ({ page }) => {
    expect(testData.certificates.validWithPocket?.csuid).toBeTruthy();

    // Navigate with view parameter
    await verificationPage.gotoWithParams({
      cert_id: testData.certificates.validWithPocket.csuid,
      view: 'pocket',
    });

    // Wait for verification
    await verificationPage.waitForLoadingComplete();

    // Pocket tab should be active
    await expect(verificationPage.pocketTab).toHaveClass(/active/);
  });
});
