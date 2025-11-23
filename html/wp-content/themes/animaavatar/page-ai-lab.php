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
        --dark-bg: #0a0a12;
        --glass-bg: rgba(20, 20, 30, 0.7);
        --border-color: rgba(0, 243, 255, 0.3);
    }

    .ai-lab-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: 'Orbitron', sans-serif;
        /* Assuming font exists or fallback */
        color: #e0e0e0;
        min-height: 80vh;
    }

    .lab-header {
        text-align: center;
        margin-bottom: 60px;
        position: relative;
    }

    .lab-title {
        font-size: 3.5rem;
        text-transform: uppercase;
        background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 0 20px rgba(0, 243, 255, 0.5);
        margin: 0;
        letter-spacing: 4px;
    }

    .lab-subtitle {
        font-size: 1.2rem;
        color: var(--neon-cyan);
        letter-spacing: 2px;
        opacity: 0.8;
        margin-top: 10px;
    }

    .lab-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    @media (max-width: 768px) {
        .lab-grid {
            grid-template-columns: 1fr;
        }
    }

    .lab-module {
        background: var(--glass-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 30px;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .lab-module:hover {
        transform: translateY(-5px);
        box-shadow: 0 0 30px rgba(0, 243, 255, 0.2);
        border-color: var(--neon-cyan);
    }

    .module-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 15px;
    }

    .module-icon {
        font-size: 2rem;
        margin-right: 15px;
    }

    .module-title {
        font-size: 1.5rem;
        color: white;
        margin: 0;
    }

    /* --- TOOL 1: AVATAR SYNTHESIZER --- */
    .synth-controls {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .synth-group label {
        display: block;
        margin-bottom: 5px;
        font-size: 0.9rem;
        color: #aaa;
    }

    .synth-select {
        width: 100%;
        background: rgba(0, 0, 0, 0.5);
        border: 1px solid var(--border-color);
        color: white;
        padding: 10px;
        border-radius: 4px;
        font-family: inherit;
    }

    .synth-btn {
        background: linear-gradient(45deg, var(--neon-cyan), #0099ff);
        border: none;
        color: black;
        padding: 12px;
        font-weight: bold;
        text-transform: uppercase;
        cursor: pointer;
        margin-top: 10px;
        clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px);
        transition: filter 0.3s;
    }

    .synth-btn:hover {
        filter: brightness(1.2);
    }

    .synth-preview {
        margin-top: 20px;
        height: 250px;
        background: black;
        border: 1px dashed var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .scan-line {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--neon-cyan);
        animation: scan 2s linear infinite;
        display: none;
        box-shadow: 0 0 10px var(--neon-cyan);
    }

    @keyframes scan {
        0% {
            top: 0;
        }

        100% {
            top: 100%;
        }
    }

    /* --- TOOL 2: CYBER ORACLE --- */
    .oracle-terminal {
        background: #000;
        border: 1px solid #333;
        height: 300px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        overflow-y: auto;
        margin-bottom: 15px;
        font-size: 0.9rem;
    }

    .oracle-msg {
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .oracle-msg.system {
        color: var(--neon-cyan);
    }

    .oracle-msg.user {
        color: #fff;
        text-align: right;
    }

    .oracle-msg.ai {
        color: var(--neon-purple);
    }

    .oracle-input-group {
        display: flex;
        gap: 10px;
    }

    .oracle-input {
        flex: 1;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        padding: 10px;
        font-family: inherit;
    }

    .oracle-btn {
        background: var(--neon-purple);
        border: none;
        color: white;
        padding: 0 20px;
        cursor: pointer;
        font-weight: bold;
    }
</style>

<div class="ai-lab-container">
    <header class="lab-header">
        <h1 class="lab-title">AI LAB <span style="font-size:0.5em; vertical-align:top;">[BETA]</span></h1>
        <div class="lab-subtitle">EXPERIMENTAL NEURAL INTERFACE</div>
    </header>

    <div class="lab-grid">
        <!-- TOOL 1 -->
        <div class="lab-module">
            <div class="module-header">
                <span class="module-icon">🧬</span>
                <h2 class="module-title">Neural Avatar Synthesizer</h2>
            </div>
            <div class="synth-controls">
                <div class="synth-group">
                    <label>ARCHETYPE</label>
                    <select class="synth-select" id="synth-archetype">
                        <option>Cyber-Samurai</option>
                        <option>Netrunner</option>
                        <option>Tech-Priest</option>
                        <option>Corporate Agent</option>
                    </select>
                </div>
                <div class="synth-group">
                    <label>AESTHETIC</label>
                    <select class="synth-select" id="synth-style">
                        <option>Neon Noir</option>
                        <option>Chrome & Steel</option>
                        <option>Bioluminescent</option>
                        <option>Glitch Art</option>
                    </select>
                </div>
                <button class="synth-btn" onclick="generateAvatar()">INITIALIZE SYNTHESIS</button>
            </div>
            <div class="synth-preview" id="synth-preview">
                <div class="scan-line" id="scan-line"></div>
                <span id="preview-text" style="color:#555;">AWAITING INPUT...</span>
                <img id="result-img" src="" style="width:100%; height:100%; object-fit:cover; display:none;">
            </div>
        </div>

        <!-- TOOL 2 -->
        <div class="lab-module">
            <div class="module-header">
                <span class="module-icon">🔮</span>
                <h2 class="module-title">Cyber-Oracle</h2>
            </div>
            <div class="oracle-terminal" id="oracle-terminal">
                <div class="oracle-msg system">> SYSTEM READY.</div>
                <div class="oracle-msg system">> CONNECTED TO NEURAL NET.</div>
                <div class="oracle-msg ai">> Ask your question, traveler.</div>
            </div>
            <div class="oracle-input-group">
                <input type="text" class="oracle-input" id="oracle-input" placeholder="Enter query..."
                    onkeypress="handleOracleKey(event)">
                <button class="oracle-btn" onclick="askOracle()">SEND</button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- AVATAR SYNTHESIZER LOGIC ---
    function generateAvatar() {
        const preview = document.getElementById('synth-preview');
        const scanLine = document.getElementById('scan-line');
        const text = document.getElementById('preview-text');
        const img = document.getElementById('result-img');
        const archetype = document.getElementById('synth-archetype').value;

        // Reset
        img.style.display = 'none';
        text.style.display = 'block';
        text.innerText = "PROCESSING DNA SEQUENCE...";
        text.style.color = "var(--neon-cyan)";
        scanLine.style.display = 'block';

        // Simulate processing
        setTimeout(() => {
            text.innerText = "RENDERING TEXTURES...";
        }, 1000);

        setTimeout(() => {
            text.innerText = "COMPILING NEURAL MESH...";
        }, 2000);

        setTimeout(() => {
            scanLine.style.display = 'none';
            text.style.display = 'none';

            // Mock result based on selection (using placeholders for now)
            // In a real app, this would call an API.
            // Using a consistent placeholder service for demo.
            const seed = Math.floor(Math.random() * 1000);
            img.src = `https://api.dicebear.com/9.x/avataaars/svg?seed=${archetype}${seed}&backgroundColor=b6e3f4`;
            img.style.display = 'block';
        }, 3000);
    }

    // --- CYBER ORACLE LOGIC ---
    const oracleResponses = [
        "The neural pathways suggest a positive outcome.",
        "Data unclear. Entropy levels too high.",
        "The corporate overlords will not be pleased.",
        "Upgrade your hardware and try again.",
        "Yes, but at a great cost.",
        "The algorithm aligns with your desire.",
        "404: Destiny Not Found.",
        "Beware of the glitch in the matrix."
    ];

    function handleOracleKey(e) {
        if (e.key === 'Enter') askOracle();
    }

    function askOracle() {
        const input = document.getElementById('oracle-input');
        const terminal = document.getElementById('oracle-terminal');
        const query = input.value.trim();

        if (!query) return;

        // User msg
        const userDiv = document.createElement('div');
        userDiv.className = 'oracle-msg user';
        userDiv.innerText = "> " + query;
        terminal.appendChild(userDiv);

        input.value = '';
        terminal.scrollTop = terminal.scrollHeight;

        // AI Response simulation
        setTimeout(() => {
            const aiDiv = document.createElement('div');
            aiDiv.className = 'oracle-msg ai';
            const randomResponse = oracleResponses[Math.floor(Math.random() * oracleResponses.length)];
            aiDiv.innerText = "> " + randomResponse;
            terminal.appendChild(aiDiv);
            terminal.scrollTop = terminal.scrollHeight;
        }, 800);
    }
</script>

<?php get_footer(); ?>