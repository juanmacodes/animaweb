<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Widget_Active_Protocol extends Widget_Base {

    public function get_name() { return 'anima-active-protocol'; }
    public function get_title() { return __( 'Active Protocol (Desafío)', 'anima-engine' ); }
    public function get_icon() { return 'eicon-alert'; }
    public function get_categories() { return [ 'anima' ]; }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Configuración de Misión', 'anima-engine' ) ]
        );

        $this->add_control(
            'mission_type', [
                'label' => 'Tipo de Misión',
                'type' => Controls_Manager::TEXT,
                'default' => 'PROTOCOL_V.09 // WEEKLY CHALLENGE',
            ]
        );

        $this->add_control(
            'title', [
                'label' => 'Título',
                'type' => Controls_Manager::TEXT,
                'default' => 'Operación: Metahuman Genesis',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'description', [
                'label' => 'Descripción',
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Completa el módulo básico de creación de avatares antes de que se cierre el servidor.',
            ]
        );

        $this->add_control(
            'reward_xp', [
                'label' => 'Recompensa (Texto)',
                'type' => Controls_Manager::TEXT,
                'default' => '500 XP + BADGE',
            ]
        );

        $this->add_control(
            'deadline', [
                'label' => 'Fecha Límite (YYYY-MM-DD)',
                'type' => Controls_Manager::DATE_TIME,
                'description' => 'Deja vacío para no mostrar cuenta atrás.',
            ]
        );

        $this->add_control(
            'link', [
                'label' => 'Enlace del Botón',
                'type' => Controls_Manager::URL,
                'placeholder' => 'https://...',
            ]
        );

        $this->add_control(
            'button_text', [
                'label' => 'Texto del Botón',
                'type' => Controls_Manager::TEXT,
                'default' => 'ACEPTAR MISIÓN',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $deadline = $settings['deadline'];
        
        // Calcular ID único para el script de cuenta atrás
        $id = 'mission-' . $this->get_id();

        ?>
        <div class="anima-active-protocol">
            <div class="protocol-border top"></div>
            
            <div class="protocol-content">
                <div class="protocol-header">
                    <span class="mission-badge blink"><?php echo esc_html($settings['mission_type']); ?></span>
                    <?php if ($deadline) : ?>
                        <div class="mission-timer" id="<?php echo esc_attr($id); ?>" data-date="<?php echo esc_attr($deadline); ?>">
                            T-MINUS: <span class="timer-digits">00:00:00</span>
                        </div>
                    <?php endif; ?>
                </div>

                <h2 class="protocol-title"><?php echo esc_html($settings['title']); ?></h2>
                <p class="protocol-desc"><?php echo esc_html($settings['description']); ?></p>

                <div class="protocol-footer">
                    <div class="protocol-reward">
                        <i class="fas fa-trophy"></i> RECOMPENSA: <span><?php echo esc_html($settings['reward_xp']); ?></span>
                    </div>
                    
                    <?php if ( ! empty( $settings['link']['url'] ) ) : ?>
                        <a href="<?php echo esc_url($settings['link']['url']); ?>" class="protocol-btn">
                            <?php echo esc_html($settings['button_text']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="protocol-border bottom"></div>
        </div>

        <?php if ($deadline) : ?>
        <script>
        (function(){
            const timer = document.getElementById('<?php echo esc_js($id); ?>');
            const deadline = new Date(timer.dataset.date).getTime();
            
            const update = setInterval(() => {
                const now = new Date().getTime();
                const diff = deadline - now;
                
                if (diff < 0) {
                    clearInterval(update);
                    timer.innerHTML = "MISSION EXPIRED";
                    return;
                }
                
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                timer.querySelector('.timer-digits').innerText = 
                    (days > 0 ? days + "d " : "") + hours + "h " + minutes + "m";
            }, 1000);
        })();
        </script>
        <?php endif; ?>
        <?php
    }
}