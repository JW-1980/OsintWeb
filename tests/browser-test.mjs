/**
 * Comprehensive Browser Test Suite for OsintWeb
 * Uses Playwright to navigate all pages and check for errors.
 */
import { chromium } from 'playwright';
import fs from 'fs';

const BASE_URL = 'http://127.0.0.1:8000';
const ADMIN_EMAIL = 'admin@example.com';
const ADMIN_PASSWORD = 'KCmGnvvA8H0Eq4SaQ&TkaW!Y';

const results = [];
const consoleErrors = [];
let browser, context, page;

// All routes from the Vue router
const PUBLIC_ROUTES = [
  { path: '/', name: 'Home' },
  { path: '/explore', name: 'Explore Map' },
  { path: '/explore/events', name: 'Explore Events' },
  { path: '/explore/equipment', name: 'Explore Equipment' },
  { path: '/explore/actors', name: 'Explore Actors' },
  { path: '/explore/conflicts', name: 'Explore Conflicts' },
  { path: '/submit-tip', name: 'Submit Tip' },
  { path: '/about', name: 'About' },
  { path: '/contact', name: 'Contact' },
  { path: '/login', name: 'Login' },
  { path: '/register', name: 'Register' },
  { path: '/forgot-password', name: 'Forgot Password' },
];

const AUTH_ROUTES = [
  { path: '/dashboard', name: 'Dashboard' },
  { path: '/map', name: 'Interactive Map' },
  { path: '/events', name: 'Events List' },
  { path: '/events/create', name: 'Create Event' },
  { path: '/equipment', name: 'Equipment List' },
  { path: '/zones', name: 'Zones List' },
  { path: '/analytics', name: 'Analytics' },
  { path: '/reports', name: 'Reports' },
  { path: '/timeline', name: 'Timeline' },
  { path: '/alerts', name: 'Alerts' },
  { path: '/settings', name: 'User Settings' },
  { path: '/profile', name: 'User Profile' },
];

const ADMIN_ROUTES = [
  { path: '/admin', name: 'Admin Dashboard' },
  { path: '/admin/users', name: 'Admin Users' },
  { path: '/admin/roles', name: 'Admin Roles' },
  { path: '/admin/permissions', name: 'Admin Permissions' },
  { path: '/admin/actors', name: 'Admin Actors' },
  { path: '/admin/conflicts', name: 'Admin Conflicts' },
  { path: '/admin/events', name: 'Admin Events' },
  { path: '/admin/equipment', name: 'Admin Equipment' },
  { path: '/admin/zones', name: 'Admin Zones' },
  { path: '/admin/achievements', name: 'Admin Achievements' },
  { path: '/admin/agents', name: 'Admin Agents' },
  { path: '/admin/skills', name: 'Admin Skills' },
  { path: '/admin/tips', name: 'Admin Tips' },
  { path: '/admin/reports', name: 'Admin Reports' },
  { path: '/admin/audit-logs', name: 'Admin Audit Logs' },
  { path: '/admin/email-templates', name: 'Admin Email Templates' },
  { path: '/admin/site-analytics', name: 'Admin Site Analytics' },
  { path: '/admin/ab-testing', name: 'Admin AB Testing' },
  { path: '/admin/settings', name: 'Admin Settings' },
];

// Detail pages with dynamic IDs
const DETAIL_ROUTES = [
  { path: '/explore/events/1', name: 'Explore Event Detail (ID:1)' },
  { path: '/explore/equipment/1', name: 'Explore Equipment Detail (ID:1)' },
  { path: '/explore/equipment/2', name: 'Explore Equipment Detail (ID:2)' },
  { path: '/explore/actors/1', name: 'Explore Actor Detail (ID:1)' },
  { path: '/explore/conflicts/1', name: 'Explore Conflict Detail (ID:1)' },
  { path: '/events/1', name: 'Event Detail (ID:1)' },
  { path: '/equipment/1', name: 'Equipment Detail (ID:1)' },
  { path: '/equipment/5', name: 'Equipment Detail (ID:5)' },
  { path: '/equipment/10', name: 'Equipment Detail (ID:10)' },
];

