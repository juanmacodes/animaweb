document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('hero-canvas-container');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    // Add some fog for depth - dark cyberpunk color
    scene.fog = new THREE.FogExp2(0x0a0a12, 0.002);

    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Geometry - Icosahedron for a techy look
    const geometry = new THREE.IcosahedronGeometry(10, 2);
    
    // Material - Wireframe with cyberpunk cyan color
    const material = new THREE.MeshBasicMaterial({ 
        color: 0x00f3ff, 
        wireframe: true,
        transparent: true,
        opacity: 0.3
    });

    const sphere = new THREE.Mesh(geometry, material);
    scene.add(sphere);

    // Inner glowing core
    const coreGeometry = new THREE.IcosahedronGeometry(5, 1);
    const coreMaterial = new THREE.MeshBasicMaterial({
        color: 0xbc13fe, // Purple
        wireframe: true,
        transparent: true,
        opacity: 0.5
    });
    const core = new THREE.Mesh(coreGeometry, coreMaterial);
    scene.add(core);

    // Particles
    const particlesGeometry = new THREE.BufferGeometry();
    const particlesCount = 700;
    const posArray = new Float32Array(particlesCount * 3);

    for(let i = 0; i < particlesCount * 3; i++) {
        posArray[i] = (Math.random() - 0.5) * 50;
    }

    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    const particlesMaterial = new THREE.PointsMaterial({
        size: 0.05,
        color: 0xffffff,
        transparent: true,
        opacity: 0.8
    });
    const particlesMesh = new THREE.Points(particlesGeometry, particlesMaterial);
    scene.add(particlesMesh);

    camera.position.z = 20;

    // Animation loop
    function animate() {
        requestAnimationFrame(animate);

        sphere.rotation.x += 0.001;
        sphere.rotation.y += 0.002;

        core.rotation.x -= 0.002;
        core.rotation.y -= 0.001;

        particlesMesh.rotation.y += 0.0005;

        // Mouse interaction (gentle parallax)
        // ... (can add later if needed)

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
