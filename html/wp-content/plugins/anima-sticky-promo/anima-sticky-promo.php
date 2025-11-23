<?php
/**
 * Plugin Name: Anima Sticky Metahuman Promo
 * Description: Crea un elemento promocional fijo, centrado y cerrable para el curso Metahuman.
 * Version: 3.0 (Centrado Vertical)
 * Author: Anima Avatar Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Enqueue CSS y JS
add_action( 'wp_enqueue_scripts', 'anima_sticky_promo_assets' );
function anima_sticky_promo_assets() {
    // Cargar CSS (Contiene el diseño futurista del overlay)
    wp_enqueue_style( 'anima-sticky-promo-style', 
                      plugin_dir_url( __FILE__ ) . 'sticky-promo.css', 
                      array(), 
                      '3.0' );
    
    // Cargar JS (Contiene la lógica para fijar y cerrar el overlay)
    wp_enqueue_script( 'anima-sticky-promo-js', 
                       plugin_dir_url( __FILE__ ) . 'sticky-promo.js', 
                       array('jquery'), 
                       '3.0', 
                       true );
}

// 2. Registrar el Shortcode
add_shortcode( 'anima_metahuman_promo', 'anima_metahuman_promo_shortcode' );

function anima_metahuman_promo_shortcode( $atts ) {
    // Atributos personalizables (¡Ahora totalmente personalizable!)
    $atts = shortcode_atts( array(
        'course_url' => home_url('/curso/creacion-metahuman/'), 
        'image_url'  => get_template_directory_uri() . '/assets/images/metahuman-avatar-small.png',
        'title'      => 'Creación Metahuman',
        'subtitle'   => 'Domina la identidad digital del futuro.',
        'position'   => 'left', // Nueva: 'left' o 'right'
        'vertical'   => 'center', // Nueva: 'top', 'center', o 'bottom'
    ), $atts, 'anima_metahuman_promo' );

    // Generar clases CSS para el posicionamiento
    $position_class = 'position-' . sanitize_html_class($atts['position']);
    $vertical_class = 'vertical-' . sanitize_html_class($atts['vertical']);

    // Generar el HTML
    ob_start();
    ?>
    <div id="anima-promo-overlay" class="anima-promo-overlay <?php echo $position_class; ?> <?php echo $vertical_class; ?>">
        <button class="promo-close-btn" title="Cerrar permanentemente">✕</button>
        <div class="promo-content">
            <img src="<?php echo esc_url($atts['image_url']); ?>" 
                 alt="<?php echo esc_attr($atts['title']); ?>" 
                 class="promo-avatar">
            <div class="promo-text">
                <h3>Curso: <?php echo esc_html($atts['title']); ?></h3>
                <p><?php echo esc_html($atts['subtitle']); ?></p>
            </div>
            <a href="<?php echo esc_url($atts['course_url']); ?>" 
               class="anima-btn promo-btn">
                ACCEDER AL CURSO
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}