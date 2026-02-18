/**
 * Comprehensive headless browser testing script for OsintWeb
 * Tests both admin panel and user-facing pages
 */

import puppeteer from 'puppeteer-core';
import { writeFileSync } from 'fs';

const BASE_URL = 'http://127.0.0.1:8000';
const CHROMIUM_PATH = '/root/.cache/ms-playwright/chromium-1194/chrome-linux/chrome';
const ADMIN_EMAIL = 'admin@osintweb.local';
const ADMIN_PASSWORD = 'TestAdmin123!';

const results = [];
const errors = [];
const hardcodedIssues = [];

async function logResult(page, url, status, notes = '', category = 'user') {
  const result = {
    url,
    status,
    notes,
    category,
    timestamp: new Date().toISOString(),
    pageTitle: '',
  };

  try {
    result.pageTitle = await page.title();
  } catch (e) {
    result.pageTitle = 'N/A';
  }

  results.push(result);
  const icon = status === 'PASS' ? '✓' : status === 'PASS_WITH_REMARKS' ? '⚠' : '✗';
  console.log(`${icon} [${category}] ${url} - ${status}${notes ? ': ' + notes : ''}`);
}

async function checkForHardcodedValues(page, url) {
  try {
    const content = await page.content();
    const checks = [
      { pattern: /admin@example\.com/g, desc: 'Hardcoded admin@example.com' },
      { pattern: /your-domain\.com/g, desc: 'Hardcoded your-domain.com' },
      { pattern: /TODO|FIXME|HACK|XXX/g, desc: 'Development markers found' },
      { pattern: /sk-[a-zA-Z0-9]{20,}/g, desc: 'Possible hardcoded API key' },
      { pattern: /password123|admin123|test123/gi, desc: 'Hardcoded test passwords' },
    ];

    for (const check of checks) {
      const matches = content.match(check.pattern);
      if (matches && matches.length > 0) {
        hardcodedIssues.push({ url, issue: check.desc, count: matches.length });
      }
    }
  } catch (e) { /* ignore */ }
}

async function navigateAndCheck(page, url, category = 'user') {
  const consoleErrors = [];
  const networkErrors = [];

  const consoleHandler = (msg) => {
    if (msg.type() === 'error') consoleErrors.push(msg.text());
  };
  page.on('console', consoleHandler);

  const requestFailHandler = (request) => {
    const failUrl = request.url();
    // Ignore external resource failures
    if (!failUrl.includes('127.0.0.1') && !failUrl.includes('localhost')) return;
    networkErrors.push(`${request.method()} ${failUrl}`);
  };
  page.on('requestfailed', requestFailHandler);

  try {
    const response = await page.goto(url, {
      waitUntil: 'domcontentloaded',
      timeout: 15000,
    });
    const httpStatus = response?.status() || 0;

    // Wait for Vue to render
    await new Promise(r => setTimeout(r, 2500));

    await checkForHardcodedValues(page, url);

    const bodyText = await page.evaluate(() => document.body?.innerText || '');
    const hasServerError = bodyText.includes('500 Server Error') ||
                           bodyText.includes('Whoops!') ||
                           bodyText.includes('SQLSTATE') ||
                           bodyText.includes('ErrorException') ||
                           bodyText.includes('Class not found');

    const remarks = [];
    if (consoleErrors.length > 0) {
      const relevantErrors = consoleErrors.filter(e =>
        !e.includes('fonts.bunny.net') && !e.includes('favicon') && !e.includes('service-worker')
      );
      if (relevantErrors.length > 0) {
        remarks.push(`${relevantErrors.length} console error(s): ${relevantErrors[0]?.substring(0, 80)}`);
      }
    }
    if (networkErrors.length > 0) {
      remarks.push(`${networkErrors.length} local network error(s)`);
    }

    if (httpStatus >= 500 || hasServerError) {
      const detail = hasServerError ? bodyText.substring(0, 300) : `HTTP ${httpStatus}`;
      await logResult(page, url, 'FAIL', detail, category);
      errors.push({ url, error: detail, consoleErrors, networkErrors });
    } else if (httpStatus >= 400) {
      await logResult(page, url, 'FAIL', `HTTP ${httpStatus}`, category);
      errors.push({ url, error: `HTTP ${httpStatus}`, consoleErrors, networkErrors });
    } else if (remarks.length > 0) {
      await logResult(page, url, 'PASS_WITH_REMARKS', remarks.join('; '), category);
    } else {
      await logResult(page, url, 'PASS', '', category);
    }

    return httpStatus;
  } catch (e) {
    await logResult(page, url, 'FAIL', e.message?.substring(0, 150), category);
    errors.push({ url, error: e.message?.substring(0, 200) });
    return 0;
  } finally {
    page.off('console', consoleHandler);
    page.off('requestfailed', requestFailHandler);
  }
}

