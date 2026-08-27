# Step 08 — HTMX UI & Partial Rendering

## Goal
Define how HTMX wires the PHP partials together, handle loading states,
and structure CSS for the game's phases.

---

## Core HTMX Principles Used

| Feature | Purpose |
|---------|---------|
| `hx-post` | Send game actions to `action.php` |
| `hx-target="#game-panel"` | Replace only the inner content area |
| `hx-swap="innerHTML"` | Swap inner HTML, preserving the outer shell |
| `hx-indicator` | Show a spinner while the server processes a turn |
| `hx-disabled-elt` | Prevent double-submits on slow connections |

---

## Shell Layout (public/index.php)

The outer shell **never changes**. HTMX only touches `#game-panel`.

```
<body>
  <header>                   ← static, never swapped
  <main id="game-panel">     ← HTMX target
    [phase view rendered here]
  </main>
  <div id="spinner">         ← hx-indicator target
</body>
```

---

## Loading Indicator

Add this to `index.php` once. HTMX adds `.htmx-request` to it during requests.

```html
<div id="spinner" class="htmx-indicator">Processing...</div>
```

Reference it on every form:

```html
<form hx-post="/action.php"
      hx-target="#game-panel"
      hx-swap="innerHTML"
      hx-indicator="#spinner">
```

---

## Preventing Double-Submit

On slow servers a player might click twice. Use `hx-disabled-elt`:

```html
<button type="submit"
        hx-disabled-elt="this">
    → Explore Next Room
</button>
```

The button is automatically disabled while the request is in flight.

---

## Form Pattern Reference

Every interactive element follows this identical pattern:

```html
<form hx-post="/action.php"
      hx-target="#game-panel"
      hx-swap="innerHTML"
      hx-indicator="#spinner">
    <input type="hidden" name="action" value="ACTION_NAME">
    <!-- optional sub-action or payload fields -->
    <button type="submit" hx-disabled-elt="this">Label</button>
</form>
```

No custom JavaScript is needed. HTMX handles everything.

---

## Phase → View Mapping

| `$state->phase` | View file | HTMX form actions available |
|----------------|-----------|------------------------------|
| `null` | `NewGameView.php` | `new_game` |
| `town` | `TownView.php` | `town` (with `sub` field) |
| `dungeon` | `DungeonView.php` | `explore`, `extract` |
| `combat` | `CombatView.php` | `attack`, `flee` |
| `gameover` | `GameOverView.php` | `new_game` |
| `victory` | `VictoryView.php` | `new_game` |

---

## src/Render/GameOverView.php

```php
<section class="gameover">
    <h2>☠ You Died</h2>
    <p>
        <?= htmlspecialchars($state->player->name) ?> fell in Room
        <?= $state->roomCount ?> on Turn <?= $state->turnCount ?>.
    </p>
    <p>Gold recovered: <?= $state->player->gold ?></p>

    <div class="log">
        <?php foreach ($state->log as $entry): ?>
            <p><?= htmlspecialchars($entry) ?></p>
        <?php endforeach ?>
    </div>

    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
        <input type="hidden" name="action" value="new_game">
        <button type="submit">Try Again</button>
    </form>
</section>
```

---

## CSS Structure — public/assets/style.css

```css
/* ── Reset ──────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: #111;
    color: #ccc;
    font-family: 'Courier New', monospace;
    max-width: 700px;
    margin: 0 auto;
    padding: 1rem;
}

header h1 {
    color: #b8860b;
    text-align: center;
    margin-bottom: 1.5rem;
    letter-spacing: 0.1em;
}

/* ── Game Panel ─────────────────────────────────────── */
#game-panel {
    border: 1px solid #333;
    padding: 1.5rem;
    min-height: 400px;
}

/* ── HUD ────────────────────────────────────────────── */
.hud {
    background: #1a1a1a;
    padding: 0.5rem;
    border: 1px solid #444;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

/* ── Log ────────────────────────────────────────────── */
.log {
    margin-top: 1.5rem;
    border-top: 1px solid #333;
    padding-top: 0.75rem;
}

.log p {
    font-size: 0.85rem;
    color: #999;
    margin-bottom: 0.25rem;
}

.log p:first-child { color: #ddd; }

/* ── Buttons ────────────────────────────────────────── */
button {
    background: #222;
    color: #ccc;
    border: 1px solid #555;
    padding: 0.5rem 1rem;
    cursor: pointer;
    font-family: inherit;
    margin: 0.25rem 0;
    display: block;
    width: 100%;
}

button:hover:not(:disabled) { background: #333; color: #fff; }
button:disabled              { opacity: 0.4; cursor: not-allowed; }

.btn-danger  { border-color: #8b0000; color: #ff6666; }
.btn-attack  { border-color: #8b6914; color: #ffd700; }
.btn-flee    { border-color: #444; }
.btn-safe    { border-color: #2e6e2e; color: #90ee90; }

/* ── Status Badges ──────────────────────────────────── */
.status-bad  { color: #ff4444; font-weight: bold; }
.status-good { color: #44ff44; }

/* ── Combat Layout ──────────────────────────────────── */
.combatants {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}

.player-card, .enemy-card {
    flex: 1;
    background: #1a1a1a;
    padding: 0.75rem;
    border: 1px solid #333;
}

.vs { font-size: 1.5rem; color: #555; }

/* ── Shop ───────────────────────────────────────────── */
.shop, .inn { margin-bottom: 1rem; }
.shop h3, .inn h3 { color: #b8860b; margin-bottom: 0.5rem; }

/* ── Spinner ────────────────────────────────────────── */
#spinner {
    display: none;   /* HTMX shows it via .htmx-request */
    position: fixed;
    top: 0.5rem;
    right: 0.5rem;
    background: #333;
    color: #ccc;
    padding: 0.25rem 0.75rem;
    border: 1px solid #555;
    font-size: 0.8rem;
}

.htmx-request #spinner,
#spinner.htmx-request { display: block; }
```

---

## No-JS Fallback

If the user has JavaScript disabled, HTMX degrades to a full page POST.
Because `action.php` renders the same partial HTML without a full shell,
disable-JS users will see unstyled partial HTML.

To support no-JS: detect `$_SERVER['HTTP_HX_REQUEST']` (set by HTMX) and
render the full shell from `action.php` when it is absent.

```php
$isHtmx = isset($_SERVER['HTTP_HX_REQUEST']);
if (!$isHtmx) {
    // render full page shell wrapping the partial
    include __DIR__ . '/shell_open.php';
}
// ... render partial ...
if (!$isHtmx) {
    include __DIR__ . '/shell_close.php';
}
```

---

## Next Step

→ [STEP-09 — Extraction & End Game](STEP-09-extraction-endgame.md)
