<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Widget_Tech_Tracks extends Widget_Base {

    public function get_name() { return 'anima-tech-tracks'; }
    public function get_title() { return __( 'Tech Tracks (Rutas)', 'anima-engine' ); }
    public function get_icon() { return 'eicon-flow'; }
    public function get_categories() { return [ 'anima' ]; }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Configuración de Rutas', 'anima-engine' ) ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'track_title', [
                'label' => 'Título',
                'type' => Controls_Manager::TEXT,
                'default' => 'Ruta Developer',
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'track_desc', [
                'label' => 'Descripción',
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Domina la lógica y los Blueprints.',
            ]
        );

        $repeater->add_control(
            'track_icon', [
                'label' => 'Icono (Clase FA o Emoji)',
                'type' => Controls_Manager::TEXT,
                'default' => 'fas fa-code',
                'description' => 'Ej: "fas fa-cube", "fab fa-unity", o pega un emoji 🚀',
            ]
        );

        $repeater->add_control(
            'track_link', [
                'label' => 'Enlace de destino',
                'type' => Controls_Manager::URL,
                'placeholder' => 'https://...',
            ]
        );

        $this->add_control(
            'tracks',
            [
                'label' => 'Tarjetas de Ruta',
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [ 'track_title' => 'Unreal Engine', 'track_desc' => 'Creación de mundos y entornos 3D.', 'track_icon' => 'fas fa-cube' ],
                    [ 'track_title' => 'Metahumans', 'track_desc' => 'Diseño de avatares hiperrealistas.', 'track_icon' => 'fas fa-user-astronaut' ],
                    [ 'track_title' => 'Interactive', 'track_desc' => 'Programación visual y lógica.', 'track_icon' => 'fas fa-code' ],
                ],
                'title_field' => '{{{ track_title }}}',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( empty( $settings['tracks'] ) ) {
            return;
        }

        echo '<div class="anima-tech-tracks-container">';
        
        foreach ( $settings['tracks'] as $item ) {
            $link_url = !empty($item['track_link']['url']) ? $item['track_link']['url'] : '#';
            $target = !empty($item['track_link']['is_external']) ? '_blank' : '_self';
            $nofollow = !empty($item['track_link']['nofollow']) ? 'nofollow' : '';
            
            // Detectar si es icono de FontAwesome o texto/emoji
            $icon_html = (strpos($item['track_icon'], 'fa') !== false) 
                ? '<i class="' . esc_attr($item['track_icon']) . '"></i>' 
                : '<span class="emoji-icon">' . esc_html($item['track_icon']) . '</span>';

            echo '<a href="' . esc_url($link_url) . '" target="' . esc_attr($target) . '" rel="' . esc_attr($nofollow) . '" class="tech-track-card">';
            
            // Icono
            echo '<div class="track-icon-box">' . $icon_html . '</div>';
            
            // Textos
            echo '<div class="track-info">';
            echo '<h3 class="track-title">' . esc_html($item['track_title']) . '</h3>';
            echo '<p class="track-desc">' . esc_html($item['track_desc']) . '</p>';
            echo '</div>';

            // Flecha decorativa
            echo '<div class="track-arrow">→</div>';
            
            echo '</a>';
        }
        
        echo '</div>';
    }
}