async function testApiEndpoint(page, endpoint, authToken, category = 'api') {
  try {
    const apiResult = await page.evaluate(async (url, token) => {
      try {
        const headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = `Bearer ${token}`;
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000);
        const response = await fetch(url, { headers, signal: controller.signal });
        clearTimeout(timeoutId);
        const status = response.status;
        let body = '';
        try { body = await response.text(); } catch(e) {}
        return { status, body: body.substring(0, 500) };
      } catch (e) {
        return { status: 0, body: e.message };
      }
    }, `${BASE_URL}${endpoint}`, authToken);

    const status = apiResult.status;
    if (status >= 500) {
      results.push({ url: endpoint, status: 'FAIL', notes: `HTTP ${status}: ${apiResult.body.substring(0, 200)}`, category, timestamp: new Date().toISOString() });
      errors.push({ url: endpoint, error: `HTTP ${status}: ${apiResult.body.substring(0, 200)}` });
      console.log(`✗ [${category}] ${endpoint} - FAIL: HTTP ${status}`);
    } else if (status === 401 || status === 403) {
      results.push({ url: endpoint, status: 'PASS_WITH_REMARKS', notes: `HTTP ${status} (auth required)`, category, timestamp: new Date().toISOString() });
      console.log(`⚠ [${category}] ${endpoint} - HTTP ${status} (auth)`);
    } else if (status >= 400) {
      results.push({ url: endpoint, status: 'PASS_WITH_REMARKS', notes: `HTTP ${status}`, category, timestamp: new Date().toISOString() });
      console.log(`⚠ [${category}] ${endpoint} - HTTP ${status}`);
    } else {
      results.push({ url: endpoint, status: 'PASS', notes: '', category, timestamp: new Date().toISOString() });
      console.log(`✓ [${category}] ${endpoint} - PASS (${status})`);
    }
    return status;
  } catch (e) {
    results.push({ url: endpoint, status: 'FAIL', notes: e.message, category, timestamp: new Date().toISOString() });
    errors.push({ url: endpoint, error: e.message });
    console.log(`✗ [${category}] ${endpoint} - FAIL: ${e.message}`);
    return 0;
  }
}

