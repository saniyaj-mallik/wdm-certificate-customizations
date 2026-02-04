import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './base.page';
import testData from '../fixtures/test-data';

/**
 * Certificate Verification Page Object
 *
 * Handles interactions with the public certificate verification page.
 */
export class VerificationPage extends BasePage {
  // Search form elements
  readonly searchForm: Locator;
  readonly searchInput: Locator;
  readonly verifyButton: Locator;
  readonly loadingSpinner: Locator;

  // Result container elements
  readonly resultContainer: Locator;
  readonly successContainer: Locator;
  readonly errorContainer: Locator;

  // Success result elements
  readonly successIcon: Locator;
  readonly verifiedTitle: Locator;
  readonly certificateIdDisplay: Locator;
  readonly recipientName: Locator;
  readonly sourceTitle: Locator;
  readonly completionDate: Locator;
  readonly statusBadge: Locator;

  // Tab elements
  readonly tabsContainer: Locator;
  readonly standardTab: Locator;
  readonly pocketTab: Locator;

  // Certificate preview
  readonly previewContainer: Locator;
  readonly certificateIframe: Locator;

  // Download buttons
  readonly downloadStandardBtn: Locator;
  readonly downloadPocketBtn: Locator;

  // Error elements
  readonly errorIcon: Locator;
  readonly errorTitle: Locator;
  readonly errorMessage: Locator;
  readonly errorReasonsList: Locator;
  readonly tryAgainButton: Locator;

  constructor(page: Page) {
    super(page);

    // Search form - matches templates/search-form.php
    this.searchForm = page.locator('#wdm-cert-search-form');
    this.searchInput = page.locator('#wdm-cert-id-input');
    this.verifyButton = page.locator('.wdm-cert-verify-btn');
    // Use .first() to avoid strict mode violation when both spinners are present
    this.loadingSpinner = page.locator('.wdm-cert-btn-loading, .wdm-cert-spinner').first();

    // Result containers - matches templates/verification-result.php and verification-error.php
    this.resultContainer = page.locator('.wdm-cert-verified-container, .wdm-cert-error-container');
    this.successContainer = page.locator('.wdm-cert-verified-container');
    this.errorContainer = page.locator('.wdm-cert-error-container');

    // Success elements - matches templates/verification-result.php
    this.successIcon = page.locator('.wdm-cert-verified-icon');
    this.verifiedTitle = page.locator('.wdm-cert-verified-title');
    this.certificateIdDisplay = page.locator('.wdm-cert-csuid');
    this.recipientName = page.locator('.wdm-cert-detail-item:has(.wdm-cert-detail-label:text("Recipient")) .wdm-cert-detail-value');
    this.sourceTitle = page.locator('.wdm-cert-detail-item:nth-child(3) .wdm-cert-detail-value a');
    this.completionDate = page.locator('.wdm-cert-detail-item:has(.wdm-cert-detail-label:text("Completed")) .wdm-cert-detail-value');
    this.statusBadge = page.locator('.wdm-cert-status-valid');

    // Tabs - matches templates/verification-result.php
    this.tabsContainer = page.locator('.wdm-cert-tabs');
    this.standardTab = page.locator('.wdm-cert-tab[data-view="standard"]');
    this.pocketTab = page.locator('.wdm-cert-tab[data-view="pocket"]');

    // Preview - matches templates/verification-result.php
    this.previewContainer = page.locator('.wdm-cert-preview-container');
    this.certificateIframe = page.locator('.wdm-cert-iframe');

    // Download buttons - matches templates/verification-result.php
    this.downloadStandardBtn = page.locator('.wdm-cert-download-standard');
    this.downloadPocketBtn = page.locator('.wdm-cert-download-pocket');

    // Error elements - matches templates/verification-error.php
    this.errorIcon = page.locator('.wdm-cert-error-icon');
    this.errorTitle = page.locator('.wdm-cert-error-title');
    this.errorMessage = page.locator('.wdm-cert-error-message');
    this.errorReasonsList = page.locator('.wdm-cert-error-reasons ul');
    this.tryAgainButton = page.locator('.wdm-cert-try-again-btn');
  }

  /**
   * Navigate to verification page
   */
  async gotoVerificationPage(certId?: string): Promise<void> {
    let url = testData.verificationPage.url;
    if (certId) {
      url = `${url}?cert_id=${certId}`;
    }
    await this.goto(url);
    await this.waitForPageLoad();
  }

  /**
   * Navigate with URL parameter
   */
  async gotoWithParams(params: Record<string, string>): Promise<void> {
    const queryString = new URLSearchParams(params).toString();
    await this.goto(`${testData.verificationPage.url}?${queryString}`);
    await this.waitForPageLoad();
  }

  /**
   * Enter certificate ID and submit
   */
  async verifyCertificate(certId: string): Promise<void> {
    await this.searchInput.clear();
    await this.searchInput.fill(certId);
    await this.verifyButton.click();

    // Wait for AJAX response
    await this.page.waitForResponse(
      (response) =>
        response.url().includes('admin-ajax.php') &&
        response.request().postData()?.includes('wdm_cert_verify')
    );
  }

