import { defineConfig, devices } from '@playwright/test';
import dotenv from 'dotenv';
import path from 'path';

// Load environment variables
dotenv.config({ path: path.resolve(__dirname, '.env') });

export default defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['html', { outputFolder: 'playwright-report' }],
    ['list'],
    ['json', { outputFile: 'test-results/results.json' }]
  ],

  use: {
    baseURL: process.env.WP_BASE_URL || 'http://localhost:10003',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'on-first-retry',
    actionTimeout: 15000,
    navigationTimeout: 30000,
  },

  projects: [
    // Setup project for authentication
    {
      name: 'setup',
      testDir: './',
      testMatch: /.*\.setup\.ts/,
    },

    // Desktop Chrome
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/admin.json',
      },
      dependencies: ['setup'],
    },

    // Desktop Firefox
    {
      name: 'firefox',
      use: {
        ...devices['Desktop Firefox'],
        storageState: 'playwright/.auth/admin.json',
      },
      dependencies: ['setup'],
    },

    // Mobile Chrome
    {
      name: 'mobile-chrome',
      use: {
        ...devices['Pixel 5'],
        storageState: 'playwright/.auth/admin.json',
      },
      dependencies: ['setup'],
    },

    // Authenticated as student
    {
      name: 'student',
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/student.json',
      },
      dependencies: ['setup'],
    },

    // Anonymous (no auth)
    {
      name: 'anonymous',
      use: {
        ...devices['Desktop Chrome'],
      },
    },
  ],

  // Web server configuration (optional - if using Local by Flywheel)
  // webServer: {
  //   command: 'npm run start',
  //   url: 'http://localhost:10003',
  //   reuseExistingServer: !process.env.CI,
  // },

  // Output directories
  outputDir: 'test-results/',
});
