<?php
namespace Berdolock;

class Enemy
{
    public string $name;
    public int    $hp;
    public int    $maxHp;
    public int    $attack;
    public int    $defense;
    public int    $goldDrop;
    public int    $xpDrop;
    public bool   $isBoss              = false;
    public bool   $enraged             = false;
    public bool   $damageImmunityUsed  = false;

    public function __construct(
        string $name,
        int    $hp,
        int    $attack,
        int    $defense  = 0,
        int    $goldDrop = 0,
        int    $xpDrop   = 1
    ) {
        $this->name     = $name;
        $this->hp       = $hp;
        $this->maxHp    = $hp;
        $this->attack   = $attack;
        $this->defense  = $defense;
        $this->goldDrop = $goldDrop;
        $this->xpDrop   = $xpDrop;
    }

    public function isAlive(): bool
    {
        return $this->hp > 0;
    }
}
