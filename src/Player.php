<?php
namespace Berdolock;

class Player
{
    public string $name = 'Adventurer';

    public int $str = 0;
    public int $agi = 0;
    public int $int = 0;
    public int $end = 0;

    public int $maxHp = 0;
    public int $hp    = 0;
    public int $maxMp = 0;
    public int $mp    = 0;

    public int $gold    = 0;
    public int $torches = 0;
    public int $rations = 0;

    public int $weaponBonus = 0;
    public int $armorBonus  = 0;
    public int $shieldBonus = 0;

    public bool $isDark     = false;
    public bool $isStarving = false;
    public bool $isPoisoned = false;

    public int $level  = 1;
    public int $xp     = 0;
    public int $xpNext = 10;

    public function attackPower(): int
    {
        return $this->str + $this->weaponBonus;
    }

    public function defensePower(): int
    {
        return $this->armorBonus + $this->shieldBonus;
    }
}
