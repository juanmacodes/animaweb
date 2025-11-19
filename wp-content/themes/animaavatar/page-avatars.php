<?php
/**
 * Template Name: Comunidad de Avatares
 * Description: Galería de usuarios con redes sociales, nivel, sistema de amigos y estatus Pro.
 */
get_header();

$current_uid = get_current_user_id();
$my_friends = (array) get_user_meta($current_uid, 'anima_friends', true);

// Obtener usuarios (Clientes y Suscriptores)
$users = get_users([
    'role__in' => ['customer', 'subscriber', 'administrator'],
    'number'   => 60, // Límite inicial
    'orderby'  => 'registered',
    'order'    => 'DESC'
]);
?>

<section class="section container" style="padding-top:40px; padding-bottom:80px;">
    
    <header class="section__header" style="margin-bottom:50px; text-align:center; max-width:700px; margin-inline:auto;">
        <h1 class="section__title">Base de Datos de Agentes</h1>
        <p class="section__description">
            Conecta con otros creadores del metaverso. Descubre sus perfiles y colabora en nuevas misiones.
        </p>
    </header>

    <div class="anima-avatars-grid">
        <?php 
        if ( ! empty($users) ) :
            foreach($users as $user): 
                // Asegurarnos de que es un objeto WP_User válido
                if ( ! is_object($user) || ! isset($user->ID) ) continue;

                $uid = $user->ID;
                if($uid === $current_uid) continue; // No mostrarse a sí mismo
                
                // 1. Datos de Nivel y Amigos
                $level_info = function_exists('anima_get_user_level_info') ? anima_get_user_level_info($uid) : ['level'=>1];
                $is_friend  = in_array($uid, $my_friends);
                
                // 2. Lógica PRO (Borde Neón): Si tiene cursos comprados es PRO
                $user_courses = function_exists('anima_get_user_courses') ? anima_get_user_courses($uid) : [];
                $is_pro = !empty($user_courses);
                $card_class = $is_pro ? 'anima-avatar-card anima-avatar-card--pro' : 'anima-avatar-card';

                // 3. Redes sociales a mostrar
                $socials = ['instagram','tiktok','twitter','linkedin','artstation','youtube'];
            ?>
            <article class="<?php echo esc_attr($card_class); ?>">
                
                <div class="av-header">
                    <div class="av-photo">
                        <?php echo get_avatar($uid, 96); ?>
                    </div>
                    <div class="av-level-badge">
                        Lvl <?php echo esc_html($level_info['level']); ?>
                    </div>
                </div>
                
                <h3 class="av-name">
                    <?php echo esc_html($user->display_name); ?>
                </h3>
                
                <div class="av-socials">
                    <?php foreach($socials as $net): 
                        $url = get_user_meta($uid, 'anima_social_'.$net, true);
                        if($url): 
                            // Usamos el favicon de Google para obtener el icono de la red social automáticamente
                            $domain = parse_url($url, PHP_URL_HOST);
                    ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" class="social-link" title="<?php echo ucfirst($net); ?>">
                            <img src="https://www.google.com/s2/favicons?domain=<?php echo esc_attr($domain); ?>&sz=32" alt="<?php echo esc_attr($net); ?>">
                        </a>
                    <?php endif; endforeach; ?>
                </div>
                
                <div class="av-actions">
                    <?php if($current_uid): ?>
                        <button class="anima-btn-small js-connect <?php echo $is_friend ? 'active' : ''; ?>" 
                                data-id="<?php echo esc_attr($uid); ?>">
                            <?php echo $is_friend ? 'Conectado ✓' : 'Conectar +'; ?>
                        </button>
                    <?php else: ?>
                        <a href="<?php echo home_url('/accede-al-metaverso/'); ?>" class="anima-btn-small ghost">Acceder</a>
                    <?php endif; ?>
                </div>

            </article>
            <?php endforeach; 
        else: ?>
            <p style="grid-column: 1/-1; text-align: center;">No hay agentes activos en este momento.</p>
        <?php endif; ?>
    </div>
</section>

<script>
document.querySelectorAll('.js-connect').forEach(btn => {
    btn.addEventListener('click', async () => {
        const uid = btn.dataset.id;
        // Efecto de carga visual
        const originalText = btn.textContent;
        btn.style.opacity = '0.7';
        btn.disabled = true;
        
        const fd = new FormData();
        fd.append('action', 'anima_toggle_friend');
        fd.append('id', uid);
        
        try {
            const res = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method:'POST', body:fd});
            const data = await res.json();
            
            if(data.success){
                if(data.data.status === 'added'){
                    btn.textContent = 'Conectado ✓';
                    btn.classList.add('active');
                } else {
                    btn.textContent = 'Conectar +';
                    btn.classList.remove('active');
                }
            } else {
                btn.textContent = originalText; // Revertir si falla
            }
        } catch(e){ 
            console.error(e);
            btn.textContent = originalText;
        }
        btn.style.opacity = '1';
        btn.disabled = false;
    });
});
</script>

<style>
/* Grid de Avatares */
.anima-avatars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 24px;
}

.anima-avatar-card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 24px 20px;
    text-align: center;
    transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}

/* Efecto Hover General */
.anima-avatar-card:hover {
    transform: translateY(-5px);
    border-color: var(--cyan);
}

/* === ESTILO PRO (NEÓN) === */
.anima-avatar-card--pro {
    border-color: #00eaff; /* Borde base cian */
    box-shadow: 0 0 15px rgba(0, 234, 255, 0.15), inset 0 0 20px rgba(0, 234, 255, 0.05);
}
.anima-avatar-card--pro:hover {
    box-shadow: 0 0 25px rgba(0, 234, 255, 0.4), inset 0 0 10px rgba(0, 234, 255, 0.1);
    border-color: #fff; /* Brillo blanco al hover */
}

.av-header {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
}
.av-photo img {
    width: 86px; height: 86px;
    border-radius: 50%;
    border: 2px solid var(--line);
    object-fit: cover;
    background: #000;
}
.av-level-badge {
    position: absolute;
    bottom: -5px; right: -10px;
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
    color: #000;
    font-weight: 800;
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.4);
}
.av-name {
    font-size: 18px;
    margin: 0 0 12px;
    color: var(--ink);
    font-weight: 700;
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis;
}
.av-socials {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
    min-height: 24px;
}
.social-link img {
    width: 20px; height: 20px;
    filter: grayscale(100%);
    transition: filter .2s, transform .2s;
}
.social-link:hover img { 
    filter: none; 
    transform: scale(1.2);
}

.anima-btn-small {
    background: transparent;
    border: 1px solid var(--cyan);
    color: var(--cyan);
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 700;
    font-size: 12px;
    transition: all .2s;
    width: 100%;
}
.anima-btn-small:hover {
    background: rgba(36, 209, 255, 0.1);
}
.anima-btn-small.active {
    background: var(--cyan);
    color: #050915;
}
.anima-btn-small.ghost {
    border-color: var(--line);
    color: var(--muted);
}
.anima-btn-small.ghost:hover {
    border-color: var(--ink);
    color: var(--ink);
}
</style>

<?php get_footer(); ?>