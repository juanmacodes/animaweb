<?php
/**
 * Template Name: Anima Cyberpunk Course Layout
 * Template Post Type: curso
 */

get_header();

$course_id = get_the_ID();

// --- NUEVA SECCIÓN DE NOTIFICACIÓN DE ESTADO ---
if (isset($_GET['redeem_status'])) {
    $status = sanitize_text_field($_GET['redeem_status']);
    if ($status === 'success') {
        echo '<div style="background:#00FF94; color:#000; padding:15px; text-align:center; font-weight:700;">ACCESO CANJEADO CON ÉXITO. INICIA LA SIMULACIÓN.</div>';
    } elseif ($status === 'failed_balance') {
        echo '<div style="background:#e74c3c; color:#fff; padding:15px; text-align:center; font-weight:700;">ERROR: SALDO INSUFICIENTE. POR FAVOR RECARGA CRÉDITOS.</div>';
    }
}

// 1. DEFINICIÓN DE VARIABLES Y DATOS DE CURSO
$subtitle = get_post_meta($course_id, '_anima_course_subtitle', true);
$level    = get_post_meta($course_id, '_anima_course_level', true);
$duration = get_post_meta($course_id, '_anima_course_duration', true);
$product_id = get_post_meta($course_id, '_anima_product_id', true);
$bg_image = get_the_post_thumbnail_url($course_id, 'full');

// Decodificación de datos JSON
$syllabus_json = get_post_meta($course_id, '_anima_syllabus_json', true);
$syllabus_temp = json_decode($syllabus_json, true);
$syllabus = is_array($syllabus_temp) ? $syllabus_temp : []; // Uso de is_array para compatibilidad
    
$downloads_json = get_post_meta($course_id, '_anima_course_downloads', true);
$downloads_temp = json_decode($downloads_json, true);
$downloads = is_array($downloads_temp) ? $downloads_temp : []; // Uso de is_array para compatibilidad

// Lógica de Créditos Neuronales
$course_credit_cost = 0;
if ($product_id) {
    $course_credit_cost = (int) get_post_meta($product_id, 'anima_course_credit_cost', true); 
}
$agent_stats = function_exists('anima_get_agent_stats') ? anima_get_agent_stats() : ['credits' => 0];
$agent_credits = $agent_stats['credits'];


// 2. LÓGICA DE ACCESO Y COMPRA
$user_id = get_current_user_id();
$cta_html = '';
$product = null;
$has_bought_course = false;
$is_purchasable = true; 

if ($product_id && function_exists('wc_get_product')) {
    $product = wc_get_product($product_id);
}

if ($product) {
    $is_purchasable = $product->is_purchasable() && $product->is_in_stock();

    if ($user_id > 0 && function_exists('wc_customer_bought_product')) {
        $has_bought_course = wc_customer_bought_product(wp_get_current_user()->user_email, $user_id, $product_id);
    }
}


// --- 3. CONSTRUCCIÓN DEL BOTÓN DE ACCIÓN (4 ESTADOS EN ORDEN) ---

if ( ! is_user_logged_in() ) {
    // ESTADO 1: NO LOGUEADO
    $cta_html = sprintf(
        '<a href="%s" class="action-btn purchase-btn">%s</a>',
        esc_url( home_url( '/login/' ) ),
        esc_html( 'INICIAR SESIÓN / REGISTRO' )
    );

} elseif ( $has_bought_course ) {
    // ESTADO 2: COMPRADO / ACCESO TOTAL
    $cta_html = '<a href="#syllabus" class="action-btn access-btn">INICIAR SIMULACIÓN</a>';

} elseif ( $course_credit_cost > 0 ) {
    // ESTADO 3: NECESITA CANJEAR CON CRÉDITOS
    if ($agent_credits >= $course_credit_cost) {
        // Tiene créditos suficientes: Muestra el formulario de canje
        $cta_html = sprintf(
            '<form class="redeem-form" method="post" action="%s">
                <input type="hidden" name="action" value="anima_redeem_course" />
                <input type="hidden" name="course_id" value="%d" />
                <input type="hidden" name="cost" value="%d" />
                %s
                <button type="submit" class="action-btn credit-redeem-btn purchase-btn">%s</button>
            </form>',
            esc_url( admin_url( 'admin-post.php' ) ),
            esc_attr($course_id),
            esc_attr($course_credit_cost),
            wp_nonce_field( 'anima_redeem_action', 'redeem_nonce', true, false ), // Nonce de seguridad
            esc_html( 'CANJEAR POR CRÉDITOS' )
        );
    } else {
        // No tiene créditos suficientes: Botón de Recarga (Clicable)
        $cta_html = sprintf(
            '<a href="%s" class="action-btn purchase-btn">%s</a>',
            esc_url(home_url('/mi-cuenta/?view=recharge')),
            esc_html( 'RECARGAR CRÉDITOS' )
        );
    }
} elseif ( $is_purchasable ) {
    // ESTADO 4: LOGUEADO Y NECESITA COMPRAR (Venta Directa WooCommerce)
     $cta_html = sprintf(
        '<form class="cart" action="%s" method="post" enctype="multipart/form-data">
            <input type="hidden" name="add-to-cart" value="%d" />
            <input type="hidden" name="product_id" value="%d" />
            <input type="hidden" name="quantity" value="1" /> 
            
            <button type="submit" name="add-to-cart" value="%d" class="action-btn purchase-btn">%s</button>
        </form>',
        esc_url( $product->add_to_cart_url() ),
        esc_attr( $product_id ),
        esc_attr( $product_id ),
        esc_attr( $product_id ),
        esc_html( 'ADQUIRIR ACCESO' )
    );
}
else {
    // ESTADO FINAL: NO DISPONIBLE
    $cta_html = '<button class="action-btn disabled">MÓDULO NO DISPONIBLE</button>';
}
?>