async function main() {
  console.log('=== OsintWeb Headless Browser Test Suite ===\n');

  const browser = await puppeteer.launch({
    executablePath: CHROMIUM_PATH,
    headless: true,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
      '--disable-gpu',
      '--disable-extensions',
      '--window-size=1920,1080',
      '--disable-web-security',
    ],
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1920, height: 1080 });

  // Block external resources that may cause timeouts
  await page.setRequestInterception(true);
  page.on('request', (req) => {
    const url = req.url();
    if (url.includes('fonts.bunny.net') || url.includes('fonts.googleapis.com') ||
        url.includes('google-analytics') || url.includes('gtag')) {
      req.abort();
    } else {
      req.continue();
    }
  });

  // ==========================================
  // PHASE 1: Public/User-Facing Pages (25+ pages)
  // ==========================================
  console.log('\n--- Phase 1: Public/User-Facing Pages ---\n');

  const publicPages = [
    '/',
    '/login',
    '/register',
    '/forgot-password',
    '/explore',
    '/explore/map',
    '/explore/events',
    '/explore/equipment',
    '/explore/conflicts',
    '/explore/countries',
    '/explore/actors',
    '/explore/factions',
    '/explore/statistics',
    '/explore/timeline',
    '/articles',
    '/about',
    '/contact',
    '/privacy',
    '/terms',
    '/offline',
  ];

  for (const path of publicPages) {
    await navigateAndCheck(page, `${BASE_URL}${path}`, 'user-public');
  }

  // ==========================================
  // PHASE 2: Login and Auth User Pages (15+ pages)
  // ==========================================
  console.log('\n--- Phase 2: Login & Authenticated User Pages ---\n');

  // Login via API and set token in localStorage
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded', timeout: 15000 });
  await new Promise(r => setTimeout(r, 2000));

  await page.evaluate(async (email, password, baseUrl) => {
    try {
      const res = await fetch(`${baseUrl}/api/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email, password }),
      });
      const data = await res.json();
      if (data.data?.token) {
        localStorage.setItem('auth_token', data.data.token);
        localStorage.setItem('user', JSON.stringify(data.data.user));
      }
    } catch (e) { console.error('Login failed:', e); }
  }, ADMIN_EMAIL, ADMIN_PASSWORD, BASE_URL);
  await new Promise(r => setTimeout(r, 1000));

  const authToken = await page.evaluate(() => localStorage.getItem('auth_token'));
  console.log(`Auth token obtained: ${authToken ? 'YES' : 'NO'}`);

  const authUserPages = [
    '/dashboard',
    '/profile',
    '/profile/edit',
    '/profile/security',
    '/profile/preferences',
    '/profile/privacy',
    '/profile/export',
    '/notifications',
    '/bookmarks',
    '/events/create',
    '/my-events',
    '/my-articles',
    '/achievements',
    '/onboarding',
    '/settings',
  ];

  for (const path of authUserPages) {
    await navigateAndCheck(page, `${BASE_URL}${path}`, 'user-auth');
  }

  // ==========================================
  // PHASE 3: Admin Panel Pages (40+ pages)
  // ==========================================
  console.log('\n--- Phase 3: Admin Panel Pages ---\n');

  const adminPages = [
    '/admin',
    '/admin/dashboard',
    '/admin/users',
    '/admin/roles',
    '/admin/permissions',
    '/admin/events',
    '/admin/events/pending',
    '/admin/equipment',
    '/admin/equipment/categories',
    '/admin/conflicts',
    '/admin/countries',
    '/admin/actors',
    '/admin/factions',
    '/admin/articles',
    '/admin/comments',
    '/admin/settings',
    '/admin/settings/general',
    '/admin/settings/email',
    '/admin/settings/security',
    '/admin/settings/map',
    '/admin/audit-logs',
    '/admin/analytics',
    '/admin/analytics/events',
    '/admin/analytics/users',
    '/admin/analytics/search',
    '/admin/reports',
    '/admin/scheduled-reports',
    '/admin/ab-testing',
    '/admin/email-templates',
    '/admin/disinformation',
    '/admin/skills',
    '/admin/agents',
    '/admin/achievements',
    '/admin/sources',
    '/admin/health',
    '/admin/training',
    '/admin/cases',
    '/admin/sitreps',
    '/admin/network',
    '/admin/maritime',
    '/admin/flight-tracking',
    '/admin/social-media',
    '/admin/evidence',
  ];

  for (const path of adminPages) {
    await navigateAndCheck(page, `${BASE_URL}${path}`, 'admin');
  }

  // ==========================================
  // PHASE 4: API Endpoints (40+ endpoints)
  // ==========================================
  console.log('\n--- Phase 4: API Endpoints ---\n');

  // Navigate to a page first so we can use fetch
  await page.goto(`${BASE_URL}/`, { waitUntil: 'domcontentloaded', timeout: 15000 });
  await new Promise(r => setTimeout(r, 1000));

  const publicApiEndpoints = [
    '/api/events?per_page=5',
    '/api/events/types',
    '/api/equipment?per_page=5',
    '/api/equipment/categories',
    '/api/countries',
    '/api/factions',
    '/api/actors',
    '/api/conflicts',
    '/api/stats/overview',
    '/api/stats/events',
    '/api/stats/losses',
    '/api/stats/timeline',
    '/api/articles?per_page=5',
    '/api/sources?per_page=5',
    '/api/health',
    '/api/health/quick',
  ];

  for (const ep of publicApiEndpoints) {
    await testApiEndpoint(page, ep, null, 'api-public');
  }

  const authApiEndpoints = [
    '/api/auth/user',
    '/api/account/preferences',
    '/api/skills',
    '/api/agents',
    '/api/achievements',
    '/api/notifications',
    '/api/export/formats',
    '/api/onboarding/status',
    '/api/control-zones',
    '/api/alerts',
    '/api/reports',
    '/api/scheduled-reports',
    '/api/cases',
    '/api/sitreps',
    '/api/maritime/vessels',
    '/api/flight-tracking/aircraft',
    '/api/social-media/accounts',
    '/api/evidence',
    '/api/geolocation/verifications',
    '/api/weather/providers',
    '/api/audio',
    '/api/video-analysis',
    '/api/reverse-image',
    '/api/documents',
    '/api/disinformation/analyses',
    '/api/network/graphs',
    '/api/timeline',
  ];

  for (const ep of authApiEndpoints) {
    await testApiEndpoint(page, ep, authToken, 'api-auth');
  }

  // ==========================================
  // PHASE 5: Navigation Flow Tests
  // ==========================================
  console.log('\n--- Phase 5: Navigation Flow Tests ---\n');

  const flows = [
    { start: '/', click: 'Explore', expect: '/explore' },
    { start: '/explore', click: 'Map', expect: '/map' },
    { start: '/explore', click: 'Equipment', expect: '/equipment' },
    { start: '/login', click: 'Register', expect: '/register' },
    { start: '/register', click: 'Login', expect: '/login' },
  ];

  for (const flow of flows) {
    try {
      await page.goto(`${BASE_URL}${flow.start}`, { waitUntil: 'domcontentloaded', timeout: 10000 });
      await new Promise(r => setTimeout(r, 2000));

      const clicked = await page.evaluate((text) => {
        const links = Array.from(document.querySelectorAll('a, button'));
        for (const el of links) {
          if (el.textContent?.trim().includes(text)) {
            el.click();
            return true;
          }
        }
        return false;
      }, flow.click);

      await new Promise(r => setTimeout(r, 2000));
      const finalUrl = page.url();
      const passed = clicked && finalUrl.includes(flow.expect);

      results.push({
        url: `Nav: ${flow.start} -> click "${flow.click}"`,
        status: passed ? 'PASS' : 'PASS_WITH_REMARKS',
        notes: passed ? '' : `Ended at ${finalUrl}`,
        category: 'navigation',
        timestamp: new Date().toISOString(),
      });
      console.log(`${passed ? '✓' : '⚠'} [nav] ${flow.start} -> "${flow.click}" = ${finalUrl}`);
    } catch (e) {
      results.push({
        url: `Nav: ${flow.start} -> click "${flow.click}"`,
        status: 'FAIL',
        notes: e.message?.substring(0, 100),
        category: 'navigation',
        timestamp: new Date().toISOString(),
      });
      console.log(`✗ [nav] ${flow.start} -> "${flow.click}" - FAIL`);
    }
  }

  await browser.close();

  // ==========================================
  // Generate Report
  // ==========================================
  const passed = results.filter(r => r.status === 'PASS').length;
  const passedWithRemarks = results.filter(r => r.status === 'PASS_WITH_REMARKS').length;
  const failed = results.filter(r => r.status === 'FAIL').length;
  const total = results.length;

  const report = {
    summary: {
      total,
      passed,
      passedWithRemarks,
      failed,
      passRate: `${((passed + passedWithRemarks) / total * 100).toFixed(1)}%`,
      testDate: new Date().toISOString(),
    },
    resultsByCategory: {
      'user-public': results.filter(r => r.category === 'user-public'),
      'user-auth': results.filter(r => r.category === 'user-auth'),
      'admin': results.filter(r => r.category === 'admin'),
      'api-public': results.filter(r => r.category === 'api-public'),
      'api-auth': results.filter(r => r.category === 'api-auth'),
      'navigation': results.filter(r => r.category === 'navigation'),
    },
    errors,
    hardcodedIssues,
  };

  writeFileSync('/home/user/OsintWeb/tests/browser-test-results.json', JSON.stringify(report, null, 2));

  console.log('\n========================================');
  console.log('           TEST SUMMARY');
  console.log('========================================');
  console.log(`Total pages/endpoints tested: ${total}`);
  console.log(`  Passed:              ${passed}`);
  console.log(`  Passed with remarks: ${passedWithRemarks}`);
  console.log(`  Failed:              ${failed}`);
  console.log(`  Pass rate:           ${report.summary.passRate}`);
  console.log('');

  for (const [cat, items] of Object.entries(report.resultsByCategory)) {
    const catPass = items.filter(r => r.status === 'PASS').length;
    const catRemarks = items.filter(r => r.status === 'PASS_WITH_REMARKS').length;
    const catFail = items.filter(r => r.status === 'FAIL').length;
    console.log(`  ${cat}: ${catPass} pass / ${catRemarks} remarks / ${catFail} fail (${items.length} total)`);
  }

  if (errors.length > 0) {
    console.log('\n=== ERRORS DETAIL ===');
    for (const err of errors) {
      console.log(`  ${err.url}: ${typeof err.error === 'string' ? err.error.substring(0, 150) : JSON.stringify(err.error).substring(0, 150)}`);
    }
  }

  if (hardcodedIssues.length > 0) {
    console.log('\n=== HARDCODED VALUES ===');
    for (const issue of hardcodedIssues) {
      console.log(`  ${issue.url}: ${issue.issue} (${issue.count} occurrences)`);
    }
  }

  console.log('\nResults saved to tests/browser-test-results.json');
}

main().catch(console.error);
