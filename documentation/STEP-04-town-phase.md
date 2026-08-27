# Step 04 — Town Phase

## Goal
Implement the safe Town Phase where the player manages resources,
buys equipment, and decides when to enter the dungeon.

---

## Available Town Actions

| Action | Cost | Effect |
|--------|------|--------|
| Buy Torch | 5 gold | +1 Torch |
| Buy Ration | 5 gold | +1 Ration |
| Buy Dagger | 10 gold | +1 weapon bonus |
| Buy Short Sword | 25 gold | +2 weapon bonus |
| Buy Leather Armor | 20 gold | +1 armor bonus (PD) |
| Buy Shield | 15 gold | +1 shield bonus (PD) |
| Sleep at Inn | 10 gold | Restore all HP; clear Poisoned |
| Enter Dungeon | Free | Begin exploration from Turn 1 |

---

## src/Actions/TownAction.php

```php
<?php
namespace Berdolock\Actions;

use Berdolock\GameState;

class TownAction
{
    public static function handle(GameState $state, array $post): GameState
    {
        $sub = $post['sub'] ?? '';

        return match($sub) {
            'buy_torch'      => self::buy($state, 5,  fn($p) => $p->torches++,     'Bought 1 Torch.'),
            'buy_ration'     => self::buy($state, 5,  fn($p) => $p->rations++,     'Bought 1 Ration.'),
            'buy_dagger'     => self::buy($state, 10, fn($p) => $p->weaponBonus++, 'Equipped Dagger (+1 ATK).'),
            'buy_sword'      => self::buy($state, 25, fn($p) => $p->weaponBonus += 2, 'Equipped Short Sword (+2 ATK).'),
            'buy_armor'      => self::buy($state, 20, fn($p) => $p->armorBonus++,  'Equipped Leather Armor (+1 PD).'),
            'buy_shield'     => self::buy($state, 15, fn($p) => $p->shieldBonus++, 'Equipped Shield (+1 PD).'),
            'rest_at_inn'    => self::rest($state),
            'enter_dungeon'  => self::enterDungeon($state),
            default          => $state,
        };
    }

    private static function buy(GameState $state, int $cost, callable $apply, string $msg): GameState
    {
        if ($state->player->gold < $cost) {
            $state->addLog("Not enough gold.");
            return $state;
        }
        $state->player->gold -= $cost;
        $apply($state->player);
        $state->addLog($msg);
        return $state;
    }

    private static function rest(GameState $state): GameState
    {
        $player = $state->player;
        if ($player->gold < 10) {
            $state->addLog("The innkeeper wants 10 gold. You can't afford it.");
            return $state;
        }
        $player->gold -= 10;
        $player->hp         = $player->maxHp;
        $player->isPoisoned = false;
        $state->addLog("You rest at the inn. HP fully restored.");
        return $state;
    }

    private static function enterDungeon(GameState $state): GameState
    {
        if ($state->player->torches === 0) {
            $state->addLog("You need at least 1 torch before entering.");
            return $state;
        }
        $state->phase     = 'dungeon';
        $state->turnCount = 0;
        $state->roomCount = 0;
        $state->addLog("You push open the iron gate and step into the darkness...");
        return $state;
    }
}
```

---

## src/Render/TownView.php

```php
<section class="town">
    <h2>Town of Threskar</h2>
    <p class="flavour">The stronghold looms in the distance. Rest, resupply, then go in.</p>

    <div class="player-stats">
        <strong><?= htmlspecialchars($state->player->name) ?></strong>
        &nbsp;|&nbsp; HP: <?= $state->player->hp ?>/<?= $state->player->maxHp ?>
        &nbsp;|&nbsp; Gold: <?= $state->player->gold ?>
        &nbsp;|&nbsp; Torches: <?= $state->player->torches ?>
        &nbsp;|&nbsp; Rations: <?= $state->player->rations ?>
        <?php if ($state->player->isPoisoned): ?>
            &nbsp;<span class="status-bad">[POISONED]</span>
        <?php endif ?>
    </div>

    <div class="shop">
        <h3>Shop</h3>
        <?php
        $items = [
            ['sub'=>'buy_torch',  'label'=>'Torch',        'cost'=>5],
            ['sub'=>'buy_ration', 'label'=>'Ration',       'cost'=>5],
            ['sub'=>'buy_dagger', 'label'=>'Dagger (+1 ATK)', 'cost'=>10],
            ['sub'=>'buy_sword',  'label'=>'Short Sword (+2 ATK)', 'cost'=>25],
            ['sub'=>'buy_armor',  'label'=>'Leather Armor (+1 PD)', 'cost'=>20],
            ['sub'=>'buy_shield', 'label'=>'Shield (+1 PD)',    'cost'=>15],
        ];
        foreach ($items as $item):
            $disabled = $state->player->gold < $item['cost'] ? 'disabled' : '';
        ?>
        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
            <input type="hidden" name="action" value="town">
            <input type="hidden" name="sub" value="<?= $item['sub'] ?>">
            <button type="submit" <?= $disabled ?>>
                <?= $item['label'] ?> — <?= $item['cost'] ?> gold
            </button>
        </form>
        <?php endforeach ?>
    </div>

    <div class="inn">
        <h3>Inn</h3>
        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
            <input type="hidden" name="action" value="town">
            <input type="hidden" name="sub" value="rest_at_inn">
            <button type="submit" <?= $state->player->gold < 10 ? 'disabled' : '' ?>>
                Sleep at Inn (Restore HP) — 10 gold
            </button>
        </form>
    </div>

    <div class="enter">
        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
            <input type="hidden" name="action" value="town">
            <input type="hidden" name="sub" value="enter_dungeon">
            <button type="submit" class="btn-danger">
                ⚔ Enter the Stronghold
            </button>
        </form>
    </div>

    <div class="log">
        <?php foreach ($state->log as $entry): ?>
            <p><?= htmlspecialchars($entry) ?></p>
        <?php endforeach ?>
    </div>
</section>
```

---

## Equipment Stacking Rules

- **Weapon bonus**: only the highest-tier weapon counts (buying a sword replaces the dagger bonus — enforce this in `TownAction` by resetting `weaponBonus` before applying)
- **Armor + Shield**: additive; both can be worn simultaneously
- **Torches / Rations**: unlimited stacking, just increment the counter

---

## Return to Town

After extracting from the dungeon, the player returns to town with all gold earned.
The `ExtractAction` sets `$state->phase = 'town'` and shows the extracted gold summary.

---

## Next Step

→ [STEP-05 — Dungeon Exploration & Turn System](STEP-05-dungeon-exploration.md)
