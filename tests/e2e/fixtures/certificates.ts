/**
 * Certificate Test Data
 *
 * Pre-defined certificate data for testing various scenarios.
 */

export interface CertificateRecord {
  csuid: string;
  standardCert: number;
  pocketCert?: number;
  sourceType: 'course' | 'quiz' | 'group';
  sourceId: number;
  userId: number;
  completionDate: string;
  isRetroactive: boolean;
}

/**
 * Sample certificate records for testing
 */
export const sampleCertificates: CertificateRecord[] = [
  {
    csuid: 'ABC123-DEF456-GHI789',
    standardCert: 456,
    sourceType: 'course',
    sourceId: 123,
    userId: 2,
    completionDate: '2024-01-15 10:30:00',
    isRetroactive: false,
  },
  {
    csuid: 'DEF789-ABC123-JKL456',
    standardCert: 456,
    pocketCert: 789,
    sourceType: 'course',
    sourceId: 124,
    userId: 2,
    completionDate: '2024-01-20 14:45:00',
    isRetroactive: false,
  },
  {
    csuid: 'QUIZ11-TEST22-USER33',
    standardCert: 500,
    sourceType: 'quiz',
    sourceId: 100,
    userId: 2,
    completionDate: '2024-02-01 09:00:00',
    isRetroactive: false,
  },
];

/**
 * Invalid CSUID test cases with expected error types
 */
export const invalidCsuidCases = [
  {
    input: 'INVALID123',
    description: 'Wrong format - no segments',
    expectedError: 'invalid_format',
  },
  {
    input: '123',
    description: 'Too short',
    expectedError: 'invalid_format',
  },
  {
    input: 'ABC-DEF',
    description: 'Only 2 segments',
    expectedError: 'invalid_format',
  },
  {
    input: 'ABC-DEF-GHI-JKL',
    description: '4 segments',
    expectedError: 'invalid_format',
  },
  {
    input: '',
    description: 'Empty string',
    expectedError: 'empty_input',
  },
  {
    input: '   ',
    description: 'Whitespace only',
    expectedError: 'empty_input',
  },
  {
    input: 'ZZZZZ-ZZZZZ-ZZZZZ',
    description: 'Non-existent but valid format',
    expectedError: 'not_found',
  },
];

/**
 * CSUID encoding/decoding test vectors
 */
export const csuidEncodingVectors = [
  {
    input: { certId: 123, sourceId: 456, userId: 1 },
    // Expected CSUID format: hex(certId)-hex(sourceId)-hex(userId+offset)
  },
  {
    input: { certId: 1, sourceId: 1, userId: 1 },
  },
  {
    input: { certId: 999999, sourceId: 888888, userId: 777777 },
  },
];

export default sampleCertificates;
