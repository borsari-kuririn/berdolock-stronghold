<div class="gb-new-game">
    <h2>BERDOLOCK'S<br>STRONGHOLD</h2>
    <p>A solo dungeon crawler.<br>Enter if you dare.</p>

    <form hx-post="/action.php"
          hx-target="#game-panel"
          hx-swap="innerHTML"
          hx-indicator="#spinner">
        <input type="hidden" name="action" value="new_game">
        <label for="name">NAME:</label>
        <input type="text" id="name" name="name"
               placeholder="ADVENTURER" maxlength="16"
               autocomplete="off">
        <button type="submit" hx-disabled-elt="this">
            ○ START GAME
        </button>
    </form>
</div>
