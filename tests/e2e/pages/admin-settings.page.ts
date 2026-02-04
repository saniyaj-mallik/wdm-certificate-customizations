import { Page, Locator } from '@playwright/test';
import { BasePage } from './base.page';

/**
 * Admin Settings Page Object
 *
 * Handles interactions with the WDM Certificate Customizations settings page.
 */
export class AdminSettingsPage extends BasePage {
  // Page container
  readonly settingsPage: Locator;
  readonly pageTitle: Locator;

  // Settings form
  readonly settingsForm: Locator;
  readonly saveButton: Locator;
  readonly submitNotice: Locator;

  // Verification page setting
  readonly verificationPageSelect: Locator;
  readonly verificationPageLabel: Locator;

  // Pocket certificate setting
  readonly pocketCertCheckbox: Locator;
  readonly pocketCertLabel: Locator;

  // QR code size setting
  readonly qrCodeSizeInput: Locator;
  readonly qrCodeSizeLabel: Locator;

  // Retroactive generation
  readonly retroactiveButton: Locator;
  readonly retroactiveProgress: Locator;
  readonly retroactiveResult: Locator;

  // Shortcode reference
  readonly shortcodeReference: Locator;

  // Admin notices
  readonly adminNotice: Locator;
  readonly noticeSuccess: Locator;
  readonly noticeError: Locator;
  readonly noticeWarning: Locator;

  constructor(page: Page) {
    super(page);

    // Page elements - matches includes/class-admin.php render_settings_page()
    this.settingsPage = page.locator('.wdm-cert-settings-wrap');
    this.pageTitle = page.locator('.wdm-cert-settings-wrap h1');

    // Form elements
    this.settingsForm = page.locator('.wdm-cert-settings-wrap form');
    this.saveButton = page.locator('#submit');
    this.submitNotice = page.locator('.notice-success, .updated');

    // Verification page - matches render_verification_page_field()
    this.verificationPageSelect = page.locator('#wdm_cert_verification_page');
    this.verificationPageLabel = page.locator('label[for="wdm_cert_verification_page"]');

    // Pocket certificate - matches render_enable_pocket_field()
    this.pocketCertCheckbox = page.locator('input[name="wdm_certificate_options[enable_pocket_certificate]"]');
    this.pocketCertLabel = page.locator('input[name="wdm_certificate_options[enable_pocket_certificate]"]').locator('..');

    // QR code size - matches render_qr_size_field()
    this.qrCodeSizeInput = page.locator('input[name="wdm_certificate_options[qr_code_size]"]');
    this.qrCodeSizeLabel = page.locator('input[name="wdm_certificate_options[qr_code_size]"]').locator('..');

    // Retroactive generation - matches render_retroactive_section()
    this.retroactiveButton = page.locator('#wdm-cert-generate-retroactive');
    this.retroactiveProgress = page.locator('#wdm-cert-retroactive-status');
    this.retroactiveResult = page.locator('#wdm-cert-retroactive-status');

    // Shortcode reference - the table with shortcodes
    this.shortcodeReference = page.locator('table.widefat');

    // Admin notices
    this.adminNotice = page.locator('.notice');
    this.noticeSuccess = page.locator('.notice-success');
    this.noticeError = page.locator('.notice-error');
    this.noticeWarning = page.locator('.notice-warning');
  }

  /**
   * Navigate to plugin settings page
   */
  async gotoSettingsPage(): Promise<void> {
    await this.goto('/wp-admin/admin.php?page=wdm-certificate-settings');
    await this.waitForPageLoad();
  }

  /**
   * Navigate via admin menu
   */
  async navigateViaMenu(): Promise<void> {
    // Click LearnDash menu
    await this.page.locator('#adminmenu a:has-text("LearnDash LMS")').click();
    // Click Certificate Customizations submenu
    await this.page.locator('#adminmenu a:has-text("Certificate Customizations")').click();
    await this.waitForPageLoad();
  }

  /**
   * Check if settings page is loaded
   */
  async isSettingsPageLoaded(): Promise<boolean> {
    return this.isVisible(this.settingsPage);
  }

  /**
   * Get page title
   */
  async getPageTitle(): Promise<string> {
    return (await this.pageTitle.textContent())?.trim() || '';
  }

