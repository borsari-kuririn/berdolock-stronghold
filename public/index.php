<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Berdolock\Session;

session_start();
$state = Session::load();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#888">
    <title>Berdolock's Stronghold</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
    <script src="/assets/htmx.min.js"></script>
    <script src="/assets/js/sprites.js" defer></script>
</head>
<body>
    <main id="game-panel">
        <?php
        if ($state === null) {
            include __DIR__ . '/../src/Render/NewGameView.php';
        } elseif ($state->phase === 'chargen') {
            include __DIR__ . '/../src/Render/CharGenView.php';
        } elseif ($state->phase === 'town') {
            include __DIR__ . '/../src/Render/TownView.php';
        } elseif ($state->phase === 'dungeon') {
            include __DIR__ . '/../src/Render/DungeonView.php';
        } elseif ($state->phase === 'combat') {
            include __DIR__ . '/../src/Render/CombatView.php';
        } elseif ($state->phase === 'victory') {
            include __DIR__ . '/../src/Render/VictoryView.php';
        } elseif ($state->phase === 'gameover') {
            include __DIR__ . '/../src/Render/GameOverView.php';
        }
        ?>
    </main>
    <div id="spinner" class="htmx-indicator">...</div>
</body>
</html>
