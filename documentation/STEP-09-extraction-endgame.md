# Step 09 — Extraction & End Game

## Goal
Implement the extraction mechanic, victory condition (defeating Berdolock),
game-over handling, and the scoring system.

---

## End States

| State | Trigger | Outcome |
|-------|---------|---------|
| **Victory** | Berdolock defeated (room 20) | Show score, gold kept |
| **Extraction** | Player extracts after Turn ≥ 30 | Return to Town with loot |
| **Game Over** | HP reaches 0 (combat, trap, starvation, poison) | All progress lost |

---

## src/Actions/ExtractAction.php

Extraction is only available when `$state->turnCount >= 30`.
The player returns to town with all gold intact.

```php
<?php
namespace Berdolock\Actions;

use Berdolock\GameState;

class ExtractAction
{
    public static function handle(GameState $state): GameState
    {
        if ($state->turnCount < 30) {
            $state->addLog("You haven't survived long enough to extract safely.");
            return $state;
        }

        $gold = $state->player->gold;
        $state->phase = 'town';

        // Reset dungeon progress but keep gold and equipment
        $state->turnCount  = 0;
        $state->roomCount  = 0;
        $state->dangerLevel = 1;
        $state->currentEnemy = null;

        // Clear dungeon status flags
        $state->player->isDark     = false;
        $state->player->isStarving = false;
        // Poison persists — cure it at the Inn

        $state->addLog("You extract successfully with {$gold} gold!");
        $state->addLog("You return to town. Find a healer if poisoned.");

        return $state;
    }
}
```

---

## Victory — Defeating Berdolock

When Berdolock's HP reaches 0 in `CombatAction::handleVictory()`,
set `$state->phase = 'victory'` instead of `'dungeon'`.

Detect this in `handleVictory()` by checking `$enemy->isBoss`:

```php
private static function handleVictory(GameState $state): GameState
{
    $enemy  = $state->currentEnemy;
    $player = $state->player;

    $player->gold += $enemy->goldDrop;
    $state->addLog("You defeat {$enemy->name}! +{$enemy->goldDrop} gold.");

    $state->currentEnemy = null;

    // Boss killed = victory
    if (property_exists($enemy, 'isBoss') && $enemy->isBoss) {
        $state->phase = 'victory';
        $state->addLog("Berdolock falls. The stronghold is yours.");
    } else {
        $state->phase = 'dungeon';
    }

    return $state;
}
```

---

## src/Render/VictoryView.php

```php
<section class="victory">
    <h2>⚔ VICTORY</h2>
    <p>
        <strong><?= htmlspecialchars($state->player->name) ?></strong>
        has conquered Berdolock's Stronghold!
    </p>

    <div class="score-card">
        <h3>Final Score</h3>
        <table>
            <tr><td>Gold Extracted</td><td><?= $state->player->gold ?></td></tr>
            <tr><td>Rooms Explored</td><td><?= $state->roomCount ?></td></tr>
            <tr><td>Turns Survived</td><td><?= $state->turnCount ?></td></tr>
            <tr>
                <td><strong>Total Score</strong></td>
                <td><strong><?= self::calculateScore($state) ?></strong></td>
            </tr>
        </table>
    </div>

    <div class="log">
        <?php foreach ($state->log as $entry): ?>
            <p><?= htmlspecialchars($entry) ?></p>
        <?php endforeach ?>
    </div>

    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
        <input type="hidden" name="action" value="new_game">
        <button type="submit" class="btn-safe">Play Again</button>
    </form>
</section>
<?php
function self_calculateScore_inline(/* passed $state */): int {
    // placeholder — see scoring section below
    return 0;
}
?>
```

> Move `calculateScore()` to a static method on a `Scoring` class
> and call it from the view via `Scoring::calculate($state)`.

---

## src/Scoring.php

```php
<?php
namespace Berdolock;

class Scoring
{
    public static function calculate(GameState $state): int
    {
        $score = $state->player->gold;               // base: gold extracted
        $score += $state->roomCount * 5;             // 5 pts per room explored
        $score += $state->player->hp * 2;            // bonus for HP remaining
        if ($state->phase === 'victory') {
            $score += 500;                           // boss kill bonus
        }
        return $score;
    }
}
```

### Score Breakdown

| Component | Points |
|-----------|--------|
| Gold carried | 1 pt per gold |
| Rooms explored | 5 pts per room |
| HP remaining | 2 pts per HP |
| Berdolock defeated | +500 flat bonus |

---

## src/Render/GameOverView.php (Full Version)

```php
<section class="gameover">
    <h2>☠ You Died</h2>

    <p>
        <strong><?= htmlspecialchars($state->player->name) ?></strong>
        fell in Room <?= $state->roomCount ?>,
        Turn <?= $state->turnCount ?>.
    </p>

    <div class="score-card">
        <h3>Final Score</h3>
        <table>
            <tr><td>Gold (lost)</td><td><?= $state->player->gold ?></td></tr>
            <tr><td>Rooms Explored</td><td><?= $state->roomCount ?></td></tr>
            <tr><td>Turns Survived</td><td><?= $state->turnCount ?></td></tr>
            <tr>
                <td><strong>Total Score</strong></td>
                <td><strong><?= \Berdolock\Scoring::calculate($state) ?></strong></td>
            </tr>
        </table>
    </div>

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

## Handling New Game from Action Dispatcher

The `new_game` action must clear the session before creating a fresh state:

```php
// In action.php, before the match()
if ($action === 'new_game') {
    \Berdolock\Session::clear();
}

$state = match($action) {
    'new_game' => \Berdolock\Actions\NewGame::handle($_POST),
    // ...
};
```

---

## Future: Persistent High Score (Phase 2)

For a first version, scores are ephemeral (session only).
To add a leaderboard later:

1. Add a `scores.json` file in a non-public directory
2. On victory/gameover, append `{ name, score, date }` entries
3. Read and display top 10 on the new game screen

```php
// src/Leaderboard.php (Phase 2 stub)
class Leaderboard
{
    private const FILE = __DIR__ . '/../data/scores.json';

    public static function save(string $name, int $score): void { /* ... */ }
    public static function top(int $limit = 10): array { /* ... */ }
}
```

---

## Complete Phase Flow Diagram

```
[New Game]
    │
    ▼
[Town] ←──────────────────────────────────┐
    │                                      │
    │ Enter Dungeon                        │ Extract (Turn ≥ 30)
    ▼                                      │
[Dungeon] ─── Enemy encounter ──► [Combat] ┤
    │                                      │
    │ Room 20                              │ Victory
    ▼                                      ▼
[Combat vs Berdolock] ──────────────► [Victory]
    │
    │ HP = 0 (any phase)
    ▼
[Game Over]
    │
    └──► [New Game]
```

---

*End of implementation steps. All steps: 01 through 09.*
