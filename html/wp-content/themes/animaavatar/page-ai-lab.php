<?php
/**
 * Template Name: AI Lab
 */
get_header();
?>

<style>
    :root {
        --neon-cyan: #00f3ff;
        --neon-purple: #bc13fe;
        --neon-green: #0aff0a;
        --neon-red: #ff003c;
        --dark-bg: #050508;
        --glass-bg: rgba(10, 10, 16, 0.85);
        --border-color: rgba(0, 243, 255, 0.2);
        --grid-color: rgba(0, 243, 255, 0.05);
    }

    body {
        background-color: var(--dark-bg);
        background-image:
            linear-gradient(var(--grid-color) 1px, transparent 1px),
            linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .ai-lab-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 60px 20px;
        font-family: 'Orbitron', 'Courier New', sans-serif;
        color: #e0e0e0;
        min-height: 90vh;
    }

    /* --- HEADER --- */
    .lab-header {
        text-align: center;
        margin-bottom: 80px;
        position: relative;
        z-index: 2;
    }

    .lab-title {
        font-size: 4rem;
        text-transform: uppercase;
        background: linear-gradient(90deg, var(--neon-cyan), #fff, var(--neon-purple));
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 0 30px rgba(0, 243, 255, 0.4);
        margin: 0;
        letter-spacing: 6px;
        animation: shine 3s linear infinite;
    }

    @keyframes shine {
        to {
            background-position: 200% center;
        }
    }

    .lab-subtitle {
        font-size: 1.2rem;
        color: var(--neon-cyan);
        letter-spacing: 4px;
        opacity: 0.9;
        margin-top: 15px;
        text-transform: uppercase;
        position: relative;
        display: inline-block;
    }

    .lab-subtitle::before,
    .lab-subtitle::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 40px;
        height: 1px;
        background: var(--neon-cyan);
    }

    .lab-subtitle::before {
        left: -60px;
    }

    .lab-subtitle::after {
        right: -60px;
    }

    /* --- GRID LAYOUT --- */
    .lab-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 40px;
    }

    @media (max-width: 768px) {
        .lab-grid {
            grid-template-columns: 1fr;
        }

        .lab-title {
            font-size: 2.5rem;
        }
    }

    /* --- MODULE CARD --- */
    .lab-module {
        background: var(--glass-bg);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        /* Cyberpunk sharp corners */
        padding: 2px;
        /* For inner border effect */
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(12px);
        transition: all 0.3s ease;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
    }

    .lab-module::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 4px;
        padding: 2px;
        background: linear-gradient(45deg, transparent, var(--border-color), transparent);
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }

    .lab-module:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 40px rgba(0, 243, 255, 0.15);
        border-color: var(--neon-cyan);
    }

    .module-inner {
        background: rgba(5, 5, 10, 0.6);
        padding: 30px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .module-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 15px;
    }

    .module-icon {
        font-size: 2rem;
        margin-right: 15px;
        filter: drop-shadow(0 0 5px var(--neon-cyan));
    }

    .module-title {
        font-size: 1.4rem;
        color: white;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* --- UI ELEMENTS --- */
    .cyber-btn {
        background: transparent;
        border: 1px solid var(--neon-cyan);
        color: var(--neon-cyan);
        padding: 12px 24px;
        font-family: 'Orbitron', sans-serif;
        font-weight: bold;
        text-transform: uppercase;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
        margin-top: auto;
    }

    .cyber-btn:hover {
        background: var(--neon-cyan);
        color: #000;
        box-shadow: 0 0 20px var(--neon-cyan);
    }

    .cyber-select,
    .cyber-input {
        width: 100%;
        background: rgba(0, 0, 0, 0.6);
        border: 1px solid #333;
        color: var(--neon-cyan);
        padding: 12px;
        font-family: 'Courier New', monospace;
        margin-bottom: 15px;
        transition: border-color 0.3s;
    }

    .cyber-select:focus,
    .cyber-input:focus {
        outline: none;
        border-color: var(--neon-cyan);
    }

    /* --- TOOL 1: AVATAR SYNTHESIZER --- */
    .synth-preview {
        margin-top: 20px;
        height: 300px;
        background: #000;
        border: 1px solid #333;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .scan-line {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--neon-cyan);
        box-shadow: 0 0 15px var(--neon-cyan);
        animation: scan 1.5s linear infinite;
        display: none;
        z-index: 10;
    }

    @keyframes scan {
        0% {
            top: -10%;
            opacity: 0;
        }

        10% {
            opacity: 1;
        }

        90% {
            opacity: 1;
        }

        100% {
            top: 110%;
            opacity: 0;
        }
    }

    .glitch-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }

    /* --- TOOL 2: CYBER ORACLE --- */
    .oracle-terminal {
        background: #080808;
        border: 1px solid #333;
        height: 300px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        overflow-y: auto;
        margin-bottom: 15px;
        font-size: 0.9rem;
        box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.8);
    }

    .oracle-terminal::-webkit-scrollbar {
        width: 5px;
    }

    .oracle-terminal::-webkit-scrollbar-thumb {
        background: #333;
    }

    .oracle-msg {
        margin-bottom: 8px;
        line-height: 1.4;
        word-wrap: break-word;
    }

    .oracle-msg.system {
        color: #555;
    }

    .oracle-msg.user {
        color: var(--neon-cyan);
        text-align: right;
    }

    .oracle-msg.ai {
        color: var(--neon-purple);
        text-shadow: 0 0 5px rgba(188, 19, 254, 0.4);
    }

    /* --- TOOL 3: NEXUS UPLINK --- */
    .nexus-canvas-container {
        width: 100%;
        height: 300px;
        background: #020205;
        border: 1px solid #333;
        position: relative;
    }

    canvas#nexus-canvas {
        width: 100%;
        height: 100%;
        display: block;
    }

    .nexus-status {
        position: absolute;
        bottom: 10px;
        left: 10px;
        font-size: 0.8rem;
        color: var(--neon-green);
        font-family: 'Courier New', monospace;
    }

    /* --- TOOL 4: CODE CONSTRUCT --- */
    .code-display {
        background: #0a0a0a;
        border: 1px solid #333;
        height: 250px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        color: var(--neon-green);
        font-size: 0.85rem;
        overflow: hidden;
        white-space: pre-wrap;
        position: relative;
        margin-bottom: 15px;
    }

    .cursor {
        display: inline-block;
        width: 8px;
        height: 15px;
        background: var(--neon-green);
        animation: blink 1s step-end infinite;
    }

    @keyframes blink {
        50% {
            opacity: 0;
        }
    }
