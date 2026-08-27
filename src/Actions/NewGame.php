<?php
namespace Berdolock\Actions;

use Berdolock\GameState;

class NewGame
{
    public static function handle(array $post): GameState
    {
        $state  = new GameState();
        $player = $state->player;

        $name = trim($post['name'] ?? '');
        $player->name = $name !== '' ? $name : 'Adventurer';

        // HP is the only stat rolled at creation
        $player->maxHp = self::roll2d6();
        $player->hp    = $player->maxHp;

        $player->gold    = 20;
        $player->torches = 2;
        $player->rations = 2;

        $state->phase   = 'chargen';
        $state->rerolls = 0;

        return $state;
    }

    private static function roll2d6(): int
    {
        return random_int(1, 6) + random_int(1, 6);
    }
}

