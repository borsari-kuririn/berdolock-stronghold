<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Berdolock\Session;
use Berdolock\Actions\{NewGame, CharGenAction, TownAction, ExploreAction, CombatAction, ExtractAction};

session_start();
$state = Session::load();

$action = $_POST['action'] ?? '';

if ($action === 'new_game') {
    Session::clear();
}

$state = match($action) {
    'new_game'        => NewGame::handle($_POST),
    'chargen_confirm' => CharGenAction::confirm($state),
    'chargen_reroll'  => CharGenAction::reroll($state),
    'town'            => TownAction::handle($state, $_POST),
    'explore'         => ExploreAction::handle($state),
    'attack'          => CombatAction::handle($state, 'attack'),
    'flee'            => CombatAction::handle($state, 'flee'),
    'extract'         => ExtractAction::handle($state),
    default           => $state,
};

Session::save($state);

match($state->phase) {
    'chargen'  => include __DIR__ . '/../src/Render/CharGenView.php',
    'town'     => include __DIR__ . '/../src/Render/TownView.php',
    'dungeon'  => include __DIR__ . '/../src/Render/DungeonView.php',
    'combat'   => include __DIR__ . '/../src/Render/CombatView.php',
    'victory'  => include __DIR__ . '/../src/Render/VictoryView.php',
    'gameover' => include __DIR__ . '/../src/Render/GameOverView.php',
    default    => include __DIR__ . '/../src/Render/NewGameView.php',
};
