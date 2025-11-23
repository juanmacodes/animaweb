<?php
use Elementor\Widget_Base;

if ( ! defined('ABSPATH') ) { exit; }

class Anima_Project_Detail_Widget extends Widget_Base {
    public function get_name() { return 'anima-project-detail'; }
    public function get_title() { return 'Proyecto — Detalle'; }
    public function get_icon() { return 'eicon-post'; }
    public function get_categories() { return ['anima']; }

    protected function render() {
        global $post;
        if ( ! $post ) { echo '<p>No hay proyecto actual.</p>'; return; }
        if ( 'projects' !== $post->post_type ) { echo '<p>Usa este widget en una entrada del tipo "projects".</p>'; return; }

        $project_id = $post->ID;
        $title      = get_the_title($project_id);
        $client     = get_post_meta($project_id, 'client', true);
        $year       = get_post_meta($project_id, 'year', true);
        $cta_label  = get_post_meta($project_id, 'cta_label', true);
        $cta_url    = get_post_meta($project_id, 'cta_url', true);
        $gallery    = get_post_meta($project_id, 'gallery_urls', true);

        $gallery_urls = [];
        if ( ! empty($gallery) ) {
            $lines = preg_split('/\r\n|\r|\n/', trim($gallery));
            foreach ($lines as $line) {
                $u = trim($line);
                if ($u) $gallery_urls[] = $u;
            }
        }

        echo '<section class="anima-project-detail">';
            echo '<header class="project-head">';
                echo '<h2 class="project-title">'. esc_html($title) .'</h2>';
                if ( $client ) echo '<p class="project-meta"><strong>Cliente:</strong> '. esc_html($client) .'</p>';
                if ( $year )   echo '<p class="project-meta"><strong>Año:</strong> '. esc_html($year) .'</p>';
                if ( $cta_label && $cta_url ) echo '<p><a class="project-cta-button" target="_blank" href="'. esc_url($cta_url) .'">'. esc_html($cta_label) .'</a></p>';
            echo '</header>';

            if ( ! empty($gallery_urls) ) {
                echo '<div class="project-gallery">';
                foreach ($gallery_urls as $img) {
                    $img = esc_url($img);
                    echo '<figure class="project-gallery-item"><img src="'. $img .'" alt="'. esc_attr($title) .'"/></figure>';
                }
                echo '</div>';
            }
        echo '</section>';
    }
}
