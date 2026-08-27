<?php use Berdolock\Scoring; ?>
<div class="gb-header">
    <span>LV:<?= $state->player->level ?></span>
    <span>HP:0</span>
    <span>MP:<?= $state->player->mp ?></span>
</div>

<div class="gb-main">
    <div class="gb-scene">
        <span style="color:#fff;font-family:'Press Start 2P',monospace;font-size:16px;">☠</span>
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
        <p><?= htmlspecialchars(strtoupper($state->player->name)) ?> HAS FALLEN.</p>
        <p style="font-size:7px;margin-top:4px;">
            ROOM <?= $state->roomCount ?>
            TURN <?= $state->turnCount ?><br>
            SCORE: <?= Scoring::calculate($state) ?>
        </p>
    </div>
</div>

<div class="gb-log" style="padding:6px 8px;">
    <?php foreach (array_slice($state->log, 0, 3) as $entry): ?>
        <p><?= htmlspecialchars($entry) ?></p>
    <?php endforeach ?>
    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner"
          style="margin-top:6px;">
        <input type="hidden" name="action" value="new_game">
        <button type="submit" style="width:100%;font-family:inherit;font-size:8px;background:#000;color:#fff;border:none;padding:6px;cursor:pointer;text-transform:uppercase;">
            ○ TRY AGAIN
        </button>
    </form>
</div>