<style>
    /* Estilos CSS (Se mantienen tus estilos base) */
    .course-hero {
        position: relative; height: 60vh; min-height: 400px; display: flex; align-items: flex-end; padding-bottom: 60px;
        background-size: cover; background-position: center; overflow: hidden;
    }
    .course-hero::before {
        content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(0deg, #050505 0%, rgba(5,5,5,0.8) 50%, rgba(5,5,5,0.4) 100%); z-index: 1;
    }
    .hero-content {
        position: relative; z-index: 2; width: 100%; max-width: 1400px; margin: 0 auto; padding: 0 20px;
    }
    .hero-badges span { background: #00F0FF; color: #000; padding: 4px 10px; font-weight: 700; font-family: 'Rajdhani'; text-transform: uppercase; font-size: 0.8rem; margin-right: 10px; }
    .hero-title { 
        font-size: 3.5rem !important; line-height: 1.1; margin: 15px 0; color: #fff; text-transform: uppercase; text-shadow: 0 0 20px rgba(0,0,0,0.8);
    }
    .hero-subtitle { font-size: 1.2rem; color: #ccc; max-width: 800px; margin-bottom: 20px; }
    
    /* LAYOUT GRID (CRÍTICO) */
    .course-layout {
        max-width: 1400px; margin: 0 auto; padding: 60px 20px;
        display: grid; grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr); gap: 50px;
    }
    
    /* Sidebar Flotante */
    .course-sidebar { 
        background: #0F0F12; border: 1px solid #2A2A35; padding: 30px; border-radius: 12px; 
        position: sticky; top: 100px; height: fit-content; box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    }

    /* === ESTILOS DEL PRECIO Y CRÉDITOS === */
    .price-display-box {
        text-align: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #2A2A35;
    }
    .price-display-box .woocommerce-Price-amount,
    .credit-cost-display { 
        font-size: 2.8rem; font-family: 'Rajdhani', sans-serif; font-weight: 700;
        color: #00FF94; display: block; line-height: 1;
    }
    .price-display-box del { color: #666; font-size: 1.1rem; display: block; margin-bottom: 5px; }
    
    /* Estilos del botón de acción */
    .action-btn {
        display: block; width: 100%; padding: 15px; text-align: center; font-family: 'Rajdhani'; font-weight: 700; 
        text-transform: uppercase; text-decoration: none; margin-bottom: 30px; transition: all 0.3s;
    }
    .purchase-btn { background: #BC13FE; color: #fff; } 
    .purchase-btn:hover { background: #fff; color: #000; box-shadow: 0 0 20px #BC13FE; }
    .access-btn { background: #00FF94; color: #000; }
    .access-btn:hover { background: #fff; box-shadow: 0 0 20px #00FF94; }
    .disabled { background: #333; color: #666; cursor: not-allowed; }

    /* Clases de Temario */
    .module-title { padding: 15px; cursor: pointer; color: #fff; font-weight: 700; display: flex; justify-content: space-between; transition: background 0.3s; }
    .module-lessons { max-height: 0; opacity: 0; overflow: hidden; transition: all 0.4s ease; list-style: none; margin: 0; padding: 0; border-top: 1px solid #333; }
    .module-item.active .module-lessons { max-height: 1000px; opacity: 1; }

    .module-lessons li { padding: 12px 20px; border-bottom: 1px solid #222; display: flex; align-items: center; gap: 10px; color: #888; }
</style>

<div id="primary" class="content-area anima-dark-theme">
    <main id="main" class="site-main">

    <?php while ( have_posts() ) : the_post(); ?>
        <div class="course-hero" style="background-image: url('<?php echo esc_url($bg_image); ?>');">
            <div class="hero-content">
                <div class="hero-badges">
                    <?php if($level): ?><span><?php echo esc_html($level); ?></span><?php endif; ?>
                    <span>UNREAL ENGINE 5</span>
                </div>
                <h1 class="hero-title"><?php the_title(); ?></h1>
                <?php if($subtitle): ?><p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
            </div>
        </div>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="course-layout">
                
                <div class="course-main-content">
                    
                    <section class="course-description">
                        <h2 class="section-title">INFORMACIÓN DE LA MISIÓN</h2>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </section>

                    <?php if (!empty($syllabus) && is_array($syllabus)): ?>
                    <section class="course-syllabus" id="syllabus">
                        <h2 class="section-title">PLAN DE ESTUDIOS (TEMARIO)</h2>

                        <?php foreach ($syllabus as $module) : ?>
                            <div class="module-item">
                                <h3 class="module-title">
                                    <span class="dashicons dashicons-media-default"></span>
                                    <?php echo esc_html($module['title'] ?? 'Módulo Sin Título'); ?>
                                </h3>
                                
                                <?php 
                                // INICIO DEL BUCLS DE LECCIONES CORREGIDO Y SEGURO
                                if (isset($module['lessons']) && is_array($module['lessons'])): ?>
                                    <ul class="module-lessons">
                                        <?php foreach ($module['lessons'] as $lesson) : 
                                            // LÓGICA DE DATOS (Compatibilidad PHP)
                                            $lesson_name = isset($lesson['title']) ? $lesson['title'] : (isset($lesson['name']) ? $lesson['name'] : 'Lección sin título'); 
                                            $vid_file = isset($lesson['video']) ? $lesson['video'] : ''; 
                                            $vid_url = '';
                                            
                                            if ($vid_file) {
                                                // RUTA CRÍTICA CORREGIDA (debe apuntar al archivo protegido)
                                                $vid_url = home_url( '/wp-content/anima-protected/courses/' . $course_id . '/' . $vid_file );
                                            }

                                            $has_video_access = ($has_bought_course && $vid_url);
                                        ?>
                                            <li class="lesson-item">
                                                <?php if ($has_video_access): ?>
                                                    <span class="dashicons dashicons-media-play" style="color:#00FF94;"></span>
                                                    <a href="<?php echo esc_url($vid_url); ?>" target="_blank" class="lesson-link">
                                                        <?php echo esc_html($lesson_name); ?>
                                                    </a>
                                                    <span class="video-badge">VÍDEO</span>
                                                <?php else: ?>
                                                    <span class="dashicons dashicons-lock"></span>
                                                    <span style="color:#666;">
                                                        <?php echo esc_html($lesson_name); ?>
                                                    </span>
                                                    <?php if ($vid_file): ?><span class="video-badge" style="background:#444; color:#fff;">VÍDEO</span><?php endif; ?>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; // Fin del if(isset($module['lessons'])) ?>
                            </div>
                        <?php endforeach; ?>
                    </section>
                    <?php endif; ?>

                </div><aside class="course-sidebar">
                    
                    <div class="price-display-box">
                        <?php if ($course_credit_cost > 0) : ?>
                            <span class="credit-cost-display">
                                <?php echo number_format($course_credit_cost, 0, ',', '.'); ?> Créditos
                            </span>
                        <?php elseif ($product && $product->get_price_html()) : ?>
                            <?php echo $product->get_price_html(); ?>
                        <?php else: ?>
                            <span class="credit-cost-display">ACCESO LIBRE</span>
                        <?php endif; ?>
                        
                        <p class="price-label"><?php echo $course_credit_cost > 0 ? 'Costo de acceso' : 'Precio de compra'; ?></p>
                    </div>

                    <?php echo $cta_html; ?>

                    <ul class="sidebar-data">
                        <?php if($duration): ?><li><span>Duración:</span> <span><?php echo esc_html($duration); ?></span></li><?php endif; ?>
                        <?php if($level): ?><li><span>Nivel:</span> <span><?php echo esc_html($level); ?></span></li><?php endif; ?>
                        <li><span>Acceso:</span> <span>De por vida</span></li>
                        <li><span>Certificado:</span> <span>Sí</span></li>
                    </ul>

                    <?php if(!empty($downloads) && is_array($downloads)): ?>
                        <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">
                            <h4 style="color:#fff; margin-bottom: 15px;">ARCHIVOS DE MISIÓN</h4>
                            <?php foreach($downloads as $dl): 
                                $dl_url = content_url( '/anima-protected/courses/' . $course_id . '/downloads/' . ($dl['file'] ?? '') );
                            ?>
                                <a href="<?php echo $has_bought_course ? esc_url($dl_url) : '#'; ?>" 
                                    <?php if(!$has_bought_course): ?>style="color:#666; cursor:not-allowed;"<?php endif; ?>
                                    download class="dl-link">
                                    <span class="dashicons <?php echo $has_bought_course ? 'dashicons-download' : 'dashicons-lock'; ?>"></span> <?php echo esc_html($dl['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </aside></div></article><?php endwhile; ?>
    </main></div><script>
document.addEventListener('DOMContentLoaded', function() {
    const titles = document.querySelectorAll('.module-title');
    titles.forEach(title => {
        title.addEventListener('click', function() {
            const parent = this.parentElement;
            const lessons = parent.querySelector('.module-lessons');
            
            parent.classList.toggle('active');
            
            if(parent.classList.contains('active')){
                lessons.style.maxHeight = lessons.scrollHeight + 'px';
            } else {
                lessons.style.maxHeight = '0';
            }
        });
    });
});
</script>
<?php get_footer(); ?>