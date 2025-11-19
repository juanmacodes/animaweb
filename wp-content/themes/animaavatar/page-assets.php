<?php
/**
 * Template Name: Galería de Assets Anima
 */
defined( 'ABSPATH' ) || exit;

get_header();

$current_user_id = get_current_user_id();
$assets = get_posts([
    'post_type'      => 'anima_asset',
    'posts_per_page' => -1, // Mostrar todos
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

?>

<section class="anima-account-wrap">
    <div class="anima-account-card anima-pad">
        <div class="flex-between" style="margin-bottom:20px">
            <h2>Galería de Assets Anima</h2>
            <a href="<?php echo anima_profile_url(); ?>" class="anima-btn ghost">← Volver al Perfil</a>
        </div>

        <?php if ( ! is_user_logged_in() ): ?>
            <div class="anima-notice anima-notice--info">
                Por favor, <a href="<?php echo home_url('/accede-al-metaverso/'); ?>">inicia sesión</a> para ver y descargar tus assets.
            </div>
        <?php elseif ( empty($assets) ): ?>
            <p class="anima-muted">No hay assets disponibles en este momento.</p>
        <?php else: ?>
            <div class="anima-asset-grid">
                <?php foreach ( $assets as $asset ):
                    $can_access = anima_user_can_access_asset($asset->ID, $current_user_id);
                    $product_id = get_post_meta($asset->ID, '_anima_asset_product_id', true);
                    $download_url = get_post_meta($asset->ID, '_anima_asset_download_url', true);
                    $product_link = $product_id ? get_permalink($product_id) : '#'; // Enlace al producto si existe
                    $is_free_or_month_asset = empty($product_id); // Determina si es "gratuito" o del mes
                ?>
                    <div class="anima-asset-card <?php echo $can_access ? 'unlocked' : 'locked'; ?>">
                        <div class="asset-thumb">
                            <?php echo get_the_post_thumbnail($asset->ID, 'an_square'); ?>
                            <?php if ( !$can_access ): ?>
                                <div class="asset-overlay">🔒</div>
                            <?php endif; ?>
                        </div>
                        <h3 class="asset-title"><?php echo esc_html($asset->post_title); ?></h3>
                        <div class="asset-actions">
                            <?php if ( $can_access ): ?>
                                <a href="<?php echo esc_url($download_url); ?>" class="anima-btn-small" target="_blank" rel="noopener noreferrer">Descargar</a>
                            <?php else: ?>
                                <a href="<?php echo esc_url($product_link); ?>" class="anima-btn-small ghost">
                                    <?php echo $is_free_or_month_asset ? 'Requiere Nivel Pro' : 'Ver Curso para Desbloquear'; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>

<?php
/**
 * Template Name: Galería de Assets Anima
 */
defined( 'ABSPATH' ) || exit;

get_header();

$current_user_id = get_current_user_id();
// Obtenemos todos los assets publicados
$assets = get_posts([
    'post_type'      => 'anima_asset',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<section class="section container" style="padding-top:40px; padding-bottom:80px;">
    
    <header class="section__header" style="margin-bottom:40px; text-align:center;">
        <h1 class="section__title">Arsenal Digital</h1>
        <p class="section__description">
            Recursos 3D, texturas y herramientas para tus misiones en el metaverso.
        </p>
    </header>

    <div class="anima-account-card anima-pad">
        <div class="flex-between" style="margin-bottom:20px">
            <h2>Mis Recursos Disponibles</h2>
            <a href="<?php echo function_exists('anima_profile_url') ? anima_profile_url() : home_url('/perfil/'); ?>" class="anima-btn ghost">← Volver al Perfil</a>
        </div>

        <?php if ( ! is_user_logged_in() ): ?>
            <div class="anima-notice anima-notice--info">
                ⚠️ Acceso denegado. <a href="<?php echo home_url('/accede-al-metaverso/'); ?>">Identifícate</a> para acceder al arsenal.
            </div>
        <?php elseif ( empty($assets) ): ?>
            <p class="anima-muted">Aún no se han suministrado assets al sistema.</p>
        <?php else: ?>
            <div class="anima-asset-grid">
                <?php foreach ( $assets as $asset ):
                    // Verificamos permisos (función definida en functions.php)
                    $can_access = function_exists('anima_user_can_access_asset') ? anima_user_can_access_asset($asset->ID, $current_user_id) : false;
                    
                    $product_id = get_post_meta($asset->ID, '_anima_asset_product_id', true);
                    $download_url = get_post_meta($asset->ID, '_anima_asset_download_url', true);
                    $glb_url = get_post_meta($asset->ID, '_anima_asset_glb_url', true); // Nueva meta para el GLB
                    
                    $product_link = $product_id ? get_permalink($product_id) : home_url('/tienda/');
                    $is_free_asset = empty($product_id); 
                ?>
                    <div class="anima-asset-card <?php echo $can_access ? 'unlocked' : 'locked'; ?>">
                        <div class="asset-thumb">
                            <?php if ($glb_url): // Si hay GLB, el click de la miniatura abre el visor ?>
                                <a href="#" class="js-open-glb-viewer" data-glb-url="<?php echo esc_url($glb_url); ?>" data-title="<?php echo esc_attr($asset->post_title); ?>">
                                    <?php echo get_the_post_thumbnail($asset->ID, 'medium'); ?>
                                </a>
                            <?php else: // Si no hay GLB, solo muestra la miniatura ?>
                                <?php echo get_the_post_thumbnail($asset->ID, 'medium'); ?>
                            <?php endif; ?>

                            <?php if ( !$can_access ): ?>
                                <div class="asset-overlay">🔒</div>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="asset-title"><?php echo esc_html($asset->post_title); ?></h3>
                        
                        <div class="asset-actions">
                            <?php if ( $can_access ): ?>
                                <?php if ($glb_url): ?>
                                <button class="anima-btn-small active js-open-glb-viewer" data-glb-url="<?php echo esc_url($glb_url); ?>" data-title="<?php echo esc_attr($asset->post_title); ?>" style="margin-bottom: 8px;">
                                    👁️ Ver 3D
                                </button>
                                <?php endif; ?>
                                <a href="<?php echo esc_url($download_url); ?>" class="anima-btn-small" target="_blank" download>
                                    ⬇ Descargar
                                </a>
                            <?php else: ?>
                                <a href="<?php echo esc_url($product_link); ?>" class="anima-btn-small ghost">
                                    <?php echo $is_free_asset ? 'Desbloquear' : 'Ver Curso'; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<div id="anima-glb-modal" class="anima-modal">
    <div class="anima-modal-content">
        <button class="anima-modal-close js-close-modal">✖</button>
        <h3 id="anima-glb-modal-title" style="text-align: center; margin-bottom: 20px; color: var(--ink);"></h3>
        <div class="anima-glb-viewer-container">
            <model-viewer id="anima-model-viewer"
                          src=""
                          alt="Modelo 3D del asset"
                          ar
                          ar-modes="webxr scene-viewer quick-look"
                          camera-controls
                          shadow-intensity="1"
                          exposure="1"
                          disable-zoom
                          auto-rotate
                          interaction-prompt="none">
            </model-viewer>
        </div>
    </div>
</div>

<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const glbModal = document.getElementById('anima-glb-modal');
    const modelViewer = document.getElementById('anima-model-viewer');
    const modalTitle = document.getElementById('anima-glb-modal-title');

    document.querySelectorAll('.js-open-glb-viewer').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const glbUrl = this.dataset.glbUrl;
            const assetTitle = this.dataset.title;

            if (glbUrl) {
                modelViewer.src = glbUrl;
                modalTitle.textContent = assetTitle;
                glbModal.classList.add('is-open');
                document.body.style.overflow = 'hidden'; // Evitar scroll en el fondo
            }
        });
    });

    document.querySelectorAll('.js-close-modal, #anima-glb-modal').forEach(el => {
        el.addEventListener('click', function(e) {
            if (e.target.classList.contains('js-close-modal') || e.target.id === 'anima-glb-modal') {
                glbModal.classList.remove('is-open');
                document.body.style.overflow = ''; // Restaurar scroll
                modelViewer.src = ''; // Limpiar el modelo para ahorrar recursos
                modalTitle.textContent = '';
            }
        });
    });

    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && glbModal.classList.contains('is-open')) {
            glbModal.classList.remove('is-open');
            document.body.style.overflow = '';
            modelViewer.src = '';
            modalTitle.textContent = '';
        }
    });
});
</script>

