<div class="gb-header">
    <span>LV:<?= $state->player->level ?></span>
    <span>LP:<?= $state->player->hp ?>/<?= $state->player->maxHp ?></span>
    <span>GOLD:<?= $state->player->gold ?></span>
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
        <div>ROOM</div>
        <div class="gb-value"><?= $state->roomCount ?>/20</div>
        <div>TURN</div>
        <div class="gb-value"><?= $state->turnCount ?></div>
        <div>TORCH</div>
        <div class="gb-value"><?= $state->player->torches ?></div>
        <div>FOOD</div>
        <div class="gb-value"><?= $state->player->rations ?></div>
        <?php if ($state->player->isDark): ?>
        <div class="gb-value" style="color:#000;font-size:6px;margin-top:2px;">DARK</div>
        <?php endif ?>
        <?php if ($state->player->isStarving): ?>
        <div class="gb-value" style="color:#000;font-size:6px;">STARV</div>
        <?php endif ?>
        <?php if ($state->player->isPoisoned): ?>
        <div class="gb-value" style="color:#000;font-size:6px;">POISO</div>
        <?php endif ?>
    </div>
</div>

<div class="gb-actions">
    <div class="gb-action-grid">

        <div class="gb-action-col">
            <div style="font-size:7px;margin-bottom:3px;">MOVE</div>
            <div class="gb-dpad">
                <div class="dpad-empty"></div>
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
                    <input type="hidden" name="action" value="explore">
                    <button type="submit" hx-disabled-elt="this">↑</button>
                </form>
                <div class="dpad-empty"></div>
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
                    <input type="hidden" name="action" value="explore">
                    <button type="submit" hx-disabled-elt="this">←</button>
                </form>
                <div class="dpad-center"></div>
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
                    <input type="hidden" name="action" value="explore">
                    <button type="submit" hx-disabled-elt="this">→</button>
                </form>
                <div class="dpad-empty"></div>
                <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
                    <input type="hidden" name="action" value="explore">
                    <button type="submit" hx-disabled-elt="this">↓</button>
                </form>
                <div class="dpad-empty"></div>
            </div>
            <div style="font-size:6px;margin-top:4px;line-height:1.8;">
                DNG:<?= $state->dangerLevel ?>
            </div>
        </div>

        <div class="gb-action-col">
            <div style="display:flex;justify-content:space-between;font-size:7px;margin-bottom:4px;">
                <span>UNDER</span><span>PATH</span>
            </div>
            <ul class="gb-menu">
                <?php if ($state->turnCount >= 30): ?>
                <li class="selected">
                    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
                        <input type="hidden" name="action" value="extract">
                        <button type="submit" hx-disabled-elt="this">EXTRACT</button>
                    </form>
                </li>
                <?php endif ?>
                <li><span style="font-size:7px;">LOOK</span></li>
                <li><span style="font-size:7px;">OPEN</span></li>
                <li><span style="font-size:7px;">HIT</span></li>
            </ul>
        </div>

    </div>
</div>

<?php if ($state->log): ?>
<div class="gb-log">
    <?php foreach ($state->log as $entry): ?>
        <p><?= htmlspecialchars($entry) ?></p>
    <?php endforeach ?>
</div>
<?php endif ?>
