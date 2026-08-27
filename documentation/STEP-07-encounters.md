# Step 07 — Encounters Detail (Traps, Loot, Enemies)

## Goal
Document every encounter type in full, including variant tables and special rules,
so the encounter system can be expanded without touching core logic.

---

## Encounter Table (1d6 per room)

| Roll | Encounter Type |
|------|---------------|
| 1 | Empty Room |
| 2 | Trap |
| 3 | Treasure Chest |
| 4 | Dead Adventurer |
| 5 | Standard Enemy |
| 6 | Elite Enemy |

> At Danger Level 3, treat a roll of 1 as Dead Adventurer instead of Empty Room
> to increase pressure without adding a separate table.

---

## 1. Empty Room

No mechanical effect. Used to pace tension and give breathing room.

**Log message examples (pick randomly):**
- "The room is empty. Dust settles in your torchlight."
- "Nothing here but old bones and cobwebs."
- "A faint breeze carries the smell of rot."

---

## 2. Traps

Roll 1d4 on the **Trap Type Table** when a trap is encountered.

### Trap Type Table (1d4)

| Roll | Trap | Damage | Extra Effect |
|------|------|--------|--------------|
| 1 | Pit Trap | 1d6 | None |
| 2 | Dart Trap | 1d4 | None |
| 3 | Gas Trap | 1d4 | Poisons on failure |
| 4 | Poison Needle | 1 | Always poisons |

### Resolution

1. Roll **AGI test** (2d6 ≤ AGI).
2. **Success**: Trap is avoided, no damage.
3. **Failure**: Apply damage and any extra effect.

> Poison Needle: the AGI test only avoids the damage (1 pt); the Poisoned
> status is applied regardless of the roll.

### Poisoned Status

- Deals **1 HP per combat round** (applied at end of each round).
- Deals **1 HP per exploration turn** via `processTurnResources`.
- Cured by: resting at the Inn.
- There is no antidote item in the base game.

---

## 3. Treasure Chest

### Resolution

1. Roll 1d6 — on a **1**, the chest has a Poison Needle trap (apply Poisoned).
2. Gold: `1d6 × 10` gold added to player.
3. Bonus item: roll 1d6 — on **5 or 6**, find one consumable:
   - Odd result → Torch
   - Even result → Ration

### Magic Weapon (Upgrade)

At Danger Level 2+, add a second bonus item roll (1d6):
- On a **6**: find a Magic Weapon shard (+1 permanent weapon bonus).
  - Implement by incrementing `player->weaponBonus` and logging the find.

---

## 4. Dead Adventurer

A fallen predecessor left behind some supplies.

### Resolution

1. Gold: roll 1d6 gold (flat, not ×10 — scavenged scraps).
2. Consumable: roll 1d6 — on **4, 5, or 6**, find one consumable (Torch or Ration, 50/50).

**Flavor log messages:**
- "You find a dead adventurer, stripped of most gear. {gold} gold in their boot."
- "A corpse slumped against the wall. You search the pockets: {gold} gold."

---

## 5. Standard Enemies

Spawned via `EnemyFactory::spawn($dangerLevel, elite: false)`.

### Base Stats Table

| Enemy | Base HP | Base ATK | Defense | Gold Drop | Special |
|-------|---------|----------|---------|-----------|---------|
| Skeleton | 6 | 4 | 0 | 5 | — |
| Zombie | 8 | 4 | 1 | 3 | — |
| Giant Rat | 4 | 3 | 0 | 2 | — |
| Spider | 5 | 4 | 0 | 4 | Poisons on hit (50% chance) |

### Spider Poison

When the Spider lands a successful hit, roll 1d6:
- On **4, 5, or 6**: player becomes Poisoned.

Implement in `CombatAction::enemyAttacks()` by checking the enemy name and rolling:

```php
if ($enemy->name === 'Spider' && $damage > 0 && Dice::roll(6) >= 4) {
    $player->isPoisoned = true;
    $state->addLog("The Spider's venom courses through you — Poisoned!");
}
```

---

## 6. Elite Enemies

Spawned via `EnemyFactory::spawn($dangerLevel, elite: true)`.

### Base Stats Table

| Enemy | Base HP | Base ATK | Defense | Gold Drop | Special |
|-------|---------|----------|---------|-----------|---------|
| Ghoul | 14 | 7 | 2 | 20 | — |
| Berdolock Champion | 18 | 9 | 3 | 35 | Enrage below 50% HP |

### Berdolock Champion — Enrage

When the Champion's HP drops below 50% of max, ATK increases by +3 for
the remainder of the fight. Track this with a flag in the `Enemy` class:

```php
class Enemy {
    public bool $enraged = false;
}
```

In `CombatAction::enemyAttacks()`:

```php
if ($enemy->name === 'Berdolock Champion'
    && !$enemy->enraged
    && $enemy->hp < ($enemy->maxHp / 2)) {
    $enemy->enraged  = true;
    $enemy->attack  += 3;
    $state->addLog("The Champion ENRAGES! ATK increased!");
}
```

---

## 7. Final Boss — Berdolock

Triggered when `$state->roomCount === 20`.

In `ExploreAction::handle()`, before rolling the encounter table:

```php
if ($state->roomCount === 20) {
    $state->currentEnemy = self::spawnBerdolock();
    $state->phase = 'combat';
    $state->addLog("You reach the Throne Room. BERDOLOCK rises.");
    return $state;
}
```

```php
private static function spawnBerdolock(): Enemy
{
    $boss           = new Enemy('Berdolock', 30, 10, 4, 100);
    $boss->isBoss   = true; // add this flag to Enemy
    return $boss;
}
```

### Berdolock Special Ability

**Once per combat**, ignores one incoming damage roll (sets damage to 0).
Track with `$enemy->damageImmunityUsed = false` on the `Enemy` class.

In `CombatAction::playerAttacks()`:

```php
if ($enemy->isBoss && !$enemy->damageImmunityUsed && $damage > 0) {
    $enemy->damageImmunityUsed = true;
    $damage = 0;
    $state->addLog("Berdolock shrugs off your blow!");
}
```

---

## Encounter Weight Tuning

To adjust difficulty without restructuring code, change the encounter table
roll in `ExploreAction::rollEncounter()` from 1d6 to a weighted array pick:

```php
$table = [1, 2, 3, 4, 5, 5, 6, 6]; // more enemies at higher danger
shuffle($table);
$roll = $table[array_rand($table)];
```

Apply this only at `$state->dangerLevel >= 2`.

---

## Next Step

→ [STEP-08 — HTMX UI & Partial Rendering](STEP-08-htmx-ui.md)
