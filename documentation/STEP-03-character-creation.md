# Step 03 — Character Creation

## Goal
Implement character initialization by rolling 2d6 for each attribute,
computing derived stats, and granting starting equipment.

---

## Flow

```
Player opens index.php (no session) →
  NewGameView shows name input + "Roll Character" button →
    POST action=new_game →
      NewGame::handle() rolls stats, sets up state →
        Redirect to Town Phase
```

---

## src/Actions/NewGame.php

```php
<?php
namespace Berdolock\Actions;

use Berdolock\{GameState, Player};

class NewGame
{
    public static function handle(array $post): GameState
    {
        $state  = new GameState();
        $player = $state->player;

        $player->name = trim($post['name'] ?? 'Adventurer');
        if ($player->name === '') {
            $player->name = 'Adventurer';
        }

        // Roll 2d6 for each attribute
        $player->str = self::roll2d6();
        $player->agi = self::roll2d6();
        $player->int = self::roll2d6();
        $player->end = self::roll2d6();

        // Derived stats
        $player->maxHp = $player->end * 2;
        $player->hp    = $player->maxHp;

        // Starting resources
        $player->gold    = 20;
        $player->torches = 2;
        $player->rations = 2;

        $state->phase = 'town';
        $state->addLog("Welcome, {$player->name}. Prepare yourself before entering the stronghold.");

        return $state;
    }

    private static function roll2d6(): int
    {
        return random_int(1, 6) + random_int(1, 6);
    }
}
```

---

## src/Render/NewGameView.php

This is a partial — rendered inside `#game-panel`.

```php
<section class="new-game">
    <h2>A New Adventurer Arrives</h2>
    <p>You stand at the gates of Berdolock's Stronghold.</p>

    <form hx-post="/action.php"
          hx-target="#game-panel"
          hx-swap="innerHTML">
        <input type="hidden" name="action" value="new_game">

        <label for="name">Your Name:</label>
        <input type="text" id="name" name="name"
               placeholder="Adventurer" maxlength="24">

        <button type="submit">Roll Character &amp; Begin</button>
    </form>
</section>
```

---

## Attribute Reference

| Attribute | Abbreviation | Rolled With | Used For |
|-----------|-------------|-------------|----------|
| Strength | STR | 2d6 | Attack power, carry limit |
| Agility | AGI | 2d6 | Initiative, flee tests, trap avoidance |
| Intelligence | INT | 2d6 | Magic item identification, perception |
| Endurance | END | 2d6 | Max HP (END × 2) |

Average roll on 2d6 is **7**, so a typical character starts with:
- HP: 14
- Attack: 7 base (before weapon bonus)
- Carry: 7 slots

---

## Attribute Test Helper

Tests are used throughout exploration and combat. Centralise the logic:

```php
<?php
namespace Berdolock;

class Dice
{
    /** Roll 2d6 and return true if result ≤ attribute (success). */
    public static function test(int $attribute, bool $advantage = false, bool $disadvantage = false): bool
    {
        if ($advantage) {
            // Roll 3d6, discard highest
            $rolls = [random_int(1,6), random_int(1,6), random_int(1,6)];
            sort($rolls);
            $result = $rolls[0] + $rolls[1];
        } elseif ($disadvantage) {
            // Roll 3d6, discard lowest
            $rolls = [random_int(1,6), random_int(1,6), random_int(1,6)];
            rsort($rolls);
            $result = $rolls[0] + $rolls[1];
        } else {
            $result = random_int(1,6) + random_int(1,6);
        }

        return $result <= $attribute;
    }

    /** Roll a die with $sides faces. */
    public static function roll(int $sides): int
    {
        return random_int(1, $sides);
    }
}
```

---

## Displaying Rolled Stats (Optional Re-roll UX)

After rolling, show the stats before confirming. Use HTMX to render a preview
panel and a "Confirm" / "Re-roll" choice:

1. First POST `action=roll_preview` → renders stat block with two buttons
2. "Confirm" POST `action=confirm_character` → saves state, goes to town
3. "Re-roll" POST `action=roll_preview` → renders fresh stats (max 3 re-rolls to preserve lethality)

Track re-rolls in a hidden field `<input name="rerolls" value="0">` and
increment server-side; disable the Re-roll button when `rerolls >= 3`.

---

## Next Step

→ [STEP-04 — Town Phase](STEP-04-town-phase.md)
