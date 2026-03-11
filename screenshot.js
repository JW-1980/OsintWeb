import { chromium } from 'playwright';
import fs from 'fs';

const BASE_URL = 'http://127.0.0.1:8000';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  const takeScreenshot = async (urlPath, filename) => {
    try {
        console.log(`Navigating to ${BASE_URL}${urlPath}`);
        await page.goto(`${BASE_URL}${urlPath}`);
        await page.waitForTimeout(2000);
        await page.screenshot({ path: `screenshots/${filename}`, fullPage: true });
        console.log(`Saved screenshot ${filename}`);
    } catch(err) {
        console.error(`Error on ${urlPath}: ${err.message}`);
    }
  }

  // Public pages (20 screenshots)
  await takeScreenshot('/', 'public_home.png');
  await takeScreenshot('/explore/map', 'public_explore_map.png');
  await takeScreenshot('/explore/events', 'public_explore_events.png');
  await takeScreenshot('/explore/equipment', 'public_explore_equipment.png');
  await takeScreenshot('/explore/actors', 'public_explore_actors.png');
  await takeScreenshot('/explore/conflicts', 'public_explore_conflicts.png');
  await takeScreenshot('/submit-tip', 'public_submit_tip.png');
  await takeScreenshot('/login', 'public_login.png');
  await takeScreenshot('/register', 'public_register.png');
  await takeScreenshot('/about', 'public_about.png');
  await takeScreenshot('/contact', 'public_contact.png');
  await takeScreenshot('/forgot-password', 'public_forgot_password.png');
  await takeScreenshot('/offline', 'public_offline.png');
  await takeScreenshot('/explore/events/1', 'public_event_detail.png');
  await takeScreenshot('/explore/equipment/1', 'public_equipment_detail.png');
  await takeScreenshot('/explore/actors/1', 'public_actor_detail.png');
  await takeScreenshot('/explore/conflicts/1', 'public_conflict_detail.png');
  await takeScreenshot('/explore/events?page=2', 'public_events_p2.png');
  await takeScreenshot('/explore/equipment?page=2', 'public_equipment_p2.png');
  await takeScreenshot('/explore/actors?page=2', 'public_actors_p2.png');

  // Login for admin pages
  console.log(`Navigating to ${BASE_URL}/login`);
  await page.goto(`${BASE_URL}/login`);
  await page.waitForSelector('input[type="email"]');
  await page.fill('input[type="email"]', 'admin@example.com');
  await page.fill('input[type="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(5000); // Wait for login and redirect

  // Admin pages (20 screenshots)
  await takeScreenshot('/dashboard', 'admin_dashboard.png');
  await takeScreenshot('/events', 'admin_events.png');
  await takeScreenshot('/events/create', 'admin_events_create.png');
  await takeScreenshot('/equipment', 'admin_equipment.png');
  await takeScreenshot('/actors', 'admin_actors.png');
  await takeScreenshot('/conflicts', 'admin_conflicts.png');
  await takeScreenshot('/zones', 'admin_zones.png');
  await takeScreenshot('/alerts', 'admin_alerts.png');
  await takeScreenshot('/reports', 'admin_reports.png');
  await takeScreenshot('/timeline', 'admin_timeline.png');
  await takeScreenshot('/analytics', 'admin_analytics.png');
  await takeScreenshot('/admin/audit-logs', 'admin_audit_logs.png');
  await takeScreenshot('/admin/settings', 'admin_settings.png');
  await takeScreenshot('/admin/users', 'admin_users.png');
  await takeScreenshot('/admin/roles', 'admin_roles.png');
  await takeScreenshot('/admin/permissions', 'admin_permissions.png');
  await takeScreenshot('/admin/email-templates', 'admin_email_templates.png');
  await takeScreenshot('/admin/ab-testing', 'admin_ab_testing.png');
  await takeScreenshot('/profile', 'admin_profile.png');
  await takeScreenshot('/admin/achievements', 'admin_achievements.png');

  await browser.close();
})();
