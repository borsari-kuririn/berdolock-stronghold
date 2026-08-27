<?php
namespace Berdolock;

class Dice
{
    public static function test(int $attribute, bool $advantage = false, bool $disadvantage = false): bool
    {
        if ($advantage) {
            $rolls = [random_int(1, 6), random_int(1, 6), random_int(1, 6)];
            sort($rolls);
            $result = $rolls[0] + $rolls[1];
        } elseif ($disadvantage) {
            $rolls = [random_int(1, 6), random_int(1, 6), random_int(1, 6)];
            rsort($rolls);
            $result = $rolls[0] + $rolls[1];
        } else {
            $result = random_int(1, 6) + random_int(1, 6);
        }

        return $result <= $attribute;
    }

    public static function roll(int $sides): int
    {
        return random_int(1, $sides);
    }
}
