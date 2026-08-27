# Berdolock's Stronghold

Berdolock's Stronghold is a browser-based dungeon adventure project built with PHP.

## Project Status

Development is in progress. The project documentation in `documentation/` defines the planned architecture, game systems, UI, and persistence behavior.

## Local Setup

### Requirements

- PHP 8.2 or newer
- Composer, if installing project dependencies

### Run locally

From the project root, start PHP's built-in web server:

```bash
php -S localhost:8080 -t public
```

Open [http://localhost:8080](http://localhost:8080) in a browser.

### Validate PHP syntax

```bash
php -l public/index.php
```

### Run automated tests

If PHPUnit tests are present:

```bash
composer install
./vendor/bin/phpunit tests/
```

Plain PHP test scripts can be run directly:

```bash
php tests/SomeTest.php
```

## Documentation

| Document | Subject |
|----------|---------|
| [Model](documentation/MODEL.md) | Project model and architecture |
| [Rules](documentation/RULES.md) | Core game rules |
| [Step 01: Project Structure](documentation/STEP-01-project-structure.md) | Project organization |
| [Step 02: Game State](documentation/STEP-02-game-state.md) | Game state management |
| [Step 03: Character Creation](documentation/STEP-03-character-creation.md) | Character creation flow |
| [Step 04: Town Phase](documentation/STEP-04-town-phase.md) | Town activities and preparation |
| [Step 05: Dungeon Exploration](documentation/STEP-05-dungeon-exploration.md) | Dungeon exploration |
| [Step 06: Combat System](documentation/STEP-06-combat-system.md) | Combat behavior |
| [Step 07: Encounters](documentation/STEP-07-encounters.md) | Encounter design |
| [Step 08: HTMX UI](documentation/STEP-08-htmx-ui.md) | Server-rendered interface |
| [Step 09: Extraction and Endgame](documentation/STEP-09-extraction-endgame.md) | Extraction and endgame rules |
| [Step 10: UI Design](documentation/STEP-10-ui-design.md) | Visual and interaction design |
| [Step 11: Local Storage Persistence](documentation/STEP-11-local-storage-persistence.md) | Browser persistence |

## Change Log

This section records meaningful project changes in reverse chronological order. Add a dated entry when a feature, behavior, documentation area, or development workflow changes.

### 2026-08-26

- Added PHP local development and testing instructions to `AGENTS.md`.
- Added the `ui-journey-tests` agent skill for Playwright-based browser journey validation.
- Added the UI journey report template and screenshot output conventions.
- Added this README as the central project and documentation index.

### Change Log Entry Template

```markdown
### YYYY-MM-DD

- Added, changed, fixed, or removed: describe the change.
- Documentation: link to the affected document or report.
```

## UI Journey Test Reports

Browser user-path validation reports are stored in [`documentation/user-tests/`](documentation/user-tests/). The `ui-journey-tests` skill records each journey's steps, expected results, screenshots, failures, and observations.

No journey reports have been recorded yet.

## Repository Guidance

Project-specific agent guidance is available in [AGENTS.md](AGENTS.md), including PHP setup, testing conventions, asset restrictions, and UI journey testing instructions.
