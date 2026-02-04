/**
 * Test User Configuration
 */

export interface TestUser {
  username: string;
  password: string;
  role: string;
  id?: number;
}

export const users: Record<string, TestUser> = {
  admin: {
    username: process.env.WP_ADMIN_USER || 'admin',
    password: process.env.WP_ADMIN_PASS || 'password',
    role: 'administrator',
  },
  student: {
    username: process.env.WP_TEST_STUDENT_USER || 'test_student',
    password: process.env.WP_TEST_STUDENT_PASS || 'test_password',
    role: 'subscriber',
    id: parseInt(process.env.WP_TEST_STUDENT_ID || '2'),
  },
  student2: {
    username: process.env.WP_TEST_STUDENT2_USER || 'test_student_2',
    password: process.env.WP_TEST_STUDENT2_PASS || 'test_password',
    role: 'subscriber',
    id: parseInt(process.env.WP_TEST_STUDENT2_ID || '3'),
  },
};

/**
 * Auth state storage paths
 */
export const authStatePaths = {
  admin: 'playwright/.auth/admin.json',
  student: 'playwright/.auth/student.json',
  student2: 'playwright/.auth/student2.json',
};

export default users;
