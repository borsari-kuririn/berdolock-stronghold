<?php
$spriteKey = strtolower(str_replace(' ', '-', $state->currentEnemy->name));
?>
<div class="gb-header">
    <span>LV:<?= $state->player->level ?></span>
    <span>LP:<?= $state->player->hp ?>/<?= $state->player->maxHp ?></span>
    <span>PA:<?= $state->player->attackPower() ?> PD:<?= $state->player->defensePower() ?></span>
</div>

<div class="gb-main">
    <div class="gb-scene" style="background:#fff;">
        <canvas data-sprite="<?= htmlspecialchars($spriteKey) ?>"></canvas>
    </div>
    <div class="gb-stats">
        <div>GOLD</div>
        <div class="gb-value"><?= $state->player->gold ?></div>
        <div>EX</div>
        <div class="gb-value"><?= $state->player->xp ?></div>
    </div>
</div>

<div class="gb-actions">
    <div class="gb-combat-menu">
        <div class="col-left">
            <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
                <input type="hidden" name="action" value="attack">
                <button type="submit" class="selected" hx-disabled-elt="this">FIGHT</button>
            </form>
            <span style="font-size:7px;line-height:2;display:block;">MAGIC</span>
            <span style="font-size:7px;line-height:2;display:block;">USE</span>
            <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
                <input type="hidden" name="action" value="flee">
                <button type="submit" hx-disabled-elt="this">ESCAPE</button>
            </form>
        </div>
        <div class="col-right">
            <span style="font-size:7px;line-height:2;display:block;">AUTO</span>
            <div class="enemy-name-box">
                <?= htmlspecialchars(strtoupper($state->currentEnemy->name)) ?>
            </div>
            <div style="font-size:6px;margin-top:4px;line-height:1.8;">
                HP:<?= $state->currentEnemy->hp ?>/<?= $state->currentEnemy->maxHp ?><br>
                ATK:<?= $state->currentEnemy->attack ?>
                PD:<?= $state->currentEnemy->defense ?>
            </div>
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
