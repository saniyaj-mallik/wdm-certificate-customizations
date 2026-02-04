import { Page, Locator } from '@playwright/test';
import { BasePage } from './base.page';

/**
 * Course Editor Page Object
 *
 * Handles interactions with LearnDash course edit page.
 */
export class CourseEditorPage extends BasePage {
  // Page container
  readonly editorPage: Locator;
  readonly pageTitle: Locator;

  // Course title
  readonly courseTitleInput: Locator;

  // LearnDash settings metabox
  readonly settingsMetabox: Locator;

  // Certificate settings section
  readonly certificateSection: Locator;
  readonly certificateSelect: Locator;

  // Pocket certificate setting (from our plugin)
  readonly pocketCertificateSection: Locator;
  readonly pocketCertificateSelect: Locator;
  readonly pocketCertificateLabel: Locator;

  // Publish/Update button
  readonly publishButton: Locator;
  readonly updateButton: Locator;

  // Update notice
  readonly updateNotice: Locator;

  constructor(page: Page) {
    super(page);

    // Page elements
    this.editorPage = page.locator('#post, .edit-post-layout');
    this.pageTitle = page.locator('#title, .editor-post-title__input');

    // Course title
    this.courseTitleInput = page.locator('#title, .editor-post-title__input');

    // LearnDash settings
    this.settingsMetabox = page.locator('#sfwd-courses, .sfwd-courses-settings');

    // Certificate settings
    this.certificateSection = page.locator('.sfwd_option_sfwd-courses_certificate');
    this.certificateSelect = page.locator(
      '#sfwd-courses_certificate, select[name="_sfwd-courses[certificate]"]'
    );

    // Pocket certificate - matches class-admin.php render_pocket_certificate_metabox()
    this.pocketCertificateSection = page.locator('#wdm_pocket_certificate_metabox');
    this.pocketCertificateSelect = page.locator('#wdm_pocket_certificate');
    this.pocketCertificateLabel = page.locator('#wdm_pocket_certificate_metabox .postbox-header h2');

    // Publish/Update
    this.publishButton = page.locator('#publish');
    this.updateButton = page.locator('#publish, .editor-post-publish-button');

    // Update notice
    this.updateNotice = page.locator('#message, .updated, .notice-success');
  }

  /**
   * Navigate to course editor by ID
   */
  async gotoCourseEditor(courseId: number): Promise<void> {
    await this.goto(`/wp-admin/post.php?post=${courseId}&action=edit`);
    await this.waitForPageLoad();
  }

  /**
   * Check if course editor is loaded
   */
  async isCourseEditorLoaded(): Promise<boolean> {
    return this.isVisible(this.editorPage);
  }

  /**
   * Get course title
   */
  async getCourseTitle(): Promise<string> {
    return await this.courseTitleInput.inputValue();
  }

  /**
   * Set course title
   */
  async setCourseTitle(title: string): Promise<void> {
    await this.courseTitleInput.clear();
    await this.courseTitleInput.fill(title);
  }

  /**
   * Expand LearnDash settings metabox if collapsed
   */
  async expandSettingsMetabox(): Promise<void> {
    const isCollapsed = await this.settingsMetabox.getAttribute('class');
    if (isCollapsed?.includes('closed')) {
      await this.settingsMetabox.locator('.hndle, h2').click();
    }
  }

  /**
   * Get selected certificate
   */
  async getSelectedCertificate(): Promise<string> {
    return await this.certificateSelect.inputValue();
  }

  /**
   * Select certificate
   */
  async selectCertificate(certificateId: string): Promise<void> {
    await this.certificateSelect.selectOption(certificateId);
  }

  /**
   * Check if pocket certificate field is visible
   */
  async isPocketCertificateFieldVisible(): Promise<boolean> {
    return this.isVisible(this.pocketCertificateSelect);
  }

  /**
   * Get selected pocket certificate
   */
  async getSelectedPocketCertificate(): Promise<string> {
    if (!(await this.isPocketCertificateFieldVisible())) {
      return '';
    }
    return await this.pocketCertificateSelect.inputValue();
  }

  /**
   * Select pocket certificate
   */
  async selectPocketCertificate(certificateId: string): Promise<void> {
    if (!(await this.isPocketCertificateFieldVisible())) {
      throw new Error('Pocket certificate field is not visible');
    }
    await this.pocketCertificateSelect.selectOption(certificateId);
  }

  /**
   * Clear pocket certificate selection
   */
  async clearPocketCertificate(): Promise<void> {
    if (await this.isPocketCertificateFieldVisible()) {
      await this.pocketCertificateSelect.selectOption('');
    }
  }

  /**
   * Get pocket certificate options
   */
  async getPocketCertificateOptions(): Promise<string[]> {
    const options: string[] = [];
    const optionElements = this.pocketCertificateSelect.locator('option');
    const count = await optionElements.count();
    for (let i = 0; i < count; i++) {
      const text = await optionElements.nth(i).textContent();
      if (text) options.push(text.trim());
    }
    return options;
  }

  /**
   * Save/Update course
   */
  async saveCourse(): Promise<void> {
    await this.updateButton.click();
    await this.waitForPageLoad();
  }

  /**
   * Check if course was saved successfully
   */
  async isCourseSaved(): Promise<boolean> {
    return this.isVisible(this.updateNotice);
  }

  /**
   * Get update notice message
   */
  async getUpdateNoticeMessage(): Promise<string> {
    return (await this.updateNotice.textContent())?.trim() || '';
  }

  /**
   * Scroll to certificate settings
   */
  async scrollToCertificateSettings(): Promise<void> {
    await this.certificateSection.scrollIntoViewIfNeeded();
  }

  /**
   * Scroll to pocket certificate settings
   */
  async scrollToPocketCertificateSettings(): Promise<void> {
    if (await this.isPocketCertificateFieldVisible()) {
      await this.pocketCertificateSection.scrollIntoViewIfNeeded();
    }
  }
}

export default CourseEditorPage;
