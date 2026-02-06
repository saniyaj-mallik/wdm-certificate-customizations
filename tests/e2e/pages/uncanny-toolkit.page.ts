import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './base.page';

/**
 * Uncanny Toolkit Admin Page Object
 *
 * Handles interactions with Uncanny Toolkit settings and modules.
 */
export class UncannyToolkitPage extends BasePage {
  // Main settings page elements
  readonly moduleCards: Locator;
  readonly courseCompletionDateModule: Locator;
  readonly courseResetModule: Locator;
  readonly multipleCertificatesModule: Locator;

  // Module toggle switches
  readonly moduleToggle: (moduleName: string) => Locator;

  // Settings modal
  readonly settingsModal: Locator;
  readonly saveSettingsBtn: Locator;
  readonly closeModalBtn: Locator;

  constructor(page: Page) {
    super(page);

    // Module cards on main settings page
    this.moduleCards = page.locator('.uo-module, .uo-pro-module, .uo-custom-module');
    this.courseCompletionDateModule = page.locator('.uo-module:has-text("Course Completion Date")');
    this.courseResetModule = page.locator('.uo-module:has-text("Course Reset")');
    this.multipleCertificatesModule = page.locator('.uo-module:has-text("Multiple Page Certificates")');

    // Toggle switch factory
    this.moduleToggle = (moduleName: string) =>
      page.locator(`.uo-module:has-text("${moduleName}") .uo-toggle, .uo-module:has-text("${moduleName}") input[type="checkbox"]`);

    // Settings modal elements
    this.settingsModal = page.locator('.uo-settings-modal, .uncanny-modal');
    this.saveSettingsBtn = page.locator('.uo-save-settings, button:has-text("Save")');
    this.closeModalBtn = page.locator('.uo-close-modal, .modal-close');
  }

  /**
   * Navigate to Uncanny Toolkit settings page
   */
  async gotoSettings(): Promise<void> {
    await this.goto('/wp-admin/admin.php?page=uncanny-toolkit');
    await this.waitForPageLoad();
  }

  /**
   * Check if a module is active
   */
  async isModuleActive(moduleName: string): Promise<boolean> {
    const moduleCard = this.page.locator(`.uo-module:has-text("${moduleName}")`);
    if (!(await moduleCard.isVisible())) {
      return false;
    }

    // Check for active class or toggle state
    const hasActiveClass = await moduleCard.getAttribute('class');
    if (hasActiveClass?.includes('active') || hasActiveClass?.includes('enabled')) {
      return true;
    }

    // Check toggle state
    const toggle = this.moduleToggle(moduleName);
    if (await toggle.isVisible()) {
      return await toggle.isChecked();
    }

    return false;
  }

  /**
   * Toggle a module on/off
   */
  async toggleModule(moduleName: string, enable: boolean): Promise<void> {
    const toggle = this.moduleToggle(moduleName);
    const isCurrentlyEnabled = await toggle.isChecked();

    if (isCurrentlyEnabled !== enable) {
      await toggle.click();
      // Wait for AJAX save
      await this.page.waitForTimeout(1000);
    }
  }

  /**
   * Open module settings
   */
  async openModuleSettings(moduleName: string): Promise<void> {
    const moduleCard = this.page.locator(`.uo-module:has-text("${moduleName}")`);
    const settingsBtn = moduleCard.locator('.uo-settings-btn, .settings-icon, button:has-text("Settings")');

    if (await settingsBtn.isVisible()) {
      await settingsBtn.click();
      await this.settingsModal.waitFor({ state: 'visible' });
    }
  }

  /**
   * Save module settings
   */
  async saveSettings(): Promise<void> {
    await this.saveSettingsBtn.click();
    await this.waitForAjax();
  }

  /**
   * Close settings modal
   */
  async closeSettings(): Promise<void> {
    await this.closeModalBtn.click();
    await this.settingsModal.waitFor({ state: 'hidden' });
  }

  /**
   * Get list of active modules
   */
  async getActiveModules(): Promise<string[]> {
    const activeModules: string[] = [];
    const modules = await this.moduleCards.all();

    for (const module of modules) {
      const title = await module.locator('.uo-module-title, h3, h4').textContent();
      const isActive = await module.getAttribute('class');

      if (title && (isActive?.includes('active') || isActive?.includes('enabled'))) {
        activeModules.push(title.trim());
      }
    }

    return activeModules;
  }

  /**
   * Check if Multiple Certificates module is disabled
   * (This should be disabled when using WDM Certificate plugin)
   */
  async isMultipleCertificatesDisabled(): Promise<boolean> {
    const module = this.multipleCertificatesModule;

    if (!(await module.isVisible())) {
      // Module not found - could be completely removed
      return true;
    }

    return !(await this.isModuleActive('Multiple Page Certificates'));
  }
}

/**
 * User Profile Page Object (for Uncanny course date features)
 */
export class UserProfilePage extends BasePage {
  readonly userIdInput: Locator;
  readonly courseInfoSection: Locator;
  readonly completionDateFields: Locator;
  readonly enrollmentDateFields: Locator;
  readonly resetLinks: Locator;
  readonly updateButton: Locator;

  constructor(page: Page) {
    super(page);

    this.userIdInput = page.locator('#user_id, input[name="user_id"]');
    this.courseInfoSection = page.locator('#learndash-course-progress, .learndash-course-info, .ld-user-course-info');
    this.completionDateFields = page.locator('input[name*="completion_date"], input[name*="course_completed"]');
    this.enrollmentDateFields = page.locator('input[name*="enrollment"], input[name*="access_from"]');
    this.resetLinks = page.locator('a[href*="uo_course_reset"], a:has-text("(reset)")');
    this.updateButton = page.locator('#submit, input[type="submit"][value*="Update"]');
  }

  /**
   * Navigate to user profile edit page
   */
  async gotoUserProfile(userId: number): Promise<void> {
    await this.goto(`/wp-admin/user-edit.php?user_id=${userId}`);
    await this.waitForPageLoad();
  }

  /**
   * Check if course completion date fields are present
   */
  async hasCompletionDateFields(): Promise<boolean> {
    return (await this.completionDateFields.count()) > 0;
  }

  /**
   * Check if enrollment date fields are present
   */
  async hasEnrollmentDateFields(): Promise<boolean> {
    return (await this.enrollmentDateFields.count()) > 0;
  }

  /**
   * Check if reset links are present
   */
  async hasResetLinks(): Promise<boolean> {
    return (await this.resetLinks.count()) > 0;
  }

  /**
   * Get count of reset links (one per enrolled course)
   */
  async getResetLinksCount(): Promise<number> {
    return await this.resetLinks.count();
  }

  /**
   * Click reset link for first course
   */
  async clickFirstResetLink(): Promise<void> {
    await this.resetLinks.first().click();
  }

  /**
   * Update user profile
   */
  async updateProfile(): Promise<void> {
    await this.updateButton.click();
    await this.waitForPageLoad();
  }

  /**
   * Set completion date for a course
   */
  async setCompletionDate(courseIndex: number, date: string): Promise<void> {
    const field = this.completionDateFields.nth(courseIndex);
    if (await field.isVisible()) {
      await field.clear();
      await field.fill(date);
    }
  }

  /**
   * Set enrollment date for a course
   */
  async setEnrollmentDate(courseIndex: number, date: string): Promise<void> {
    const field = this.enrollmentDateFields.nth(courseIndex);
    if (await field.isVisible()) {
      await field.clear();
      await field.fill(date);
    }
  }
}

export { UncannyToolkitPage, UserProfilePage };
