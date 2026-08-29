<div class="gb-header">
    <span>LV:<?= $state->player->level ?></span>
    <span>LP:<?= $state->player->hp ?>/<?= $state->player->maxHp ?></span>
    <span>GOLD:<?= $state->player->gold ?></span>
</div>

<div class="gb-main">
    <div class="gb-scene" style="background:#fff; flex-direction:column; gap:4px;">
        <canvas data-sprite="npc-default"></canvas>
    </div>
    <div class="gb-stats">
        <div>GOLD</div>
        <div class="gb-value"><?= $state->player->gold ?></div>
        <div>TORCH</div>
        <div class="gb-value"><?= $state->player->torches ?></div>
        <div>FOOD</div>
        <div class="gb-value"><?= $state->player->rations ?></div>
        <div>PA</div>
        <div class="gb-value"><?= $state->player->attackPower() ?></div>
        <div>PD</div>
        <div class="gb-value"><?= $state->player->defensePower() ?></div>
    </div>
</div>

<div class="gb-town">
    <h3>SHOP</h3>
    <?php
    $items = [
        ['sub' => 'buy_torch',  'label' => 'TORCH',       'cost' => 5],
        ['sub' => 'buy_ration', 'label' => 'RATION',      'cost' => 5],
        ['sub' => 'buy_dagger', 'label' => 'DAGGER PA:1', 'cost' => 10],
        ['sub' => 'buy_sword',  'label' => 'SWORD  PA:2', 'cost' => 25],
        ['sub' => 'buy_armor',  'label' => 'ARMOR  PD:1', 'cost' => 20],
        ['sub' => 'buy_shield', 'label' => 'SHIELD PD+1', 'cost' => 15],
    ];
    foreach ($items as $item):
        $canAfford = $state->player->gold >= $item['cost'];
    ?>
    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
        <input type="hidden" name="action" value="town">
        <input type="hidden" name="sub" value="<?= $item['sub'] ?>">
        <button type="submit" <?= $canAfford ? '' : 'disabled' ?> hx-disabled-elt="this">
            <?= $item['label'] ?> <?= $item['cost'] ?>G
        </button>
    </form>
    <?php endforeach ?>

    <h3>INN</h3>
    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
        <input type="hidden" name="action" value="town">
        <input type="hidden" name="sub" value="rest_at_inn">
        <button type="submit" <?= $state->player->gold >= 10 ? '' : 'disabled' ?> hx-disabled-elt="this">
            REST (10G) — <?= $state->player->hp ?>/<?= $state->player->maxHp ?> HP
        </button>
    </form>

    <form hx-post="/action.php" hx-target="#game-panel" hx-swap="innerHTML" hx-indicator="#spinner">
        <input type="hidden" name="action" value="town">
        <input type="hidden" name="sub" value="enter_dungeon">
        <button type="submit" class="btn-enter" hx-disabled-elt="this">
            ○ ENTER STRONGHOLD
        </button>
    </form>
</div>

<?php if ($state->log): ?>
<div class="gb-log">
    <?php foreach ($state->log as $entry): ?>
        <p><?= htmlspecialchars($entry) ?></p>
    <?php endforeach ?>
</div>
<?php endif ?>
