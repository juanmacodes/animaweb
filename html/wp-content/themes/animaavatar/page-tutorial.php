<?php
/**
 * Template Name: Anima Tutorial
 *
 * @package Anima_Avatar_Agency
 */

get_header(); ?>

<div class="anima-tutorial-container">

    <!-- HERO SECTION -->
    <section class="tutorial-hero">
        <div class="hero-content">
            <h1 class="glitch-text" data-text="MANUAL DE OPERACIONES">MANUAL DE OPERACIONES</h1>
            <p class="hero-subtitle">Guía de supervivencia para el Agente del Metaverso</p>
        </div>
        <div class="hero-overlay"></div>
    </section>

    <!-- NAVIGATION -->
    <nav class="tutorial-nav">
        <a href="#nexus" class="tut-nav-item active">NEXUS</a>
        <a href="#tablon" class="tut-nav-item">TABLÓN</a>
        <a href="#perfil" class="tut-nav-item">PERFIL</a>
        <a href="#misiones" class="tut-nav-item">MISIONES</a>
    </nav>

    <div class="tutorial-content-wrapper">

        <!-- SECTION 1: NEXUS -->
        <section id="nexus" class="tutorial-section">
            <div class="section-header">
                <span class="section-number">01</span>
                <h2>EL NEXUS</h2>
            </div>
            <div class="cyberpunk-box">
                <div class="tut-grid">
                    <div class="tut-text">
                        <h3><span class="dashicons dashicons-networking"></span> Conexiones Neuronales</h3>
                        <p>El <strong>Nexus</strong> es tu directorio de agentes activos. Aquí puedes encontrar a otros
                            usuarios, ver sus rangos y establecer conexiones.</p>
                        <ul class="tut-list">
                            <li><strong>Filtrar:</strong> Usa los botones para ver "Todos" o solo tus "Amigos".</li>
                            <li><strong>Buscar:</strong> Localiza agentes por nombre clave.</li>
                            <li><strong>Conectar:</strong> Envía solicitudes de enlace neuronal para expandir tu red.
                            </li>
                        </ul>
                        <a href="<?php echo home_url('/comunidad/'); ?>" class="anima-btn small">ACCEDER AL NEXUS</a>
                    </div>
                    <div class="tut-visual">
                        <div class="mock-card">
                            <div class="mock-avatar"></div>
                            <div class="mock-lines"></div>
                            <div class="mock-btn">CONECTAR</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: TABLON -->
        <section id="tablon" class="tutorial-section">
            <div class="section-header">
                <span class="section-number">02</span>
                <h2>EL TABLÓN</h2>
            </div>
            <div class="cyberpunk-box">
                <div class="tut-grid reverse">
                    <div class="tut-text">
                        <h3><span class="dashicons dashicons-format-status"></span> Transmisiones</h3>
                        <p>El <strong>Tablón</strong> es el flujo de comunicación central. Comparte ideas, dudas o
                            eventos con la comunidad.</p>
                        <ul class="tut-list">
                            <li><strong>Categorías:</strong> Clasifica tu mensaje como General, Duda, Idea o Evento.
                            </li>
                            <li><strong>Interacción:</strong> Dale "Like" (<span
                                    class="dashicons dashicons-heart"></span>) a lo que te guste y responde a otros
                                agentes.</li>
                            <li><strong>Eventos:</strong> Los mensajes de tipo "Evento" muestran fecha y hora
                                destacadas.</li>
                        </ul>
                        <a href="<?php echo home_url('/comunidad/'); ?>" class="anima-btn small">IR AL TABLÓN</a>
                    </div>
                    <div class="tut-visual">
                        <div class="mock-feed">
                            <div class="mock-post"></div>
                            <div class="mock-post"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 3: PERFIL -->
        <section id="perfil" class="tutorial-section">
            <div class="section-header">
                <span class="section-number">03</span>
                <h2>TU PERFIL</h2>
            </div>
            <div class="cyberpunk-box">
                <div class="tut-grid">
                    <div class="tut-text">
                        <h3><span class="dashicons dashicons-id"></span> Identidad Digital</h3>
                        <p>Tu perfil es tu carta de presentación. Gestiona tu avatar y revisa tu progreso.</p>
                        <ul class="tut-list">
                            <li><strong>Créditos (Karma):</strong> Gana créditos completando misiones y participando.
                            </li>
                            <li><strong>Avatar:</strong> Sube una imagen personalizada para destacar en el Nexus.</li>
                            <li><strong>Rango:</strong> Sube de nivel acumulando experiencia.</li>
                        </ul>
                        <a href="<?php echo home_url('/perfil/'); ?>" class="anima-btn small">VER MI PERFIL</a>
                    </div>
                    <div class="tut-visual">
                        <div class="mock-profile">
                            <div class="mock-circle"></div>
                            <div class="mock-stats"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

