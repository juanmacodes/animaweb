<?php
/**
 * Template Name: Metaverse Entry Point V11 (Nexus Ultimate: Final Form)
 * Description: La versión definitiva con IA Real, Push y el nuevo diseño de tarjetas HUD futuristas.
 */

get_header();

$is_logged_in = is_user_logged_in();
$cta_text = $is_logged_in ? 'INICIAR INMERSIÓN [DASHBOARD]' : 'PROTOCOLO DE ACCESO [REGISTRO]';
$cta_url = $is_logged_in ? home_url('/mi-cuenta/?view=dashboard') : home_url('/login/');

// URLs de Assets (REEMPLAZA CON TUS RECURSOS FINALES DE UNREAL)
$hero_video = "https://assets.mixkit.co/videos/preview/mixkit-futuristic-city-interface-994-large.mp4"; 
$img_live   = "https://picsum.photos/seed/livevr3/800/600";  
$img_world  = "https://picsum.photos/seed/worldvr3/800/600"; 
$img_academy= "https://picsum.photos/seed/academy3/800/600"; 
$img_ai     = "https://picsum.photos/seed/ailab3/800/600";   
?>

<div id="primary" class="content-area anima-home-v11">
    <main id="main" class="site-main">

        <section id="hero-core" class="hero-fullscreen">
            <div class="video-bg-wrapper">
                <video autoplay muted loop playsinline class="video-bg">
                    <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
                </video>
                <div class="overlay-scanline"></div>
                <div class="overlay-vignette"></div>
                <div class="overlay-particles">
                    <div class="p-light p1"></div><div class="p-light p2"></div><div class="p-light p3"></div>
                </div>
            </div>

            <div class="hero-content-layer text-center">
                <div class="system-status">
                    <span class="status-led"></span> SISTEMA ONLINE // NODO CENTRAL V11.0
                </div>
                <h1 class="hero-main-title cyber-glitch" data-text="BIENVENIDO AL NÚCLEO">BIENVENIDO AL NÚCLEO</h1>
                <p class="hero-subtitle">Tu realidad termina aquí. La simulación comienza.</p>
                <div class="hero-divider"></div>
            </div>
            
            <a href="#ai-oracle-section" class="scroll-indicator">
                <span class="scroll-text">INICIAR DESCENSO</span>
                <svg class="scroll-icon" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><polyline points="7 13 12 18 17 13"></polyline><polyline points="7 6 12 11 17 6"></polyline></svg>
            </a>
        </section>

        <section id="ai-oracle-section" class="section-oracle">
            <div class="oracle-wrapper text-center">
                <span class="section-badge">INTERFAZ GPT-NEURAL</span>
                <h2 class="section-heading">ORÁCULO NEURONAL VIVO</h2>
                
                <div class="ai-vortex-visualizer">
                    <div class="vortex-ring outer-ring"></div>
                    <div class="vortex-ring inner-ring"></div>
                    <div class="vortex-core">
                        <div class="data-stream-texture"></div>
                    </div>
                </div>

                <div class="crt-console-output">
                    <div class="console-screen">
                        <span id="oracle-cursor_v11">█</span> <span id="oracle-text_v11" class="console-text">ESPERANDO INPUT... CONECTA CON LA IA CENTRAL.</span>
                    </div>
                </div>

                <button id="activate-oracle-btn_v11" class="cyber-button outline-cyan" style="margin-top: 40px;">
                    <span class="btn-text">CONSULTAR ALGORITMO (ONLINE)</span>
                    <span class="btn-glitch"></span>
                </button>
            </div>
        </section>


        <section id="the-four-sectors" class="section-pillars">
            <div class="section-header text-center">
                <span class="section-badge">ARQUITECTURA DEL SISTEMA</span>
                <h2 class="section-heading">CUATRO PILARES. UNA AGENCIA.</h2>
            </div>

            <div class="pillars-grid-container">
                
                <a href="#" class="pillar-card">
                    <div class="card-bg-layer" style="background-image: url('<?php echo esc_url($img_live); ?>');"></div>
                    <div class="card-glass-layer">
                        <div class="card-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><path d="M12 11V7"></path><path d="M9 8l3 3 3-3"></path></svg>
                        </div>
                        <h3>ANIMA LIVE <span class="mini-tag">CREACIÓN</span></h3>
                        <p class="card-description">El taller de dioses. Diseña tu avatar hiperrealista con captura facial en tiempo real. Tu rostro, tu alma digital.</p>
                        <div class="card-footer-action">EJECUTAR PROTOCOLO ></div>
                    </div>
                </a>

                <a href="#" class="pillar-card">
                     <div class="card-bg-layer" style="background-image: url('<?php echo esc_url($img_world); ?>');"></div>
                    <div class="card-glass-layer">
                        <div class="card-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </div>
                        <h3>ANIMA WORLD <span class="mini-tag">METAVERSO</span></h3>
                        <p class="card-description">La red social encarnada. Salas inmersivas, eventos en vivo y el Parque Central. Conecta en el espacio 3D.</p>
                        <div class="card-footer-action">ENTRAR A LA RED ></div>
                    </div>
                </a>

                <a href="<?php echo home_url('/academy/'); ?>" class="pillar-card">
                     <div class="card-bg-layer" style="background-image: url('<?php echo esc_url($img_academy); ?>');"></div>
                    <div class="card-glass-layer">
                        <div class="card-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2" ry="2"></rect><rect x="8" y="8" width="8" height="8" rx="1" ry="1"></rect><path d="M12 2v6"></path><path d="M12 16v6"></path><path d="M2 12h6"></path><path d="M16 12h6"></path></svg>
                        </div>
                        <h3>XR ACADEMY <span class="mini-tag">ENTRENAMIENTO</span></h3>
                        <p class="card-description">Sube de nivel. Domina Unreal Engine 5, blueprints y animación. Gana XP y desbloquea tu potencial.</p>
                        <div class="card-footer-action">INICIAR CARGA DE DATOS ></div>
                    </div>
                </a>

                <a href="<?php echo home_url('/mi-cuenta/?view=ai-lab'); ?>" class="pillar-card">
                     <div class="card-bg-layer" style="background-image: url('<?php echo esc_url($img_ai); ?>');"></div>
                    <div class="card-glass-layer">
                        <div class="card-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.55 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"></path><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.55 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"></path><path d="M12 12h.01"></path><path d="M12 8h.01"></path><path d="M12 16h.01"></path></svg>
                        </div>
                        <h3>A.I. LAB <span class="mini-tag">INTELIGENCIA</span></h3>
                        <p class="card-description">Tu asistente neuronal. Genera biografías complejas e imágenes conceptuales usando Créditos y DALL-E.</p>
                        <div class="card-footer-action">CONECTAR CEREBRO ></div>
                    </div>
                </a>
            </div>
        </section>

        <section id="final-protocol" class="section-final-cta text-center">
            <div class="final-bg-grid"></div>
            <h2 class="final-heading">¿ESTÁS LISTO PARA LA SINCRONIZACIÓN?</h2>
            
            <div class="final-actions-container">
                <a href="<?php echo esc_url($cta_url); ?>" class="cyber-button solid-pink main-cta">
                    <span class="btn-text"><?php echo esc_html($cta_text); ?></span>
                    <span class="btn-glitch"></span>
                </a>
                
                <button onclick="animaSubscribeToPush()" class="cyber-button outline-cyan secondary-cta">
                    <span class="btn-text"><span class="dashicons dashicons-rss"></span> ACTIVAR ALERTA NEURONAL</span>
                </button>
            </div>
        </section>

    </main>
