<?php
namespace Berdolock\Actions;

use Berdolock\{GameState, Player, Dice};

class NewGame
{
    public static function handle(array $post): GameState
    {
        $state  = new GameState();
        $player = $state->player;

        $name = trim($post['name'] ?? '');
        $player->name = $name !== '' ? $name : 'Adventurer';

        $player->str = self::roll2d6();
        $player->agi = self::roll2d6();
        $player->int = self::roll2d6();
        $player->end = self::roll2d6();

        $player->maxHp = $player->end * 2;
        $player->hp    = $player->maxHp;
        $player->maxMp = $player->int;
        $player->mp    = $player->maxMp;

        $player->gold    = 20;
        $player->torches = 2;
        $player->rations = 2;

        $state->phase = 'town';
        $state->addLog("Welcome, {$player->name}. Prepare before entering.");

        return $state;
    }

    private static function roll2d6(): int
    {
        return random_int(1, 6) + random_int(1, 6);
    }
}
