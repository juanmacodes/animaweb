<?php
/**
 * Template Name: Modern Login
 *
 * @package Anima_Avatar_Agency
 */

if (is_user_logged_in()) {
    wp_redirect(home_url('/perfil/'));
    exit;
}

get_header();
?>

<div class="anima-login-wrapper">
    <div class="anima-login-container glass-panel">
        <div class="login-header">
            <h1 class="glitch-text" data-text="ACCESS_GRANTED">SYSTEM_LOGIN</h1>
            <p class="login-subtitle">Enter the Metaverse</p>
        </div>

        <div class="login-form-box">
            <?php
            $args = array(
                'echo' => true,
                'redirect' => home_url('/perfil/'),
                'form_id' => 'anima-login-form',
                'label_username' => __('Username / Email'),
                'label_password' => __('Password'),
                'label_remember' => __('Remember Me'),
                'label_log_in' => __('INITIALIZE LINK'),
                'id_username' => 'user_login',
                'id_password' => 'user_pass',
                'id_remember' => 'rememberme',
                'id_submit' => 'wp-submit',
                'remember' => true,
                'value_username' => '',
                'value_remember' => false
            );
            wp_login_form($args);
            ?>
        </div>

        <div class="login-footer">
            <a href="<?php echo wp_registration_url(); ?>" class="register-link">Create New Identity</a>
            <span class="separator">|</span>
            <a href="<?php echo wp_lostpassword_url(); ?>" class="lost-pass-link">Reset Credentials</a>
        </div>
    </div>

    <!-- Background Particles or Effect -->
    <div class="login-bg-effect"></div>
</div>

<?php get_footer(); ?>