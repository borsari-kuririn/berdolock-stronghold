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
        <div>ROOM</div>
        <div class="gb-value"><?= $state->roomCount ?></div>
        <div>TURN</div>
        <div class="gb-value"><?= $state->turnCount ?></div>
    </div>
</div>

<div class="gb-actions">
    <div class="gb-dialogue">
        <p><?= htmlspecialchars(strtoupper($state->player->name)) ?><br>CONQUERED THE STRONGHOLD!</p>
    </div>
</div>

<div class="gb-score-card">
    <div class="gb-score-row"><span>GOLD</span><span><?= $state->player->gold ?></span></div>
    <div class="gb-score-row"><span>ROOMS &times;5</span><span><?= $state->roomCount * 5 ?></span></div>
    <div class="gb-score-row"><span>LP &times;2</span><span><?= $state->player->hp * 2 ?></span></div>
    <div class="gb-score-row"><span>BERDOLOCK</span><span>+500</span></div>
    <div class="gb-score-row gb-score-total"><span>TOTAL</span><span><?= Scoring::calculate($state) ?></span></div>
</div>

<div class="gb-log" style="padding:6px 8px;">
    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
        <input type="hidden" name="action" value="new_game">
        <button type="submit" class="gb-btn-full" hx-disabled-elt="this">
            ○ PLAY AGAIN
        </button>
    </form>
</div>

