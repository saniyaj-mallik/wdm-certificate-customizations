import { Page, Locator } from '@playwright/test';
import { BasePage } from './base.page';

/**
 * WordPress Login Page Object
 */
export class LoginPage extends BasePage {
  readonly usernameInput: Locator;
  readonly passwordInput: Locator;
  readonly loginButton: Locator;
  readonly rememberMeCheckbox: Locator;
  readonly loginError: Locator;
  readonly lostPasswordLink: Locator;

  constructor(page: Page) {
    super(page);
    this.usernameInput = page.locator('#user_login');
    this.passwordInput = page.locator('#user_pass');
    this.loginButton = page.locator('#wp-submit');
    this.rememberMeCheckbox = page.locator('#rememberme');
    this.loginError = page.locator('#login_error');
    this.lostPasswordLink = page.locator('#nav a');
  }

  /**
   * Navigate to WordPress login page
   */
  async gotoLoginPage(): Promise<void> {
    await this.goto('/wp-login.php');
  }

  /**
   * Perform login
   */
  async login(username: string, password: string, rememberMe: boolean = false): Promise<void> {
    await this.gotoLoginPage();

    await this.usernameInput.fill(username);
    await this.passwordInput.fill(password);

    if (rememberMe) {
      await this.rememberMeCheckbox.check();
    }

    await this.loginButton.click();
    await this.page.waitForURL(/wp-admin|dashboard/);
  }

  /**
   * Check if logged in
   */
  async isLoggedIn(): Promise<boolean> {
    return this.page.url().includes('wp-admin') || (await this.page.locator('#wpadminbar').isVisible());
  }

  /**
   * Check if login error is displayed
   */
  async hasLoginError(): Promise<boolean> {
    return this.isVisible(this.loginError);
  }

  /**
   * Get login error message
   */
  async getLoginErrorMessage(): Promise<string> {
    if (await this.hasLoginError()) {
      return (await this.loginError.textContent()) || '';
    }
    return '';
  }

  /**
   * Logout
   */
  async logout(): Promise<void> {
    // Hover over admin bar user menu
    await this.page.locator('#wp-admin-bar-my-account').hover();
    // Click logout link
    await this.page.locator('#wp-admin-bar-logout a').click();
    await this.page.waitForURL(/wp-login\.php/);
  }
}

export default LoginPage;
