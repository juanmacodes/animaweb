<?php
/**
 * Plugin Name: Anima Curso + Elementor (Fix)
 * Description: Habilita el CPT 'curso' para Elementor si ya existe. No registra el CPT por si lo hace el tema.
 * Version: 1.1
 * Author: Anima Engine
 */

// Solo añade soporte Elementor si el CPT ya existe
function anima_elementor_support_for_curso() {
    if ( post_type_exists('curso') ) {
        add_post_type_support('curso', 'elementor');
    }
}
add_action('init', 'anima_elementor_support_for_curso', 20);

// Forzar inclusión en post types públicos de Elementor Pro
add_filter('elementor_pro/utils/get_public_post_types', function($post_types) {
    $post_types[] = 'curso';
    return array_unique($post_types);
});
