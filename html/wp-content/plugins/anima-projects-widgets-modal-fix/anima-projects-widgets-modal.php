<?php
/**
 * Plugin Name: Anima Projects Widgets (Modal)
 * Description: Widgets de Elementor para el CPT "projects": Grid con popup de detalle y widget de Detalle.
 * Version: 1.1.0
 * Author: Anima Avatar Agency
 * Text Domain: anima-projects-widgets
 */

if ( ! defined('ABSPATH') ) { exit; }

// Enqueue estilos y scripts
add_action('wp_enqueue_scripts', function () {
    $base = plugin_dir_url(__FILE__) . 'assets/';
    wp_register_style('anima-projects-widgets', $base . 'projects-widgets.css', [], '1.1.0');
    wp_register_style('anima-projects-modal',   $base . 'modal.css', [], '1.1.0');
    wp_register_script('anima-projects-modal',  $base . 'modal.js', ['jquery'], '1.1.0', true);

    wp_enqueue_style('anima-projects-widgets');
    wp_enqueue_style('anima-projects-modal');
    wp_enqueue_script('anima-projects-modal');
});

// Categoría "Anima" para Elementor
add_action('elementor/elements/categories_registered', function($elements_manager){
    $elements_manager->add_category('anima', [
        'title' => __('Anima', 'anima-projects-widgets'),
        'icon'  => 'fa fa-plug'
    ]);
});

// Registrar widgets
add_action('elementor/widgets/register', function($widgets_manager){
    if ( ! did_action('elementor/loaded') ) return;
    require_once __DIR__ . '/widgets/class-projects-grid-widget.php';
    require_once __DIR__ . '/widgets/class-project-detail-widget.php';
    $widgets_manager->register( new \Anima_Projects_Grid_Widget() );
    $widgets_manager->register( new \Anima_Project_Detail_Widget() );
});
