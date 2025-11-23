<?php
/**
 * Plugin Name: Anima Projects Widgets (Modal – Editor Safe)
 * Description: Grid con popup para CPT "projects". En el editor de Elementor desactiva interacciones y no imprime modales.
 * Version: 1.1.1
 * Author: Anima Avatar Agency
 * Text Domain: anima-projects-widgets
 */

if ( ! defined('ABSPATH') ) { exit; }

function anima_is_elementor_edit_mode(){
    if ( ! defined('ELEMENTOR_VERSION') ) return false;
    try {
        return \Elementor\Plugin::$instance->editor->is_edit_mode();
    } catch ( \Throwable $e ) {
        return false;
    }
}

add_action('wp_enqueue_scripts', function () {
    $base = plugin_dir_url(__FILE__) . 'assets/';
    wp_register_style('anima-projects-widgets', $base . 'projects-widgets.css', [], '1.1.1');
    wp_register_style('anima-projects-modal',   $base . 'modal.css', [], '1.1.1');
    wp_register_script('anima-projects-modal',  $base . 'modal.js', ['jquery'], '1.1.1', true);

    wp_enqueue_style('anima-projects-widgets');

    // Encolamos modal solo si NO estamos en modo editor
    if ( ! anima_is_elementor_edit_mode() ) {
        wp_enqueue_style('anima-projects-modal');
        wp_enqueue_script('anima-projects-modal');
    }
});

add_action('elementor/elements/categories_registered', function($elements_manager){
    $elements_manager->add_category('anima', [
        'title' => __('Anima', 'anima-projects-widgets'),
        'icon'  => 'fa fa-plug'
    ]);
});

add_action('elementor/widgets/register', function($widgets_manager){
    if ( ! did_action('elementor/loaded') ) return;
    require_once __DIR__ . '/widgets/class-projects-grid-widget.php';
    require_once __DIR__ . '/widgets/class-project-detail-widget.php';
    $widgets_manager->register( new \Anima_Projects_Grid_Widget() );
    $widgets_manager->register( new \Anima_Project_Detail_Widget() );
});