  /**
   * Get selected verification page
   */
  async getSelectedVerificationPage(): Promise<string> {
    return await this.verificationPageSelect.inputValue();
  }

  /**
   * Select verification page
   */
  async selectVerificationPage(pageId: string): Promise<void> {
    await this.verificationPageSelect.selectOption(pageId);
  }

  /**
   * Check if pocket certificate is enabled
   */
  async isPocketCertEnabled(): Promise<boolean> {
    return await this.pocketCertCheckbox.isChecked();
  }

  /**
   * Enable pocket certificate
   */
  async enablePocketCert(): Promise<void> {
    if (!(await this.isPocketCertEnabled())) {
      await this.pocketCertCheckbox.check();
    }
  }

  /**
   * Disable pocket certificate
   */
  async disablePocketCert(): Promise<void> {
    if (await this.isPocketCertEnabled()) {
      await this.pocketCertCheckbox.uncheck();
    }
  }

  /**
   * Get QR code size
   */
  async getQrCodeSize(): Promise<number> {
    const value = await this.qrCodeSizeInput.inputValue();
    return parseInt(value) || 150;
  }

  /**
   * Set QR code size
   */
  async setQrCodeSize(size: number): Promise<void> {
    await this.qrCodeSizeInput.clear();
    await this.qrCodeSizeInput.fill(size.toString());
  }

  /**
   * Save settings
   */
  async saveSettings(): Promise<void> {
    await this.saveButton.click();
    await this.waitForPageLoad();
  }

  /**
   * Check if settings saved successfully
   */
  async isSettingsSaved(): Promise<boolean> {
    return this.isVisible(this.submitNotice);
  }

  /**
   * Click retroactive generation button
   */
  async clickRetroactiveGeneration(): Promise<void> {
    await this.retroactiveButton.click();
  }

  /**
   * Confirm retroactive generation dialog
   */
  async confirmRetroactiveGeneration(): Promise<void> {
    // Handle browser dialog
    this.page.on('dialog', async (dialog) => {
      await dialog.accept();
    });
    await this.clickRetroactiveGeneration();
  }

  /**
   * Wait for retroactive generation to complete
   */
  async waitForRetroactiveComplete(): Promise<void> {
    await this.retroactiveResult.waitFor({ state: 'visible', timeout: 60000 });
  }

  /**
   * Get retroactive generation result text
   */
  async getRetroactiveResult(): Promise<string> {
    return (await this.retroactiveResult.textContent())?.trim() || '';
  }

  /**
   * Check if retroactive progress is visible
   */
  async isRetroactiveInProgress(): Promise<boolean> {
    return this.isVisible(this.retroactiveProgress);
  }

  /**
   * Check if shortcode reference is visible
   */
  async isShortcodeReferenceVisible(): Promise<boolean> {
    return this.isVisible(this.shortcodeReference);
  }

  /**
   * Get all shortcodes listed in reference
   */
  async getShortcodesInReference(): Promise<string[]> {
    const codes: string[] = [];
    const codeElements = this.shortcodeReference.locator('code');
    const count = await codeElements.count();
    for (let i = 0; i < count; i++) {
      const text = await codeElements.nth(i).textContent();
      if (text) codes.push(text.trim());
    }
    return codes;
  }

  /**
   * Check for admin notice
   */
  async hasAdminNotice(type?: 'success' | 'error' | 'warning'): Promise<boolean> {
    if (type === 'success') return this.isVisible(this.noticeSuccess);
    if (type === 'error') return this.isVisible(this.noticeError);
    if (type === 'warning') return this.isVisible(this.noticeWarning);
    return this.isVisible(this.adminNotice);
  }

  /**
   * Get admin notice message
   */
  async getAdminNoticeMessage(): Promise<string> {
    const notice = this.adminNotice.first();
    return (await notice.textContent())?.trim() || '';
  }

  /**
   * Dismiss admin notice
   */
  async dismissAdminNotice(): Promise<void> {
    const dismissBtn = this.adminNotice.locator('.notice-dismiss');
    if (await this.isVisible(dismissBtn)) {
      await dismissBtn.click();
    }
  }
}

export default AdminSettingsPage;
