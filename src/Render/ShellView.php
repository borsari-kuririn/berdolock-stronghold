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
        <?= $panelContent ?>
    </main>
    <div id="spinner" class="htmx-indicator">...</div>
</body>
</html>
