<?php
/**
 * Plugin Name: Anima Projects — Portfolio (Inline)
 * Description: Widget de Elementor para mostrar un portfolio minimalista inline del CPT de proyectos (sin popups ni páginas externas).
 * Version: 1.0.0
 * Author: Anima Avatar Agency
 * Text Domain: anima-projects-portfolio
 */

if ( ! defined('ABSPATH') ) { exit; }

add_action('wp_enqueue_scripts', function(){
    $base = plugin_dir_url(__FILE__) . 'assets/';
    wp_register_style('anima-projects-portfolio', $base . 'portfolio.css', [], '1.0.0');
    wp_enqueue_style('anima-projects-portfolio');
});

add_action('elementor/elements/categories_registered', function($elements_manager){
    $elements_manager->add_category('anima', [
        'title' => __('Anima', 'anima-projects-portfolio'),
        'icon'  => 'fa fa-plug'
    ]);
});

add_action('elementor/widgets/register', function($widgets_manager){
    if ( ! did_action('elementor/loaded') ) return;
    require_once __DIR__ . '/widgets/class-projects-portfolio-widget.php';
    $widgets_manager->register( new \Anima_Projects_Portfolio_Widget() );
});