<style>
/* Estilos para la Galería de Assets (Mantener y añadir el modal) */
.anima-asset-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; margin-top:20px;
}
.anima-asset-card {
    background: rgba(255,255,255,0.03); border: 1px solid var(--line); border-radius: 16px; padding: 15px;
    text-align: center; transition: transform 0.3s ease;
    display: flex; flex-direction: column; justify-content: space-between;
}
.anima-asset-card:hover { transform: translateY(-5px); border-color: var(--cyan); }
.asset-thumb {
    height: 160px; overflow: hidden; border-radius: 12px; margin-bottom: 15px; position: relative;
    display: flex; justify-content: center; align-items: center;
}
.asset-thumb img { width: 100%; height: 100%; object-fit: cover; }
.asset-thumb a { display: block; width: 100%; height: 100%; } /* Asegurar que el enlace ocupa todo */
.asset-overlay {
    position: absolute; inset:0; background: rgba(0,0,0,0.7); display:flex; align-items:center; justify-content:center; font-size:2rem;
    border-radius: 12px; /* Añadido para que coincida con el borde del thumb */
}
.asset-title { font-size: 16px; margin: 0 0 15px; color: var(--ink); }
.anima-asset-card.locked .asset-thumb img { filter: grayscale(100%) blur(2px); }
.asset-actions { margin-top: auto; display: flex; flex-direction: column; gap: 8px;} /* Ajuste para botones apilados */

/* Estilos para el Modal (Pop-up) del Visor GLB */
.anima-modal {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.85); /* Fondo oscuro semitransparente */
    display: flex; justify-content: center; align-items: center;
    opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 10000; /* Asegurar que esté por encima de todo */
}
.anima-modal.is-open {
    opacity: 1; visibility: visible;
}
.anima-modal-content {
    background: var(--card); border: 1px solid var(--line); border-radius: 20px;
    padding: 30px; max-width: 90vw; max-height: 90vh; width: 800px;
    position: relative; box-shadow: 0 5px 30px rgba(0, 234, 255, 0.2);
    display: flex; flex-direction: column;
}
.anima-modal-close {
    position: absolute; top: 15px; right: 15px;
    background: transparent; border: none; font-size: 24px; color: var(--muted);
    cursor: pointer; transition: color 0.2s;
}
.anima-modal-close:hover { color: var(--ink); }

.anima-glb-viewer-container {
    flex-grow: 1; /* Permite que el contenedor del visor crezca y ocupe espacio */
    min-height: 400px; /* Altura mínima para el visor */
    width: 100%;
    border-radius: 15px;
    overflow: hidden; /* Asegura que el modelo no se salga de los bordes */
    background: #000; /* Fondo negro para el visor */
}
#anima-model-viewer {
    width: 100%; height: 100%;
    --poster-color: transparent; /* Oculta el póster por defecto */
}
</style>