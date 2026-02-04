import { FullConfig } from '@playwright/test';
import fs from 'fs';
import path from 'path';

/**
 * Global Setup
 *
 * Runs once before all tests to prepare the test environment.
 */
async function globalSetup(config: FullConfig) {
  console.log('Running global setup...');

  // Create directories for auth state storage
  const authDir = path.join(__dirname, 'playwright', '.auth');
  if (!fs.existsSync(authDir)) {
    fs.mkdirSync(authDir, { recursive: true });
  }

  // Create directories for test results
  const resultsDir = path.join(__dirname, 'test-results');
  if (!fs.existsSync(resultsDir)) {
    fs.mkdirSync(resultsDir, { recursive: true });
  }

  // Create screenshots directory
  const screenshotsDir = path.join(__dirname, 'test-results', 'screenshots');
  if (!fs.existsSync(screenshotsDir)) {
    fs.mkdirSync(screenshotsDir, { recursive: true });
  }

  // Verify environment variables
  const requiredEnvVars = ['WP_BASE_URL', 'WP_ADMIN_USER', 'WP_ADMIN_PASS'];
  const missingVars = requiredEnvVars.filter((v) => !process.env[v]);

  if (missingVars.length > 0) {
    console.warn(`Warning: Missing environment variables: ${missingVars.join(', ')}`);
    console.warn('Using default values. Copy .env.example to .env and configure your settings.');
  }

  // Log test configuration
  console.log(`Base URL: ${process.env.WP_BASE_URL || 'http://localhost:10003'}`);
  console.log(`Admin user: ${process.env.WP_ADMIN_USER || 'admin'}`);

  console.log('Global setup complete.');
}

export default globalSetup;
