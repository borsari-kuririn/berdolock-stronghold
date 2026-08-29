<?php
namespace Berdolock\Actions;

use Berdolock\{GameState, Dice, EnemyFactory};

class ExploreAction
{
    public static function handle(GameState $state): GameState
    {
        $state->turnCount++;
        $state->roomCount++;

        $state = self::processTurnResources($state);

        // Danger escalates every 4 rooms — updated after resource tick
        $state->dangerLevel = min(3, intdiv($state->roomCount, 4) + 1);

        if ($state->player->hp <= 0) {
            $state->phase = 'gameover';
            $state->addLog("You collapsed in the dark.");
            return $state;
        }

        if ($state->roomCount === 20) {
            $state->currentEnemy = EnemyFactory::spawnBoss();
            $state->phase        = 'combat';
            $state->addLog("You reach the Throne Room. BERDOLOCK rises.");
            return $state;
        }

        return self::rollEncounter($state);
    }

    private static function processTurnResources(GameState $state): GameState
    {
        $player = $state->player;

        if ($state->turnCount % 10 === 0) {
            if ($player->torches > 0) {
                $player->torches--;
                if ($player->torches === 0) {
                    $player->isDark = true;
                    $state->addLog("Your last torch burns out. Darkness swallows you.");
                }
            }
            if ($player->rations > 0) {
                $player->rations--;
                if ($player->rations === 0) {
                    $player->isStarving = true;
                    $state->addLog("You have no food left. Hunger gnaws at you.");
                }
            }
        }

        if ($player->isStarving) {
            $player->hp--;
            $state->addLog("Hunger: -1 LP. ({$player->hp}/{$player->maxHp})");
        }

        if ($player->isPoisoned) {
            $player->hp--;
            $state->addLog("Poison: -1 LP. ({$player->hp}/{$player->maxHp})");
        }

        return $state;
    }

    private static function rollEncounter(GameState $state): GameState
    {
        // Weighted table at DL2+; at DL3 empty rooms become dead adventurers
        if ($state->dangerLevel >= 2) {
            $table = [1, 2, 3, 4, 5, 5, 6, 6];
        } else {
            $table = [1, 2, 3, 4, 5, 6];
        }
        $roll = $table[array_rand($table)];

        // DL3: pressure replaces empty rooms with dead adventurers
        if ($state->dangerLevel === 3 && $roll === 1) {
            $roll = 4;
        }

        return match($roll) {
            1 => self::emptyRoom($state),
            2 => self::trapEncounter($state),
            3 => self::treasureEncounter($state),
            4 => self::deadAdventurer($state),
            5 => self::spawnEnemy($state, false),
            6 => self::spawnEnemy($state, true),
        };
    }

    private static function emptyRoom(GameState $state): GameState
    {
        $msgs = [
            "The room is empty. Dust settles in your torchlight.",
            "Nothing here but old bones and cobwebs.",
            "A faint breeze carries the smell of rot.",
        ];
        $state->addLog($msgs[array_rand($msgs)]);
        return $state;
    }

    private static function trapEncounter(GameState $state): GameState
    {
        $traps = [
            ['name' => 'Pit Trap',      'dmg' => Dice::roll(6)],
            ['name' => 'Dart Trap',     'dmg' => Dice::roll(4)],
            ['name' => 'Gas Trap',      'dmg' => Dice::roll(4), 'poison' => true],
            ['name' => 'Poison Needle', 'dmg' => 1,             'poison' => true],
        ];
        $trap = $traps[array_rand($traps)];

        if (Dice::luck()) {
            $state->addLog("{$trap['name']} — Dodged!");
        } else {
            $state->player->hp -= $trap['dmg'];
            $state->addLog("{$trap['name']} — {$trap['dmg']} dmg. LP:{$state->player->hp}/{$state->player->maxHp}");
            if (!empty($trap['poison'])) {
                $state->player->isPoisoned = true;
                $state->addLog("Poisoned!");
            }
        }

        if ($state->player->hp <= 0) {
            $state->phase = 'gameover';
        }
        return $state;
    }

    private static function treasureEncounter(GameState $state): GameState
    {
        if (Dice::roll(6) === 1) {
            $state->player->isPoisoned = true;
            $state->addLog("The chest was rigged! Poison Needle \u2014 you are Poisoned.");
        }
        $gold = Dice::roll(6) * 10;
        $state->player->gold += $gold;
        $state->addLog("Treasure chest! You find {$gold} gold.");

        if (Dice::roll(6) >= 5) {
            if (Dice::roll(2) === 1) {
                $state->player->torches++;
                $state->addLog("Also found a Torch.");
            } else {
                $state->player->rations++;
                $state->addLog("Also found a Ration.");
            }
        }

        // Magic weapon shard at DL2+
        if ($state->dangerLevel >= 2 && Dice::roll(6) === 6) {
            $state->player->weaponBonus++;
            $state->addLog("A Magic Weapon shard! PA +1.");
        }

        return $state;
    }

    private static function deadAdventurer(GameState $state): GameState
    {
        $gold = Dice::roll(6);
        $state->player->gold += $gold;

        $msgs = [
            "You find a dead adventurer, stripped of most gear. {$gold} gold in their boot.",
            "A corpse slumped against the wall. You search the pockets: {$gold} gold.",
        ];
        $state->addLog($msgs[array_rand($msgs)]);

        if (Dice::roll(6) >= 4) {
            if (Dice::roll(2) === 1) {
                $state->player->torches++;
                $state->addLog("Found a usable Torch on the body.");
            } else {
                $state->player->rations++;
                $state->addLog("Found a Ration on the body.");
            }
        }
        return $state;
    }

    private static function spawnEnemy(GameState $state, bool $elite): GameState
    {
        $state->currentEnemy = EnemyFactory::spawn($state->dangerLevel, $elite);
        $state->phase        = 'combat';
        $state->addLog("A {$state->currentEnemy->name} blocks your path!");
        return $state;
    }
}
