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
        // Silently discard sessions serialized against an older class schema
        $state = @unserialize($data);
        if (!$state instanceof GameState) {
            self::clear();
            return null;
        }
        return $state;
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
