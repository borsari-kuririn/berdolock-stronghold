<?php
namespace Berdolock;

class Session
{
    private const KEY = 'berdolock_state';

    public static function load(): ?GameState
    {
        $data = $_SESSION[self::KEY] ?? null;
        if ($data === null) {
            return null;
        }
        return unserialize($data);
    }

    public static function save(GameState $state): void
    {
        $_SESSION[self::KEY] = serialize($state);
    }

    public static function clear(): void
    {
        unset($_SESSION[self::KEY]);
    }
}
