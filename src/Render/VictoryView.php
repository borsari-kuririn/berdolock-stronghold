<?php use Berdolock\Scoring; ?>
<div class="gb-header">
    <span>LV:<?= $state->player->level ?></span>
    <span>LP:<?= $state->player->hp ?>/<?= $state->player->maxHp ?></span>
    <span>GOLD:<?= $state->player->gold ?></span>
</div>

<div class="gb-main">
    <div class="gb-scene">
        <canvas data-sprite="berdolock"></canvas>
    </div>
    <div class="gb-stats">
        <div>GOLD</div>
        <div class="gb-value"><?= $state->player->gold ?></div>
        <div>EX</div>
        <div class="gb-value"><?= $state->player->xp ?></div>
    </div>
</div>

<div class="gb-actions">
    <div class="gb-dialogue">
        <p>BERDOLOCK FALLS.</p>
        <p>THE STRONGHOLD IS YOURS.</p>
        <p style="margin-top:6px;font-size:7px;">SCORE: <?= Scoring::calculate($state) ?></p>
    </div>
</div>

<div class="gb-log" style="padding:6px 8px;">
    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
        <input type="hidden" name="action" value="new_game">
        <button type="submit" style="width:100%;font-family:inherit;font-size:8px;background:#000;color:#fff;border:none;padding:6px;cursor:pointer;text-transform:uppercase;">
            ○ PLAY AGAIN
        </button>
    </form>
</div>
