<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined('ABSPATH') ) exit;

class Anima_Register_Widget extends Widget_Base {
    public function get_name() { return 'anima-register'; }
    public function get_title() { return 'Acceso — Registro'; }
    public function get_icon() { return 'eicon-user-circle-o'; }
    public function get_categories() { return ['anima']; }

    protected function register_controls(){ /* no options for now */ }

    protected function render(){
        echo do_shortcode('[anima_register_form]');
    }
}
