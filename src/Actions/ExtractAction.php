<?php
namespace Berdolock\Actions;

use Berdolock\GameState;

class ExtractAction
{
    public static function handle(GameState $state): GameState
    {
        if ($state->turnCount < 30) {
            $state->addLog("Not enough turns to extract safely.");
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

        $state->addLog("Extracted with {$gold} gold!");
        $state->addLog("Back in town. Cure poison at the inn.");

        return $state;
    }
}