  /**
   * Check if verification was successful
   */
  async isVerificationSuccessful(): Promise<boolean> {
    return this.isVisible(this.successContainer);
  }

  /**
   * Check if verification resulted in error
   */
  async isVerificationError(): Promise<boolean> {
    return this.isVisible(this.errorContainer);
  }

  /**
   * Get the recipient name from verification result
   */
  async getRecipientName(): Promise<string> {
    await this.successContainer.waitFor({ state: 'visible' });
    return (await this.recipientName.textContent())?.trim() || '';
  }

  /**
   * Get the source/course title from verification result
   */
  async getSourceTitle(): Promise<string> {
    return (await this.sourceTitle.textContent())?.trim() || '';
  }

  /**
   * Get the completion date from verification result
   */
  async getCompletionDate(): Promise<string> {
    return (await this.completionDate.textContent())?.trim() || '';
  }

  /**
   * Get the displayed certificate ID
   */
  async getDisplayedCertificateId(): Promise<string> {
    return (await this.certificateIdDisplay.textContent())?.trim() || '';
  }

  /**
   * Get the status badge text
   */
  async getStatusBadgeText(): Promise<string> {
    return (await this.statusBadge.textContent())?.trim() || '';
  }

  /**
   * Check if tabs are visible (dual certificate)
   */
  async areTabsVisible(): Promise<boolean> {
    return this.isVisible(this.tabsContainer);
  }

  /**
   * Switch to standard certificate tab
   */
  async switchToStandardTab(): Promise<void> {
    await this.standardTab.click();
  }

  /**
   * Switch to pocket certificate tab
   */
  async switchToPocketTab(): Promise<void> {
    await this.pocketTab.click();
  }

  /**
   * Get the active tab
   */
  async getActiveTab(): Promise<'standard' | 'pocket' | null> {
    if (await this.standardTab.getAttribute('class')?.then((c) => c?.includes('active'))) {
      return 'standard';
    }
    if (await this.pocketTab.getAttribute('class')?.then((c) => c?.includes('active'))) {
      return 'pocket';
    }
    return null;
  }

  /**
   * Check if download buttons are visible
   */
  async areDownloadButtonsVisible(): Promise<boolean> {
    return this.isVisible(this.downloadStandardBtn);
  }

  /**
   * Click download standard certificate
   */
  async downloadStandardCertificate(): Promise<void> {
    await this.downloadStandardBtn.click();
  }

  /**
   * Click download pocket certificate
   */
  async downloadPocketCertificate(): Promise<void> {
    await this.downloadPocketBtn.click();
  }

  /**
   * Check if certificate iframe is loaded
   */
  async isCertificatePreviewLoaded(): Promise<boolean> {
    await this.certificateIframe.waitFor({ state: 'attached' });
    const src = await this.certificateIframe.getAttribute('src');
    return src !== null && src !== '';
  }

  /**
   * Get the certificate iframe source URL
   */
  async getCertificatePreviewUrl(): Promise<string> {
    return (await this.certificateIframe.getAttribute('src')) || '';
  }

  /**
   * Get error message text
   */
  async getErrorMessage(): Promise<string> {
    await this.errorContainer.waitFor({ state: 'visible' });
    return (await this.errorMessage.textContent())?.trim() || '';
  }

  /**
   * Get error reasons list
   */
  async getErrorReasons(): Promise<string[]> {
    // Wait for error container to be visible first
    await this.errorContainer.waitFor({ state: 'visible' });
    const reasons: string[] = [];
    const items = this.errorReasonsList.locator('li');
    const count = await items.count();
    for (let i = 0; i < count; i++) {
      const text = await items.nth(i).textContent();
      if (text) reasons.push(text.trim());
    }
    return reasons;
  }

  /**
   * Click try again button
   */
  async clickTryAgain(): Promise<void> {
    await this.tryAgainButton.click();
  }

  /**
   * Check if loading spinner is visible
   */
  async isLoading(): Promise<boolean> {
    return this.isVisible(this.loadingSpinner);
  }

  /**
   * Wait for loading to complete
   */
  async waitForLoadingComplete(): Promise<void> {
    await this.loadingSpinner.waitFor({ state: 'hidden', timeout: 10000 });
  }

  /**
   * Get current URL parameters
   */
  getUrlParams(): URLSearchParams {
    const url = new URL(this.page.url());
    return url.searchParams;
  }

  /**
   * Check if cert_id is in URL
   */
  isCertIdInUrl(): boolean {
    const params = this.getUrlParams();
    return params.has('cert_id');
  }

  /**
   * Get cert_id from URL
   */
  getCertIdFromUrl(): string | null {
    const params = this.getUrlParams();
    return params.get('cert_id');
  }

  /**
   * Get view parameter from URL
   */
  getViewFromUrl(): string | null {
    const params = this.getUrlParams();
    return params.get('view');
  }
}

export default VerificationPage;
