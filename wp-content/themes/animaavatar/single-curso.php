<?php
/**
 * Plantilla single para CPT "curso" (Diseño Hero + Sidebar Izquierda)
 */
defined( 'ABSPATH' ) || exit;

get_header();

// 1. Obtener ID y Metadatos
$curso_id = get_the_ID();
$product_id = (int) get_post_meta( $curso_id, '_anima_product_id', true );

// Helpers de datos (Definidos localmente para evitar fallos si no están en functions.php)
$level    = get_post_meta( $curso_id, '_anima_level', true ) ?: 'Inicial';
$duration = get_post_meta( $curso_id, '_anima_duration', true ) ?: '2h';
$mode     = get_post_meta( $curso_id, '_anima_mode', true ) ?: 'Grabado';
$software = get_post_meta( $curso_id, '_anima_software', true ) ?: 'Unreal';

// Recuperar Temario (JSON)
$syllabus_raw = get_post_meta( $curso_id, '_anima_syllabus_json', true );
$syllabus = [];
if ( is_string($syllabus_raw) && !empty($syllabus_raw) ) {
    $decoded = json_decode($syllabus_raw, true);
    if (json_last_error() === JSON_ERROR_NONE) $syllabus = $decoded;
}

// Recuperar Descargas (JSON)
$downloads_raw = get_post_meta( $curso_id, '_anima_downloads_json', true );
$downloads = [];
if ( is_string($downloads_raw) && !empty($downloads_raw) ) {
    $decoded = json_decode($downloads_raw, true);
    if (json_last_error() === JSON_ERROR_NONE) $downloads = $decoded;
}

// 2. Verificar Acceso
$has_access = false;
$product = $product_id ? wc_get_product( $product_id ) : false;

if ( is_user_logged_in() && $product_id && function_exists( 'wc_get_orders' ) ) {
    $orders = wc_get_orders([
        'customer_id' => get_current_user_id(),
        'status' => ['completed', 'processing', 'on-hold'],
        'limit' => -1
    ]);
    foreach ( $orders as $order ) {
        foreach ( $order->get_items() as $item ) {
            if ( (int)$item->get_product_id() === $product_id ) {
                $has_access = true; break 2;
            }
        }
    }
}

// 3. Configurar Botón de Acción (CTA)
$cta_html = '';
if ( $has_access ) {
    // Usuario ya tiene el curso
    $cta_html = '<a href="#temario" class="anima-btn-primary">Ver Contenido</a>';
} elseif ( $product && $product->is_purchasable() && $product->is_in_stock() ) {
    // Botón de Compra WooCommerce
    $cta_html = sprintf(
        '<a href="%s" class="anima-btn-primary button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="%s" aria-label="%s">%s</a>',
        esc_url( $product->add_to_cart_url() ),
        esc_attr( $product->get_id() ),
        esc_attr( $product->get_name() ),
        esc_html( 'Comprar Ahora' )
    );
} else {
    $cta_html = '<button class="anima-btn-disabled" disabled>No disponible</button>';
}

$price_html = $product ? $product->get_price_html() : '';
?>

<main id="primary" class="site-main">
<?php while ( have_posts() ) : the_post(); ?>

    <section class="course-hero">
        <div class="anima-container hero-grid">
            <div class="hero-text">
                <span class="software-badge"><?php echo esc_html($software); ?></span>
                <h1 class="hero-title"><?php the_title(); ?></h1>
                <div class="hero-excerpt"><?php the_excerpt(); ?></div>
                
                <div class="hero-pricing">
                    <?php if ($price_html && !$has_access): ?>
                        <div class="price-tag"><?php echo wp_kses_post($price_html); ?></div>
                    <?php endif; ?>
                    
                    <div class="cta-wrapper">
                        <?php echo $cta_html; ?>
                    </div>
                </div>
            </div>
            
            <div class="hero-media">
                <?php if ( has_post_thumbnail() ): ?>
                    <div class="hero-img-box">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="course-body">
        <div class="anima-container body-grid">
            
            <aside class="course-sidebar">
                <div class="meta-card sticky-card">
                    <div class="meta-row">
                        <label>Nivel</label>
                        <strong><?php echo esc_html($level); ?></strong>
                    </div>
                    <div class="meta-row">
                        <label>Duración</label>
                        <strong><?php echo esc_html($duration); ?></strong>
                    </div>
                    <div class="meta-row">
                        <label>Modalidad</label>
                        <strong><?php echo esc_html($mode); ?></strong>
                    </div>
                </div>
            </aside>

            <div class="course-content">
                <h2 class="section-title">Descripción del curso</h2>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <h2 class="section-title" id="temario">Temario del curso</h2>
                <div class="syllabus-container">
                    <?php if (!empty($syllabus)): foreach($syllabus as $idx => $module): ?>
                        <div class="syllabus-module <?php echo $has_access ? 'unlocked' : 'locked'; ?>">
                            <div class="module-header">
                                <span class="module-title">
                                    <?php if(!$has_access) echo '🔒 '; ?>
                                    <?php echo esc_html($module['title']); ?>
                                </span>
                            </div>
                            <?php if($has_access): ?>
                                <ul class="module-lessons">
                                    <?php foreach($module['lessons'] as $lesson): ?>
                                        <li><?php echo esc_html($lesson['title']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; else: ?>
                        <p>No hay temario disponible aún.</p>
                    <?php endif; ?>
                </div>
                
                <?php if(!empty($downloads)): ?>
                <h2 class="section-title">Material Descargable</h2>
                <div class="downloads-container">
                    <?php foreach($downloads as $file): 
                        $link = $has_access ? esc_url($file['uri']) : '#';
                        $cls = $has_access ? 'download-btn' : 'download-btn locked';
                    ?>
                        <a href="<?php echo $link; ?>" class="<?php echo $cls; ?>">
                            📄 <?php echo esc_html($file['label']); ?>
                            <?php if(!$has_access) echo ' (Bloqueado)'; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

<?php endwhile; ?>
</main>

<?php get_footer(); ?>