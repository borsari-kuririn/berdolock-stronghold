const SCALE = 4;

const SPRITES = {
    skeleton: [
        [0,0,0,1,1,1,0,0,0],
        [0,0,1,0,0,0,1,0,0],
        [0,0,1,0,1,0,1,0,0],
        [0,0,0,1,0,1,0,0,0],
        [0,0,0,1,1,1,0,0,0],
        [0,1,1,1,1,1,1,1,0],
        [0,0,1,1,1,1,1,0,0],
        [0,0,1,0,0,0,1,0,0],
        [0,0,1,0,0,0,1,0,0],
    ],
    zombie: [
        [0,1,1,1,1,1,1,0],
        [1,0,1,0,0,1,0,1],
        [1,0,0,0,0,0,0,1],
        [1,0,1,1,1,1,0,1],
        [0,1,1,1,1,1,1,0],
        [0,1,1,0,0,1,1,0],
        [1,1,1,0,0,1,1,1],
        [1,0,0,0,0,0,0,1],
    ],
    'giant-rat': [
        [0,0,0,0,1,1,0,0],
        [0,0,0,1,0,0,1,0],
        [0,1,1,1,0,1,0,1],
        [1,0,1,1,1,1,1,0],
        [0,1,1,1,1,1,1,0],
        [0,0,1,0,0,1,0,0],
        [0,0,1,0,0,1,0,0],
    ],
    spider: [
        [1,0,0,1,1,0,0,1],
        [0,1,1,0,0,1,1,0],
        [1,0,1,1,1,1,0,1],
        [0,0,1,0,0,1,0,0],
        [1,0,1,0,0,1,0,1],
        [0,1,0,0,0,0,1,0],
    ],
    ghoul: [
        [0,1,1,1,1,1,1,0],
        [1,0,0,1,1,0,0,1],
        [1,0,1,0,0,1,0,1],
        [1,1,0,1,1,0,1,1],
        [0,1,1,1,1,1,1,0],
        [1,0,1,1,1,1,0,1],
        [1,0,0,0,0,0,0,1],
        [0,1,1,0,0,1,1,0],
    ],
    'berdolock-champion': [
        [0,1,1,1,1,1,1,0],
        [1,1,0,1,1,0,1,1],
        [1,0,1,1,1,1,0,1],
        [1,1,1,1,1,1,1,1],
        [0,1,1,1,1,1,1,0],
        [1,1,1,1,1,1,1,1],
        [1,0,1,1,1,1,0,1],
        [0,1,1,0,0,1,1,0],
        [0,1,0,0,0,0,1,0],
    ],
    berdolock: [
        [0,1,1,1,1,1,1,0],
        [1,1,1,1,1,1,1,1],
        [1,0,1,0,0,1,0,1],
        [1,1,0,1,1,0,1,1],
        [0,1,1,1,1,1,1,0],
        [1,1,1,1,1,1,1,1],
        [1,0,1,1,1,1,0,1],
        [0,1,1,0,0,1,1,0],
        [1,1,0,0,0,0,1,1],
        [0,1,0,0,0,0,1,0],
    ],
    'npc-default': [
        [0,0,1,1,1,1,0,0],
        [0,1,0,0,0,0,1,0],
        [1,0,0,1,1,0,0,1],
        [1,0,0,0,0,0,0,1],
        [1,0,1,0,0,1,0,1],
        [1,0,0,1,1,0,0,1],
        [0,1,0,0,0,0,1,0],
        [0,0,1,1,1,1,0,0],
    ],
};

function drawSprite(canvasEl, key) {
    const pixels = SPRITES[key];
    if (!pixels) return;

    const rows = pixels.length;
    const cols = pixels[0].length;
    canvasEl.width  = cols * SCALE;
    canvasEl.height = rows * SCALE;

    const ctx = canvasEl.getContext('2d');
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvasEl.width, canvasEl.height);

    ctx.fillStyle = '#000';
    pixels.forEach((row, y) =>
        row.forEach((px, x) => {
            if (px) ctx.fillRect(x * SCALE, y * SCALE, SCALE, SCALE);
        })
    );
}

function renderAll() {
    document.querySelectorAll('canvas[data-sprite]').forEach(el =>
        drawSprite(el, el.dataset.sprite)
    );
}

document.addEventListener('DOMContentLoaded', renderAll);
document.addEventListener('htmx:afterSwap',   renderAll);
