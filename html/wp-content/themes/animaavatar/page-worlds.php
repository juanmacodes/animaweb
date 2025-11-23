<?php
/**
 * Template Name: Anima Worlds (Ephemeral)
 * Description: Salas 3D efímeras para chat inmersivo.
 */

get_header();
?>

<div id="anima-worlds-container">
    <div id="world-canvas"></div>

    <div class="world-ui">
        <div class="world-header">
            <h1>NEXUS LOBBY <span class="live-indicator">LIVE</span></h1>
            <div class="user-count"><span class="count">12</span> ORBS ONLINE</div>
        </div>

        <div class="chat-interface">
            <div class="chat-messages" id="world-chat-messages">
                <div class="msg system">Bienvenido al Nodo Central.</div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="world-chat-input" placeholder="Transmitir mensaje...">
                <button id="world-chat-send">SEND</button>
            </div>
        </div>

        <div class="controls-help">
            WASD to Move | Mouse to Look
        </div>
    </div>
</div>

<style>
    body {
        overflow: hidden;
        background: #000;
    }

    #anima-worlds-container {
        position: relative;
        width: 100vw;
        height: 100vh;
    }

    #world-canvas {
        width: 100%;
        height: 100%;
    }

    .world-ui {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .world-header {
        position: absolute;
        top: 20px;
        left: 20px;
        color: #fff;
        font-family: 'Orbitron', sans-serif;
    }

    .world-header h1 {
        margin: 0;
        font-size: 1.5rem;
        letter-spacing: 2px;
    }

    .live-indicator {
        background: #ff0055;
        font-size: 0.6rem;
        padding: 2px 6px;
        border-radius: 4px;
        vertical-align: middle;
        animation: blink 2s infinite;
    }

    .user-count {
        color: #00F0FF;
        font-family: 'Share Tech Mono', monospace;
        margin-top: 5px;
    }

    .chat-interface {
        position: absolute;
        bottom: 20px;
        left: 20px;
        width: 350px;
        background: rgba(0, 0, 0, 0.8);
        border: 1px solid #333;
        border-radius: 8px;
        pointer-events: auto;
        display: flex;
        flex-direction: column;
    }

    .chat-messages {
        height: 200px;
        overflow-y: auto;
        padding: 10px;
        font-family: 'Rajdhani', sans-serif;
        color: #ddd;
        font-size: 0.9rem;
    }

    .msg {
        margin-bottom: 5px;
    }

    .msg.system {
        color: #FFD700;
        font-style: italic;
    }

    .msg .user {
        color: #00F0FF;
        font-weight: bold;
    }

    .chat-input-area {
        display: flex;
        border-top: 1px solid #333;
    }

    #world-chat-input {
        flex: 1;
        background: transparent;
        border: none;
        padding: 10px;
        color: #fff;
        font-family: inherit;
    }

    #world-chat-input:focus {
        outline: none;
    }

    #world-chat-send {
        background: #00F0FF;
        color: #000;
        border: none;
        padding: 0 15px;
        font-weight: bold;
        cursor: pointer;
    }

    .controls-help {
        position: absolute;
        bottom: 20px;
        right: 20px;
        color: #888;
        font-family: monospace;
        font-size: 0.8rem;
    }

    @keyframes blink {
<?php
/**
 * Template Name: Anima Worlds (Ephemeral)
 * Description: Salas 3D efímeras para chat inmersivo.
 */

get_header();
?>

<div id="anima-worlds-container">
    <div id="world-canvas"></div>

    <div class="world-ui">
        <div class="world-header">
            <h1>NEXUS LOBBY <span class="live-indicator">LIVE</span></h1>
            <div class="user-count"><span class="count">12</span> ORBS ONLINE</div>
        </div>

        <div class="chat-interface">
            <div class="chat-messages" id="world-chat-messages">
                <div class="msg system">Bienvenido al Nodo Central.</div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="world-chat-input" placeholder="Transmitir mensaje...">
                <button id="world-chat-send">SEND</button>
            </div>
        </div>

        <div class="controls-help">
            WASD to Move | Mouse to Look
        </div>
    </div>
</div>

<style>
    body {
        overflow: hidden;
        background: #000;
    }

    #anima-worlds-container {
        position: relative;
        width: 100vw;
        height: 100vh;
    }

    #world-canvas {
        width: 100%;
        height: 100%;
    }

    .world-ui {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .world-header {
        position: absolute;
        top: 20px;
        left: 20px;
        color: #fff;
        font-family: 'Orbitron', sans-serif;
    }

    .world-header h1 {
        margin: 0;
        font-size: 1.5rem;
        letter-spacing: 2px;
    }

    .live-indicator {
        background: #ff0055;
        font-size: 0.6rem;
        padding: 2px 6px;
        border-radius: 4px;
        vertical-align: middle;
        animation: blink 2s infinite;
    }

    .user-count {
        color: #00F0FF;
        font-family: 'Share Tech Mono', monospace;
        margin-top: 5px;
    }

    .chat-interface {
        position: absolute;
        bottom: 20px;
        left: 20px;
        width: 350px;
        background: rgba(0, 0, 0, 0.8);
        border: 1px solid #333;
        border-radius: 8px;
        pointer-events: auto;
        display: flex;
        flex-direction: column;
    }

    .chat-messages {
        height: 200px;
        overflow-y: auto;
        padding: 10px;
        font-family: 'Rajdhani', sans-serif;
        color: #ddd;
        font-size: 0.9rem;
    }

    .msg {
        margin-bottom: 5px;
    }

    .msg.system {
        color: #FFD700;
        font-style: italic;
    }

    .msg .user {
        color: #00F0FF;
        font-weight: bold;
    }

    .chat-input-area {
        display: flex;
        border-top: 1px solid #333;
    }

    #world-chat-input {
        flex: 1;
        background: transparent;
        border: none;
        padding: 10px;
        color: #fff;
        font-family: inherit;
    }

    #world-chat-input:focus {
        outline: none;
    }

    #world-chat-send {
        background: #00F0FF;
        color: #000;
        border: none;
        padding: 0 15px;
        font-weight: bold;
        cursor: pointer;
    }

    .controls-help {
        position: absolute;
        bottom: 20px;
        right: 20px;
        color: #888;
        font-family: monospace;
        font-size: 0.8rem;
    }

    @keyframes blink {
        50% {
            opacity: 0.5;
        }
    }
