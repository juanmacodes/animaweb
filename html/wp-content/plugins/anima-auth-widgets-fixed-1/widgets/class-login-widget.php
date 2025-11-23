<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined('ABSPATH') ) exit;

class Anima_Login_Widget extends Widget_Base {
    public function get_name() { return 'anima-login'; }
    public function get_title() { return 'Acceso — Login'; }
    public function get_icon() { return 'eicon-lock-user'; }
    public function get_categories() { return ['anima']; }

    protected function register_controls(){
        $this->start_controls_section('content', ['label' => __('Contenido','anima-auth-widgets')]);
        $this->add_control('redirect', [
            'label' => __('Redirigir a','anima-auth-widgets'),
            'type'  => Controls_Manager::TEXT,
            'placeholder' => __('(opcional) URL tras login','anima-auth-widgets'),
        ]);
        $this->end_controls_section();
    }

    protected function render(){
        $s = $this->get_settings_for_display();
        if (!empty($s['redirect'])){
            $_REQUEST['redirect_to'] = esc_url_raw($s['redirect']);
        }
        echo do_shortcode('[anima_login_form]');
    }
}
