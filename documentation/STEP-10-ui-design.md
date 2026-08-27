# Step 10 — Game Boy–Style UI Design

## Visual Reference

The interface is inspired by classic Game Boy RPGs (Dragon Warrior Monsters, Pokémon, Final Fantasy Legend).
Target aesthetic: **black and white, pixel font, bordered panels, zero decoration, uppercase text**.

---

## Design Rules

| Rule | Value |
|------|-------|
| Palette | Black `#000` and White `#fff` only |
| Font | `Press Start 2P` (Google Fonts) — 8px base size |
| Borders | 2–4px solid black, hard pixel corners (no border-radius) |
| Layout | Fixed 320px wide container (2× Game Boy 160px) |
| Selection cursor | `○` prefix on the active option |
| Interaction | Buttons only — no hover effects, no transitions |
| Assets | **No image files.** All visuals drawn with CSS + JS canvas |

---

## Font Setup

Add to `<head>` in `index.php`:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
```

---

## Screen Anatomy

Every screen shares the same three-zone layout:

```
┌──────────────────────────────────────┐
│  HEADER BAR  LV:xx  HP:xx  MP:xx     │  ← always visible
├───────────────────────┬──────────────┤
│                       │  GOLD: xxx   │
│   MAIN PANEL          │  EX:  xxxx   │  ← content changes per phase
│   (sprite / scene)    │              │
├───────────────────────┴──────────────┤
│  ACTION PANEL                        │  ← buttons/menu/dialogue
└──────────────────────────────────────┘
```

---

## Base HTML Shell

```html
<!-- Replaces the <main id="game-panel"> content in index.php -->
<div class="gb-screen">

    <!-- Zone 1 — Header bar -->
    <div class="gb-header">
        <span>LV:<?= $state->player->level ?? 1 ?></span>
        <span>HP:<?= $state->player->hp ?></span>
        <span>MP:<?= $state->player->mp ?? 0 ?></span>
    </div>

    <!-- Zone 2 — Main panel + side stats -->
    <div class="gb-main">
        <div class="gb-scene">
            <!-- Phase-specific content: dungeon view, portrait, or enemy sprite -->
        </div>
        <div class="gb-stats">
            <div>GOLD</div>
            <div class="gb-value"><?= $state->player->gold ?></div>
            <div>EX</div>
            <div class="gb-value"><?= $state->player->xp ?? 0 ?></div>
        </div>
    </div>

    <!-- Zone 3 — Action panel -->
    <div class="gb-actions">
        <!-- Phase-specific buttons/menu/dialogue -->
    </div>

</div>
```

---

## CSS — public/assets/style.css (full replacement)

```css
@import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

/* ── Reset ──────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: #888;          /* grey bezel like a Game Boy */
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 2rem;
    min-height: 100vh;
    font-family: 'Press Start 2P', monospace;
}

/* ── Outer screen bezel ─────────────────────────────── */
#game-panel {
    width: 320px;
    background: #fff;
    border: 4px solid #000;
    image-rendering: pixelated;
}

/* ── Zone 1 · Header bar ────────────────────────────── */
.gb-header {
    display: flex;
    justify-content: space-between;
    padding: 4px 8px;
    font-size: 8px;
    border-bottom: 2px solid #000;
    background: #fff;
    white-space: nowrap;
}

/* ── Zone 2 · Main panel ────────────────────────────── */
.gb-main {
    display: flex;
    border-bottom: 2px solid #000;
}

