<?php $p = $state->player; $rerollsLeft = 3 - $state->rerolls; ?>
<div class="gb-chargen">

    <div class="gb-chargen-title">
        <span><?= htmlspecialchars(strtoupper($p->name)) ?></span>
    </div>

    <div class="gb-chargen-stats">
        <div class="gb-chargen-row">
            <span class="gb-chargen-label">STR</span>
            <span class="gb-chargen-bar"><?= str_repeat('█', $p->str) ?></span>
            <span class="gb-chargen-val"><?= $p->str ?></span>
        </div>
        <div class="gb-chargen-row">
            <span class="gb-chargen-label">AGI</span>
            <span class="gb-chargen-bar"><?= str_repeat('█', $p->agi) ?></span>
            <span class="gb-chargen-val"><?= $p->agi ?></span>
        </div>
        <div class="gb-chargen-row">
            <span class="gb-chargen-label">INT</span>
            <span class="gb-chargen-bar"><?= str_repeat('█', $p->int) ?></span>
            <span class="gb-chargen-val"><?= $p->int ?></span>
        </div>
        <div class="gb-chargen-row">
            <span class="gb-chargen-label">END</span>
            <span class="gb-chargen-bar"><?= str_repeat('█', $p->end) ?></span>
            <span class="gb-chargen-val"><?= $p->end ?></span>
        </div>
    </div>

    <div class="gb-chargen-derived">
        <span>HP:<?= $p->maxHp ?></span>
        <span>MP:<?= $p->maxMp ?></span>
        <span>ATK:<?= $p->str ?></span>
        <span>PD:0</span>
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
