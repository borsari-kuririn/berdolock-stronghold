# Berdolock's Stronghold — Agent Instructions

## Documentation Convention

All Markdown (`.md`) files created in this project must be saved inside the `documentation/` directory at the root of the repository.

Do **not** create `.md` files at the root or in any other directory unless the file is a project-level meta file (`AGENTS.md`, `README.md`).

### Rule summary

| File type | Correct location |
|-----------|-----------------|
| Rules, lore, tables, guides | `documentation/` |
| Agent instructions | root (`AGENTS.md`) |

---

## Asset Convention

All visual assets (sprites, dungeon views, portraits, UI elements) **must be drawn using CSS and/or JavaScript**.

Do **not** add image files (`.png`, `.jpg`, `.gif`, `.svg`, `.webp`) to the project for game visuals.

| Asset type | Correct implementation |
|------------|------------------------|
| Enemy / NPC sprites | JS canvas pixel arrays (`public/assets/js/sprites.js`) |
| Dungeon corridor view | Pure CSS geometry (nested `div` perspective) |
| UI icons / decorations | CSS only (`::before` / `::after` content or `box-shadow`) |
| External image files | **Forbidden** for game assets |

---

## Local Development Setup (PHP)

### Requirements

- PHP 8.1+ installed and available on `PATH`
- No framework or build tool is required — the built-in PHP server is sufficient

### Starting the dev server

Run from the project root:

```bash
php -S localhost:8080 -t public
```

This serves the `public/` directory on `http://localhost:8080`.

### Running tests

If a `tests/` directory exists with PHPUnit tests:

```bash
# Install PHPUnit (first time only)
composer require --dev phpunit/phpunit

# Run all tests
./vendor/bin/phpunit tests/
```

If no Composer/PHPUnit is set up, plain PHP scripts can be executed directly:

```bash
php tests/SomeTest.php
```

### Verifying the setup

| Check | Command |
|-------|---------|
| PHP version | `php -v` |
| List routes / entry point | open `http://localhost:8080` in browser |
| Syntax-check a file | `php -l public/index.php` |

---

## UI Journey Tests (skill)

The `ui-journey-tests` skill ([`.github/skills/ui-journey-tests/`](.github/skills/ui-journey-tests/SKILL.md)) validates user paths through the browser interface using Playwright.

**Invoke it by describing a user path**, for example:

> "Run a journey test for character creation → enter dungeon → combat → extract"

The skill will:
1. Start the PHP dev server (if not already running)
2. Execute the path step-by-step in a real browser
3. Screenshot each step
4. Save a report to `documentation/user-tests/YYYY-MM-DD-<slug>.md`

### Playwright dependency

```bash
npm install playwright
npx playwright install chromium
```

### Validated User Paths

_Add an entry here each time a new canonical path is confirmed working._

| Path | Report | Date |
|------|--------|------|
| _(none yet)_ | — | — |
