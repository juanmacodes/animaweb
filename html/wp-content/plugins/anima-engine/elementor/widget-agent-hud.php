<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Widget_Agent_HUD extends Widget_Base {

    public function get_name() { return 'anima-agent-hud'; }
    public function get_title() { return __( 'Agent HUD (Cabecera)', 'anima-engine' ); }
    public function get_icon() { return 'eicon-user-circle-o'; }
    public function get_categories() { return [ 'anima' ]; }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [ 'label' => __( 'Configuración', 'anima-engine' ) ]
        );
        // Aquí podrías poner opciones para cambiar el mensaje de bienvenida
        $this->add_control(
            'welcome_text',
            [
                'label' => 'Texto Bienvenida',
                'type' => Controls_Manager::TEXT,
                'default' => 'Bienvenido al Nexus de Entrenamiento',
            ]
        );
        $this->end_controls_section();
    }

    protected function render() {
        // 1. Si no está logueado, mostrar botón de acceso
        if ( ! is_user_logged_in() ) {
            echo '<div class="anima-hud-guest">';
            echo '<h2>ACCESO DENEGADO // IDENTIFÍCATE</h2>';
            echo '<p>Debes iniciar sesión para acceder al entrenamiento.</p>';
            echo '<a href="' . wp_login_url( get_permalink() ) . '" class="anima-btn-glitch">INICIAR SESIÓN</a>';
            echo '</div>';
            return;
        }

        // 2. Obtener datos reales del usuario
        $user = wp_get_current_user();
        $stats = function_exists('anima_get_agent_stats') ? anima_get_agent_stats( $user->ID ) : ['level'=>1, 'xp'=>0, 'progress'=>0, 'credits'=>0];
        
        // LÓGICA MEJORADA DE AVATAR PERSONALIZADO
        $custom_avatar_id = get_user_meta( $user->ID, 'profile_picture', true );
        $avatar_url = '';

        if ( $custom_avatar_id ) {
            $img_data = wp_get_attachment_image_src( $custom_avatar_id, 'thumbnail' );
            if ( $img_data ) {
                $avatar_url = $img_data[0];
            }
        }

        // Fallback si no hay imagen personalizada
        if ( empty( $avatar_url ) ) {
            $avatar_url = get_avatar_url( $user->ID, ['size' => 150] );
        }

        ?>
        <div class="anima-agent-hud">
            
            <div class="hud-profile">
                <div class="hud-avatar-wrapper">
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="Avatar" class="hud-avatar">
                    <div class="hud-level-badge">Lvl <?php echo esc_html($stats['level']); ?></div>
                </div>
                <div class="hud-identity">
                    <h3 class="hud-username"><?php echo esc_html($user->display_name); ?></h3>
                    <span class="hud-email"><?php echo esc_html($user->user_email); ?></span>
                </div>
            </div>

            <div class="hud-progress-container">
                <div class="hud-label-row">
                    <span>Sincronización Neural (XP)</span>
                    <span><?php echo $stats['xp_partial']; ?> / <?php echo $stats['xp_needed']; ?></span>
                </div>
                <div class="hud-progress-track">
                    <div class="hud-progress-bar" style="width: <?php echo esc_attr($stats['progress']); ?>%;"></div>
                    <div class="hud-progress-glow" style="left: <?php echo esc_attr($stats['progress']); ?>%;"></div>
                </div>
                <div class="hud-message">
                    <?php echo esc_html($this->get_settings_for_display('welcome_text')); ?>
                </div>
            </div>

            <div class="hud-stats-grid">
                <div class="hud-stat-box">
                    <span class="hud-icon">💳</span>
                    <div class="hud-stat-info">
                        <span class="hud-stat-val"><?php echo esc_html($stats['credits']); ?></span>
                        <span class="hud-stat-label">Créditos</span>
                    </div>
                </div>
                
                <a href="<?php echo esc_url(home_url('/mi-cuenta/')); ?>" class="hud-stat-box link-box">
                    <span class="hud-icon">🏠</span>
                    <div class="hud-stat-info">
                        <span class="hud-stat-label">Dashboard</span>
                    </div>
                </a>
            </div>

        </div>
        <?php
    }
}