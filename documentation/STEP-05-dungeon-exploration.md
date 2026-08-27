# Step 05 — Dungeon Exploration & Turn System

## Goal
Implement the turn-based exploration loop: resource decay, the encounter table,
Danger level escalation, and the extraction window.

---

## Turn Flow (per room entered)

```
Player clicks "Explore Next Room"
    │
    ▼
TurnCount++, RoomCount++
    │
    ▼
ProcessTurnResources()      ← torch & ration decay, status flags
    │
    ▼
DangerLevel update          ← every 4 rooms
    │
    ▼
Roll Encounter Table (1d6)  ← empty / trap / loot / dead adv. / enemy / elite
    │
    ├── Empty / Dead Adventurer / Treasure → stay in dungeon phase
    ├── Trap → apply effect, stay in dungeon phase
    └── Enemy / Elite → transition to combat phase
    │
    ▼
Check extraction eligibility (TurnCount >= 30)
    │
    ▼
Render DungeonView
```

---

## src/Actions/ExploreAction.php

```php
<?php
namespace Berdolock\Actions;

use Berdolock\{GameState, Dice, EnemyFactory};

class ExploreAction
{
    public static function handle(GameState $state): GameState
    {
        $state->turnCount++;
        $state->roomCount++;

        $state = self::processTurnResources($state);

        // Escalate danger every 4 rooms
        $state->dangerLevel = min(3, intdiv($state->roomCount, 4) + 1);

        // Player dies to starvation
        if ($state->player->hp <= 0) {
            $state->phase = 'gameover';
            $state->addLog("You collapsed from starvation in the dark.");
            return $state;
        }

        $state = self::rollEncounter($state);

        return $state;
    }

    // ------------------------------------------------------------------ //

    private static function processTurnResources(GameState $state): GameState
    {
        $player = $state->player;

        // Every 10 turns consume 1 torch and 1 ration
        if ($state->turnCount % 10 === 0) {
            if ($player->torches > 0) {
                $player->torches--;
                if ($player->torches === 0) {
                    $player->isDark = true;
                    $state->addLog("Your last torch burns out. Darkness swallows you.");
                }
            }

            if ($player->rations > 0) {
                $player->rations--;
                if ($player->rations === 0) {
                    $player->isStarving = true;
                    $state->addLog("You have no food left. Hunger gnaws at you.");
                }
            }
        }

        // Starvation: 1 HP per turn (bypasses defense)
        if ($player->isStarving) {
            $player->hp--;
            $state->addLog("Hunger deals 1 damage. HP: {$player->hp}/{$player->maxHp}");
        }

        // Poison: 1 HP per turn (bypasses defense)
        if ($player->isPoisoned) {
            $player->hp--;
            $state->addLog("Poison deals 1 damage. HP: {$player->hp}/{$player->maxHp}");
        }

        return $state;
    }

    // ------------------------------------------------------------------ //

    private static function rollEncounter(GameState $state): GameState
    {
        $roll = Dice::roll(6);

        return match($roll) {
            1 => self::emptyRoom($state),
            2 => self::trapEncounter($state),
            3 => self::treasureEncounter($state),
            4 => self::deadAdventurerEncounter($state),
            5 => self::spawnEnemy($state, elite: false),
            6 => self::spawnEnemy($state, elite: true),
        };
    }

    // ------------------------------------------------------------------ //

    private static function emptyRoom(GameState $state): GameState
    {
        $state->addLog("Room {$state->roomCount}: Empty. The silence is unsettling.");
        return $state;
    }

    private static function trapEncounter(GameState $state): GameState
    {
        $traps = [
            ['name' => 'Pit Trap',      'damage' => Dice::roll(6)],
            ['name' => 'Dart Trap',     'damage' => Dice::roll(4)],
            ['name' => 'Gas Trap',      'damage' => Dice::roll(4), 'poison' => true],
            ['name' => 'Poison Needle', 'damage' => 1,             'poison' => true],
        ];
        $trap = $traps[array_rand($traps)];

        // AGI test to avoid
        $avoided = Dice::test($state->player->agi);
        if ($avoided) {
            $state->addLog("{$trap['name']} — You dodge it!");
        } else {
            $state->player->hp -= $trap['damage'];
            $state->addLog("{$trap['name']} — Takes {$trap['damage']} damage. HP: {$state->player->hp}/{$state->player->maxHp}");
            if (!empty($trap['poison'])) {
                $state->player->isPoisoned = true;
                $state->addLog("You are Poisoned!");
            }
        }

        if ($state->player->hp <= 0) {
            $state->phase = 'gameover';
        }

        return $state;
    }

    private static function treasureEncounter(GameState $state): GameState
    {
        // Optional needle trap on chest
        if (Dice::roll(6) === 1) {
            $state->player->isPoisoned = true;
            $state->addLog("The chest was rigged! Poison Needle — you are Poisoned.");
        }

        $gold = Dice::roll(6) * 10;
        $state->player->gold += $gold;
        $state->addLog("Treasure chest! You find {$gold} gold.");

        // Chance for consumable
        if (Dice::roll(6) >= 5) {
            $bonus = Dice::roll(2); // 1 = torch, 2 = ration
            if ($bonus === 1) {
                $state->player->torches++;
                $state->addLog("Also found a Torch.");
            } else {
                $state->player->rations++;
                $state->addLog("Also found a Ration.");
            }
        }

        return $state;
    }

    private static function deadAdventurerEncounter(GameState $state): GameState
    {
        $gold = Dice::roll(6);
        $state->player->gold += $gold;
        $state->addLog("A dead adventurer. Looted {$gold} gold.");

        if (Dice::roll(6) >= 4) {
            $bonus = Dice::roll(2);
            if ($bonus === 1) {
                $state->player->torches++;
                $state->addLog("Found a usable Torch on the body.");
            } else {
                $state->player->rations++;
                $state->addLog("Found a Ration on the body.");
            }
        }

        return $state;
    }

    private static function spawnEnemy(GameState $state, bool $elite): GameState
    {
        $state->currentEnemy = EnemyFactory::spawn($state->dangerLevel, $elite);
        $state->phase        = 'combat';
        $state->addLog("A {$state->currentEnemy->name} blocks your path!");
        return $state;
    }
}
```

