<?php
/**
 * Template Name: Streamer Minigames Arcade
 * Description: Una sala de arcade con minijuegos para streamers (Whack-a-Hater, etc.)
 */

get_header();
?>

<div id="minigames-arcade" class="arcade-container">

    <header class="arcade-header text-center">
        <h1 class="glitch-text" data-text="STREAMER ARCADE">STREAMER ARCADE</h1>
        <p class="arcade-subtitle">ENTRENA TUS REFLEJOS PARA EL CHAT</p>
    </header>

    <!-- Game Selector / Tabs -->
    <div class="arcade-tabs">
        <button class="arcade-tab active" data-game="whack">CAZA-HATERS</button>
        <button class="arcade-tab" data-game="runner">STREAM RUNNER</button>
        <button class="arcade-tab" data-game="rain">LLUVIA DE HYPE</button>
    </div>

    <!-- Game Container -->
    <div class="game-viewport">

        <!-- GAME 1: WHACK-A-HATER -->
        <div id="game-whack" class="game-screen active">
            <div class="game-ui-overlay">
                <div class="score-board">SCORE: <span id="whack-score">0</span></div>
                <div class="timer-board">TIME: <span id="whack-time">30</span>s</div>
                <button id="whack-start-btn" class="start-btn">START BANNING</button>
            </div>
            <div class="whack-grid">
                <!-- 9 Holes -->
                <div class="hole" id="hole-1">
                    <div class="hater"></div>
                </div>
                <div class="hole" id="hole-2">
                    <div class="hater"></div>
                </div>
                <div class="hole" id="hole-3">
                    <div class="hater"></div>
                </div>
                <div class="hole" id="hole-4">
                    <div class="hater"></div>
                </div>
                <div class="hole" id="hole-5">
                    <div class="hater"></div>
                </div>
                <div class="hole" id="hole-6">
                    <div class="hater"></div>
                </div>
                <div class="hole" id="hole-7">
                    <div class="hater"></div>
                </div>
                <div class="hole" id="hole-8">
                    <div class="hater"></div>
                </div>
                <div class="hole" id="hole-9">
                    <div class="hater"></div>
                </div>
            </div>
        </div>

        <!-- GAME 2: STREAM RUNNER -->
        <div id="game-runner" class="game-screen">
            <canvas id="runner-canvas" width="800" height="600"></canvas>
            <div id="runner-ui" class="game-ui-overlay">
                <div class="score-board">SCORE: <span id="runner-score">0</span></div>
                <button id="runner-start-btn" class="start-btn">START RUNNING</button>
            </div>
        </div>

        <!-- GAME 3: EMOJI RAIN -->
        <div id="game-rain" class="game-screen">
            <canvas id="rain-canvas" width="800" height="600"></canvas>
            <div id="rain-ui" class="game-ui-overlay">
                <div class="score-board">HYPE: <span id="rain-score">0</span></div>
                <button id="rain-start-btn" class="start-btn">START THE HYPE</button>
            </div>
        </div>

    </div>

</div>

