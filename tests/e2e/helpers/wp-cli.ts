import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

/**
 * WP-CLI Helper Functions
 *
 * Provides helper functions for interacting with WordPress via WP-CLI.
 * These require WP-CLI to be installed and accessible.
 */

const WP_PATH = process.env.WP_PATH || 'C:/Users/saniy/Local Sites/dave-small/app/public';

/**
 * Execute a WP-CLI command
 */
export async function wpCli(command: string): Promise<string> {
  try {
    const { stdout } = await execAsync(`wp ${command} --path="${WP_PATH}"`);
    return stdout.trim();
  } catch (error: unknown) {
    if (error instanceof Error) {
      throw new Error(`WP-CLI command failed: ${error.message}`);
    }
    throw error;
  }
}

/**
 * Get user meta value
 */
export async function getUserMeta(userId: number, metaKey: string): Promise<string> {
  return wpCli(`user meta get ${userId} ${metaKey} --format=json`);
}

/**
 * Set user meta value
 */
export async function setUserMeta(userId: number, metaKey: string, value: string): Promise<void> {
  await wpCli(`user meta update ${userId} ${metaKey} '${value}'`);
}

/**
 * Delete user meta
 */
export async function deleteUserMeta(userId: number, metaKey: string): Promise<void> {
  await wpCli(`user meta delete ${userId} ${metaKey}`);
}

/**
 * Get option value
 */
export async function getOption(optionName: string): Promise<string> {
  return wpCli(`option get ${optionName} --format=json`);
}

/**
 * Set option value
 */
export async function setOption(optionName: string, value: string): Promise<void> {
  await wpCli(`option update ${optionName} '${value}' --format=json`);
}

/**
 * Create a user
 */
export async function createUser(
  username: string,
  email: string,
  password: string,
  role: string = 'subscriber'
): Promise<number> {
  const result = await wpCli(
    `user create ${username} ${email} --user_pass=${password} --role=${role} --porcelain`
  );
  return parseInt(result);
}

/**
 * Delete a user
 */
export async function deleteUser(userId: number, reassign?: number): Promise<void> {
  const reassignArg = reassign ? `--reassign=${reassign}` : '--yes';
  await wpCli(`user delete ${userId} ${reassignArg}`);
}

/**
 * Get post meta
 */
export async function getPostMeta(postId: number, metaKey: string): Promise<string> {
  return wpCli(`post meta get ${postId} ${metaKey}`);
}

/**
 * Set post meta
 */
export async function setPostMeta(postId: number, metaKey: string, value: string): Promise<void> {
  await wpCli(`post meta update ${postId} ${metaKey} '${value}'`);
}

/**
 * Get user's certificate records
 */
export async function getCertificateRecords(userId: number): Promise<unknown> {
  const result = await getUserMeta(userId, 'wdm_certificate_records');
  try {
    return JSON.parse(result);
  } catch {
    return null;
  }
}

/**
 * Clear user's certificate records
 */
export async function clearCertificateRecords(userId: number): Promise<void> {
  await deleteUserMeta(userId, 'wdm_certificate_records');
}

/**
 * Mark course as complete for a user
 */
export async function markCourseComplete(userId: number, courseId: number): Promise<void> {
  await wpCli(`learndash course-progress set ${userId} ${courseId} complete`);
}

/**
 * Reset course progress for a user
 */
export async function resetCourseProgress(userId: number, courseId: number): Promise<void> {
  await wpCli(`learndash course-progress delete ${userId} ${courseId}`);
}

/**
 * Enroll user in course
 */
export async function enrollUserInCourse(userId: number, courseId: number): Promise<void> {
  await wpCli(`learndash enroll ${userId} ${courseId}`);
}

/**
 * Check if plugin is active
 */
export async function isPluginActive(pluginSlug: string): Promise<boolean> {
  try {
    const result = await wpCli(`plugin is-active ${pluginSlug}`);
    return true;
  } catch {
    return false;
  }
}

/**
 * Activate plugin
 */
export async function activatePlugin(pluginSlug: string): Promise<void> {
  await wpCli(`plugin activate ${pluginSlug}`);
}

/**
 * Deactivate plugin
 */
export async function deactivatePlugin(pluginSlug: string): Promise<void> {
  await wpCli(`plugin deactivate ${pluginSlug}`);
}

/**
 * Flush rewrite rules
 */
export async function flushRewriteRules(): Promise<void> {
  await wpCli('rewrite flush');
}

export default {
  wpCli,
  getUserMeta,
  setUserMeta,
  deleteUserMeta,
  getOption,
  setOption,
  createUser,
  deleteUser,
  getPostMeta,
  setPostMeta,
  getCertificateRecords,
  clearCertificateRecords,
  markCourseComplete,
  resetCourseProgress,
  enrollUserInCourse,
  isPluginActive,
  activatePlugin,
  deactivatePlugin,
  flushRewriteRules,
};
