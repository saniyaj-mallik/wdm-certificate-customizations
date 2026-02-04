import { APIRequestContext, request } from '@playwright/test';

/**
 * REST API Helper Functions
 *
 * Provides helper functions for interacting with WordPress REST API.
 */

const BASE_URL = process.env.WP_BASE_URL || 'http://localhost:10003';

/**
 * Create an authenticated API context
 */
export async function createAuthenticatedContext(
  username: string,
  password: string
): Promise<APIRequestContext> {
  return request.newContext({
    baseURL: BASE_URL,
    httpCredentials: {
      username,
      password,
    },
  });
}

/**
 * Get nonce for AJAX requests
 */
export async function getNonce(context: APIRequestContext): Promise<string> {
  const response = await context.get('/wp-admin/admin-ajax.php?action=rest-nonce');
  return response.text();
}

/**
 * Verify certificate via AJAX
 */
export async function verifyCertificateApi(
  context: APIRequestContext,
  certId: string
): Promise<{
  success: boolean;
  data?: {
    cert_id: string;
    recipient_name: string;
    source_title: string;
    completion_date: string;
    status: string;
    standard_cert_url?: string;
    pocket_cert_url?: string;
  };
  error?: string;
}> {
  const response = await context.post('/wp-admin/admin-ajax.php', {
    form: {
      action: 'wdm_cert_verify',
      cert_id: certId,
    },
  });

  return response.json();
}

/**
 * Run retroactive generation via AJAX
 */
export async function runRetroactiveGeneration(
  context: APIRequestContext,
  nonce: string
): Promise<{
  success: boolean;
  data?: {
    generated: number;
    skipped: number;
    message: string;
  };
  error?: string;
}> {
  const response = await context.post('/wp-admin/admin-ajax.php', {
    form: {
      action: 'wdm_generate_retroactive_certs',
      nonce,
    },
  });

  return response.json();
}

/**
 * Get WordPress REST API root
 */
export async function getApiRoot(context: APIRequestContext): Promise<unknown> {
  const response = await context.get('/wp-json/');
  return response.json();
}

/**
 * Get current user info
 */
export async function getCurrentUser(context: APIRequestContext): Promise<unknown> {
  const response = await context.get('/wp-json/wp/v2/users/me');
  return response.json();
}

/**
 * Get course by ID
 */
export async function getCourse(context: APIRequestContext, courseId: number): Promise<unknown> {
  const response = await context.get(`/wp-json/ldlms/v2/sfwd-courses/${courseId}`);
  return response.json();
}

/**
 * Get user's course progress
 */
export async function getCourseProgress(
  context: APIRequestContext,
  userId: number,
  courseId: number
): Promise<unknown> {
  const response = await context.get(`/wp-json/ldlms/v2/users/${userId}/course-progress/${courseId}`);
  return response.json();
}

/**
 * Create test data via REST API
 */
export async function createTestCertificateRecord(
  context: APIRequestContext,
  userId: number,
  data: {
    csuid: string;
    standardCert: number;
    pocketCert?: number;
    sourceType: string;
    sourceId: number;
  }
): Promise<void> {
  // This would require a custom REST endpoint in the plugin
  // For now, we'll use WP-CLI or direct database access
  throw new Error('Not implemented - use WP-CLI helper instead');
}

/**
 * Clean up test data
 */
export async function cleanupTestData(context: APIRequestContext, userId: number): Promise<void> {
  // This would require a custom REST endpoint in the plugin
  throw new Error('Not implemented - use WP-CLI helper instead');
}

export default {
  createAuthenticatedContext,
  getNonce,
  verifyCertificateApi,
  runRetroactiveGeneration,
  getApiRoot,
  getCurrentUser,
  getCourse,
  getCourseProgress,
  createTestCertificateRecord,
  cleanupTestData,
};
