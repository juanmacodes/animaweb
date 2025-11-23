<?php
/**
 * Menú Móvil FINAL - Estilo Cyberpunk
 * CORRECCIÓN: URL de Comunidad forzada a /comunidad-2/
 */

// 1. DEFINICIÓN DE ENLACES (Aquí controlas las URLs exactas)
$home_url      = home_url('/');
$academy_url   = home_url('/academy/');
$nexus_url     = home_url('/nexus/');
$lab_url       = home_url('/anima-lab/');

// --- LA URL CORREGIDA ---
$comunidad_url = home_url('/comunidad-2/'); 

// Lógica del botón Mi Perfil
$profile_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/mi-cuenta/');
$login_url   = home_url('/login/');
$cta_url     = is_user_logged_in() ? $profile_url : $login_url;
$cta_text    = is_user_logged_in() ? 'MI ÁREA' : 'ACCESO AGENTE';
?>

<style>
    /* Activador (Solo móvil) */
    .mobile-trigger-cyber {
        display: none;
        margin-left: auto;
    }

    @media (max-width: 992px) {
        .mobile-trigger-cyber { display: block; }
        .site-header__actions .header-btn { display: none !important; }
    }

    /* Botón Hamburguesa */
    .cyber-burger-btn {
        background: rgba(0, 240, 255, 0.1);
        border: 1px solid #00F0FF;
        color: #00F0FF;
        width: 40px;
        height: 40px;
        font-size: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.3s ease;
        box-shadow: 0 0 5px rgba(0, 240, 255, 0.2);
    }
    
    .cyber-burger-btn:hover {
        background: #00F0FF;
        color: #000;
        box-shadow: 0 0 15px #00F0FF;
    }

    /* CAJA DEL MENÚ (DROPDOWN) */
    #cyber-dropdown-menu {
        display: none; 
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: rgba(5, 5, 5, 0.98);
        border-bottom: 2px solid #BC13FE;
        box-shadow: 0 20px 40px rgba(0,0,0,0.9);
        z-index: 9999;
        backdrop-filter: blur(10px);
    }

    /* Lista del menú */
    .cyber-menu-list {
        list-style: none;
        padding: 10px 0;
        margin: 0;
        text-align: center;
    }

    .cyber-menu-list li {
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .cyber-menu-list li a {
        color: #E0E0E0;
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.4rem;
        font-weight: 600;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: block;
        padding: 18px 0;
        transition: all 0.3s;
    }
    
    .cyber-menu-list li a:hover {
        color: #00F0FF;
        background: rgba(0, 240, 255, 0.05);
        text-shadow: 0 0 8px rgba(0, 240, 255, 0.6);
        padding-left: 10px;
    }

    /* Botón destacado */
    .cyber-cta-container {
        padding: 25px 0 35px 0;
        text-align: center;
        background: linear-gradient(180deg, transparent, rgba(188, 19, 254, 0.05));
    }
    
    .cyber-cta-link {
        display: inline-block;
        padding: 12px 35px;
        background: transparent;
        border: 1px solid #BC13FE;
        color: #BC13FE;
        text-decoration: none;
        font-weight: 700;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 2px;
        transition: all 0.3s ease;
    }
    
    .cyber-cta-link:hover {
        background: #BC13FE;
        color: #fff;
        box-shadow: 0 0 20px rgba(188, 19, 254, 0.5);
        transform: translateY(-2px);
    }
</style>

<div class="mobile-trigger-cyber">
    <button id="cyber-toggle-btn" class="cyber-burger-btn" onclick="toggleCyberMenu()">
        ☰
    </button>
</div>

<script>
    function toggleCyberMenu() {
        var menu = document.getElementById('cyber-dropdown-menu');
        var btn = document.getElementById('cyber-toggle-btn');
        
        if (menu.style.display === 'block') {
            menu.style.display = 'none'; // CERRAR
            btn.innerHTML = '☰'; 
            btn.style.borderColor = '#00F0FF';
            btn.style.color = '#00F0FF';
        } else {
            menu.style.display = 'block'; // ABRIR
            btn.innerHTML = '✕'; 
            btn.style.borderColor = '#BC13FE';
            btn.style.color = '#BC13FE';
        }
    }
</script>

<div id="cyber-dropdown-menu">
    <ul class="cyber-menu-list">
        <li><a href="<?php echo esc_url($home_url); ?>">Inicio</a></li>
        
        <li><a href="<?php echo esc_url($academy_url); ?>">Academy</a></li>
        
        <li><a href="<?php echo esc_url($comunidad_url); ?>">Comunidad</a></li>
        <li><a href="<?php echo esc_url($nexus_url); ?>">Nexus</a></li>
        <li><a href="<?php echo esc_url($lab_url); ?>">Anima Lab</a></li>
    </ul>

    <div class="cyber-cta-container">
        <a href="<?php echo esc_url($cta_url); ?>" class="cyber-cta-link">
            <?php echo esc_html($cta_text); ?>
        </a>
    </div>
</div>