</style>

<script type="module">
    import * as THREE from 'https://unpkg.com/three@0.160.0/build/three.module.js';
    import { OrbitControls } from 'https://unpkg.com/three@0.160.0/examples/jsm/controls/OrbitControls.js';
    
    // Firebase Imports (v9 Modular)
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js';
    import { getDatabase, ref, set, onValue, onDisconnect, push, onChildAdded, remove } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-database.js';
    import { getAuth, signInAnonymously, onAuthStateChanged } from 'https://www.gstatic.com/firebasejs/9.23.0/firebase-auth.js';

    // --- FIREBASE CONFIGURATION (PLACEHOLDERS) ---
    // User must replace these with their own Firebase project keys
    const firebaseConfig = {
        apiKey: "AIzaSyD-PLACEHOLDER-KEY",
        authDomain: "anima-avatar-placeholder.firebaseapp.com",
        databaseURL: "https://anima-avatar-placeholder-default-rtdb.firebaseio.com",
        projectId: "anima-avatar-placeholder",
        storageBucket: "anima-avatar-placeholder.appspot.com",
        messagingSenderId: "123456789",
        appId: "1:123456789:web:abcdef123456"
    };

    // Initialize Firebase
    // Wrap in try-catch to handle config errors gracefully
    let db, auth, myUserId;
    try {
        const app = initializeApp(firebaseConfig);
        db = getDatabase(app);
        auth = getAuth(app);
    } catch (e) {
        console.error("Firebase init failed. Check config.", e);
        document.querySelector('.msg.system').textContent = "Error: Firebase Config Missing.";
    }

    // Scene Setup
    const container = document.getElementById('world-canvas');
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x050505);
    scene.fog = new THREE.FogExp2(0x050505, 0.02);

    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.set(0, 2, 5);

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    container.appendChild(renderer.domElement);

    // Controls
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;

    // Environment (Grid Floor)
    const gridHelper = new THREE.GridHelper(100, 100, 0x00F0FF, 0x111111);
    scene.add(gridHelper);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040);
    scene.add(ambientLight);
    const pointLight = new THREE.PointLight(0x00F0FF, 1, 100);
    pointLight.position.set(0, 10, 0);
    scene.add(pointLight);

    // Players Map
    const players = {}; // { uid: Mesh }

    // Auth & Connection Logic
    if (auth) {
        signInAnonymously(auth).catch((error) => {
            console.error("Auth Error:", error);
            document.querySelector('.msg.system').textContent = "Error: Auth Failed.";
        });

        onAuthStateChanged(auth, (user) => {
            if (user) {
                myUserId = user.uid;
                console.log("Logged in as:", myUserId);
                document.querySelector('.msg.system').textContent = "Connected to Nexus. ID: " + myUserId.substring(0, 5);
                
                // Set initial presence
                const userRef = ref(db, 'worlds/lobby/users/' + myUserId);
                set(userRef, {
                    x: 0,
                    z: 0,
                    color: Math.random() * 0xffffff,
                    lastSeen: Date.now()
                });

                // Remove on disconnect
                onDisconnect(userRef).remove();

                // Start Listeners
                startMultiplayerListeners();
            }
        });
    }

    function startMultiplayerListeners() {
        const usersRef = ref(db, 'worlds/lobby/users');
        
        // Listen for player updates
        onValue(usersRef, (snapshot) => {
            const data = snapshot.val();
            if (!data) return;

            // Update UI Count
            const count = Object.keys(data).length;
            document.querySelector('.user-count .count').textContent = count;

            // Update/Create Players
            Object.keys(data).forEach(uid => {
                if (uid === myUserId) return; // Ignore self

                const playerData = data[uid];
                
                if (!players[uid]) {
                    // Create new player orb
                    const orbGeo = new THREE.SphereGeometry(0.3, 16, 16);
                    const orbMat = new THREE.MeshBasicMaterial({ color: playerData.color || 0xBC13FE, wireframe: true });
                    const orb = new THREE.Mesh(orbGeo, orbMat);
                    scene.add(orb);
                    players[uid] = orb;
                }

                // Update position (lerp could be added here for smoothness)
                players[uid].position.set(playerData.x, 0.5, playerData.z);
            });

            // Remove disconnected players
            Object.keys(players).forEach(uid => {
                if (!data[uid]) {
                    scene.remove(players[uid]);
                    delete players[uid];
                }
            });
        });

        // Chat Listeners
        const chatRef = ref(db, 'worlds/lobby/chat');
        onChildAdded(chatRef, (data) => {
            const msg = data.val();
            addMessageToUI(msg.user, msg.text);
        });
    }

    // Animation Loop
    let lastUpdate = 0;

    function animate() {
        requestAnimationFrame(animate);

        controls.update();

        // Sync my position (Throttled to 100ms)
        if (myUserId && db) {
            const now = Date.now();
            if (now - lastUpdate > 100) {
                const userRef = ref(db, 'worlds/lobby/users/' + myUserId);
                // We use camera position as "player" position for now, or controls target
                // Let's use controls.target as the "avatar" position
                set(userRef, {
                    x: controls.target.x,
                    z: controls.target.z,
                    lastSeen: now
                }).catch(() => {}); // Ignore errors (e.g. permission denied)
                lastUpdate = now;
            }
        }

        renderer.render(scene, camera);
    }
    animate();

    // Resize
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    // Chat Logic
    const chatInput = document.getElementById('world-chat-input');
    const chatSend = document.getElementById('world-chat-send');
    const chatMsgs = document.getElementById('world-chat-messages');

    function addMessageToUI(user, text) {
        const div = document.createElement('div');
        div.className = 'msg';
        const isMe = user === myUserId;
        const displayUser = isMe ? 'YOU' : user.substring(0, 5);
        div.innerHTML = `<span class="user" style="color: ${isMe ? '#00F0FF' : '#BC13FE'}">${displayUser}:</span> ${text}`;
        chatMsgs.appendChild(div);
        chatMsgs.scrollTop = chatMsgs.scrollHeight;
    }

    chatSend.addEventListener('click', () => {
        const text = chatInput.value.trim();
        if (!text || !myUserId) return;
        
        const chatRef = ref(db, 'worlds/lobby/chat');
        const newMsgRef = push(chatRef);
        set(newMsgRef, {
            user: myUserId,
            text: text,
            timestamp: Date.now()
        });
        
        chatInput.value = '';
    });

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') chatSend.click();
    });

</script>

<?php get_footer(); ?>