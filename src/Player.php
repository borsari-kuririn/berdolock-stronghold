<?php
namespace Berdolock;

class Player
{
    public string $name = 'Adventurer';

    public int $maxHp = 0;
    public int $hp    = 0;

    public int $gold    = 0;
    public int $torches = 0;
    public int $rations = 0;

    // Equipment bonuses — these ARE the attack and defense stats
    public int $weaponBonus = 0; // PA: added to attack roll
    public int $armorBonus  = 0; // PD: flat damage reduction
    public int $shieldBonus = 0; // PD: flat damage reduction

    public bool $isDark     = false;
    public bool $isStarving = false;
    public bool $isPoisoned = false;

    public int $level  = 1;
    public int $xp     = 0;
    public int $xpNext = 10;

    public function attackPower(): int
    {
        return $this->weaponBonus;
    }

    public function defensePower(): int
    {
        return $this->armorBonus + $this->shieldBonus;
    }
}