// Random additional paths
const RANDOM_ROUTES = [
  { path: '/explore/equipment/3', name: 'Equipment ID:3' },
  { path: '/explore/equipment/15', name: 'Equipment ID:15' },
  { path: '/explore/equipment/50', name: 'Equipment ID:50' },
  { path: '/explore/equipment/100', name: 'Equipment ID:100' },
  { path: '/explore/actors/2', name: 'Actor ID:2' },
  { path: '/explore/actors/5', name: 'Actor ID:5' },
  { path: '/explore/actors/10', name: 'Actor ID:10' },
  { path: '/explore/conflicts/2', name: 'Conflict ID:2' },
  { path: '/explore/conflicts/5', name: 'Conflict ID:5' },
  { path: '/explore/conflicts/10', name: 'Conflict ID:10' },
  { path: '/explore/events/2', name: 'Explore Event ID:2' },
  { path: '/explore/events/5', name: 'Explore Event ID:5' },
  { path: '/explore/events/10', name: 'Explore Event ID:10' },
  { path: '/events/2', name: 'Auth Event ID:2' },
  { path: '/events/5', name: 'Auth Event ID:5' },
  { path: '/events/10', name: 'Auth Event ID:10' },
  { path: '/equipment/2', name: 'Auth Equipment ID:2' },
  { path: '/equipment/20', name: 'Auth Equipment ID:20' },
  { path: '/equipment/50', name: 'Auth Equipment ID:50' },
  { path: '/equipment/100', name: 'Auth Equipment ID:100' },
  { path: '/nonexistent-page', name: '404 Not Found' },
  { path: '/admin/login', name: 'Admin Login Page' },
];

async function visitPage(route, authenticated = false) {
  const pageErrors = [];
  const pageConsoleErrors = [];
  const networkErrors = [];
  let status = 'PASS';
  let remarks = '';
  let httpStatus = 0;
  let pageTitle = '';
  let hasContent = false;
  let hardcodedItems = [];

  try {
    // Collect console errors
    const consoleHandler = (msg) => {
      if (msg.type() === 'error') {
        pageConsoleErrors.push(msg.text());
      }
    };
    page.on('console', consoleHandler);

    // Collect page errors
    const errorHandler = (error) => {
      pageErrors.push(error.message);
    };
    page.on('pageerror', errorHandler);

    // Track network errors (API 500s etc.)
    const responseHandler = (response) => {
      if (response.status() >= 500) {
        networkErrors.push(`${response.status()} ${response.url()}`);
      }
    };
    page.on('response', responseHandler);

    // Navigate - use 'commit' for faster resolution with single-threaded PHP server
    const response = await page.goto(`${BASE_URL}${route.path}`, {
      waitUntil: 'commit',
      timeout: 30000
    });

    httpStatus = response?.status() || 0;

    // Wait for Vue to render (SPA needs time after initial HTML load)
    await page.waitForTimeout(3000);

    // Get page title
    pageTitle = await page.title();

    // Check if page has meaningful content
    const bodyText = await page.evaluate(() => document.body?.innerText || '');
    hasContent = bodyText.length > 50;

    // Check for hardcoded values that should be dynamic
    const htmlContent = await page.content();
    if (htmlContent.includes('example.com') && !route.path.includes('login') && !route.path.includes('register')) {
      hardcodedItems.push('Contains hardcoded "example.com"');
    }
    if (htmlContent.includes('localhost:8000') || htmlContent.includes('127.0.0.1:8000')) {
      // This is expected in dev, but check for hardcoded base URLs in content
      const bodyHtml = await page.evaluate(() => document.body?.innerHTML || '');
      if (bodyHtml.includes('http://127.0.0.1:8000') && !bodyHtml.includes('href="http://127.0.0.1:8000"')) {
        hardcodedItems.push('Contains hardcoded localhost URL in body content');
      }
    }
    if (htmlContent.includes('TODO') || htmlContent.includes('FIXME') || htmlContent.includes('HACK')) {
      hardcodedItems.push('Contains TODO/FIXME/HACK markers in rendered HTML');
    }
    if (htmlContent.includes('Lorem ipsum')) {
      hardcodedItems.push('Contains placeholder Lorem ipsum text');
    }

    // Determine result
    if (httpStatus >= 500) {
      status = 'FAIL';
      remarks = `HTTP ${httpStatus} server error`;
    } else if (pageErrors.length > 0) {
      status = 'FAIL';
      remarks = `JS errors: ${pageErrors.join('; ')}`;
    } else if (networkErrors.length > 0) {
      status = 'FAIL';
      remarks = `Network errors: ${networkErrors.join('; ')}`;
    } else if (!hasContent && httpStatus === 200) {
      status = 'REMARKS';
      remarks = 'Page loaded but has very little content';
    } else if (pageConsoleErrors.length > 0) {
      status = 'REMARKS';
      remarks = `Console errors: ${pageConsoleErrors.slice(0, 3).join('; ')}`;
    } else if (hardcodedItems.length > 0) {
      status = 'REMARKS';
      remarks = `Hardcoded content: ${hardcodedItems.join('; ')}`;
    }

    // Remove listeners
    page.off('console', consoleHandler);
    page.off('pageerror', errorHandler);
    page.off('response', responseHandler);

  } catch (error) {
    status = 'FAIL';
    remarks = `Navigation error: ${error.message}`;
  }

  const result = {
    route: route.path,
    name: route.name,
    status,
    httpStatus,
    pageTitle,
    hasContent,
    remarks,
    consoleErrors: pageConsoleErrors,
    jsErrors: pageErrors,
    networkErrors,
    hardcodedItems,
    authenticated,
    timestamp: new Date().toISOString(),
  };

  results.push(result);

  const statusIcon = status === 'PASS' ? '[PASS]' : status === 'FAIL' ? '[FAIL]' : '[WARN]';
  console.log(`${statusIcon} ${route.name} (${route.path}) - HTTP ${httpStatus} ${remarks ? '- ' + remarks : ''}`);

  return result;
}