</div>

<style>
    /* TUTORIAL STYLES */
    .anima-tutorial-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px 60px;
        color: #eee;
    }

    /* HERO */
    .tutorial-hero {
        position: relative;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin-bottom: 40px;
        overflow: hidden;
        border-bottom: 1px solid var(--neon-blue);
    }

    .tutorial-hero h1 {
        font-size: 3.5rem;
        margin: 0;
        color: #fff;
        text-shadow: 0 0 10px var(--neon-blue);
        font-family: 'Orbitron', sans-serif;
        /* Assuming theme font */
    }

    .hero-subtitle {
        font-size: 1.2rem;
        color: var(--neon-pink);
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    /* NAV */
    .tutorial-nav {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 60px;
        position: sticky;
        top: 80px;
        /* Adjust based on header height */
        z-index: 100;
        background: rgba(10, 10, 15, 0.9);
        padding: 15px;
        border-radius: 30px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .tut-nav-item {
        color: #888;
        text-decoration: none;
        font-weight: bold;
        padding: 8px 20px;
        border-radius: 20px;
        transition: all 0.3s;
        text-transform: uppercase;
        font-size: 0.9rem;
    }

    .tut-nav-item:hover,
    .tut-nav-item.active {
        color: #fff;
        background: var(--neon-blue);
        box-shadow: 0 0 15px var(--neon-blue);
    }

    /* SECTIONS */
    .tutorial-section {
        margin-bottom: 100px;
        scroll-margin-top: 150px;
    }

    .section-header {
        display: flex;
        align-items: baseline;
        gap: 15px;
        margin-bottom: 20px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 10px;
    }

    .section-number {
        font-size: 4rem;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.05);
        line-height: 1;
    }

    .section-header h2 {
        font-size: 2rem;
        color: var(--neon-blue);
        margin: 0;
        text-transform: uppercase;
    }

    /* GRID & CONTENT */
    .tut-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: center;
        padding: 20px;
    }

    .tut-grid.reverse {
        direction: rtl;
    }

    .tut-grid.reverse .tut-text {
        direction: ltr;
    }

    .tut-text h3 {
        color: #fff;
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tut-list {
        list-style: none;
        padding: 0;
        margin: 20px 0;
    }

    .tut-list li {
        margin-bottom: 15px;
        padding-left: 20px;
        position: relative;
        color: #ccc;
    }

    .tut-list li:before {
        content: '>';
        position: absolute;
        left: 0;
        color: var(--neon-pink);
        font-weight: bold;
    }

    /* VISUAL MOCKS (CSS ONLY) */
    .tut-visual {
        display: flex;
        justify-content: center;
    }

    .mock-card,
    .mock-feed,
    .mock-profile {
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid #333;
        border-radius: 10px;
        padding: 20px;
        width: 100%;
        max-width: 300px;
        height: 200px;
        position: relative;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    }

    /* Nexus Mock */
    .mock-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .mock-avatar {
        width: 60px;
        height: 60px;
        background: #333;
        border-radius: 50%;
        border: 2px solid var(--neon-green);
    }

    .mock-lines {
        width: 80%;
        height: 10px;
        background: #222;
        border-radius: 5px;
    }

    .mock-btn {
        padding: 5px 15px;
        background: var(--neon-blue);
        color: #000;
        font-size: 0.7rem;
        font-weight: bold;
        border-radius: 4px;
    }

    /* Feed Mock */
    .mock-feed {
        display: flex;
        flex-direction: column;
        gap: 10px;
        justify-content: center;
    }

    .mock-post {
        height: 60px;
        background: rgba(255, 255, 255, 0.05);
        border-left: 3px solid var(--neon-pink);
        border-radius: 4px;
    }

    /* Profile Mock */
    .mock-profile {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .mock-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid var(--neon-purple);
    }

    .mock-stats {
        flex: 1;
        height: 60px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
    }

    @media (max-width: 768px) {
        .tut-grid {
            grid-template-columns: 1fr;
            direction: ltr !important;
        }

        .tutorial-hero h1 {
            font-size: 2rem;
        }

        .tutorial-nav {
            overflow-x: auto;
            justify-content: flex-start;
        }
    }
</style>

<script>
    // Simple scroll spy for nav
    document.addEventListener('DOMContentLoaded', () => {
        const sections = document.querySelectorAll('.tutorial-section');
        const navItems = document.querySelectorAll('.tut-nav-item');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= (sectionTop - 200)) {
                    current = section.getAttribute('id');
                }
            });

            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href').includes(current)) {
                    item.classList.add('active');
                }
            });
        });
    });
</script>

<?php get_footer(); ?>