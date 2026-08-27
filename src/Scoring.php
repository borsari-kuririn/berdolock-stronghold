<?php
namespace Berdolock;

class Scoring
{
    public static function calculate(GameState $state): int
    {
        $score  = $state->player->gold;
        $score += $state->roomCount * 5;
        $score += $state->player->hp * 2;
        if ($state->phase === 'victory') {
            $score += 500;
        }
        return $score;
    }
}
