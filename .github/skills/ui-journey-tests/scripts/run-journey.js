#!/usr/bin/env node
/**
 * UI Journey Test Runner (Playwright)
 * Usage: node run-journey.js --baseUrl <url> --steps '<json>' [--headed] [--outputDir <path>]
 */

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

// --- CLI args ---
const args = process.argv.slice(2);
const get = (flag) => { const i = args.indexOf(flag); return i !== -1 ? args[i + 1] : null; };

const baseUrl   = get('--baseUrl')   || 'http://localhost:8080';
const stepsRaw  = get('--steps')     || '[]';
const outputDir = get('--outputDir') || path.join('documentation', 'user-tests', 'screenshots');
const headed    = !args.includes('--headless');

const steps = JSON.parse(stepsRaw);

// --- Helpers ---
function resolve(url) {
  return url.startsWith('http') ? url : `${baseUrl}${url}`;
}

async function takeScreenshot(page, dir, label) {
  fs.mkdirSync(dir, { recursive: true });
  const file = path.join(dir, `${label}.png`);
  await page.screenshot({ path: file, fullPage: true });
  return file;
}

// --- Runner ---
(async () => {
  const browser = await chromium.launch({ headless: !headed });
  const context = await browser.newContext();
  const page    = await context.newPage();

  const results = [];
  const slug    = `run-${Date.now()}`;
  const shotDir = path.join(outputDir, slug);
  let   passed  = true;

  for (let i = 0; i < steps.length; i++) {
    const step   = steps[i];
    const label  = `step-${String(i + 1).padStart(2, '0')}`;
    const result = { step: i + 1, action: step.action, status: 'pass', screenshot: null, error: null };

    try {
      if (step.action === 'goto') {
        await page.goto(resolve(step.url));

      } else if (step.action === 'click') {
        await page.click(step.selector);

      } else if (step.action === 'fill') {
        await page.fill(step.selector, step.value);

      } else if (step.action === 'select') {
        await page.selectOption(step.selector, step.value);

      } else if (step.action === 'wait') {
        await page.waitForSelector(step.selector, { timeout: 8000 });

      } else if (step.action === 'expect') {
        if ('visible' in step) {
          const el = page.locator(step.selector);
          if (step.visible) await el.waitFor({ state: 'visible', timeout: 8000 });
          else              await el.waitFor({ state: 'hidden',  timeout: 8000 });
        }
        if ('text' in step) {
          await page.locator(step.selector).filter({ hasText: step.text }).waitFor({ timeout: 8000 });
        }

      } else if (step.action === 'screenshot') {
        // explicit screenshot — handled below
      }

      result.screenshot = await takeScreenshot(page, shotDir, label);

    } catch (err) {
      result.status    = 'fail';
      result.error     = err.message;
      result.screenshot = await takeScreenshot(page, shotDir, `${label}-FAIL`).catch(() => null);
      passed = false;
    }

    results.push(result);
    if (!passed && step.haltOnFail !== false) break;
  }

  await browser.close();

  const report = { baseUrl, passed, steps: results };
  process.stdout.write(JSON.stringify(report, null, 2) + '\n');
  process.exitCode = passed ? 0 : 1;
})();
