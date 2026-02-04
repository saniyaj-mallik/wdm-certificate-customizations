import { test as setup, expect } from '@playwright/test';
import { users, authStatePaths } from './fixtures/users';
import { LoginPage } from './pages/login.page';

/**
 * Authentication Setup
 *
 * Creates authenticated storage states for different user roles.
 * These are used by test projects to avoid logging in for every test.
 */

// Setup admin authentication
setup('authenticate as admin', async ({ page }) => {
  const loginPage = new LoginPage(page);

  await loginPage.login(users.admin.username, users.admin.password);

  // Verify login was successful
  expect(await loginPage.isLoggedIn()).toBeTruthy();

  // Save authentication state
  await page.context().storageState({ path: authStatePaths.admin });
});

// Setup student authentication
setup('authenticate as student', async ({ page }) => {
  const loginPage = new LoginPage(page);

  await loginPage.login(users.student.username, users.student.password);

  // Verify login was successful
  expect(await loginPage.isLoggedIn()).toBeTruthy();

  // Save authentication state
  await page.context().storageState({ path: authStatePaths.student });
});

// Setup student2 authentication (for non-owner tests)
setup('authenticate as student2', async ({ page }) => {
  const loginPage = new LoginPage(page);

  await loginPage.login(users.student2.username, users.student2.password);

  // Verify login was successful
  expect(await loginPage.isLoggedIn()).toBeTruthy();

  // Save authentication state
  await page.context().storageState({ path: authStatePaths.student2 });
});
