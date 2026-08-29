<?php
namespace Berdolock\Actions;

use Berdolock\GameState;

class ExtractAction
{
    public static function handle(GameState $state): GameState
    {
        if ($state->turnCount < 30) {
            $state->addLog("You haven't survived long enough to extract safely.");
            return $state;
        }

        $gold = $state->player->gold;

        $state->phase        = 'town';
        $state->turnCount    = 0;
        $state->roomCount    = 0;
        $state->dangerLevel  = 1;
        $state->currentEnemy = null;

        $state->player->isDark     = false;
        $state->player->isStarving = false;

        $state->addLog("You extract successfully with {$gold} gold!");
        $state->addLog("You return to town. Find a healer if poisoned.");

        return $state;
    }
}
