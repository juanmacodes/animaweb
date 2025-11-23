<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined('ABSPATH') ) exit;

class Anima_Access_Combined_Widget extends Widget_Base {
    public function get_name() { return 'anima-access-combined'; }
    public function get_title() { return 'Acceso — Login + Registro'; }
    public function get_icon() { return 'eicon-device-mobile'; }
    public function get_categories() { return ['anima']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label' => __('Contenido','anima-auth-widgets')]);
        $this->add_control('columns', [
            'label' => __('Columnas','anima-auth-widgets'),
            'type'  => Controls_Manager::SELECT,
            'default' => '2',
            'options' => ['1'=>'1','2'=>'2']
        ]);
        $this->end_controls_section();
    }

    protected function render(){
        $s = $this->get_settings_for_display();
        $cols = ($s['columns'] === '1') ? 'one' : 'two';
        echo '<div class="anima-access-wrap cols-'.$cols.'">';
        echo do_shortcode('[anima_login_form]');
        echo do_shortcode('[anima_register_form]');
        echo '</div>';
    }
}
