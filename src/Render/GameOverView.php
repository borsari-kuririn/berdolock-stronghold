<?php use Berdolock\Scoring; ?>
<div class="gb-header">
    <span>LV:<?= $state->player->level ?></span>
    <span>LP:0/<?= $state->player->maxHp ?></span>
    <span>GOLD:<?= $state->player->gold ?></span>
</div>

<div class="gb-main">
    <div class="gb-scene">
        <span style="color:#fff;font-family:'Press Start 2P',monospace;font-size:16px;">☠</span>
    </div>
    <div class="gb-stats">
        <div>ROOM</div>
        <div class="gb-value"><?= $state->roomCount ?></div>
        <div>TURN</div>
        <div class="gb-value"><?= $state->turnCount ?></div>
        <div>GOLD</div>
        <div class="gb-value"><?= $state->player->gold ?></div>
    </div>
</div>

<div class="gb-actions">
    <div class="gb-dialogue">
        <p><?= htmlspecialchars(strtoupper($state->player->name)) ?> HAS FALLEN.</p>
    </div>
</div>

<div class="gb-score-card">
    <div class="gb-score-row"><span>GOLD (LOST)</span><span><?= $state->player->gold ?></span></div>
    <div class="gb-score-row"><span>ROOMS &times;5</span><span><?= $state->roomCount * 5 ?></span></div>
    <div class="gb-score-row"><span>TURNS</span><span><?= $state->turnCount ?></span></div>
    <div class="gb-score-row gb-score-total"><span>TOTAL</span><span><?= Scoring::calculate($state) ?></span></div>
</div>

<div class="gb-log" style="padding:4px 8px;">
    <?php foreach (array_slice($state->log, 0, 3) as $entry): ?>
        <p><?= htmlspecialchars($entry) ?></p>
    <?php endforeach ?>
    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner"
          style="margin-top:6px;">
        <input type="hidden" name="action" value="new_game">
        <button type="submit" class="gb-btn-full" hx-disabled-elt="this">
            ○ TRY AGAIN
        </button>
    </form>
</div>

