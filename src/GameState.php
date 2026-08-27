<?php
namespace Berdolock;

class GameState
{
    public string  $phase       = 'town';
    public Player  $player;
    public ?Enemy  $currentEnemy = null;
    public int     $turnCount    = 0;
    public int     $roomCount    = 0;
    public int     $dangerLevel  = 1;
    public int     $rerolls      = 0;
    public array   $log          = [];

    public function __construct()
    {
        $this->player = new Player();
    }

    public function addLog(string $message): void
    {
        array_unshift($this->log, $message);
        $this->log = array_slice($this->log, 0, 8);
    }

    public function dangerMultiplier(): float
    {
        return match($this->dangerLevel) {
            2       => 1.25,
            3       => 1.5,
            default => 1.0,
        };
    }
}