</div>

<style>
    /* IMPORTACIÓN DE FUENTES Y VARIABLES GLOBALES */
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Orbitron:wght@700;800;900&family=Share+Tech+Mono&display=swap');
    :root {
        --neon-cyan: #00F0FF; --neon-pink: #BC13FE; --neon-green: #00FF94; --neon-gold: #FFD700;
        --bg-void: #020204; --bg-panel: #09090c;
        --glass-bg-pro: rgba(15, 15, 22, 0.65); 
        --glass-border-pro: rgba(255, 255, 255, 0.08);
        /* Nuevas Variables para Tarjetas HUD */
        --energy-pink: #ff0055; --energy-cyan: #00f7ff; --energy-green: #00ff33; --energy-gold: #ffcc00;
        --card-bg-dark: #0a0a0f;
        --font-tech: 'Orbitron', sans-serif; --font-ui: 'Rajdhani', sans-serif; --font-mono: 'Share Tech Mono', monospace;
    }
    
    /* Reset y Base */
    .anima-home-v11 { background-color: var(--bg-void); color: #e0e0e0; font-family: var(--font-ui); overflow-x: hidden; line-height: 1.6; }
    .text-center { text-align: center; }
    a { text-decoration: none; }

    /* === HERO SECTION (V10 POLISHED) === */
    .hero-fullscreen { position: relative; height: 92vh; min-height: 700px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .video-bg-wrapper { position: absolute; inset: 0; z-index: 1; }
    .video-bg { width: 100%; height: 100%; object-fit: cover; filter: brightness(0.4) contrast(1.1) saturate(1.1); }
    .overlay-scanline { position: absolute; inset: 0; background: repeating-linear-gradient(0deg, transparent 0px, rgba(0, 0, 0, 0.3) 1px, transparent 3px); pointer-events: none; mix-blend-mode: overlay; z-index: 2; }
    .overlay-vignette { position: absolute; inset: 0; background: radial-gradient(circle at center, transparent 20%, var(--bg-void) 100%); z-index: 3; }
    .overlay-particles { position: absolute; inset: 0; pointer-events: none; z-index: 4; }
    .p-light { position: absolute; width: 3px; height: 3px; border-radius: 50%; opacity: 0; animation: particleFloatUp 12s infinite linear; }
    .p1 { background: var(--neon-cyan); left: 25%; animation-delay: 0s; } .p2 { background: var(--neon-pink); left: 65%; animation-delay: 4s; width: 2px; height: 2px; } .p3 { background: var(--neon-green); left: 45%; animation-delay: 8s; }
    @keyframes particleFloatUp { 0% { transform: translateY(100vh) scale(1); opacity: 0; } 20% { opacity: 0.7; } 100% { transform: translateY(-10vh) scale(0.5); opacity: 0; } }
    .hero-content-layer { position: relative; z-index: 10; padding: 20px; max-width: 900px; margin: 0 auto; }
    .system-status { font-family: var(--font-mono); color: var(--neon-green); font-size: 0.85rem; letter-spacing: 2px; margin-bottom: 25px; display: flex; align-items: center; justify-content: center; gap: 10px; }
    .status-led { width: 8px; height: 8px; background: var(--neon-green); border-radius: 50%; box-shadow: 0 0 10px var(--neon-green); animation: blinkStatus 2s infinite; } @keyframes blinkStatus { 50% { opacity: 0.4; } }
    .hero-main-title { font-family: var(--font-tech); font-size: 5.5rem; line-height: 1.05; margin: 0; letter-spacing: 4px; color: #fff; text-transform: uppercase; position: relative; }
    .hero-subtitle { font-size: 1.7rem; letter-spacing: 3px; color: #ccc; margin-top: 30px; font-weight: 600; text-transform: uppercase; }
    .hero-divider { width: 120px; height: 3px; background: linear-gradient(90deg, transparent, var(--neon-cyan), var(--neon-pink), transparent); margin: 45px auto 0; opacity: 0.7; }
    .cyber-glitch { position: relative; text-shadow: 0 0 30px rgba(0, 240, 255, 0.4); }
    .cyber-glitch::before, .cyber-glitch::after { content: attr(data-text); position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.8; }
    .cyber-glitch::before { color: var(--neon-pink); z-index: -1; clip-path: inset(25% 0 70% 0); animation: glitch-shift-1 3s infinite steps(1) alternate; transform: translate(-4px, 0); }
    .cyber-glitch::after { color: var(--neon-cyan); z-index: -2; clip-path: inset(65% 0 30% 0); animation: glitch-shift-2 2.5s infinite steps(1) alternate-reverse; transform: translate(4px, 0); }
    @keyframes glitch-shift-1 { 0%, 100% { clip-path: inset(25% 0 70% 0); transform: translate(0); } 10% { clip-path: inset(10% 0 85% 0); transform: translate(-4px, 2px); } 30% { clip-path: inset(50% 0 45% 0); transform: translate(4px, -2px); } }
    @keyframes glitch-shift-2 { 0%, 100% { clip-path: inset(65% 0 30% 0); transform: translate(0); } 15% { clip-path: inset(80% 0 15% 0); transform: translate(4px, 2px); } 40% { clip-path: inset(35% 0 60% 0); transform: translate(-4px, -2px); } }
    .scroll-indicator { position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); color: var(--neon-cyan); display: flex; flex-direction: column; align-items: center; z-index: 10; opacity: 0.6; transition: 0.3s; }
    .scroll-indicator:hover { opacity: 1; color: #fff; }
    .scroll-text { font-size: 0.75rem; letter-spacing: 3px; font-weight: 700; margin-bottom: 12px; font-family: var(--font-tech); }
    .scroll-icon { width: 24px; height: 24px; animation: rubberBandArrow 2s infinite; } @keyframes rubberBandArrow { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(8px); } }

    /* === SECCIÓN 2: ORÁCULO NEURONAL (V10) === */
    .section-oracle { padding: 130px 20px; background: #050507; position: relative; overflow: hidden; border-top: 1px solid #111; border-bottom: 1px solid #111; box-shadow: inset 0 0 100px #000; }
    .oracle-wrapper { max-width: 850px; margin: 0 auto; position: relative; z-index: 10; }
    .section-badge { color: var(--neon-cyan); border: 1px solid var(--neon-cyan); padding: 8px 20px; font-weight: 700; letter-spacing: 4px; font-size: 0.8rem; text-transform: uppercase; display: inline-block; margin-bottom: 25px; }
    .section-heading { font-family: var(--font-tech); font-size: 3.5rem; margin: 0 0 70px; color: #fff; text-transform: uppercase; letter-spacing: 3px; }
    .ai-vortex-visualizer { position: relative; width: 220px; height: 220px; margin: 0 auto 50px; display: flex; justify-content: center; align-items: center; }
    .vortex-ring { position: absolute; border-radius: 50%; border: 2px solid transparent; box-shadow: 0 0 20px rgba(0,240,255,0.2); }
    .outer-ring { width: 100%; height: 100%; border-top-color: var(--neon-cyan); border-bottom-color: var(--neon-pink); animation: spinVortex 5s linear infinite; }
    .inner-ring { width: 75%; height: 75%; border-left-color: var(--neon-green); border-right-color: var(--neon-gold); animation: spinVortex 7s linear infinite reverse; }
    .vortex-core { width: 55%; height: 55%; background: radial-gradient(circle, rgba(0,240,255,0.8) 0%, rgba(188,19,254,0.4) 60%, transparent 100%); border-radius: 50%; position: relative; overflow: hidden; box-shadow: 0 0 50px var(--neon-cyan); transition: 0.5s; }
    .data-stream-texture { position: absolute; inset: -100%; background: repeating-conic-gradient(from 0deg, transparent 0deg, rgba(255,255,255,0.3) 15deg, transparent 30deg); animation: rotateData 4s linear infinite; mix-blend-mode: overlay; }
    .ai-vortex-visualizer.processing .outer-ring { animation-duration: 1.5s; border-color: #fff; box-shadow: 0 0 30px var(--neon-cyan); }
    .ai-vortex-visualizer.processing .inner-ring { animation-duration: 2s; border-color: #fff; }
    .ai-vortex-visualizer.processing .vortex-core { box-shadow: 0 0 80px #fff, 0 0 120px var(--neon-pink); filter: brightness(1.8); }
    .crt-console-output { background: #0a0a0d; padding: 8px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5), inset 0 0 10px rgba(0,0,0,0.8); border: 1px solid #222; max-width: 700px; margin: 0 auto; }
    .console-screen { background: rgba(10, 14, 20, 0.8); border: 1px solid var(--glass-border-pro); border-radius: 8px; padding: 25px; min-height: 110px; text-align: left; position: relative; overflow: hidden; display: flex; align-items: flex-start; }
    .console-screen::after { content: ''; position: absolute; inset: 0; background: linear-gradient(rgba(255,255,255,0.05) 50%, transparent 50%); background-size: 100% 4px; pointer-events: none; opacity: 0.3; }
    .console-screen::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, transparent 60%, rgba(0,0,0,0.8) 100%); pointer-events: none; }
    #oracle-cursor_v11 { color: var(--neon-green); margin-right: 12px; animation: blinkCursor 0.8s step-end infinite; font-family: var(--font-mono); } @keyframes blinkCursor { 50% { opacity: 0; } }
    .console-text { color: var(--neon-cyan); font-family: var(--font-mono); font-size: 1.15rem; letter-spacing: 1px; line-height: 1.5; text-shadow: 0 0 8px var(--neon-cyan); }
    @keyframes spinVortex { 100% { transform: rotate(360deg); } } @keyframes rotateData { 100% { transform: rotate(360deg); } }

    /* === SECCIÓN 3: PILARES (NUEVO DISEÑO FUTURISTA HUD) === */
    .section-pillars { padding: 140px 5%; background: linear-gradient(180deg, var(--bg-void) 0%, var(--bg-panel) 100%); position: relative; z-index: 5; }
    .pillars-grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 40px; max-width: 1400px; margin: 0 auto; }
    
    /* Asignar colores de energía por posición */
    .pillar-card:nth-child(1) { --theme-color: var(--energy-pink); }
    .pillar-card:nth-child(2) { --theme-color: var(--energy-cyan); }
    .pillar-card:nth-child(3) { --theme-color: var(--energy-green); }
    .pillar-card:nth-child(4) { --theme-color: var(--energy-gold); }

    /* Contenedor Principal de la Tarjeta */
    .pillar-card {
        position: relative; height: 520px; border-radius: 4px; overflow: hidden; display: flex; align-items: flex-end; text-decoration: none;
        border: 2px solid var(--theme-color); /* Borde de Energía */
        box-shadow: 0 0 15px rgba(0,0,0,0.8), 0 0 20px var(--theme-color), inset 0 0 30px rgba(0,0,0,0.8); /* Resplandor */
        background: var(--card-bg-dark); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 1;
    }
    /* Esquinas técnicas "cortadas" */
    .pillar-card::before, .pillar-card::after { content: ''; position: absolute; width: 20px; height: 20px; border: 2px solid var(--theme-color); z-index: 5; }
    .pillar-card::before { top: -2px; left: -2px; border-right: none; border-bottom: none; }
    .pillar-card::after { bottom: -2px; right: -2px; border-left: none; border-top: none; }

    /* Capa de Imagen con Scanline */
    .card-bg-layer { position: absolute; top: 0; left: 0; width: 100%; height: 60%; background-size: cover; background-position: center; filter: grayscale(80%) contrast(1.2) brightness(0.7); transition: all 0.6s ease; }
    .card-bg-layer::after { content: ''; position: absolute; inset: 0; background: repeating-linear-gradient(to bottom, transparent 0px, transparent 2px, rgba(0,0,0,0.5) 3px); pointer-events: none; mix-blend-mode: overlay; }

    /* Capa de Contenido (Cristal HUD) */
    .card-glass-layer {
        position: relative; z-index: 10; width: 100%; padding: 30px;
        background: rgba(10, 14, 20, 0.9); /* Fondo oscuro semi-transparente */
        border-top: 3px solid var(--theme-color); box-shadow: 0 -10px 20px var(--theme-color); /* Luz superior */
        /* Textura de circuito */
        background-image: linear-gradient(rgba(var(--theme-color), 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(var(--theme-color), 0.05) 1px, transparent 1px);
        background-size: 20px 20px;
        transform: translateY(0); transition: all 0.4s ease;
    }

    /* Tipografía y Elementos Internos */
    .card-icon-box svg { stroke: var(--theme-color); filter: drop-shadow(0 0 5px var(--theme-color)); width: 45px; height: 45px; margin-bottom: 20px; }
    .pillar-card h3 { font-family: 'Orbitron', sans-serif; font-size: 1.5rem; margin-bottom: 15px; color: #fff; text-transform: uppercase; letter-spacing: 2px; text-shadow: 0 0 10px var(--theme-color); display: flex; justify-content: space-between; align-items: center; }
    .mini-tag { font-size: 0.7rem; padding: 4px 10px; background: rgba(0,0,0,0.6); border: 1px solid var(--theme-color); color: var(--theme-color); border-radius: 2px; box-shadow: 0 0 8px var(--theme-color) inset; text-transform: uppercase; letter-spacing: 1px; font-family: var(--font-ui); }
    .card-description { color: #aaa; font-family: 'Rajdhani', sans-serif; font-size: 1rem; line-height: 1.5; margin-bottom: 25px; opacity: 0.8; }
    .card-footer-action { font-family: 'Orbitron', sans-serif; font-weight: 700; letter-spacing: 2px; font-size: 0.8rem; color: var(--theme-color); text-transform: uppercase; padding: 10px 0; border-bottom: 2px solid var(--theme-color); background: linear-gradient(90deg, rgba(var(--theme-color), 0.2) 0%, transparent 100%); width: 100%; display: block; }

    /* HOVER EFFECTS (SOBRECARGA DE ENERGÍA) */
    .pillar-card:hover { transform: translateY(-15px) scale(1.03); box-shadow: 0 20px 50px rgba(0,0,0,0.8), 0 0 40px var(--theme-color), inset 0 0 50px var(--theme-color); z-index: 10; }
    .pillar-card:hover .card-bg-layer { filter: grayscale(0%) contrast(1.3) brightness(1.1); transform: scale(1.1); height: 65%; }
    .pillar-card:hover .card-glass-layer { background: rgba(10, 14, 20, 0.95); border-top-width: 4px; box-shadow: 0 -15px 30px var(--theme-color); }
    .pillar-card:hover .card-icon-box svg { transform: scale(1.2) rotate(5deg); filter: drop-shadow(0 0 15px var(--theme-color)); }
    .pillar-card:hover .mini-tag { background: var(--theme-color); color: #000; box-shadow: 0 0 15px var(--theme-color); }
    .pillar-card:hover .card-footer-action { letter-spacing: 4px; background: linear-gradient(90deg, rgba(var(--theme-color), 0.6) 0%, transparent 100%); }

    /* === SECCIÓN 4: FINAL CTA + NOTIFICACIONES (V10) === */
    .section-final-cta { padding: 160px 20px; background: var(--bg-panel); position: relative; overflow: hidden; border-top: 1px solid #111; }
    .final-bg-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px); background-size: 50px 50px; opacity: 0.4; pointer-events: none; }
    .final-heading { font-family: var(--font-tech); font-size: 3.2rem; margin-bottom: 70px; color: #fff; position: relative; z-index: 5; letter-spacing: 3px; text-transform: uppercase; }
    .final-actions-container { display: flex; justify-content: center; align-items: center; gap: 30px; position: relative; z-index: 5; flex-wrap: wrap; }
    .cyber-button { position: relative; padding: 24px 50px; font-family: var(--font-tech); font-size: 1.1rem; text-transform: uppercase; letter-spacing: 3px; color: #fff; background: #000; border: none; cursor: pointer; transition: 0.4s cubic-bezier(0.23, 1, 0.32, 1); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; clip-path: polygon(15px 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%, 0 15px); }
    .btn-text { position: relative; z-index: 2; display: flex; align-items: center; gap: 10px; }
    .btn-glitch { position: absolute; inset: 0; background: #fff; z-index: 1; opacity: 0; transition: 0.3s; mix-blend-mode: overlay; }
    .solid-pink { background: linear-gradient(135deg, var(--neon-pink), #9c12d4); box-shadow: 0 10px 30px rgba(188, 19, 254, 0.3); }
    .solid-pink:hover { transform: translateY(-3px); box-shadow: 0 20px 50px rgba(188, 19, 254, 0.5); }
    .solid-pink:hover .btn-glitch { opacity: 0.2; animation: glitchBtn 0.3s linear infinite; }
    .outline-cyan { background: transparent; box-shadow: inset 0 0 0 2px var(--neon-cyan); color: var(--neon-cyan); padding: 22px 40px; font-size: 1rem; }
    .outline-cyan:hover { background: rgba(0, 240, 255, 0.05); box-shadow: inset 0 0 0 2px var(--neon-cyan), 0 0 30px var(--neon-cyan); color: #fff; transform: translateY(-2px); }
    .outline-cyan .dashicons { font-size: 1.3rem; }
    @keyframes glitchBtn { 0% { clip-path: inset(0 0 0 0); } 20% { clip-path: inset(10% 0 40% 0); transform: translate(-2px, 2px); } 40% { clip-path: inset(40% 0 10% 0); transform: translate(2px, -2px); } 100% { clip-path: inset(0 0 0 0); } }

    /* RESPONSIVE V11 */
    @media (max-width: 992px) { .hero-main-title { font-size: 4rem; } }
    @media (max-width: 768px) { 
        .hero-main-title { font-size: 3.2rem; letter-spacing: 2px; } .hero-subtitle { font-size: 1.2rem; }
        .section-oracle, .section-pillars, .section-final-cta { padding: 80px 5%; }
        .pillar-card { height: auto; min-height: 480px; } /* Ajuste de altura en móvil */
        .final-actions-container { flex-direction: column; gap: 20px; width: 100%; }
        .cyber-button { width: 100%; padding: 20px; font-size: 1rem; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- LÓGICA DEL ORÁCULO IA (REAL AJAX) ---
    const oracleBtn = document.getElementById('activate-oracle-btn_v11');
    const oracleText = document.getElementById('oracle-text_v11');
    const oracleCursor = document.getElementById('oracle-cursor_v11');
    const vortexVisualizer = document.querySelector('.ai-vortex-visualizer');
    const ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
    let isProcessing = false;

    function typeWriter(text, i = 0) {
        if (i === 0) oracleText.innerHTML = "";
        if (i < text.length) {
            oracleText.innerHTML += text.charAt(i);
            i++;
            setTimeout(() => typeWriter(text, i), Math.random() * 30 + 20);
        } else {
            resetOracleState();
        }
    }

    function resetOracleState() {
        vortexVisualizer.classList.remove('processing');
        oracleBtn.querySelector('.btn-text').innerText = "CONSULTAR ALGORITMO NUEVAMENTE";
        oracleBtn.disabled = false;
        oracleCursor.style.display = 'inline';
        isProcessing = false;
    }

    oracleBtn.addEventListener('click', async function() {
        if (isProcessing) return;
        isProcessing = true;
        
        oracleBtn.disabled = true;
        oracleBtn.querySelector('.btn-text').innerText = "ESTABLECIENDO ENLACE CUÁNTICO...";
        vortexVisualizer.classList.add('processing');
        oracleText.innerText = "INICIANDO SECUENCIA DE COMUNICACIÓN CON EL NÚCLEO...";
        oracleCursor.style.display = 'none';

        try {
            const fd = new FormData();
            fd.append('action', 'anima_oracle_consult');
            const response = await fetch(ajaxUrl, { method: 'POST', body: fd });
            const data = await response.json();

            if (data.success) {
                typeWriter(data.data.toUpperCase());
            } else {
                oracleText.innerText = "ERROR DEL SISTEMA: " + data.data;
                resetOracleState();
            }
        } catch (error) {
            oracleText.innerText = "FALLO CRÍTICO DE RED. ENLACE INTERRUMPIDO.";
            resetOracleState();
        }
    });
});

// --- FUNCIÓN GLOBAL PARA NOTIFICACIONES PUSH ---
function animaSubscribeToPush() {
    if (typeof OneSignal !== 'undefined') {
        OneSignal.showSlidedownPrompt().then(() => {
            console.log("Prompt de suscripción mostrado.");
        });
    } else {
        alert("El motor de notificaciones se está inicializando. Inténtalo en unos segundos.");
    }
}
</script>

<?php get_footer(); ?>