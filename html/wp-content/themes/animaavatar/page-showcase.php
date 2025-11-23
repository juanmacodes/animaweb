<?php
/**
 * Template Name: Showcase 3D
 * Description: Galería dinámica de modelos 3D desde el backend.
 */
get_header();

// 1. Obtener modelos desde el CPT 'anima_model'
$args = [
    'post_type'      => 'anima_model',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order date', 
    'order'          => 'ASC'
];
$query = new WP_Query($args);
$models = [];

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $id = get_the_ID();
        
        // Recuperar metadatos
        $src = get_post_meta($id, '_anima_model_glb', true);
        $poster = get_post_meta($id, '_anima_model_poster', true);
        $link = get_post_meta($id, '_anima_model_link', true);
        
        // CORRECCIÓN: Ya NO forzamos un enlace por defecto si está vacío.
        // Dejamos $link tal cual viene de la base de datos (vacío si no has puesto nada).

        if ($src) { 
            $models[] = [
                'id'   => 'model-' . $id,
                'name' => get_the_title(),
                'desc' => get_the_excerpt() ?: 'Modelo de alta fidelidad listo para producción.', 
                'src'  => $src,
                'poster' => $poster,
                'link' => $link // Puede estar vacío
            ];
        }
    }
    wp_reset_postdata();
}

// Modelo inicial
$initial_model = !empty($models) ? $models[0] : [
    'name' => 'No hay modelos', 
    'desc' => 'Añade modelos desde el backend.', 
    'src' => '', 
    'poster' => '',
    'link' => ''
];
?>

<section class="section container" style="padding-top:40px; padding-bottom:80px;">
    
    <header class="section__header" style="text-align:center; margin-bottom:40px;">
        <h1 class="section__title">Anima Lab: Prototipos</h1>
        <p class="section__description">Inspecciona la calidad de nuestros activos digitales. Rota, acerca y explora.</p>
    </header>

    <?php if (!empty($models)): ?>
    <div class="anima-showcase-layout">
        
        <div class="anima-viewer-stage">
            <model-viewer 
                id="main-viewer"
                src="<?php echo esc_url($initial_model['src']); ?>" 
                poster="<?php echo esc_url($initial_model['poster']); ?>"
                alt="Modelo 3D"
                shadow-intensity="1"
                camera-controls
                auto-rotate
                ar
                environment-image="neutral"
                exposure="1"
                loading="eager"
                style="width:100%; height:100%;">
                
                <div class="progress-bar hide" slot="progress-bar">
                    <div class="update-bar"></div>
                </div>
                
                <button slot="ar-button" class="anima-ar-btn">
                    Ver en tu espacio (AR)
                </button>
            </model-viewer>
            
            <div class="viewer-info">
                <h3 id="model-title"><?php echo esc_html($initial_model['name']); ?></h3>
                <p id="model-desc" class="anima-muted"><?php echo esc_html($initial_model['desc']); ?></p>
            </div>
        </div>

        <div class="anima-model-list">
            <h3>Modelos Disponibles</h3>
            <div class="model-grid">
                <?php foreach($models as $i => $m): ?>
                <button class="model-btn <?php echo $i===0 ? 'active' : ''; ?>" 
                        onclick="switchModel(this, '<?php echo esc_js($m['src']); ?>', '<?php echo esc_js($m['name']); ?>', '<?php echo esc_js($m['desc']); ?>', '<?php echo esc_js($m['poster']); ?>', '<?php echo esc_js($m['link']); ?>')">
                    <span class="btn-label"><?php echo esc_html($m['name']); ?></span>
                    <span class="btn-arrow">→</span>
                </button>
                <?php endforeach; ?>
            </div>
            
            <div class="showcase-cta">
                <p class="anima-muted" style="font-size:14px; margin-bottom:15px;">¿Te gusta este modelo?</p>
                
                <a id="model-cta-link" 
                   href="<?php echo esc_url($initial_model['link']); ?>" 
                   class="anima-btn full-width <?php echo empty($initial_model['link']) ? 'disabled ghost' : ''; ?>"
                   <?php echo empty($initial_model['link']) ? 'style="pointer-events:none; opacity:0.6;"' : ''; ?>>
                   <?php echo empty($initial_model['link']) ? 'Próximamente disponible' : 'Conseguir Activo'; ?>
                </a>

            </div>
        </div>

    </div>
    <?php else: ?>
        <div class="anima-card" style="text-align:center; padding:40px;">
            <h3>Aún no hay modelos cargados</h3>
            <p>Ve al panel de administración > Modelos 3D para añadir el primero.</p>
        </div>
    <?php endif; ?>

</section>

<script>
const viewer = document.querySelector('#main-viewer');
const title  = document.querySelector('#model-title');
const desc   = document.querySelector('#model-desc');
const ctaBtn = document.querySelector('#model-cta-link');

function switchModel(element, src, name, description, poster, link) {
    // Cambiar modelo
    viewer.src = src;
    viewer.poster = poster;
    
    // Cambiar textos
    title.textContent = name;
    desc.textContent = description;
    
    // Cambiar estado del botón según si hay link o no
    if(ctaBtn) {
        if (link && link !== '') {
            // Si hay link: Activar botón
            ctaBtn.href = link;
            ctaBtn.textContent = 'Conseguir Activo';
            ctaBtn.classList.remove('disabled', 'ghost');
            ctaBtn.style.pointerEvents = 'auto';
            ctaBtn.style.opacity = '1';
        } else {
            // Si NO hay link: Desactivar botón
            ctaBtn.removeAttribute('href');
            ctaBtn.textContent = 'Próximamente disponible';
            ctaBtn.classList.add('disabled', 'ghost');
            ctaBtn.style.pointerEvents = 'none';
            ctaBtn.style.opacity = '0.6';
        }
    }
    
    // Actualizar clases visuales de la lista
    document.querySelectorAll('.model-btn').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
}
</script>

<?php get_footer(); ?>