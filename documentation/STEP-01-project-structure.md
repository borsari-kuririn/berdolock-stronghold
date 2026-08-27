# Step 01 — Project Structure & Setup

## Goal
Establish the directory layout, entry point, and dependencies for an HTMX + PHP dungeon crawler.

---

## Directory Layout

```
berdolock-stronghold/
├── public/                  # Web root (point server here)
│   ├── index.php            # Entry point — renders shell + initial state
│   ├── action.php           # HTMX endpoint — all game actions POST here
│   └── assets/
│       ├── style.css
│       └── htmx.min.js      # htmx 2.x (self-hosted)
├── src/
│   ├── GameState.php        # Core data classes
│   ├── Session.php          # Load/save GameState from $_SESSION
│   ├── Actions/
│   │   ├── NewGame.php
│   │   ├── TownAction.php
│   │   ├── ExploreAction.php
│   │   ├── CombatAction.php
│   │   └── ExtractAction.php
│   └── Render/
│       ├── TownView.php
│       ├── DungeonView.php
│       └── CombatView.php
├── documentation/
└── composer.json
```

---

## PHP Requirements

- PHP 8.2+
- No framework — vanilla PHP with autoloading
- Sessions for state persistence (no database in first version)

### composer.json

```json
{
    "name": "berdolock/stronghold",
    "require": {
        "php": ">=8.2"
    },
    "autoload": {
        "psr-4": {
            "Berdolock\\": "src/"
        }
    }
}
```

Run `composer install` and `composer dump-autoload` after creating this file.

---

## Entry Point — public/index.php

This file starts the session, loads state, and renders the full page shell.
HTMX swaps will replace `#game-panel` only — the shell itself never reloads.

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Berdolock\Session;

session_start();
$state = Session::load();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Berdolock's Stronghold</title>
    <link rel="stylesheet" href="/assets/style.css">
    <script src="/assets/htmx.min.js"></script>
</head>
<body>
    <header>
        <h1>Berdolock's Stronghold</h1>
    </header>

    <main id="game-panel">
        <?php
        if ($state === null) {
            include __DIR__ . '/../src/Render/NewGameView.php';
        } elseif ($state->phase === 'town') {
            include __DIR__ . '/../src/Render/TownView.php';
        } elseif ($state->phase === 'dungeon') {
            include __DIR__ . '/../src/Render/DungeonView.php';
        } elseif ($state->phase === 'combat') {
            include __DIR__ . '/../src/Render/CombatView.php';
        } elseif ($state->phase === 'gameover') {
            include __DIR__ . '/../src/Render/GameOverView.php';
        }
        ?>
    </main>
</body>
</html>
```

---

## Action Dispatcher — public/action.php

All HTMX `hx-post` targets point here. The `action` field in the form/request
determines which handler runs.

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Berdolock\Session;
use Berdolock\Actions\{NewGame, TownAction, ExploreAction, CombatAction, ExtractAction};

session_start();
$state = Session::load();

$action = $_POST['action'] ?? '';

$state = match($action) {
    'new_game'  => NewGame::handle($_POST),
    'town'      => TownAction::handle($state, $_POST),
    'explore'   => ExploreAction::handle($state),
    'attack'    => CombatAction::handle($state, 'attack'),
    'flee'      => CombatAction::handle($state, 'flee'),
    'extract'   => ExtractAction::handle($state),
    default     => $state,
};

Session::save($state);

// Render only the inner panel — HTMX swaps #game-panel content
match($state->phase) {
    'town'     => include __DIR__ . '/../src/Render/TownView.php',
    'dungeon'  => include __DIR__ . '/../src/Render/DungeonView.php',
    'combat'   => include __DIR__ . '/../src/Render/CombatView.php',
    'gameover' => include __DIR__ . '/../src/Render/GameOverView.php',
    default    => include __DIR__ . '/../src/Render/NewGameView.php',
};
```

---

## HTMX Wiring Pattern

Every game button uses this pattern:

```html
<form hx-post="/action.php"
      hx-target="#game-panel"
      hx-swap="innerHTML">
    <input type="hidden" name="action" value="explore">
    <button type="submit">Explore Next Room</button>
</form>
```

`hx-target="#game-panel"` ensures the outer shell is preserved and only the
game content area is replaced.

---

## Local Development

```bash
cd public
php -S localhost:8080
```

Open `http://localhost:8080` in a browser.

---

## Next Step

→ [STEP-02 — Game State & Session](STEP-02-game-state.md)
