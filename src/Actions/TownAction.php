<?php
namespace Berdolock\Actions;

use Berdolock\GameState;

class TownAction
{
    public static function handle(GameState $state, array $post): GameState
    {
        $sub = $post['sub'] ?? '';

        return match($sub) {
            'buy_torch'     => self::buy($state, 5,  fn($p) => $p->torches++,        'Bought 1 Torch.'),
            'buy_ration'    => self::buy($state, 5,  fn($p) => $p->rations++,        'Bought 1 Ration.'),
            'buy_dagger'    => self::buy($state, 10, fn($p) => $p->weaponBonus = max($p->weaponBonus, 1), 'Equipped Dagger (+1 ATK).'),
            'buy_sword'     => self::buy($state, 25, fn($p) => $p->weaponBonus = max($p->weaponBonus, 3), 'Equipped Short Sword (+3 ATK).'),
            'buy_armor'     => self::buy($state, 20, fn($p) => $p->armorBonus++,     'Equipped Leather Armor (+1 PD).'),
            'buy_shield'    => self::buy($state, 15, fn($p) => $p->shieldBonus++,    'Equipped Shield (+1 PD).'),
            'rest_at_inn'   => self::rest($state),
            'enter_dungeon' => self::enterDungeon($state),
            default         => $state,
        };
    }

    private static function buy(GameState $state, int $cost, callable $apply, string $msg): GameState
    {
        if ($state->player->gold < $cost) {
            $state->addLog("Not enough gold.");
            return $state;
        }
        $state->player->gold -= $cost;
        $apply($state->player);
        $state->addLog($msg);
        return $state;
    }

    private static function rest(GameState $state): GameState
    {
        if ($state->player->gold < 10) {
            $state->addLog("The innkeeper wants 10 gold.");
            return $state;
        }
        $state->player->gold       -= 10;
        $state->player->hp          = $state->player->maxHp;
        $state->player->isPoisoned  = false;
        $state->addLog("Rested at the inn. HP restored.");
        return $state;
    }

    private static function enterDungeon(GameState $state): GameState
    {
        if ($state->player->torches === 0) {
            $state->addLog("You need at least 1 torch.");
            return $state;
        }
        $state->phase      = 'dungeon';
        $state->turnCount  = 0;
        $state->roomCount  = 0;
        $state->dangerLevel = 1;
        $state->addLog("You step into the darkness...");
        return $state;
    }
}
