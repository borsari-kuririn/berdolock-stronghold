<?php
namespace Berdolock\Actions;

use Berdolock\{GameState, Dice};

class CombatAction
{
    public static function handle(GameState $state, string $choice): GameState
    {
        return match($choice) {
            'attack' => self::resolveAttack($state),
            'flee'   => self::resolveFlee($state),
            default  => $state,
        };
    }

    private static function resolveAttack(GameState $state): GameState
    {
        $player = $state->player;
        $enemy  = $state->currentEnemy;

        if ($player->isDark) {
            $state->addLog("[DARK] You can't see clearly \u2014 the enemy strikes first!");
            $state = self::enemyAttacks($state);
            if ($state->phase === 'gameover') return $state;
            $state = self::playerAttacks($state);
        } else {
            $playerInit = Dice::roll(6);
            $enemyInit  = Dice::roll(6);

            if ($playerInit >= $enemyInit) {
                $state = self::playerAttacks($state);
                if ($enemy->isAlive()) {
                    $state = self::enemyAttacks($state);
                }
            } else {
                $state->addLog("The {$enemy->name} acts first!");
                $state = self::enemyAttacks($state);
                if ($state->phase !== 'gameover') {
                    $state = self::playerAttacks($state);
                }
            }
        }

        if ($state->phase === 'gameover') return $state;

        if ($player->isPoisoned) {
            $player->hp--;
            $state->addLog("Poison deals 1 damage. LP: {$player->hp}/{$player->maxHp}");
            if ($player->hp <= 0) {
                $state->phase = 'gameover';
                $state->addLog("You succumb to poison.");
                return $state;
            }
        }

        if (!$enemy->isAlive()) {
            $state = self::handleVictory($state);
        }

        return $state;
    }

    private static function playerAttacks(GameState $state): GameState
    {
        $player = $state->player;
        $enemy  = $state->currentEnemy;

        // Berdolock immunity (once per fight)
        $roll   = Dice::roll(6) + $player->attackPower();
        $damage = max(0, $roll - $enemy->defense);

        if ($enemy->isBoss && !$enemy->damageImmunityUsed && $damage > 0) {
            $enemy->damageImmunityUsed = true;
            $damage = 0;
            $state->addLog("Berdolock shrugs off your blow!");
        } else {
            $enemy->hp -= $damage;
            $state->addLog("You attack the {$enemy->name} for {$damage} damage. (LP: {$enemy->hp}/{$enemy->maxHp})");
        }

        return $state;
    }

    private static function enemyAttacks(GameState $state): GameState
    {
        $player      = $state->player;
        $enemy       = $state->currentEnemy;
        $attackBonus = $player->isDark ? 2 : 0;

        // Champion enrage at 50% HP
        if ($enemy->name === 'Berdolock Champion'
            && !$enemy->enraged
            && $enemy->hp < ($enemy->maxHp / 2)) {
            $enemy->enraged  = true;
            $enemy->attack  += 3;
            $state->addLog("The Champion ENRAGES! ATK increased!");
        }

        $roll   = Dice::roll(6) + $enemy->attack + $attackBonus;
        $damage = max(0, $roll - $player->defensePower());
        $player->hp -= $damage;

        $state->addLog("The {$enemy->name} deals {$damage} damage to you. (LP: {$player->hp}/{$player->maxHp})");

        // Spider poison
        if ($enemy->name === 'Spider' && $damage > 0 && Dice::roll(6) >= 4) {
            $player->isPoisoned = true;
            $state->addLog("The Spider's venom courses through you \u2014 Poisoned!");
        }

        if ($player->hp <= 0) {
            $state->phase = 'gameover';
            $state->addLog("You have been slain by the {$enemy->name}.");
        }

        return $state;
    }

    private static function resolveFlee(GameState $state): GameState
    {
        $enemy = $state->currentEnemy;

        if (Dice::luck()) {
            $state->currentEnemy = null;
            $state->phase        = 'dungeon';
            $state->addLog("You flee from the {$enemy->name}!");
        } else {
            $state->addLog("Flee failed! The {$enemy->name} attacks you as you run.");
            $state = self::enemyAttacks($state);
        }

        return $state;
    }

    private static function handleVictory(GameState $state): GameState
    {
        $enemy  = $state->currentEnemy;
        $player = $state->player;

        $player->gold += $enemy->goldDrop;
        $state->addLog("You defeat the {$enemy->name}! +{$enemy->goldDrop} gold.");

        $player->xp += $enemy->xpDrop;
        if ($player->xp >= $player->xpNext) {
            $player->level++;
            $player->xpNext = $player->level * 10;
            $player->maxHp += 2;
            $player->hp     = min($player->hp + 2, $player->maxHp);
            $state->addLog("LEVEL UP! LV:{$player->level}");
        }

        $state->currentEnemy = null;
        $state->phase        = $enemy->isBoss ? 'victory' : 'dungeon';

        if ($enemy->isBoss) {
            $state->addLog("Berdolock falls. The stronghold is yours.");
        }

        return $state;
    }
}
