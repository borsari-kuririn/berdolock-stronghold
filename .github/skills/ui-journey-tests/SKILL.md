---
name: ui-journey-tests
description: 'Run UI journey tests for Berdolock Stronghold. Use when validating a specific user path through the game interface, testing a flow end-to-end, verifying a feature works in the browser, or saving a test report to documentation/user-tests/. Triggers: "journey test", "test user path", "validate UI flow", "run UI test", "test the interface".'
argument-hint: 'Describe the user path to test, e.g. "character creation → enter dungeon → combat → extract"'
---

# UI Journey Tests

Validates specific user paths through the browser UI, then saves a structured report to `documentation/user-tests/`.

## When to Use

- Verifying a feature or game flow works end-to-end in the browser
- Regression-testing a user path after changes
- Capturing UI state (screenshots, DOM snapshots) at each step
- Producing a reviewable, persistent record in `documentation/user-tests/`

## Procedure

### 1. Start the dev server

```bash
php -S localhost:8080 -t public
```

Confirm the server is up before continuing.

### 2. Define the journey

Collect the user path to test. Each step is:
- A URL or action (click, fill, submit)
- An expected outcome (element visible, text present, URL change)

Use the [journey template](./assets/journey-template.md) as the report skeleton.

### 3. Execute with Playwright

Run [run-journey.js](./scripts/run-journey.js), passing the steps as a JSON array via `--steps`:

```bash
node .github/skills/ui-journey-tests/scripts/run-journey.js \
  --baseUrl http://localhost:8080 \
  --steps '[{"action":"goto","url":"/"},{"action":"click","selector":"#start-game"},{"action":"expect","selector":"#character-name","visible":true}]'
```

The script:
1. Opens a Chromium instance (headed by default for visibility)
2. Executes each step in order
3. Takes a screenshot on every step and on any failure
4. Outputs a JSON result array

### 4. Save the report

Create a new file in `documentation/user-tests/` named `YYYY-MM-DD-<slug>.md` using the [journey template](./assets/journey-template.md). Fill in:

| Field | Source |
|-------|--------|
| Date | today |
| Path tested | argument passed to the skill |
| Steps | from `--steps` input |
| Results | JSON output from the script |
| Screenshots | file paths emitted by the script |
| Pass / Fail | overall outcome |

### 5. Update AGENTS.md (optional)

If a new canonical user path was validated for the first time, add it to the **Validated User Paths** section in `AGENTS.md`.

## Step Action Reference

| `action` | Required fields | Description |
|----------|-----------------|-------------|
| `goto` | `url` | Navigate to URL (relative or absolute) |
| `click` | `selector` | Click an element |
| `fill` | `selector`, `value` | Type text into an input |
| `select` | `selector`, `value` | Pick an `<option>` by value |
| `expect` | `selector`, `visible`\|`text` | Assert element state |
| `wait` | `selector` | Wait for element to appear |
| `screenshot` | _(none)_ | Force a screenshot at this step |

## Output Conventions

- Screenshots go to `documentation/user-tests/screenshots/<report-slug>/step-N.png`
- Report files must follow `YYYY-MM-DD-<slug>.md` naming
- One report per journey run — do not append to existing reports
