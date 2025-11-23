<?php
use Elementor\Widget_Base;

if ( ! defined('ABSPATH') ) { exit; }

class Anima_Project_Detail_Widget extends Widget_Base {
    public function get_name() { return 'anima-project-detail'; }
    public function get_title() { return 'Proyecto — Detalle'; }
    public function get_icon() { return 'eicon-post'; }
    public function get_categories() { return ['anima']; }

    protected function render() {
        echo '<p>Usa el grid con popup para mostrar el detalle en modal, o coloca este widget en el single del proyecto.</p>';
    }
}
