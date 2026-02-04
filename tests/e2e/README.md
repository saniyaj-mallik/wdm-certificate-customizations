# WDM Certificate Customizations - E2E Tests

End-to-end tests for the WDM Certificate Customizations WordPress plugin using Playwright.

## Quick Start

```bash
# Install dependencies
npm install

# Copy environment configuration
cp .env.example .env
# Edit .env with your WordPress site details

# Run all tests
npm test

# Run tests with UI
npm run test:ui

# Run specific test suites
npm run test:critical   # P1 Critical tests
npm run test:important  # P2 Important tests
npm run test:standard   # P3 Standard tests
```

## Test Structure

```
tests/e2e/
├── playwright.config.ts       # Playwright configuration
├── .env.example              # Environment variables template
├── package.json              # Dependencies and scripts
├── auth.setup.ts             # Authentication setup
├── global-setup.ts           # Global test setup
│
├── fixtures/                 # Test data and configuration
│   ├── test-data.ts         # Test constants
│   ├── users.ts             # User credentials
│   └── certificates.ts      # Certificate test data
│
├── pages/                    # Page Object Models
│   ├── base.page.ts         # Base page class
│   ├── login.page.ts        # WordPress login
│   ├── verification.page.ts # Certificate verification
│   ├── admin-settings.page.ts # Plugin settings
│   └── course-editor.page.ts # LearnDash course editor
│
├── helpers/                  # Utility functions
│   ├── wp-cli.ts            # WP-CLI commands
│   ├── api.ts               # REST API helpers
│   └── database.ts          # Database helpers
│
└── tests/                    # Test specifications
    ├── critical/            # P1 Critical tests
    │   ├── verification.spec.ts
    │   ├── download.spec.ts
    │   ├── admin.spec.ts
    │   └── generation.spec.ts
    │
    ├── important/           # P2 Important tests
    │   ├── ui.spec.ts
    │   ├── settings.spec.ts
    │   ├── edge-cases.spec.ts
    │   └── shortcodes.spec.ts
    │
    └── standard/            # P3 Standard tests
        ├── activation.spec.ts
        ├── mobile.spec.ts
        └── misc.spec.ts
```

## Environment Setup

1. Copy `.env.example` to `.env`
2. Update the following variables:

```bash
WP_BASE_URL=http://your-local-site.local
WP_ADMIN_USER=admin
WP_ADMIN_PASS=your_password
WP_TEST_STUDENT_USER=test_student
WP_TEST_STUDENT_PASS=test_password
```

3. Create test users in WordPress:
   - `test_student` - subscriber role for student tests
   - `test_student_2` - subscriber role for non-owner tests

4. Create test data:
   - Course with certificate assigned
   - Course with both standard and pocket certificates
   - Quiz with certificate

## Running Tests

### All Tests
```bash
npm test
```

### By Priority
```bash
npm run test:critical    # P1 - Must pass
npm run test:important   # P2 - Should pass
npm run test:standard    # P3 - Nice to have
```

### By Browser
```bash
npm run test:chromium
npm run test:firefox
npm run test:mobile
```

### By User Role
```bash
npm run test:student     # As student user
npm run test:anonymous   # No authentication
```

### Interactive Modes
```bash
npm run test:ui          # Visual UI mode
npm run test:headed      # Headed browser
npm run test:debug       # Debug mode
```

### Generate Code
```bash
npm run codegen          # Record actions to generate code
```

## Test Reports

```bash
npm run report           # Open HTML report
```

Reports are generated at:
- `playwright-report/` - HTML report
- `test-results/` - JSON results and screenshots

## Test Scenarios

### P1 Critical (15 tests)
- TC-001 to TC-015
- Certificate verification
- Download permissions
- Admin settings access
- CSUID encoding
- QR code generation

### P2 Important (18 tests)
- TC-016 to TC-033
- Shortcodes
- Settings validation
- Edge cases
- UI interactions

### P3 Standard (14 tests)
- TC-034 to TC-047
- Activation/deactivation
- Mobile responsiveness
- i18n

## Writing New Tests

### Using Page Objects

```typescript
import { test, expect } from '@playwright/test';
import { VerificationPage } from '../../pages/verification.page';

test('verify certificate', async ({ page }) => {
  const verificationPage = new VerificationPage(page);

  await verificationPage.gotoVerificationPage();
  await verificationPage.verifyCertificate('ABC123-DEF456-GHI789');

  expect(await verificationPage.isVerificationSuccessful()).toBeTruthy();
});
```

### Using Fixtures

```typescript
import { testData } from '../../fixtures/test-data';
import { users, authStatePaths } from '../../fixtures/users';

test.describe('Admin Tests', () => {
  test.use({ storageState: authStatePaths.admin });

  test('admin can access settings', async ({ page }) => {
    // Test as admin user
  });
});
```

## Troubleshooting

### Tests fail to authenticate
1. Verify user credentials in `.env`
2. Check users exist in WordPress
3. Clear `playwright/.auth/` directory

### Tests timeout
1. Increase timeout in `playwright.config.ts`
2. Check WordPress site is running
3. Check for JavaScript errors in browser console

### Flaky tests
1. Add explicit waits for elements
2. Use `waitForLoadState('networkidle')`
3. Check for race conditions in AJAX calls

## CI/CD Integration

```yaml
# GitHub Actions example
jobs:
  e2e:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - run: npm ci
      - run: npx playwright install --with-deps
      - run: npm test
      - uses: actions/upload-artifact@v3
        if: always()
        with:
          name: playwright-report
          path: playwright-report/
```

## Contributing

1. Follow the Page Object pattern for new pages
2. Add tests to the appropriate priority directory
3. Use descriptive test names with TC-XXX prefix
4. Include assertions for all acceptance criteria
5. Document any test data requirements
