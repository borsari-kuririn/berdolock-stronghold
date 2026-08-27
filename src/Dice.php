<?php
namespace Berdolock;

class Dice
{
    public static function roll(int $sides): int
    {
        return random_int(1, $sides);
    }

    // 50% chance — succeeds on 4, 5, or 6
    public static function luck(): bool
    {
        return random_int(1, 6) >= 4;
    }
}