.gb-scene {
    flex: 1;
    min-height: 96px;
    background: #000;
    border-right: 2px solid #000;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.gb-scene img {
    image-rendering: pixelated;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* White text on black dungeon scene */
.gb-scene-text {
    color: #fff;
    font-size: 7px;
    text-align: center;
    padding: 4px;
}

.gb-stats {
    width: 96px;
    padding: 6px 8px;
    font-size: 8px;
    line-height: 1;
    display: grid;
    grid-template-columns: 1fr;
    gap: 4px;
}

.gb-stats .gb-value {
    text-align: right;
    margin-bottom: 4px;
}

/* ── Zone 3 · Action panel ──────────────────────────── */
.gb-actions {
    padding: 6px 8px;
    font-size: 8px;
    min-height: 72px;
}

/* ── Action grid (dungeon phase) ────────────────────── */
.gb-action-grid {
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 0;
}

.gb-action-col {
    padding: 4px;
}

.gb-action-col + .gb-action-col {
    border-left: 2px solid #000;
}

/* ── D-pad ──────────────────────────────────────────── */
.gb-dpad {
    display: grid;
    grid-template-columns: repeat(3, 16px);
    grid-template-rows: repeat(3, 16px);
    gap: 1px;
    margin: 4px auto;
    width: fit-content;
}

.gb-dpad form { display: contents; }

.gb-dpad button {
    width: 16px;
    height: 16px;
    background: #000;
    color: #fff;
    border: none;
    font-size: 7px;
    padding: 0;
    cursor: pointer;
    font-family: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
}

.gb-dpad .dpad-center { background: #000; } /* dead center cell */
.gb-dpad .dpad-empty  { background: transparent; }

/* ── Menu list (combat / town) ──────────────────────── */
.gb-menu {
    list-style: none;
    line-height: 2;
}

.gb-menu li {
    font-size: 8px;
    cursor: pointer;
}

.gb-menu li.selected::before {
    content: '○';
    margin-right: 4px;
}

.gb-menu form {
    display: inline;
}

.gb-menu button {
    background: none;
    border: none;
    font-family: inherit;
    font-size: 8px;
    cursor: pointer;
    padding: 0;
    text-transform: uppercase;
}

.gb-menu button:hover { text-decoration: underline; }

/* Active/selected menu item — inverted colours */
.gb-menu .selected button {
    background: #000;
    color: #fff;
    padding: 1px 3px;
}

/* ── Dialogue box ───────────────────────────────────── */
.gb-dialogue {
    border: 2px solid #000;
    padding: 6px;
    font-size: 8px;
    line-height: 1.8;
    text-transform: uppercase;
    min-height: 56px;
}

.gb-dialogue .gb-choice {
    margin-top: 6px;
    display: flex;
    gap: 12px;
}

.gb-dialogue .gb-choice form { display: inline; }

.gb-dialogue .gb-choice button {
    background: none;
    border: none;
    font-family: inherit;
    font-size: 8px;
    cursor: pointer;
    text-transform: uppercase;
    padding: 0;
}

.gb-dialogue .gb-choice button.selected::before {
    content: '○';
    margin-right: 3px;
}

/* ── Two-column combat menu ─────────────────────────── */
.gb-combat-menu {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border: 2px solid #000;
    font-size: 8px;
}

.gb-combat-menu .col-left,
.gb-combat-menu .col-right {
    padding: 4px 6px;
}

.gb-combat-menu .col-right {
    border-left: 2px solid #000;
}

.gb-combat-menu .enemy-name-box {
    border: 2px solid #000;
    padding: 2px 4px;
    font-size: 7px;
    margin-top: 2px;
}

.gb-combat-menu button {
    background: none;
    border: none;
    font-family: inherit;
    font-size: 8px;
    cursor: pointer;
    display: block;
    line-height: 2;
    text-transform: uppercase;
    padding: 0;
    width: 100%;
    text-align: left;
}

.gb-combat-menu button.selected::before {
    content: '○';
    margin-right: 3px;
}

/* ── HTMX spinner ───────────────────────────────────── */
#spinner {
    display: none;
    text-align: center;
    font-size: 7px;
    padding: 2px;
    border-top: 1px solid #000;
}

.htmx-request #spinner,
#spinner.htmx-request { display: block; }
```

---

## Phase View Templates

### Dungeon Phase — DungeonView.php

```php
<div class="gb-screen">
    <div class="gb-header">
        <span>LV:<?= $state->player->level ?? 1 ?></span>
        <span>HP:<?= $state->player->hp ?></span>
        <span>MP:<?= $state->player->mp ?? 0 ?></span>
    </div>

    <div class="gb-main">
        <div class="gb-scene">
            <div class="gb-dungeon">
                <div class="frame frame-1"></div>
                <div class="frame frame-2"></div>
                <div class="frame frame-3"></div>
                <div class="door"></div>
                <div class="floor-left"></div>
                <div class="floor-right"></div>
            </div>
        </div>
        <div class="gb-stats">
            <div>GOLD</div><div class="gb-value"><?= $state->player->gold ?></div>
            <div>EX</div><div class="gb-value"><?= $state->player->xp ?? 0 ?></div>
        </div>
    </div>

    <div class="gb-actions">
        <div class="gb-action-grid">

            <!-- D-pad / move column -->
            <div class="gb-action-col">
                <div style="font-size:7px;margin-bottom:3px;">MOVE</div>
                <div class="gb-dpad">
                    <div class="dpad-empty"></div>
                    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                        <input type="hidden" name="action" value="explore">
                        <input type="hidden" name="dir" value="north">
                        <button type="submit">↑</button>
                    </form>
                    <div class="dpad-empty"></div>
                    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                        <input type="hidden" name="action" value="explore">
                        <input type="hidden" name="dir" value="west">
                        <button type="submit">←</button>
                    </form>
                    <div class="dpad-center"></div>
                    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                        <input type="hidden" name="action" value="explore">
                        <input type="hidden" name="dir" value="east">
                        <button type="submit">→</button>
                    </form>
                    <div class="dpad-empty"></div>
                    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                        <input type="hidden" name="action" value="explore">
                        <input type="hidden" name="dir" value="south">
                        <button type="submit">↓</button>
                    </form>
                    <div class="dpad-empty"></div>
                </div>
            </div>

            <!-- Command column -->
            <div class="gb-action-col">
                <div style="display:flex;justify-content:space-between;font-size:7px;margin-bottom:4px;">
                    <span>UNDER</span><span>PATH</span>
                </div>
                <?php
                $commands = [
                    ['action'=>'cmd_look',  'label'=>'LOOK'],
                    ['action'=>'cmd_use',   'label'=>'USE',   'selected'=>true],
                    ['action'=>'cmd_open',  'label'=>'OPEN'],
                    ['action'=>'cmd_magic', 'label'=>'MAGIC'],
                    ['action'=>'cmd_hit',   'label'=>'HIT'],
                    ['action'=>'cmd_power', 'label'=>'POWER'],
                ];
                ?>
                <ul class="gb-menu">
                <?php foreach ($commands as $cmd): ?>
                    <li class="<?= !empty($cmd['selected']) ? 'selected' : '' ?>">
                        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                            <input type="hidden" name="action" value="<?= $cmd['action'] ?>">
                            <button type="submit"><?= $cmd['label'] ?></button>
                        </form>
                    </li>
                <?php endforeach ?>
                </ul>
            </div>

        </div>
    </div>
</div>
```

---

### Combat Phase — CombatView.php

```php
<div class="gb-screen">
    <div class="gb-header">
        <span>LV:<?= $state->player->level ?? 1 ?></span>
        <span>HP:<?= $state->player->hp ?></span>
        <span>MP:<?= $state->player->mp ?? 0 ?></span>
    </div>

    <div class="gb-main">
        <div class="gb-scene" style="background:#fff;">
            <canvas data-sprite="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $state->currentEnemy->name))) ?>"></canvas>
        </div>
        <div class="gb-stats">
            <div>GOLD</div><div class="gb-value"><?= $state->player->gold ?></div>
            <div>EX</div><div class="gb-value"><?= $state->player->xp ?? 0 ?></div>
        </div>
    </div>

    <div class="gb-actions">
        <div class="gb-combat-menu">
            <div class="col-left">
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                    <input type="hidden" name="action" value="attack">
                    <button type="submit" class="selected">FIGHT</button>
                </form>
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                    <input type="hidden" name="action" value="cmd_magic">
                    <button type="submit">MAGIC</button>
                </form>
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                    <input type="hidden" name="action" value="cmd_use">
                    <button type="submit">USE</button>
                </form>
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                    <input type="hidden" name="action" value="flee">
                    <button type="submit">ESCAPE</button>
                </form>
            </div>
            <div class="col-right">
                <button type="button">AUTO</button>
                <div class="enemy-name-box">
                    <?= htmlspecialchars(strtoupper($state->currentEnemy->name)) ?>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

### Dialogue Phase — DialogueView.php

Used when an NPC or event triggers a yes/no choice.

```php
<div class="gb-screen">
    <div class="gb-header">
        <span>LV:<?= $state->player->level ?? 1 ?></span>
        <span>HP:<?= $state->player->hp ?></span>
        <span>MP:<?= $state->player->mp ?? 0 ?></span>
    </div>

    <div class="gb-main">
        <div class="gb-scene">
            <canvas data-sprite="npc-<?= htmlspecialchars($state->dialogue->npcId ?? 'default') ?>"></canvas>
        </div>
        <div class="gb-stats">
            <div>GOLD</div><div class="gb-value"><?= $state->player->gold ?></div>
            <div>EX</div><div class="gb-value"><?= $state->player->xp ?? 0 ?></div>
        </div>
    </div>

    <div class="gb-actions">
        <div class="gb-dialogue">
            <p><?= htmlspecialchars(strtoupper($state->dialogue->text ?? '')) ?></p>
            <div class="gb-choice">
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                    <input type="hidden" name="action" value="dialogue_yes">
                    <button type="submit" class="selected">YES</button>
                </form>
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML">
                    <input type="hidden" name="action" value="dialogue_no">
                    <button type="submit">NO</button>
                </form>
            </div>
        </div>
    </div>
</div>
```

---

## CSS + JS Asset System

No image files are used. All visuals are drawn in two ways:

1. **Dungeon / scene views** — pure CSS geometry inside `.gb-scene`
2. **Sprites (enemies, NPCs)** — JS pixel arrays rendered to `<canvas>`

---

### 1. CSS Dungeon Corridor View

Add to `style.css`. The corridor is built from nested `div` borders simulating
a first-person perspective — no images needed.

```css
.gb-dungeon {
    width: 100%;
    height: 100%;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

/* Each frame is one perspective depth layer */
.gb-dungeon .frame {
    position: absolute;
    border: 3px solid #fff;
    box-sizing: border-box;
}

.gb-dungeon .frame-1 { width: 88%; height: 80%; }
.gb-dungeon .frame-2 { width: 66%; height: 58%; }
.gb-dungeon .frame-3 { width: 44%; height: 36%; }

/* Door at the end of the corridor */
.gb-dungeon .door {
    position: absolute;
    width: 22%;
    height: 26%;
    border: 3px solid #fff;
    bottom: 37%;   /* sits on the floor line of frame-3 */
}

/* Floor lines */
.gb-dungeon .floor-left,
.gb-dungeon .floor-right {
    position: absolute;
    bottom: 10%;
    width: 25%;
    height: 2px;
    background: #fff;
}
.gb-dungeon .floor-left  { left: 6%;  transform: rotate(20deg);  transform-origin: left bottom; }
.gb-dungeon .floor-right { right: 6%; transform: rotate(-20deg); transform-origin: right bottom; }
```

HTML inside `.gb-scene` for the dungeon phase:

```html
<div class="gb-dungeon">
    <div class="frame frame-1"></div>
    <div class="frame frame-2"></div>
    <div class="frame frame-3"></div>
    <div class="door"></div>
    <div class="floor-left"></div>
    <div class="floor-right"></div>
</div>
```

---

### 2. JS Canvas Sprite Engine

Create `public/assets/js/sprites.js`. Sprites are 2D arrays: `0` = white, `1` = black.
Each array defines a 16×16 or 24×24 pixel grid scaled up by a factor at draw time.

```js
// Pixel scale factor — 4 = each pixel becomes a 4×4 block
const SCALE = 4;

const SPRITES = {
  skeleton: [
    [0,0,0,1,1,1,0,0,0],
    [0,0,1,0,0,0,1,0,0],
    [0,0,1,0,1,0,1,0,0],
    [0,0,0,1,0,1,0,0,0],
    [0,0,0,1,1,1,0,0,0],
    [0,1,1,1,1,1,1,1,0],
    [0,0,1,1,1,1,1,0,0],
    [0,0,1,0,0,0,1,0,0],
    [0,0,1,0,0,0,1,0,0],
  ],
  zombie: [
    [0,1,1,1,1,1,1,0],
    [1,0,1,0,0,1,0,1],
    [1,0,0,0,0,0,0,1],
    [1,0,1,1,1,1,0,1],
    [0,1,1,1,1,1,1,0],
    [0,1,1,0,0,1,1,0],
    [1,1,1,0,0,1,1,1],
    [1,0,0,0,0,0,0,1],
  ],
  berdolock: [
    [0,1,1,1,1,1,1,0],
    [1,1,0,1,1,0,1,1],
    [1,0,1,1,1,1,0,1],
    [1,1,1,1,1,1,1,1],
    [0,1,1,1,1,1,1,0],
    [1,1,1,1,1,1,1,1],
    [1,0,1,1,1,1,0,1],
    [0,1,1,0,0,1,1,0],
  ],
  'npc-default': [
    [0,0,1,1,1,1,0,0],
    [0,1,0,0,0,0,1,0],
    [1,0,0,1,1,0,0,1],
    [1,0,0,0,0,0,0,1],
    [1,0,1,0,0,1,0,1],
    [1,0,0,1,1,0,0,1],
    [0,1,0,0,0,0,1,0],
    [0,0,1,1,1,1,0,0],
  ],
};

function drawSprite(canvasEl, key) {
  const pixels = SPRITES[key];
  if (!pixels) return;

  const rows = pixels.length;
  const cols = pixels[0].length;
  canvasEl.width  = cols * SCALE;
  canvasEl.height = rows * SCALE;

  const ctx = canvasEl.getContext('2d');
  // fill background
  ctx.fillStyle = '#fff';
  ctx.fillRect(0, 0, canvasEl.width, canvasEl.height);

  ctx.fillStyle = '#000';
  pixels.forEach((row, y) =>
    row.forEach((px, x) => {
      if (px) ctx.fillRect(x * SCALE, y * SCALE, SCALE, SCALE);
    })
  );
}

// Auto-render every canvas with data-sprite on page load and after HTMX swaps
function renderAll() {
  document.querySelectorAll('canvas[data-sprite]').forEach(el =>
    drawSprite(el, el.dataset.sprite)
  );
}

document.addEventListener('DOMContentLoaded', renderAll);
document.addEventListener('htmx:afterSwap',   renderAll);
```

Add the script to `index.php` **after** htmx:

```html
<script src="/assets/js/sprites.js"></script>
```

---

### 3. Using Sprites in Views

Replace every `<img>` with a `<canvas data-sprite="...">` tag.
The JS engine reads `data-sprite`, looks it up in `SPRITES`, and draws it.

```html
<!-- Enemy in CombatView.php -->
<canvas data-sprite="skeleton"></canvas>

<!-- NPC portrait in DialogueView.php -->
<canvas data-sprite="npc-default"></canvas>
```

For the dungeon scene, use the CSS dungeon markup instead of a canvas:

```html
<!-- In DungeonView.php .gb-scene -->
<div class="gb-dungeon">
    <div class="frame frame-1"></div>
    <div class="frame frame-2"></div>
    <div class="frame frame-3"></div>
    <div class="door"></div>
    <div class="floor-left"></div>
    <div class="floor-right"></div>
</div>
```

---

### 4. Adding New Sprites

To add a new sprite:
1. Define a 2D pixel array in `SPRITES` inside `sprites.js`
2. Use `<canvas data-sprite="your-key">` anywhere in a view
3. No other changes needed — `renderAll()` fires automatically after every HTMX swap

---

### 5. Sprite Key Registry

| `data-sprite` key | Used in |
|-------------------|---------|
| `skeleton` | Combat — Skeleton |
| `zombie` | Combat — Zombie |
| `giant-rat` | Combat — Giant Rat |
| `spider` | Combat — Spider |
| `ghoul` | Combat — Ghoul |
| `berdolock-champion` | Combat — Champion |
| `berdolock` | Combat — Final Boss |
| `npc-default` | Dialogue — Generic NPC |

---

## MP Attribute Addition

The screenshots show an `MP` stat. Add it to `Player`:

```php
// In src/Player.php
public int $mp    = 0;
public int $maxMp = 0;
```

In `NewGame::handle()`, roll MP from INT:

```php
$player->maxMp = $player->int;
$player->mp    = $player->maxMp;
```

---

## Level / XP Addition

Add `level` and `xp` to `Player`:

```php
public int $level  = 1;
public int $xp     = 0;
public int $xpNext = 10; // XP needed for next level
```

In `CombatAction::handleVictory()`, award XP and check level-up:

```php
$player->xp += $enemy->xpDrop;
if ($player->xp >= $player->xpNext) {
    $player->level++;
    $player->xpNext = $player->level * 10;
    $player->maxHp += 2;
    $player->hp     = min($player->hp + 2, $player->maxHp);
    $state->addLog("LEVEL UP! Now LV:{$player->level}");
}
```

---

## Next Step

→ All steps complete. Begin implementation from [STEP-01](STEP-01-project-structure.md).
