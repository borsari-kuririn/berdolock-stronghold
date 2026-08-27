<?php
namespace Berdolock;

class EnemyFactory
{
    private static array $standard = [
        ['name' => 'Skeleton',  'hp' => 6,  'atk' => 4, 'def' => 0, 'gold' => 5,  'xp' => 1],
        ['name' => 'Zombie',    'hp' => 8,  'atk' => 4, 'def' => 1, 'gold' => 3,  'xp' => 1],
        ['name' => 'Giant Rat', 'hp' => 4,  'atk' => 3, 'def' => 0, 'gold' => 2,  'xp' => 1],
        ['name' => 'Spider',    'hp' => 5,  'atk' => 4, 'def' => 0, 'gold' => 4,  'xp' => 1],
    ];

    private static array $elite = [
        ['name' => 'Ghoul',              'hp' => 14, 'atk' => 7, 'def' => 2, 'gold' => 20, 'xp' => 3],
        ['name' => 'Berdolock Champion', 'hp' => 18, 'atk' => 9, 'def' => 3, 'gold' => 35, 'xp' => 5],
    ];

    public static function spawn(int $dangerLevel, bool $elite): Enemy
    {
        $pool  = $elite ? self::$elite : self::$standard;
        $data  = $pool[array_rand($pool)];
        $multi = match($dangerLevel) {
            2       => 1.25,
            3       => 1.5,
            default => 1.0,
        };

        return new Enemy(
            name:     $data['name'],
            hp:       (int) round($data['hp']  * $multi),
            attack:   (int) round($data['atk'] * $multi),
            defense:  $data['def'],
            goldDrop: $data['gold'],
            xpDrop:   $data['xp'],
        );
    }

    public static function spawnBoss(): Enemy
    {
        $boss         = new Enemy('Berdolock', 30, 10, 4, 100, 20);
        $boss->isBoss = true;
        return $boss;
    }
}
