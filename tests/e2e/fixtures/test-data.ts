/**
 * Test Data Constants
 *
 * These values should be configured via environment variables
 * or set after creating test data in WordPress.
 */

export const testData = {
  // Course test data
  courses: {
    withCertificate: {
      id: parseInt(process.env.WP_TEST_COURSE_ID || '123'),
      title: 'Test Course with Certificate',
      certificateId: parseInt(process.env.WP_TEST_CERTIFICATE_ID || '456'),
    },
    withDualCertificate: {
      id: parseInt(process.env.WP_TEST_COURSE_WITH_DUAL_CERT_ID || '124'),
      title: 'Test Course with Dual Certificates',
      standardCertId: parseInt(process.env.WP_TEST_CERTIFICATE_ID || '456'),
      pocketCertId: parseInt(process.env.WP_TEST_POCKET_CERTIFICATE_ID || '789'),
    },
    withoutCertificate: {
      id: 125,
      title: 'Test Course without Certificate',
    },
  },

  // Quiz test data
  quizzes: {
    withCertificate: {
      id: parseInt(process.env.WP_TEST_QUIZ_ID || '100'),
      title: 'Test Quiz with Certificate',
      passingScore: 80,
    },
  },

  // Certificate test data
  certificates: {
    valid: {
      csuid: process.env.WP_VALID_CERT_ID || 'ABC123-DEF456-GHI789',
      userId: parseInt(process.env.WP_TEST_STUDENT_ID || '2'),
      courseId: parseInt(process.env.WP_TEST_COURSE_ID || '123'),
      certificateId: parseInt(process.env.WP_TEST_CERTIFICATE_ID || '456'),
    },
    validWithPocket: {
      csuid: process.env.WP_VALID_CERT_ID_WITH_POCKET || 'DEF789-ABC123-JKL456',
      userId: parseInt(process.env.WP_TEST_STUDENT_ID || '2'),
      courseId: parseInt(process.env.WP_TEST_COURSE_WITH_DUAL_CERT_ID || '124'),
      standardCertId: parseInt(process.env.WP_TEST_CERTIFICATE_ID || '456'),
      pocketCertId: parseInt(process.env.WP_TEST_POCKET_CERTIFICATE_ID || '789'),
    },
    // Invalid certificate IDs for negative testing
    invalid: [
      'INVALID123',           // Wrong format
      '123',                  // Too short
      'ABC-DEF',              // Only 2 segments
      'ABC-DEF-GHI-JKL',      // 4 segments
      '',                     // Empty
      '   ',                  // Whitespace only
      'ZZZZZ-ZZZZZ-ZZZZZ',    // Non-existent
    ],
  },

  // Verification page
  verificationPage: {
    id: parseInt(process.env.WP_VERIFICATION_PAGE_ID || '999'),
    url: process.env.WP_VERIFICATION_URL || '/certificate-verification/',
    title: 'Certificate Verification',
  },

  // Settings defaults
  settings: {
    defaultQrSize: 150,
    minQrSize: 50,
    maxQrSize: 500,
    pocketCertEnabled: true,
  },

  // CSUID encoding test cases
  csuidTestCases: [
    // Small IDs
    { cert_id: 123, source_id: 456, user_id: 1 },
    // Medium IDs
    { cert_id: 1234, source_id: 5678, user_id: 100 },
    // Large IDs
    { cert_id: 12345678, source_id: 87654321, user_id: 99999999 },
  ],
};

export default testData;
