<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit;

class Anima_Section_Heading extends Widget_Base {

  public function get_name() {
    return 'anima_section_heading';
  }

  public function get_title() {
    return 'Anima — Título de Sección';
  }

  public function get_icon() {
    return 'eicon-heading';
  }

  public function get_categories() {
    return ['anima-theme'];
  }

  public function _register_controls() {
    $this->start_controls_section(
      'section_content',
      [ 'label' => 'Contenido' ]
    );

    $this->add_control(
      'heading',
      [
        'label' => 'Título',
        'type' => Controls_Manager::TEXT,
        'default' => 'Título de sección',
        'placeholder' => 'Escribe el título...',
      ]
    );

    $this->add_control(
      'description',
      [
        'label' => 'Subtítulo (opcional)',
        'type' => Controls_Manager::TEXT,
        'default' => '',
        'placeholder' => 'Texto secundario',
      ]
    );

    $this->add_control(
      'alignment',
      [
        'label' => 'Alineación',
        'type' => Controls_Manager::CHOOSE,
        'options' => [
          'left' => ['title' => 'Izquierda', 'icon' => 'eicon-text-align-left'],
          'center' => ['title' => 'Centrado', 'icon' => 'eicon-text-align-center'],
          'right' => ['title' => 'Derecha', 'icon' => 'eicon-text-align-right'],
        ],
        'default' => 'center',
        'toggle' => true,
      ]
    );

    $this->end_controls_section();
  }

  protected function render() {
    $settings = $this->get_settings_for_display();
    $align = $settings['alignment'];
    ?>
    <div class="anima-section-heading align-<?= esc_attr($align) ?>">
      <div class="anima-separator-line"></div>
      <h2 class="anima-heading"><?= esc_html($settings['heading']) ?></h2>
      <?php if (!empty($settings['description'])): ?>
        <p class="anima-subtitle"><?= esc_html($settings['description']) ?></p>
      <?php endif; ?>
    </div>
    <?php
  }
}
