import { Page, Locator } from '@playwright/test';

/**
 * Base Page Object
 *
 * Provides common functionality for all page objects.
 */
export class BasePage {
  readonly page: Page;
  readonly baseUrl: string;

  constructor(page: Page) {
    this.page = page;
    this.baseUrl = process.env.WP_BASE_URL || 'http://localhost:10003';
  }

  /**
   * Navigate to a URL (relative or absolute)
   */
  async goto(path: string): Promise<void> {
    const url = path.startsWith('http') ? path : `${this.baseUrl}${path}`;
    await this.page.goto(url);
  }

  /**
   * Wait for page to be fully loaded
   */
  async waitForPageLoad(): Promise<void> {
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Get the current page URL
   */
  getCurrentUrl(): string {
    return this.page.url();
  }

  /**
   * Check if an element is visible
   */
  async isVisible(locator: Locator): Promise<boolean> {
    try {
      await locator.waitFor({ state: 'visible', timeout: 5000 });
      return true;
    } catch {
      return false;
    }
  }

  /**
   * Wait for AJAX request to complete
   */
  async waitForAjax(urlPattern: string | RegExp = /admin-ajax\.php/): Promise<void> {
    await this.page.waitForResponse(
      (response) =>
        (typeof urlPattern === 'string'
          ? response.url().includes(urlPattern)
          : urlPattern.test(response.url())) && response.status() === 200
    );
  }

  /**
   * Wait for WordPress notice
   */
  async waitForNotice(type: 'success' | 'error' | 'warning' | 'info' = 'success'): Promise<Locator> {
    const selector = `.notice-${type}, .notice.is-dismissible`;
    await this.page.waitForSelector(selector);
    return this.page.locator(selector).first();
  }

  /**
   * Dismiss WordPress notice
   */
  async dismissNotice(): Promise<void> {
    const dismissButton = this.page.locator('.notice-dismiss');
    if (await this.isVisible(dismissButton)) {
      await dismissButton.click();
    }
  }

  /**
   * Take a screenshot
   */
  async screenshot(name: string): Promise<void> {
    await this.page.screenshot({ path: `test-results/screenshots/${name}.png`, fullPage: true });
  }
}

export default BasePage;