</style>

<div class="ai-lab-container">
    <header class="lab-header">
        <h1 class="lab-title">AI LAB <span
                style="font-size:0.4em; vertical-align:top; color:var(--neon-cyan);">[v2.0]</span></h1>
        <div class="lab-subtitle">Advanced Neural Interface & Tools</div>
    </header>

    <div class="lab-grid">

        <!-- TOOL 1: AVATAR SYNTHESIZER -->
        <div class="lab-module">
            <div class="module-inner">
                <div class="module-header">
                    <span class="module-icon">🧬</span>
                    <h2 class="module-title">Neural Avatar Synthesizer</h2>
                </div>
                <div style="margin-bottom: 15px;">
                    <select class="cyber-select" id="synth-archetype">
                        <option value="avataaars">Archetype: Humanoid</option>
                        <option value="bottts">Archetype: Mecha-Droid</option>
                        <option value="shapes">Archetype: Abstract Entity</option>
                        <option value="identicon">Archetype: Data Ghost</option>
                    </select>
                </div>
                <div class="synth-preview" id="synth-preview">
                    <div class="scan-line" id="scan-line"></div>
                    <span id="preview-text" style="color:#555; font-family:'Courier New';">AWAITING DNA
                        SEQUENCE...</span>
                    <img id="result-img" class="glitch-img" src="">
                </div>
                <button class="cyber-btn" onclick="generateAvatar()" style="margin-top: 20px;">Initialize
                    Synthesis</button>
            </div>
        </div>

        <!-- TOOL 2: CYBER ORACLE -->
        <div class="lab-module">
            <div class="module-inner">
                <div class="module-header">
                    <span class="module-icon">🔮</span>
                    <h2 class="module-title">Cyber-Oracle</h2>
                </div>
                <div class="oracle-terminal" id="oracle-terminal">
                    <div class="oracle-msg system">> SYSTEM BOOT SEQUENCE...</div>
                    <div class="oracle-msg system">> CONNECTED TO AKASHIC RECORDS.</div>
                    <div class="oracle-msg ai">> Ask, and the network shall answer.</div>
                </div>
                <div style="display:flex; gap:10px;">
                    <input type="text" class="cyber-input" id="oracle-input" placeholder="Enter query..."
                        style="margin-bottom:0;" onkeypress="handleOracleKey(event)">
                    <button class="cyber-btn" onclick="askOracle()" style="padding: 12px 15px;">Send</button>
                </div>
            </div>
        </div>

        <!-- TOOL 3: NEXUS UPLINK -->
        <div class="lab-module">
            <div class="module-inner">
                <div class="module-header">
                    <span class="module-icon">🌐</span>
                    <h2 class="module-title">Nexus Uplink</h2>
                </div>
                <div class="nexus-canvas-container">
                    <canvas id="nexus-canvas"></canvas>
                    <div class="nexus-status">STATUS: CONNECTED <span id="ping">24ms</span></div>
                </div>
                <div style="margin-top: 15px; font-size: 0.9rem; color: #888;">
                    Visualizing real-time data streams from the Anima Network.
                </div>
                <button class="cyber-btn" onclick="resetNexus()" style="margin-top: 20px;">Refresh Connection</button>
            </div>
        </div>

        <!-- TOOL 4: CODE CONSTRUCT -->
        <div class="lab-module">
            <div class="module-inner">
                <div class="module-header">
                    <span class="module-icon">💻</span>
                    <h2 class="module-title">Code Construct</h2>
                </div>
                <div class="code-display" id="code-display">
                    <span id="code-content"></span><span class="cursor"></span>
                </div>
                <select class="cyber-select" id="hack-type">
                    <option value="firewall">Script: Bypass Firewall</option>
                    <option value="decrypt">Script: Decrypt Data Shard</option>
                    <option value="trace">Script: Trace Network Ghost</option>
                </select>
                <button class="cyber-btn" onclick="generateCode()">Execute Construct</button>
            </div>
            const lines = codeSnippets[type];
            display.innerText = "";

            let i = 0;
            function typeLine() {
            if (i < lines.length) { display.innerText +=lines[i] + "\n" ; i++; setTimeout(typeLine, 500); } }
                typeLine(); } </script>

                <?php get_footer(); ?>