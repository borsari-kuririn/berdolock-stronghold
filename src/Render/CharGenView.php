<?php $p = $state->player; $rerollsLeft = 3 - $state->rerolls; ?>
<div class="gb-chargen">

    <div class="gb-chargen-title">
        <span><?= htmlspecialchars(strtoupper($p->name)) ?></span>
    </div>

    <div class="gb-chargen-stats">
        <div class="gb-chargen-row">
            <span class="gb-chargen-label">LP</span>
            <span class="gb-chargen-bar"><?= str_repeat('█', $p->maxHp) ?></span>
            <span class="gb-chargen-val"><?= $p->maxHp ?></span>
        </div>
    </div>

    <div class="gb-chargen-derived">
        <span>LP:<?= $p->maxHp ?></span>
        <span>GOLD:<?= $p->gold ?></span>
    </div>

    <div class="gb-chargen-actions">
        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
            <input type="hidden" name="action" value="chargen_confirm">
            <button type="submit" class="btn-confirm" hx-disabled-elt="this">
                ○ CONFIRM
            </button>
        </form>

        <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
            <input type="hidden" name="action" value="chargen_reroll">
            <button type="submit"
                    <?= $rerollsLeft <= 0 ? 'disabled' : '' ?>
                    hx-disabled-elt="this">
                RE-ROLL (<?= $rerollsLeft ?> LEFT)
            </button>
        </form>
    </div>

</div>

