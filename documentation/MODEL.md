# Role & Project Scope
You are an expert game developer and systems designer. Your task is to design and code a lightweight, turn-based, solo dungeon crawler inspired by the classic Brazilian print-and-play micro-RPG "A Fortaleza de Berdolock" (The Fortress of Berdolock).

The game follows classic OSR (Old School Renaissance) and text-based roguelike principles: high lethality, procedural generation, deterministic combat, resource management, and zero complex stats.

---

# Core Game Loop
1. Town Phase: Manage inventory, buy supplies (Torches, Rations), hire henchmen, or sleep at the Inn to heal.
2. Dungeon Phase: Turn-based corridor/room exploration. Each turn consumes resources (light/food) and triggers procedural encounters (Enemies, Traps, Empty Rooms, Treasure, or Dead Adventurers).
3. Survival & Decision: Player decides to push deeper or extract safely to the surface before resources expire.
4. Extraction / Victory: Surviving 30 turns allows the player to attempt extraction with their loot. Extracted gold serves as the high score.

---

# Data Structures & Core Rules

### 1. Character State
- HP (Hit Points): Rolled with 2d6 at initialization (Max HP = Initial Roll).
- Attack Power (PA): Base Attack Roll + Weapon Bonus.
- Defense Power (PD): Armor Bonus + Shield Bonus (Directly reduces incoming damage).
- Resources:
  - Gold (PO): Universal currency and endgame score.
  - Torches: Counter-based (Each torch lasts 10 turns).
  - Rations: Counter-based (1 ration consumed every 10 turns).
  - Status Flags: `IsDark` (no torch active), `IsStarving` (no rations left), `IsPoisoned`.

### 2. Exploration & Turn Consumption
- Every movement/action advances the `TurnCount` by 1.
- Every 10 turns:
  - Decrement 1 Torch. If Torches == 0, set `IsDark = true`.
  - Decrement 1 Ration. If Rations == 0, set `IsStarving = true`.
- Penalty Logic:
  - If `IsDark = true`: Player suffers -2 to Attack Roll; enemies attack first (Ambush).
  - If `IsStarving = true`: Player loses 1 HP directly per turn (ignores PD).
  - If `IsPoisoned = true`: Player loses 1 HP directly at the start of each combat round.

### 3. Procedural Encounter Table (Rolled via 1d6 or 2d6 per turn)
- 1: Empty Room / Corridor (Safe turn).
- 2: Trap Encounter (Roll trap type: Pit, Dart, Gas, Poison Needle).
- 3: Loot / Treasure Chest (Roll gold amount or consumable; optional Poison Needle trap check).
- 4: Dead Adventurer (Loot 1d6 Gold + Chance for Torch/Ration).
- 5: Standard Enemy (Skelet, Zombie, Giant Rat, Spider).
- 6: Elite Enemy / Boss (Ghoul, Berdolock Champion).

### 4. Combat System
- Deterministic, turn-based combat round:
  1. Ambush Check: If `IsDark` is true, Enemy attacks first. Otherwise, roll initiative (1d6 Player vs 1d6 Enemy).
  2. Player Attack Phase:
     - Damage Dealt = Max(0, (Player Attack Roll + Weapon Bonus) - Enemy Defense).
  3. Enemy Attack Phase:
     - Damage Taken = Max(0, (Enemy Attack Roll) - Player PD).
  4. Repeat until Player HP <= 0 (Game Over) or Enemy HP <= 0 (Victory & Loot).

---

# Execution Task
Please generate the foundational code architecture for this game (using standard CLI output or cleanly separated modular classes).

Include:
1. Complete state definitions (Player, Enemy, Item, GameState).
2. The primary `GameLoop()` handling Town Phase, Exploration Turn Step, and Extraction.
3. The `ResolveCombat()` and `ProcessTurnResources()` functions implementing all mechanics listed above.