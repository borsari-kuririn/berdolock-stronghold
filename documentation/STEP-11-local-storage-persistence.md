# Step 11 — Local Storage Persistence

## Goal
Persist the game state to `localStorage` so the player can close the browser
and resume their run without losing progress. On page load, restore the saved
state automatically; on every state-changing action, write the latest snapshot.

---

## Design Principle

PHP `$_SESSION` remains the authoritative state for all server logic.
`localStorage` is a client-side mirror used only for **restore-on-reload**.
The flow is:

```
Page load → read localStorage → POST /action.php?action=restore → $_SESSION updated
State change → server returns new state JSON → JS writes to localStorage
```

---

## JSON Snapshot Format

The server encodes the full session state as JSON and injects it into every
response. Add a hidden element in `index.php`:

```html
<meta id="game-state-snapshot"
      data-state="<?= htmlspecialchars(json_encode($state), ENT_QUOTES, 'UTF-8') ?>">
```

The JS layer reads and writes this value to `localStorage` under the key
`berdolock_state`.

---

## public/assets/js/persistence.js

```js
const STORAGE_KEY = 'berdolock_state';

/** Save the current snapshot after every server response. */
function saveState() {
    const meta = document.getElementById('game-state-snapshot');
    if (!meta) return;
    try {
        localStorage.setItem(STORAGE_KEY, meta.dataset.state);
    } catch (e) {
        // localStorage quota exceeded or unavailable — fail silently
    }
}

/** On page load, POST the saved snapshot to the server to re-hydrate $_SESSION. */
async function restoreState() {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;

    try {
        JSON.parse(raw); // validate before sending
    } catch (e) {
        localStorage.removeItem(STORAGE_KEY);
        return;
    }

    const body = new URLSearchParams({ action: 'restore', state: raw });
    const res  = await fetch('action.php', { method: 'POST', body });

    if (res.ok) {
        // Reload so PHP renders the restored state
        window.location.reload();
    } else {
        localStorage.removeItem(STORAGE_KEY);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Only attempt restore when the server has no active session yet
    const meta = document.getElementById('game-state-snapshot');
    const hasSession = meta && meta.dataset.state !== 'null';
    if (!hasSession) {
        restoreState();
    }
});

// Re-save after every HTMX swap
document.body.addEventListener('htmx:afterSwap', saveState);
// Also save on initial full-page load
window.addEventListener('load', saveState);
```

---

## src/Actions/RestoreAction.php

Validates and re-hydrates `$_SESSION` from the client-supplied JSON.
All values are validated; untrusted input is never executed.

```php
<?php
namespace Berdolock\Actions;

use Berdolock\GameState;
use Berdolock\Session;

class RestoreAction
{
    public static function handle(string $rawJson): bool
    {
        $data = json_decode($rawJson, true);

        if (!is_array($data)) {
            return false;
        }

        // Only restore when no active session exists
        if (Session::has()) {
            return false;
        }

        $state = GameState::fromArray($data);
        Session::save($state);

        return true;
    }
}
```

---

## GameState::fromArray()

Add a static constructor to `src/GameState.php` that rebuilds the object
graph from a plain associative array (the decoded JSON):

```php
public static function fromArray(array $data): self
{
    $state = new self();
    $state->phase      = $data['phase']      ?? 'town';
    $state->turnCount  = (int)($data['turnCount']  ?? 0);
    $state->roomCount  = (int)($data['roomCount']  ?? 0);
    $state->log        = (array)($data['log']       ?? []);
    $state->player     = Player::fromArray($data['player'] ?? []);

    if (!empty($data['currentEnemy'])) {
        $state->currentEnemy = Enemy::fromArray($data['currentEnemy']);
    }

    return $state;
}
```

Add matching `fromArray()` methods to `Player` and `Enemy` following the
same pattern.

---

## action.php — restore route

Add the restore case to the action dispatcher:

```php
case 'restore':
    $raw = $_POST['state'] ?? '';
    // Limit payload size to 64 KB to prevent abuse
    if (strlen($raw) > 65536) {
        http_response_code(400);
        exit;
    }
    $restored = \Berdolock\Actions\RestoreAction::handle($raw);
    http_response_code($restored ? 200 : 409);
    exit;
```

---

## Clearing Saved State

Call `clearSavedState()` from JavaScript when the player starts a new game
or reaches a terminal state (victory / game over) so a stale snapshot cannot
accidentally reload a finished run.

```js
function clearSavedState() {
    localStorage.removeItem(STORAGE_KEY);
}

// Wire up to New Game and terminal screens
document.body.addEventListener('htmx:afterSwap', () => {
    const phase = document.getElementById('game-state-snapshot')?.dataset?.phase;
    if (phase === 'gameover' || phase === 'victory') {
        clearSavedState();
    }
});
```

---

## Security Notes

| Risk | Mitigation |
|------|------------|
| Oversized payload | Hard limit of 64 KB in action.php |
| Malformed JSON | `json_decode` check + validation in `RestoreAction` |
| State tampering | `RestoreAction::handle` only runs when no server session exists; server logic always re-validates moves |
| XSS via snapshot | `htmlspecialchars` with `ENT_QUOTES` when embedding JSON |

---

## Script Tag

Add to `index.php` before `</body>`:

```html
<script src="assets/js/persistence.js"></script>
```
