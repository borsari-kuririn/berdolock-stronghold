# Step 06 — Combat System

## Goal
Implement deterministic, turn-based combat: initiative, attack resolution,
flee logic, poison tick, and dark-penalty handling.

---

## Combat Flow

```
Phase = 'combat' (enemy already set in $state->currentEnemy)
    │
    ▼
Render CombatView — show enemy, player stats, actions (Attack / Flee)
    │
    ├── Player chooses Attack
    │       │
    │       ▼
    │   Ambush Check (IsDark → enemy acts first)
    │       │
    │       ▼
    │   Initiative Roll (1d6 vs 1d6)
    │       │
    │       ├── Player first → Player attacks → Enemy attacks (if alive)
    │       └── Enemy first  → Enemy attacks  → Player attacks (if alive)
    │       │
    │       ▼
    │   Poison tick (end of round)
    │       │
    │       ▼
    │   Check HP — Player dead → gameover
    │                Enemy dead  → loot + return to dungeon
    │
    └── Player chooses Flee
            │
            ▼
        AGI test — Success: return to dungeon
                   Failure: enemy attacks once, then check HP
```

---

## src/Actions/CombatAction.php

```php
<?php
namespace Berdolock\Actions;

use Berdolock\{GameState, Dice};

class CombatAction
{
    public static function handle(GameState $state, string $choice): GameState
    {
        return match($choice) {
            'attack' => self::resolveAttack($state),
            'flee'   => self::resolveFlee($state),
            default  => $state,
        };
    }

    // ------------------------------------------------------------------ //

    private static function resolveAttack(GameState $state): GameState
    {
        $player = $state->player;
        $enemy  = $state->currentEnemy;

        // Dark penalty: enemy always acts first (ambush)
        if ($player->isDark) {
            $state->addLog("[DARK] You can't see clearly — the enemy strikes first!");
            $state = self::enemyAttacks($state);
            if ($state->phase === 'gameover') return $state;
            $state = self::playerAttacks($state);
        } else {
            // Initiative: each rolls 1d6; player wins ties
            $playerInit = Dice::roll(6) + intdiv($player->agi, 4);
            $enemyInit  = Dice::roll(6);

            if ($playerInit >= $enemyInit) {
                $state = self::playerAttacks($state);
                if ($enemy->isAlive()) {
                    $state = self::enemyAttacks($state);
                }
            } else {
                $state->addLog("The {$enemy->name} acts first!");
                $state = self::enemyAttacks($state);
                if ($state->phase !== 'gameover') {
                    $state = self::playerAttacks($state);
                }
            }
        }

        if ($state->phase === 'gameover') return $state;

        // Poison tick at end of round
        if ($player->isPoisoned) {
            $player->hp--;
            $state->addLog("Poison deals 1 damage. HP: {$player->hp}/{$player->maxHp}");
            if ($player->hp <= 0) {
                $state->phase = 'gameover';
                $state->addLog("You succumb to poison.");
                return $state;
            }
        }

        // Check if enemy is still alive after the round
        if (!$enemy->isAlive()) {
            $state = self::handleVictory($state);
        }

        return $state;
    }

    // ------------------------------------------------------------------ //

    private static function playerAttacks(GameState $state): GameState
    {
        $player = $state->player;
        $enemy  = $state->currentEnemy;

        // Attack roll: player ATK vs enemy defense
        $roll   = Dice::roll(6) + $player->attackPower();
        $damage = max(0, $roll - $enemy->defense);
        $enemy->hp -= $damage;

        $state->addLog("You attack the {$enemy->name} for {$damage} damage. (Enemy HP: {$enemy->hp}/{$enemy->maxHp})");

        return $state;
    }

    private static function enemyAttacks(GameState $state): GameState
    {
        $player = $state->player;
        $enemy  = $state->currentEnemy;

        // Dark penalty: -2 to player defense effectively (enemy gets +2 attack)
        $attackBonus = $player->isDark ? 2 : 0;
        $roll        = Dice::roll(6) + $enemy->attack + $attackBonus;
        $damage      = max(0, $roll - $player->defensePower());
        $player->hp -= $damage;

        $state->addLog("The {$enemy->name} deals {$damage} damage to you. (HP: {$player->hp}/{$player->maxHp})");

        if ($player->hp <= 0) {
            $state->phase = 'gameover';
            $state->addLog("You have been slain by the {$enemy->name}.");
        }

        return $state;
    }

    // ------------------------------------------------------------------ //

    private static function resolveFlee(GameState $state): GameState
    {
        $player = $state->player;
        $enemy  = $state->currentEnemy;

        $fled = Dice::test($player->agi);

        if ($fled) {
            $state->currentEnemy = null;
            $state->phase        = 'dungeon';
            $state->addLog("You flee from the {$enemy->name}!");
        } else {
            $state->addLog("Flee failed! The {$enemy->name} attacks you as you run.");
            $state = self::enemyAttacks($state);
            // On failed flee, combat continues (enemy still present)
        }

        return $state;
    }

    // ------------------------------------------------------------------ //

    private static function handleVictory(GameState $state): GameState
    {
        $enemy  = $state->currentEnemy;
        $player = $state->player;

        $player->gold += $enemy->goldDrop;
        $state->addLog("Victory! You defeat the {$enemy->name} and loot {$enemy->goldDrop} gold.");

        $state->currentEnemy = null;
        $state->phase        = 'dungeon';

        return $state;
    }
}
```

---

## src/Render/CombatView.php

```php
<section class="combat">
    <h2>⚔ Combat!</h2>

    <div class="combatants">
        <div class="player-card">
            <strong><?= htmlspecialchars($state->player->name) ?></strong><br>
            HP: <?= $state->player->hp ?>/<?= $state->player->maxHp ?><br>
            ATK: <?= $state->player->attackPower() ?>
            &nbsp; PD: <?= $state->player->defensePower() ?>
            <?php if ($state->player->isDark): ?>
                <span class="status-bad">[DARK −2 ATK]</span>
            <?php endif ?>
            <?php if ($state->player->isPoisoned): ?>
                <span class="status-bad">[POISONED]</span>
            <?php endif ?>
        </div>

        <div class="vs">VS</div>

        <div class="enemy-card">
            <strong><?= htmlspecialchars($state->currentEnemy->name) ?></strong><br>
            HP: <?= $state->currentEnemy->hp ?>/<?= $state->currentEnemy->maxHp ?><br>
            ATK: <?= $state->currentEnemy->attack ?>
            &nbsp; DEF: <?= $state->currentEnemy->defense ?>
        </div>
    </div>

    <div class="combat-actions">
        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
            <input type="hidden" name="action" value="attack">
            <button type="submit" class="btn-attack">⚔ Attack</button>
        </form>

        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
            <input type="hidden" name="action" value="flee">
            <button type="submit" class="btn-flee">↩ Flee</button>
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

## Damage Formula Summary

| Scenario | Formula |
|----------|---------|
| Player damage to enemy | `max(0, roll(1d6) + STR + weaponBonus − enemy.defense)` |
| Enemy damage to player | `max(0, roll(1d6) + enemy.attack − (armorBonus + shieldBonus))` |
| Dark penalty (enemy) | Enemy ATK +2 on all rolls; enemy always has initiative |
| Starvation / Poison | 1 HP per turn / per round, bypasses defense |

---

## Next Step

→ [STEP-07 — Traps, Loot & Encounters Detail](STEP-07-encounters.md)
