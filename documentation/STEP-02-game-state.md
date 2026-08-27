# Step 02 — Game State & Session Management

## Goal
Define all PHP data classes that represent the complete game state,
and provide a session helper to persist/restore them between requests.

---

## Design Principle

State is a single serializable object stored in `$_SESSION`.
No database is needed for the first version.
Every action receives the current state and returns a new (mutated) state.

---

## src/GameState.php

```php
<?php
namespace Berdolock;

class GameState
{
    public string $phase = 'town'; // town | dungeon | combat | gameover | victory

    public Player  $player;
    public ?Enemy  $currentEnemy = null;
    public int     $turnCount    = 0;
    public int     $roomCount    = 0;
    public int     $dangerLevel  = 1;  // 1–3; increases every 4 rooms
    public array   $log          = []; // last N messages shown to player

    public function __construct()
    {
        $this->player = new Player();
    }

    public function addLog(string $message): void
    {
        array_unshift($this->log, $message);
        $this->log = array_slice($this->log, 0, 8); // keep last 8 entries
    }
}
```

---

## src/Player.php

```php
<?php
namespace Berdolock;

class Player
{
    // Core attributes (each rolled with 2d6 at character creation)
    public int $str = 0; // Strength   — carry capacity, attack
    public int $agi = 0; // Agility    — initiative, flee, traps
    public int $int = 0; // Intelligence — perception, magic items
    public int $end = 0; // Endurance  — HP base

    // Derived stats
    public int $maxHp = 0;
    public int $hp    = 0;

    // Resources
    public int $gold    = 0;
    public int $torches = 0;
    public int $rations = 0;

    // Equipment bonuses (flat values for simplicity)
    public int $weaponBonus  = 0; // +damage from equipped weapon
    public int $armorBonus   = 0; // flat damage reduction (PD)
    public int $shieldBonus  = 0;

    // Status flags
    public bool $isDark     = false;
    public bool $isStarving = false;
    public bool $isPoisoned = false;

    // Computed attack power and defense power
    public function attackPower(): int
    {
        return $this->str + $this->weaponBonus;
    }

    public function defensePower(): int
    {
        return $this->armorBonus + $this->shieldBonus;
    }
}
```

---

## src/Enemy.php

```php
<?php
namespace Berdolock;

class Enemy
{
    public string $name;
    public int    $hp;
    public int    $maxHp;
    public int    $attack;   // flat attack value
    public int    $defense;  // flat damage reduction
    public int    $goldDrop; // gold rewarded on defeat
    public int    $xpDrop;   // used for progression threshold

    public function __construct(
        string $name,
        int $hp,
        int $attack,
        int $defense = 0,
        int $goldDrop = 0,
        int $xpDrop = 1
    ) {
        $this->name     = $name;
        $this->hp       = $hp;
        $this->maxHp    = $hp;
        $this->attack   = $attack;
        $this->defense  = $defense;
        $this->goldDrop = $goldDrop;
        $this->xpDrop   = $xpDrop;
    }

    public function isAlive(): bool
    {
        return $this->hp > 0;
    }
}
```

---

## src/Item.php

```php
<?php
namespace Berdolock;

class Item
{
    public const TYPE_WEAPON  = 'weapon';
    public const TYPE_ARMOR   = 'armor';
    public const TYPE_SHIELD  = 'shield';
    public const TYPE_CONSUMABLE = 'consumable';

    public string $name;
    public string $type;
    public int    $value;   // bonus granted (damage, reduction, hp restored)
    public int    $cost;    // gold cost in the shop
    public int    $slots;   // inventory slots used (1 or 2)

    public function __construct(
        string $name,
        string $type,
        int $value,
        int $cost = 0,
        int $slots = 1
    ) {
        $this->name  = $name;
        $this->type  = $type;
        $this->value = $value;
        $this->cost  = $cost;
        $this->slots = $slots;
    }
}
```

---

## src/Session.php

```php
<?php
namespace Berdolock;

class Session
{
    private const KEY = 'berdolock_state';

    public static function load(): ?GameState
    {
        $data = $_SESSION[self::KEY] ?? null;
        if ($data === null) {
            return null;
        }
        return unserialize($data);
    }

    public static function save(GameState $state): void
    {
        $_SESSION[self::KEY] = serialize($state);
    }

    public static function clear(): void
    {
        unset($_SESSION[self::KEY]);
    }
}
```

> `serialize`/`unserialize` works for all plain PHP objects.
> Ensure all classes are loaded before calling `unserialize`.

---

## State Lifecycle

```
Session::load()
    │
    ▼
GameState (null = no game started)
    │
    ▼
Action handler mutates state
    │
    ▼
Session::save($state)
    │
    ▼
Render view from $state->phase
```

---

## Danger Level Calculation

Danger escalates every 4 rooms and modifies enemy stats at spawn time.
The mapping lives in `GameState` as a helper:

```php
public function dangerMultiplier(): float
{
    return match($this->dangerLevel) {
        1 => 1.0,
        2 => 1.25,
        3 => 1.5,
        default => 1.0,
    };
}
```

Enemy HP and attack are multiplied by this value when spawned.

---

## Next Step

→ [STEP-03 — Character Creation](STEP-03-character-creation.md)
