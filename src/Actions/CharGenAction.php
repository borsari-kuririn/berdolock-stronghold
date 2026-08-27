<?php
namespace Berdolock\Actions;

use Berdolock\GameState;

class CharGenAction
{
    public static function confirm(GameState $state): GameState
    {
        $state->phase = 'town';
        $state->addLog("Welcome, {$state->player->name}. Prepare before entering.");
        return $state;
    }

    public static function reroll(GameState $state): GameState
    {
        if ($state->rerolls >= 3) {
            $state->addLog("No re-rolls remaining.");
            return $state;
        }

        $player = $state->player;
        $player->maxHp = self::roll2d6();
        $player->hp    = $player->maxHp;
        $state->rerolls++;

        return $state;
    }

    private static function roll2d6(): int
    {
        return random_int(1, 6) + random_int(1, 6);
    }
}