async function loginAsAdmin() {
  console.log('\n--- Logging in as admin ---');
  try {
    // First navigate to the app to get cookies set
    await page.goto(`${BASE_URL}/`, { waitUntil: 'commit', timeout: 10000 });
    await page.waitForTimeout(1000);

    // Get CSRF cookie from Sanctum
    await page.evaluate(async (baseUrl) => {
      await fetch(`${baseUrl}/sanctum/csrf-cookie`, { credentials: 'same-origin' });
    }, BASE_URL);
    await page.waitForTimeout(500);

    // Use the API to login with CSRF cookie
    const response = await page.evaluate(async ({ email, password, baseUrl }) => {
      // Get XSRF token from cookies
      const xsrfToken = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='));
      const token = xsrfToken ? decodeURIComponent(xsrfToken.split('=')[1]) : '';

      const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };
      if (token) headers['X-XSRF-TOKEN'] = token;

      const res = await fetch(`${baseUrl}/api/auth/login`, {
        method: 'POST',
        headers,
        credentials: 'same-origin',
        body: JSON.stringify({ email, password }),
      });
      const text = await res.text();
      let data;
      try { data = JSON.parse(text); } catch { data = { raw: text.substring(0, 200) }; }
      return { status: res.status, data };
    }, { email: ADMIN_EMAIL, password: ADMIN_PASSWORD, baseUrl: BASE_URL });

    const authToken = response.data?.token || response.data?.data?.token;
    if (response.status === 200 && authToken) {
      // Store token in localStorage for Vue app to use
      await page.evaluate((t) => {
        localStorage.setItem('auth_token', t);
        localStorage.setItem('token', t);
      }, authToken);
      console.log('Login successful. Token stored.');
      return true;
    } else {
      console.log(`Login failed: HTTP ${response.status}`, JSON.stringify(response.data).substring(0, 300));
      return false;
    }
  } catch (error) {
    console.log(`Login error: ${error.message}`);
    return false;
  }
}

