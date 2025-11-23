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

    // Other Orbs (Fake Users)
    const orbs = [];
    const orbGeo = new THREE.SphereGeometry(0.3, 16, 16);
    const orbMat = new THREE.MeshBasicMaterial({ color: 0xBC13FE, wireframe: true });

    for (let i = 0; i < 10; i++) {
        const orb = new THREE.Mesh(orbGeo, orbMat);
        orb.position.set(
            (Math.random() - 0.5) * 20,
            0.5,
            (Math.random() - 0.5) * 20
        );
        orb.userData = {
            velocity: new THREE.Vector3(
                (Math.random() - 0.5) * 0.05,
                0,
                (Math.random() - 0.5) * 0.05
            )
        };
        scene.add(orb);
        orbs.push(orb);
    }

    // My Avatar (Invisible, just camera for now, or maybe a visible orb too?)
    // For now, first person view essentially via OrbitControls center

    // Animation Loop
    function animate() {
        requestAnimationFrame(animate);

        // Move Orbs
        orbs.forEach(orb => {
            orb.position.add(orb.userData.velocity);
            if (orb.position.x > 10 || orb.position.x < -10) orb.userData.velocity.x *= -1;
            if (orb.position.z > 10 || orb.position.z < -10) orb.userData.velocity.z *= -1;
            orb.rotation.y += 0.02;
        });

        controls.update();
        renderer.render(scene, camera);
    }
    animate();

    // Resize
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    // Chat Logic (Fake)
    const chatInput = document.getElementById('world-chat-input');
    const chatSend = document.getElementById('world-chat-send');
    const chatMsgs = document.getElementById('world-chat-messages');

    function addMessage(user, text) {
        const div = document.createElement('div');
        div.className = 'msg';
        div.innerHTML = `<span class="user">${user}:</span> ${text}`;
        chatMsgs.appendChild(div);
        chatMsgs.scrollTop = chatMsgs.scrollHeight;
    }

    chatSend.addEventListener('click', () => {
        const text = chatInput.value.trim();
        if (!text) return;
        addMessage('YOU', text);
        chatInput.value = '';

        // Fake reply
        setTimeout(() => {
            const replies = ['Hola!', 'Interesante...', 'Nos vemos en el evento?', 'Cool avatar!'];
            const randomReply = replies[Math.floor(Math.random() * replies.length)];
            const randomUser = 'User_' + Math.floor(Math.random() * 1000);
            addMessage(randomUser, randomReply);
        }, 2000);
    });

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') chatSend.click();
    });

</script>

<?php get_footer(); ?>