<style>
    /* ARCADE STYLES */
    :root {
        --neon-red: #ff0055;
        --neon-blue: #00f0ff;
        --neon-yellow: #ffd700;
        --bg-dark: #050505;
        --panel-bg: #111;
    }

    .arcade-container {
        background-color: var(--bg-dark);
        color: #fff;
        min-height: 100vh;
        padding: 100px 20px;
        font-family: 'Orbitron', sans-serif;
    }

    .arcade-header h1 {
        font-size: 3rem;
        color: var(--neon-blue);
        text-shadow: 0 0 10px var(--neon-blue);
        margin-bottom: 10px;
    }

    .arcade-subtitle {
        color: #888;
        letter-spacing: 2px;
        margin-bottom: 40px;
    }

    /* TABS */
    .arcade-tabs {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 30px;
    }

    .arcade-tab {
        background: transparent;
        border: 1px solid #333;
        color: #888;
        padding: 10px 20px;
        cursor: pointer;
        font-family: 'Rajdhani', sans-serif;
        font-weight: bold;
        font-size: 1.2rem;
        transition: 0.3s;
        text-transform: uppercase;
    }

    .arcade-tab:hover,
    .arcade-tab.active {
        border-color: var(--neon-blue);
        color: var(--neon-blue);
        box-shadow: 0 0 15px rgba(0, 240, 255, 0.2);
    }

    /* GAME VIEWPORT */
    .game-viewport {
        max-width: 800px;
        height: 600px;
        margin: 0 auto;
        background: #000;
        border: 2px solid #333;
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.8);
    }

    .game-screen {
        display: none;
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
    }

    .game-screen.active {
        display: block;
    }

    /* WHACK-A-HATER STYLES */
    #game-whack {
        background: radial-gradient(circle, #1a1a1a 0%, #000 100%);
        cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="%23ff0055" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v2"/><path d="M14 2v2"/><path d="M12 10v12"/><path d="M12 10l4-4"/><path d="M12 10l-4-4"/><circle cx="12" cy="12" r="2"/></svg>') 16 16, crosshair;
        /* Custom Ban Hammer Cursor attempt */
    }

    .game-ui-overlay {
        position: absolute;
        top: 20px;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: space-around;
        z-index: 10;
        pointer-events: none;
    }

    .score-board,
    .timer-board {
        font-size: 1.5rem;
        color: #fff;
        background: rgba(0, 0, 0, 0.7);
        padding: 5px 15px;
        border-radius: 5px;
        border: 1px solid #444;
    }

    .start-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 20px 40px;
        font-size: 2rem;
        background: var(--neon-red);
        color: #fff;
        border: none;
        cursor: pointer;
        font-family: 'Orbitron', sans-serif;
        box-shadow: 0 0 20px var(--neon-red);
        pointer-events: auto;
        z-index: 20;
    }

    .whack-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        width: 100%;
        height: 100%;
        padding: 80px 20px 20px;
    }

    .hole {
        background: #222;
        border-radius: 50%;
        position: relative;
        overflow: hidden;
        border: 2px solid #333;
        box-shadow: inset 0 0 20px #000;
    }

    .hater {
        width: 100%;
        height: 100%;
        background-image: url('https://api.iconify.design/noto:clown-face.svg');
        /* Placeholder Hater */
        background-size: 70%;
        background-position: center bottom;
        background-repeat: no-repeat;
        position: absolute;
        top: 100%;
        /* Hidden */
        transition: top 0.1s;
    }

    .hole.up .hater {
        top: 10%;
    }

    .hole.bonked .hater {
        filter: grayscale(100%) brightness(0.5);
        transform: scale(0.9);
    }

    /* COMING SOON */
    .coming-soon-overlay {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // --- TABS LOGIC ---
        const tabs = document.querySelectorAll('.arcade-tab');
        const screens = document.querySelectorAll('.game-screen');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class
                tabs.forEach(t => t.classList.remove('active'));
                screens.forEach(s => s.classList.remove('active'));

                // Add active class
                tab.classList.add('active');
                const gameId = tab.getAttribute('data-game');
                document.getElementById('game-' + gameId).classList.add('active');
            });
        });

        // --- GAME 1: WHACK-A-HATER LOGIC ---
        const holes = document.querySelectorAll('.hole');
        const scoreBoard = document.getElementById('whack-score');
        height: 50,
            speed: 7,
                color: '#FF0055'
    };

    // Emojis
    let drops = [];
    const goodEmojis = ['🔥', '❤️', '💎', '🚀'];
    const badEmojis = ['💩', '💀', '🤡', '📉'];

    function spawnDrop() {
        if (!rainIsPlaying) return;
        const isGood = Math.random() > 0.3; // 70% good
        const emojiList = isGood ? goodEmojis : badEmojis;
        const emoji = emojiList[Math.floor(Math.random() * emojiList.length)];

        drops.push({
            x: Math.random() * (rainCanvas.width - 30),
            y: -30,
            size: 30,
            emoji: emoji,
            type: isGood ? 'good' : 'bad',
            speed: Math.random() * 2 + rainSpeed
        });

        setTimeout(spawnDrop, Math.random() * 800 + 200);
    }

    function updateRain() {
        if (!rainIsPlaying) return;

        rainCtx.clearRect(0, 0, rainCanvas.width, rainCanvas.height);

        // Draw Catcher
        rainCtx.fillStyle = catcher.color;
        rainCtx.fillRect(catcher.x, catcher.y, catcher.width, catcher.height);

        // Move Catcher (Mouse tracking)
        // (Handled by event listener below)

        // Drops Logic
        drops.forEach((drop, index) => {
            drop.y += drop.speed;

            // Draw Drop
            rainCtx.font = '30px Arial';
            rainCtx.fillText(drop.emoji, drop.x, drop.y);

            // Collision
            if (
                drop.x < catcher.x + catcher.width &&
                drop.x + drop.size > catcher.x &&
                drop.y < catcher.y + catcher.height &&
                drop.y + drop.size > catcher.y
            ) {
                // Caught!
                drops.splice(index, 1);
                if (drop.type === 'good') {
                    rainScore += 10;
                    rainScoreEl.textContent = rainScore;
                    // Speed up slightly
                    if (rainScore % 50 === 0) rainSpeed += 0.5;
                } else {
                    gameOverRain();
                }
            }

            // Missed (Remove off screen)
            if (drop.y > rainCanvas.height) {
                drops.splice(index, 1);
            }
        });

        rainGameLoop = requestAnimationFrame(updateRain);
    }

    function startRain() {
        rainIsPlaying = true;
        rainScore = 0;
        rainSpeed = 3;
        drops = [];
        rainScoreEl.textContent = 0;
        rainStartBtn.style.display = 'none';

        spawnDrop();
        updateRain();
    }

    function gameOverRain() {
        rainIsPlaying = false;
        cancelAnimationFrame(rainGameLoop);
        rainStartBtn.textContent = "TOXIC OVERLOAD! RESTART";
        rainStartBtn.style.display = 'block';
    }

    // Mouse Movement
    rainCanvas.addEventListener('mousemove', (e) => {
        if (!rainIsPlaying) return;
        const rect = rainCanvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        catcher.x = mouseX - catcher.width / 2;

        // Clamp
        if (catcher.x < 0) catcher.x = 0;
        if (catcher.x + catcher.width > rainCanvas.width) catcher.x = rainCanvas.width - catcher.width;
    });

    rainStartBtn.addEventListener('click', startRain);

    });
</script>

<?php get_footer(); ?>