---

## src/EnemyFactory.php

```php
<?php
namespace Berdolock;

class EnemyFactory
{
    private static array $standard = [
        ['name'=>'Skeleton',  'hp'=>6,  'atk'=>4, 'def'=>0, 'gold'=>5],
        ['name'=>'Zombie',    'hp'=>8,  'atk'=>4, 'def'=>1, 'gold'=>3],
        ['name'=>'Giant Rat', 'hp'=>4,  'atk'=>3, 'def'=>0, 'gold'=>2],
        ['name'=>'Spider',    'hp'=>5,  'atk'=>4, 'def'=>0, 'gold'=>4, 'poison'=>true],
    ];

    private static array $elite = [
        ['name'=>'Ghoul',              'hp'=>14, 'atk'=>7, 'def'=>2, 'gold'=>20],
        ['name'=>'Berdolock Champion', 'hp'=>18, 'atk'=>9, 'def'=>3, 'gold'=>35],
    ];

    public static function spawn(int $dangerLevel, bool $elite): Enemy
    {
        $pool  = $elite ? self::$elite : self::$standard;
        $data  = $pool[array_rand($pool)];
        $multi = match($dangerLevel) { 1 => 1.0, 2 => 1.25, 3 => 1.5, default => 1.0 };

        $enemy = new Enemy(
            name:     $data['name'],
            hp:       (int) round($data['hp']  * $multi),
            attack:   (int) round($data['atk'] * $multi),
            defense:  $data['def'],
            goldDrop: $data['gold'],
        );

        return $enemy;
    }
}
```

---

## src/Render/DungeonView.php

```php
<section class="dungeon">
    <h2>Berdolock's Stronghold — Room <?= $state->roomCount ?></h2>

    <div class="hud">
        HP: <?= $state->player->hp ?>/<?= $state->player->maxHp ?>
        &nbsp;| Gold: <?= $state->player->gold ?>
        &nbsp;| Torches: <?= $state->player->torches ?>
        &nbsp;| Rations: <?= $state->player->rations ?>
        &nbsp;| Turn: <?= $state->turnCount ?>
        &nbsp;| Danger: <?= $state->dangerLevel ?>
        <?php if ($state->player->isDark): ?><span class="status-bad"> [DARK]</span><?php endif ?>
        <?php if ($state->player->isStarving): ?><span class="status-bad"> [STARVING]</span><?php endif ?>
        <?php if ($state->player->isPoisoned): ?><span class="status-bad"> [POISONED]</span><?php endif ?>
    </div>

    <div class="actions">
        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
            <input type="hidden" name="action" value="explore">
            <button type="submit">→ Explore Next Room</button>
        </form>

        <?php if ($state->turnCount >= 30): ?>
        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
            <input type="hidden" name="action" value="extract">
            <button type="submit" class="btn-safe">↑ Extract to Surface</button>
        </form>
        <?php endif ?>
    </div>

    <div class="log">
        <?php foreach ($state->log as $entry): ?>
            <p><?= htmlspecialchars($entry) ?></p>
        <?php endforeach ?>
    </div>
</section>
```

---

## Danger Level Table

| Rooms Explored | Danger Level | Enemy HP Multiplier | Enemy ATK Multiplier |
|---------------|-------------|---------------------|----------------------|
| 0–3 | 1 | ×1.0 | ×1.0 |
| 4–7 | 2 | ×1.25 | ×1.25 |
| 8+ | 3 | ×1.5 | ×1.5 |

---

## Next Step

→ [STEP-06 — Combat System](STEP-06-combat-system.md)
