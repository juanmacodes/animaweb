<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined('ABSPATH') ) { exit; }

class Anima_Projects_Grid_Widget extends Widget_Base {
    public function get_name() { return 'anima-projects-grid'; }
    public function get_title() { return 'Proyectos — Grid (con popup)'; }
    public function get_icon() { return 'eicon-posts-grid'; }
    public function get_categories() { return ['anima']; }

    protected function _register_controls() {
        $this->start_controls_section('content', [ 'label' => __('Contenido', 'anima-projects-widgets') ]);

        $this->add_control('post_type', [
            'label' => __('Slug del post type', 'anima-projects-widgets'),
            'type'  => Controls_Manager::TEXT,
            'default' => 'projects',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $slug = !empty($settings['post_type']) ? sanitize_key($settings['post_type']) : 'projects';

        $q = new \WP_Query([
            'post_type'      => $slug,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        if ( ! $q->have_posts() ) { echo '<p>No hay proyectos disponibles.</p>'; return; }

        echo '<div class="anima-projects-grid">';
        while ( $q->have_posts() ) { $q->the_post();
            $post_id   = get_the_ID();
            $title     = get_the_title();
            $permalink = get_permalink();
            $thumb     = get_the_post_thumbnail( $post_id, 'large', ['class'=>'project-thumb', 'alt'=>$title] );

            // Campos meta
            $client    = get_post_meta($post_id, 'client', true);
            $year      = get_post_meta($post_id, 'year', true);
            $cta_label = get_post_meta($post_id, 'cta_label', true);
            $cta_url   = get_post_meta($post_id, 'cta_url', true);
            $gallery   = get_post_meta($post_id, 'gallery_urls', true);
            $gallery_urls = [];
            if ( ! empty($gallery) ) {
                $lines = preg_split('/\r\n|\r|\n/', trim($gallery));
                foreach ($lines as $line) {
                    $u = trim($line);
                    if ($u) $gallery_urls[] = $u;
                }
            }
            $content = apply_filters('the_content', get_post_field('post_content', $post_id));

            echo '<article class="anima-project-card">';
                if ( $thumb ) echo '<a href="'. esc_url($permalink) .'" class="thumb-wrap">'. $thumb .'</a>';
                echo '<h3 class="project-title"><a href="'. esc_url($permalink) .'">'. esc_html($title) .'</a></h3>';
                echo '<div class="project-actions">';
                    echo '<button class="project-open-modal" data-modal="#anima-modal-'. esc_attr($post_id) .'">Ver detalle</button>';
                    if ( $cta_label && $cta_url ) {
                        echo '<a class="project-cta-button" target="_blank" href="'. esc_url($cta_url) .'">'. esc_html($cta_label) .'</a>';
                    }
                echo '</div>';
            echo '</article>';

            // ----- MODAL por proyecto (oculto hasta abrir) -----
            echo '<div id="anima-modal-'. esc_attr($post_id) .'" class="anima-modal" aria-hidden="true">';
                echo '<div class="anima-modal__overlay" data-close></div>';
                echo '<div class="anima-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="am-title-'. esc_attr($post_id) .'">';
                    echo '<button class="anima-modal__close" aria-label="Cerrar" data-close>&times;</button>';
                    echo '<header class="anima-modal__header">';
                        echo '<h3 id="am-title-'. esc_attr($post_id) .'" class="anima-modal__title">'. esc_html($title) .'</h3>';
                        if ( $client ) echo '<p class="anima-modal__meta"><strong>Cliente:</strong> '. esc_html($client) .'</p>';
                        if ( $year )   echo '<p class="anima-modal__meta"><strong>Año:</strong> '. esc_html($year) .'</p>';
                    echo '</header>';

                    if ( $thumb ) {
                        echo '<div class="anima-modal__media">'. $thumb .'</div>';
                    }

                    echo '<div class="anima-modal__content">'. $content .'</div>';

                    if ( ! empty($gallery_urls) ) {
                        echo '<div class="anima-modal__gallery">';
                        foreach ($gallery_urls as $img) {
                            $img = esc_url($img);
                            echo '<img src="'. $img .'" alt="'. esc_attr($title) .'" />';
                        }
                        echo '</div>';
                    }

                    if ( $cta_label && $cta_url ) {
                        echo '<div class="anima-modal__footer">';
                        echo '<a class="project-cta-button" target="_blank" href="'. esc_url($cta_url) .'">'. esc_html($cta_label) .'</a>';
                        echo '</div>';
                    }
                echo '</div>';
            echo '</div>';
            // ----- FIN MODAL -----
        }
        echo '</div>';

        wp_reset_postdata();
    }
}
