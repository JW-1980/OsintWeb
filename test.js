import { chromium } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  await page.goto('http://127.0.0.1:8000/install');
  await page.waitForTimeout(2000); // give vue time to mount
  console.log(await page.content());

  await browser.close();
})();