async function main() {
  console.log('='.repeat(70));
  console.log('OsintWeb Comprehensive Browser Test');
  console.log('='.repeat(70));
  console.log(`Base URL: ${BASE_URL}`);
  console.log(`Test started: ${new Date().toISOString()}\n`);

  browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CHROMIUM_PATH || '/root/.cache/ms-playwright/chromium-1194/chrome-linux/chrome',
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu', '--disable-dev-shm-usage']
  });

  context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    ignoreHTTPSErrors: true,
  });

  page = await context.newPage();

  // ============================================
  // PHASE 1: PUBLIC PAGES (no auth required)
  // ============================================
  console.log('\n' + '='.repeat(50));
  console.log('PHASE 1: PUBLIC PAGES');
  console.log('='.repeat(50));

  for (const route of PUBLIC_ROUTES) {
    await visitPage(route, false);
  }

  // ============================================
  // PHASE 2: LOGIN AND AUTHENTICATED PAGES
  // ============================================
  console.log('\n' + '='.repeat(50));
  console.log('PHASE 2: AUTHENTICATED PAGES');
  console.log('='.repeat(50));

  const loggedIn = await loginAsAdmin();
  if (loggedIn) {
    for (const route of AUTH_ROUTES) {
      await visitPage(route, true);
    }
  } else {
    console.log('SKIPPING AUTH ROUTES - Login failed');
    for (const route of AUTH_ROUTES) {
      results.push({
        route: route.path,
        name: route.name,
        status: 'FAIL',
        httpStatus: 0,
        pageTitle: '',
        hasContent: false,
        remarks: 'Skipped - Login failed',
        consoleErrors: [],
        jsErrors: [],
        networkErrors: [],
        hardcodedItems: [],
        authenticated: true,
        timestamp: new Date().toISOString(),
      });
    }
  }

  // ============================================
  // PHASE 3: ADMIN PAGES
  // ============================================
  console.log('\n' + '='.repeat(50));
  console.log('PHASE 3: ADMIN PAGES');
  console.log('='.repeat(50));

  for (const route of ADMIN_ROUTES) {
    await visitPage(route, true);
  }

  // ============================================
  // PHASE 4: DETAIL PAGES (with data)
  // ============================================
  console.log('\n' + '='.repeat(50));
  console.log('PHASE 4: DETAIL PAGES');
  console.log('='.repeat(50));

  for (const route of DETAIL_ROUTES) {
    await visitPage(route, true);
  }

  // ============================================
  // PHASE 5: RANDOM/ADDITIONAL PAGES
  // ============================================
  console.log('\n' + '='.repeat(50));
  console.log('PHASE 5: RANDOM/EDGE CASE PAGES');
  console.log('='.repeat(50));

  for (const route of RANDOM_ROUTES) {
    await visitPage(route, true);
  }

  // ============================================
  // PHASE 6: API ENDPOINTS (health checks)
  // ============================================
  console.log('\n' + '='.repeat(50));
  console.log('PHASE 6: API ENDPOINTS');
  console.log('='.repeat(50));

  const apiEndpoints = [
    '/api/events',
    '/api/equipment',
    '/api/actors',
    '/api/conflicts',
    '/api/health',
    '/api/health/quick',
  ];

  for (const endpoint of apiEndpoints) {
    try {
      const response = await page.evaluate(async (url) => {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch { data = text; }
        return { status: res.status, dataLength: text.length, isJson: typeof data === 'object' };
      }, `${BASE_URL}${endpoint}`);

      const endpointResult = {
        route: endpoint,
        name: `API: ${endpoint}`,
        status: response.status < 400 ? 'PASS' : response.status < 500 ? 'REMARKS' : 'FAIL',
        httpStatus: response.status,
        pageTitle: '',
        hasContent: response.dataLength > 2,
        remarks: response.status >= 400 ? `HTTP ${response.status}` : (response.isJson ? 'Valid JSON response' : 'Non-JSON response'),
        consoleErrors: [],
        jsErrors: [],
        networkErrors: [],
        hardcodedItems: [],
        authenticated: true,
        timestamp: new Date().toISOString(),
      };
      results.push(endpointResult);
      const icon = endpointResult.status === 'PASS' ? '[PASS]' : endpointResult.status === 'FAIL' ? '[FAIL]' : '[WARN]';
      console.log(`${icon} API ${endpoint} - HTTP ${response.status}`);
    } catch (error) {
      console.log(`[FAIL] API ${endpoint} - ${error.message}`);
    }
  }

  await browser.close();

  // ============================================
  // GENERATE REPORT
  // ============================================
  const passed = results.filter(r => r.status === 'PASS').length;
  const failed = results.filter(r => r.status === 'FAIL').length;
  const remarks = results.filter(r => r.status === 'REMARKS').length;
  const total = results.length;

  console.log('\n' + '='.repeat(70));
  console.log('TEST SUMMARY');
  console.log('='.repeat(70));
  console.log(`Total pages tested: ${total}`);
  console.log(`PASS: ${passed}`);
  console.log(`FAIL: ${failed}`);
  console.log(`REMARKS: ${remarks}`);
  console.log(`Pass rate: ${((passed / total) * 100).toFixed(1)}%`);

  if (failed > 0) {
    console.log('\nFAILED PAGES:');
    results.filter(r => r.status === 'FAIL').forEach(r => {
      console.log(`  - ${r.name} (${r.route}): ${r.remarks}`);
    });
  }

  if (remarks > 0) {
    console.log('\nPAGES WITH REMARKS:');
    results.filter(r => r.status === 'REMARKS').forEach(r => {
      console.log(`  - ${r.name} (${r.route}): ${r.remarks}`);
    });
  }

  // Save detailed report
  const report = {
    summary: { total, passed, failed, remarks, passRate: ((passed / total) * 100).toFixed(1) + '%' },
    testDate: new Date().toISOString(),
    baseUrl: BASE_URL,
    results,
  };

  fs.writeFileSync('/home/user/OsintWeb/tests/browser-test-report.json', JSON.stringify(report, null, 2));
  console.log('\nDetailed report saved to tests/browser-test-report.json');
}

main().catch(console.error);
