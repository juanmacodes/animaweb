<?php
/**
 * Template Name: Public Profile
 *
 * @package Anima_Avatar_Agency
 */

get_header();

// 1. Obtener el usuario del perfil (por parámetro 'u' o el usuario actual)
$profile_user = null;
if (isset($_GET['u'])) {
    $profile_user = get_user_by('login', sanitize_text_field($_GET['u']));
} elseif (is_user_logged_in()) {
    $profile_user = wp_get_current_user();
}

// Si no hay usuario válido, mostrar error o redirigir
if (!$profile_user) {
    echo '<div class="anima-container" style="padding: 100px 0; text-align: center;"><h2>Agente no encontrado.</h2></div>';
    get_footer();
    exit;
}

$user_id = $profile_user->ID;
$user_xp = get_user_meta($user_id, 'anima_xp', true) ?: 0;
$user_credits = get_user_meta($user_id, 'anima_credits', true) ?: 0;
$user_rank = Anima_Karma_System::get_instance()->get_user_rank($user_id);

// URL para compartir
$profile_url = home_url('/profile/?u=' . $profile_user->user_login);
?>

<div class="anima-public-profile-wrapper">
    <div class="anima-profile-card cyberpunk-border">

        <!-- Header del Card: Avatar y Nombre -->
        <div class="profile-header">
            <div class="profile-avatar-container">
                <?php echo get_avatar($user_id, 150); ?>
                <div class="rank-badge"><?php echo esc_html($user_rank); ?></div>
            </div>
            <h1 class="profile-username glitch-text" data-text="<?php echo esc_attr($profile_user->display_name); ?>">
                <?php echo esc_html($profile_user->display_name); ?>
            </h1>
            <p class="profile-bio">
                <?php echo get_user_meta($user_id, 'description', true) ?: 'Agente encubierto. Sin biografía disponible.'; ?>
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="profile-stats-grid">
            <div class="stat-box">
                <span class="stat-label">XP</span>
                <span class="stat-value text-cyan"><?php echo number_format($user_xp); ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Créditos</span>
                <span class="stat-value text-purple"><?php echo number_format($user_credits); ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Nivel</span>
                <span class="stat-value"><?php echo $user_rank; ?></span>
            </div>
        </div>

        <!-- XP Progress Bar -->
        <?php
        $rank_info = Anima_Karma_System::get_instance()->get_next_rank_info($user_id);
        ?>
        <div class="xp-progress-container">
            <div class="xp-labels">
                <span><?php echo $rank_info['current_rank']; ?></span>
                <span><?php echo $rank_info['next_rank']; ?></span>
            </div>
            <div class="xp-bar-bg">
                <div class="xp-bar-fill" style="width: <?php echo $rank_info['progress_percent']; ?>%;"></div>
            </div>
            <div class="xp-details">
                <?php if ($rank_info['xp_needed'] > 0): ?>
                    Faltan <?php echo number_format($rank_info['xp_needed'] - $user_xp); ?> XP para subir de nivel.
                <?php else: ?>
                    ¡Nivel Máximo Alcanzado!
                <?php endif; ?>
            </div>
        </div>

        <!-- Rewards Shop Link -->
        <div style="margin-bottom: 30px;">
            <a href="<?php echo home_url('/rewards-shop/'); ?>" class="anima-btn btn-primary"
                style="width: 100%; display: block;">
                <span class="dashicons dashicons-cart"></span> Ir a la Tienda de Recompensas
            </a>
        </div>

        <!-- Badges / Logros (Placeholder) -->
        <div class="profile-badges-section">
            <h3 class="section-title">Insignias</h3>
            <div class="badges-grid">
                <div class="badge-item" title="Pionero"><span class="dashicons dashicons-flag"></span></div>
                <div class="badge-item" title="Verificado"><span class="dashicons dashicons-yes-alt"></span></div>
                <div class="badge-item locked" title="Bloqueado"><span class="dashicons dashicons-lock"></span></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="profile-actions">
            <button class="anima-btn btn-outline"
                onclick="navigator.clipboard.writeText('<?php echo esc_js($profile_url); ?>'); alert('Enlace copiado al portapapeles!');">
                <span class="dashicons dashicons-share"></span> Compartir Perfil
            </button>
            <?php if (get_current_user_id() === $user_id): ?>
                <a href="<?php echo home_url('/perfil/'); ?>" class="anima-btn btn-primary">
                    <span class="dashicons dashicons-edit"></span> Editar
                </a>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
    /* Estilos específicos para el Perfil Público */
    .anima-public-profile-wrapper {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-dark);
        background-image: radial-gradient(circle at 50% 50%, rgba(0, 255, 255, 0.05) 0%, transparent 50%);
        padding: 20px;
    }

    .anima-profile-card {
        background: rgba(10, 10, 15, 0.8);
        backdrop-filter: blur(10px);
        border: 1px solid var(--cyan);
        box-shadow: 0 0 20px rgba(0, 255, 255, 0.2);
        padding: 40px;
        border-radius: 15px;
        max-width: 500px;
        width: 100%;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .anima-profile-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--cyan), transparent);
        animation: scanline 3s infinite linear;
    }

    .profile-avatar-container {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .profile-avatar-container img {
        border-radius: 50%;
        border: 3px solid var(--purple);
        box-shadow: 0 0 15px var(--purple);
    }

    .rank-badge {
        position: absolute;
        bottom: 0;
        right: -10px;
        background: var(--gradient-primary);
        color: #fff;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }

    .profile-username {
        font-family: 'Rajdhani', sans-serif;
        font-size: 2.5rem;
        margin: 0;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .profile-bio {
        color: #aaa;
        font-style: italic;
        margin-top: 10px;
    }

    .profile-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin: 30px 0;
        background: rgba(255, 255, 255, 0.03);
        padding: 15px;
        border-radius: 10px;
    }

    .stat-box {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #888;
        text-transform: uppercase;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #fff;
    }

    .text-cyan {
        color: var(--cyan);
        text-shadow: 0 0 5px var(--cyan);
    }

    .text-purple {
        color: var(--purple);
        text-shadow: 0 0 5px var(--purple);
    }

    .profile-badges-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 1rem;
        color: var(--cyan);
        text-transform: uppercase;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(0, 255, 255, 0.2);
        display: inline-block;
        padding-bottom: 5px;
    }

    .badges-grid {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .badge-item {
        width: 40px;
        height: 40px;
        background: rgba(0, 255, 255, 0.1);
        border: 1px solid var(--cyan);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cyan);
        font-size: 20px;
        transition: all 0.3s ease;
    }

    .badge-item:hover {
        transform: scale(1.1);
        box-shadow: 0 0 10px var(--cyan);
        background: var(--cyan);
        color: #000;
    }

    .badge-item.locked {
        border-color: #444;
        color: #444;
        background: rgba(0, 0, 0, 0.2);
    }

    .profile-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    @keyframes scanline {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }
</style>

<?php get_footer(); ?>