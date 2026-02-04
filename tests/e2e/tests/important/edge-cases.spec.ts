import { test, expect } from '@playwright/test';
import { VerificationPage } from '../../pages/verification.page';
import { testData } from '../../fixtures/test-data';
import { authStatePaths } from '../../fixtures/users';

/**
 * Important Edge Case Tests
 *
 * TC-021: Certificate with Deleted User
 * TC-022: Certificate with Deleted Course
 * TC-028: Multiple Completions Same Course
 * TC-032: Network Error Handling
 */

test.describe('Edge Cases - Important', () => {
  /**
   * TC-021: Certificate with Deleted User
   *
   * Certificate for deleted user shows appropriate error.
   */
  test.describe('TC-021: Deleted User', () => {
    test.skip('Certificate for deleted user shows error', async ({ page }) => {
      // This test requires:
      // 1. Creating a certificate record
      // 2. Deleting the user
      // 3. Trying to verify the certificate

      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Use a CSUID for a deleted user (would need test setup)
      // await verificationPage.verifyCertificate('DELETED-USER-CSUID');

      // Should show error
      // expect(await verificationPage.isVerificationError()).toBeTruthy();
      // const errorMessage = await verificationPage.getErrorMessage();
      // expect(errorMessage.toLowerCase()).toContain('recipient not found');
    });
  });

  /**
   * TC-022: Certificate with Deleted Course
   *
   * Certificate for deleted course shows appropriate error.
   */
  test.describe('TC-022: Deleted Course', () => {
    test.skip('Certificate for deleted course shows error', async ({ page }) => {
      // This test requires:
      // 1. Creating a certificate record for a course
      // 2. Deleting the course
      // 3. Trying to verify the certificate

      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Use a CSUID for a deleted course (would need test setup)
      // await verificationPage.verifyCertificate('DELETED-COURSE-CSUID');

      // Should show error
      // expect(await verificationPage.isVerificationError()).toBeTruthy();
      // const errorMessage = await verificationPage.getErrorMessage();
      // expect(errorMessage.toLowerCase()).toContain('source not found');
    });
  });

  /**
   * TC-028: Multiple Completions Same Course
   *
   * Re-completing course doesn't duplicate certificate record.
   */
  test.describe('TC-028: Multiple Completions', () => {
    test.use({ storageState: authStatePaths.student });

    test.skip('Re-completion does not create duplicate record', async ({ page }) => {
      // This test requires:
      // 1. Student completes course
      // 2. Certificate record is created with CSUID
      // 3. Student's progress is reset
      // 4. Student completes course again
      // 5. Verify only one certificate record exists

      // Would need WP-CLI or database access to:
      // - Reset course progress
      // - Mark course complete
      // - Check user meta for duplicate records
    });
  });

  /**
   * TC-032: Network Error Handling
   *
   * Network errors during AJAX show appropriate message.
   */
  test.describe('TC-032: Network Errors', () => {
    test('Network error shows user-friendly message', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Block the AJAX endpoint
      await page.route('**/admin-ajax.php', (route) => {
        route.abort('failed');
      });

      // Try to verify
      await verificationPage.searchInput.fill(testData.certificates.valid.csuid);
      await verificationPage.verifyButton.click();

      // Wait for error handling
      await page.waitForTimeout(2000);

      // Should show some error state
      // Either error container or button re-enabled
      const hasError = await verificationPage.isVerificationError();
      const buttonEnabled = await verificationPage.verifyButton.isEnabled();

      // At minimum, button should be re-enabled for retry
      expect(hasError || buttonEnabled).toBeTruthy();
    });

    test('Button re-enables after network error', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Block AJAX
      await page.route('**/admin-ajax.php', (route) => {
        route.abort('connectionrefused');
      });

      // Try to verify
      await verificationPage.searchInput.fill('TEST-NETWORK-ERROR');
      await verificationPage.verifyButton.click();

      // Wait for error handling
      await page.waitForTimeout(2000);

      // Button should be re-enabled for retry
      await expect(verificationPage.verifyButton).toBeEnabled();
    });

    test('No console errors crash the page after network error', async ({ page }) => {
      const consoleErrors: string[] = [];
      page.on('console', (msg) => {
        if (msg.type() === 'error') {
          consoleErrors.push(msg.text());
        }
      });

      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Block AJAX
      await page.route('**/admin-ajax.php', (route) => {
        route.abort('failed');
      });

      // Try to verify
      await verificationPage.searchInput.fill('TEST-NETWORK-ERROR');
      await verificationPage.verifyButton.click();

      await page.waitForTimeout(2000);

      // Filter out expected network errors
      const unexpectedErrors = consoleErrors.filter(
        (err) =>
          !err.includes('net::ERR') &&
          !err.includes('Failed to fetch') &&
          !err.includes('NetworkError')
      );

      // Should have no unexpected JavaScript errors
      expect(unexpectedErrors.length).toBe(0);
    });
  });

  /**
   * Additional edge case: Verify with special characters
   */
  test.describe('Special Characters in Input', () => {
    test('Special characters in input are handled safely', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Try XSS-like input
      await verificationPage.verifyCertificate('<script>alert("xss")</script>');

      // Should show error (invalid format), not execute script
      expect(await verificationPage.isVerificationError()).toBeTruthy();

      // Page should not have been compromised
      // Check that script tags are not rendered
      const scripts = await page.locator('script:has-text("xss")').count();
      expect(scripts).toBe(0);
    });

    test('SQL injection attempts are handled safely', async ({ page }) => {
      const verificationPage = new VerificationPage(page);
      await verificationPage.gotoVerificationPage();

      // Try SQL injection-like input
      await verificationPage.verifyCertificate("'; DROP TABLE users; --");

      // Should show error (invalid format)
      expect(await verificationPage.isVerificationError()).toBeTruthy();
    });
  });
});
