import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('hero-canvas-container');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x0a0a12, 0.002);

    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.z = 5;
    camera.position.y = 1.5;

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight(0x00F0FF, 2);
    dirLight.position.set(5, 5, 5);
    scene.add(dirLight);

    const purpleLight = new THREE.PointLight(0xBC13FE, 2, 10);
    purpleLight.position.set(-2, 3, 2);
    scene.add(purpleLight);

    // Load Model
    const loader = new GLTFLoader();
    let avatar;
    let mixer;

    // Placeholder URL - Replace with actual .glb file
    const modelUrl = 'https://models.readyplayer.me/64d61e9e16b7f32491f6d354.glb';

    loader.load(modelUrl, function (gltf) {
        avatar = gltf.scene;
        avatar.scale.set(1.5, 1.5, 1.5);
        avatar.position.y = -2;
        scene.add(avatar);

        // Animation Mixer
        mixer = new THREE.AnimationMixer(avatar);
        // If the model has animations, play the first one
        if (gltf.animations.length > 0) {
            const action = mixer.clipAction(gltf.animations[0]);
            action.play();
        }

    }, undefined, function (error) {
        console.error('An error happened loading the avatar:', error);
        // Fallback to wireframe sphere if model fails
        const geo = new THREE.IcosahedronGeometry(1, 1);
        const mat = new THREE.MeshBasicMaterial({ color: 0x00F0FF, wireframe: true });
        const sphere = new THREE.Mesh(geo, mat);
        scene.add(sphere);
    });

    // Particles (Cyberpunk Dust)
    const particlesGeometry = new THREE.BufferGeometry();
    const particlesCount = 500;
    const posArray = new Float32Array(particlesCount * 3);

    for (let i = 0; i < particlesCount * 3; i++) {
        posArray[i] = (Math.random() - 0.5) * 20;
    }

    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    const particlesMaterial = new THREE.PointsMaterial({
        size: 0.02,
        color: 0x00F0FF,
        transparent: true,
        opacity: 0.6
    });
    const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
    scene.add(particlesMesh);

    // Interaction
    let mouseX = 0;
    let mouseY = 0;
    let targetRotationX = 0;
    let targetRotationY = 0;

    const windowHalfX = window.innerWidth / 2;
    const windowHalfY = window.innerHeight / 2;

    document.addEventListener('mousemove', (event) => {
        mouseX = (event.clientX - windowHalfX);
        mouseY = (event.clientY - windowHalfY);
    });

    const clock = new THREE.Clock();

    function animate() {
        requestAnimationFrame(animate);

        const delta = clock.getDelta();

        if (mixer) mixer.update(delta);

        if (avatar) {
            targetRotationY = mouseX * 0.001;
            targetRotationX = mouseY * 0.001;

            avatar.rotation.y += 0.05 * (targetRotationY - avatar.rotation.y);
            // avatar.rotation.x += 0.05 * (targetRotationX - avatar.rotation.x);
        }

        particlesMesh.rotation.y += 0.001;

        renderer.render(scene, camera);
    }

    animate();

    // Handle resize
    window.addEventListener('resize', () => {
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    });
});
