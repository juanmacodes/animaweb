<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined('ABSPATH') ) { exit; }

class Anima_Projects_Portfolio_Widget extends Widget_Base {
    public function get_name() { return 'anima-projects-portfolio'; }
    public function get_title() { return 'Proyectos — Portfolio (inline)'; }
    public function get_icon() { return 'eicon-gallery-grid'; }
    public function get_categories() { return ['anima']; }

    protected function _register_controls() {
        $this->start_controls_section('content', [ 'label' => __('Contenido', 'anima-projects-portfolio') ]);

        $this->add_control('post_type', [
            'label' => __('Slug del post type', 'anima-projects-portfolio'),
            'type'  => Controls_Manager::TEXT,
            'default' => 'projects',
        ]);

        $this->add_control('columns', [
            'label' => __('Columnas', 'anima-projects-portfolio'),
            'type'  => Controls_Manager::NUMBER,
            'default' => 3,
            'min' => 1,
            'max' => 6
        ]);

        $this->add_control('max_gallery', [
            'label' => __('Máx. imágenes por proyecto', 'anima-projects-portfolio'),
            'type'  => Controls_Manager::NUMBER,
            'default' => 3,
            'min' => 0,
            'max' => 12
        ]);

        $this->add_control('show_excerpt', [
            'label' => __('Mostrar contenido/extracto', 'anima-projects-portfolio'),
            'type'  => Controls_Manager::SWITCHER,
            'label_on' => 'Sí',
            'label_off'=> 'No',
            'return_value' => 'yes',
            'default' => 'yes'
        ]);

        $this->add_control('chips', [
            'label' => __('Mostrar chips (Cliente / Año)', 'anima-projects-portfolio'),
            'type'  => Controls_Manager::SWITCHER,
            'label_on' => 'Sí',
            'label_off'=> 'No',
            'return_value' => 'yes',
            'default' => 'yes'
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $slug = !empty($s['post_type']) ? sanitize_key($s['post_type']) : 'projects';
        $cols = !empty($s['columns']) ? max(1, (int)$s['columns']) : 3;
        $max_gallery = isset($s['max_gallery']) ? (int)$s['max_gallery'] : 3;
        $show_excerpt = ($s['show_excerpt'] === 'yes');
        $show_chips   = ($s['chips'] === 'yes');

        $q = new \WP_Query([
            'post_type'      => $slug,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true
        ]);

        if ( ! $q->have_posts() ) { echo '<p>No hay proyectos disponibles.</p>'; return; }

        echo '<div class="anima-portfolio" style="--cols:'. esc_attr($cols) .'">';
        while ( $q->have_posts() ) { $q->the_post();
            $id    = get_the_ID();
            $title = get_the_title();
            $thumb = get_the_post_thumbnail( $id, 'large', ['alt'=>$title] );

            $client = get_post_meta($id, 'client', true);
            $year   = get_post_meta($id, 'year', true);
            $gallery= get_post_meta($id, 'gallery_urls', true);

            $gallery_urls = [];
            if ( ! empty($gallery) ) {
                $lines = preg_split('/\r\n|\r|\n/', trim($gallery));
                foreach ($lines as $line) { $u = trim($line); if ($u) $gallery_urls[] = esc_url($u); }
            }

            echo '<article class="anima-portfolio__item">';
                if ($thumb) echo '<div class="anima-portfolio__cover">'. $thumb .'</div>';
                echo '<h3 class="anima-portfolio__title">'. esc_html($title) .'</h3>';

                if ($show_chips && ($client || $year)) {
                    echo '<div class="anima-portfolio__chips">';
                    if ($client) echo '<span class="chip">'. esc_html($client) .'</span>';
                    if ($year)   echo '<span class="chip">'. esc_html($year) .'</span>';
                    echo '</div>';
                }

                if ($show_excerpt) {
                    // Usa el contenido o extracto y lo acorta
                    $content = get_the_excerpt();
                    if (! $content) {
                        $raw = strip_shortcodes( wp_strip_all_tags( get_post_field('post_content', $id) ) );
                        $content = wp_trim_words($raw, 28, '…');
                    }
                    if ($content) echo '<p class="anima-portfolio__excerpt">'. esc_html($content) .'</p>';
                }

                if ($max_gallery !== 0 && !empty($gallery_urls)) {
                    $slice = $max_gallery > 0 ? array_slice($gallery_urls, 0, $max_gallery) : $gallery_urls;
                    $rest  = max(0, count($gallery_urls) - count($slice));
                    echo '<div class="anima-portfolio__gallery">';
                    foreach ($slice as $g) {
                        echo '<span class="anima-portfolio__thumb"><img src="'. esc_url($g) .'" alt="'. esc_attr($title) .'"/></span>';
                    }
                    if ($rest > 0) {
                        echo '<span class="anima-portfolio__more">+'. intval($rest) .'</span>';
                    }
                    echo '</div>';
                }
            echo '</article>';
        }
        echo '</div>';

        wp_reset_postdata();
    }
}
