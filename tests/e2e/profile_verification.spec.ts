import { test, expect } from '@playwright/test';

test('Verify profile page components', async ({ page }) => {
  // Mock API responses
  await page.route('**/api/account/profile', async route => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        data: {
          name: 'Test User',
          email: 'test@example.com',
          role: 'Member',
          created_at: '2023-01-01T00:00:00Z',
          avatar_url: null,
          bio: 'Test bio',
          organization: 'Test Org',
          location: 'Test Location',
          website: 'https://example.com',
          timezone: 'UTC'
        }
      })
    });
  });

  await page.route('**/api/account/sessions', async route => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ data: [] })
    });
  });

  await page.route('**/api/account/activity', async route => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ data: [] })
    });
  });

  await page.route('**/api/achievements/user', async route => {
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ data: [] })
    });
  });

  // Navigate to profile page
  // We need to use the full URL including the port 3000 where the vite server is running
  await page.goto('http://localhost:3000/login');

  // Mock login by setting local storage directly if possible, or mocking the auth endpoint
  // For simplicity in this environment, we'll try to access the profile page directly after setting up auth state
  // However, since we can't easily mock Pinia state from outside, we might need to simulate a login flow
  // OR, we can mock the auth check in the router or auth store if we were running unit tests.

  // Let's try to mock the user in the auth store by executing script in the page
  await page.addInitScript(() => {
    window.localStorage.setItem('auth_token', 'mock_token');
    window.localStorage.setItem('user', JSON.stringify({
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
      role: 'Member'
    }));
  });

  // Navigate to profile page
  await page.goto('http://localhost:3000/profile');

  // Force reload to pick up local storage if needed, or simply wait
  // In SPA, local storage might be read on mount.

  // Wait for the page to load
  await page.waitForLoadState('networkidle');

  // Verify Profile Header
  await expect(page.locator('h1')).toContainText('Test User');
  await expect(page.locator('text=test@example.com')).toBeVisible();

  // Verify Tabs exist
  await expect(page.getByRole('button', { name: 'Profile' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Activity' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Security' })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Data & Privacy' })).toBeVisible();

  // Verify Profile Tab Content (default)
  await expect(page.getByLabel('Full Name')).toHaveValue('Test User');
  await expect(page.getByLabel('Email')).toHaveValue('test@example.com');

  // Switch to Activity Tab
  await page.getByRole('button', { name: 'Activity' }).click();
  await expect(page.getByText('Activity History')).toBeVisible();

  // Switch to Security Tab
  await page.getByRole('button', { name: 'Security' }).click();
  await expect(page.getByText('Change Password')).toBeVisible();
  await expect(page.getByText('Two-Factor Authentication')).toBeVisible();

  // Switch to Data Tab
  await page.getByRole('button', { name: 'Data & Privacy' }).click();
  await expect(page.getByText('Export Your Data')).toBeVisible();
  await expect(page.getByText('Danger Zone')).toBeVisible();

  // Take screenshot
  await page.screenshot({ path: 'profile_verification.png', fullPage: true });
});
