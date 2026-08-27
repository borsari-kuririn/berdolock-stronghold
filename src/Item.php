<?php
namespace Berdolock;

class Item
{
    public const TYPE_WEAPON     = 'weapon';
    public const TYPE_ARMOR      = 'armor';
    public const TYPE_SHIELD     = 'shield';
    public const TYPE_CONSUMABLE = 'consumable';

    public string $name;
    public string $type;
    public int    $value; // bonus granted (damage, reduction, hp restored)
    public int    $cost;  // gold cost in shop
    public int    $slots; // inventory slots used

    public function __construct(
        string $name,
        string $type,
        int    $value,
        int    $cost  = 0,
        int    $slots = 1
    ) {
        $this->name  = $name;
        $this->type  = $type;
        $this->value = $value;
        $this->cost  = $cost;
        $this->slots = $slots;
    }
}
