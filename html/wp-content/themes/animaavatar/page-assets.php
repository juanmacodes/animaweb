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